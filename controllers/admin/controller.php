<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Admincontroller {

	private $registry;
	private $urlBits;
	private $message;
	private $errorMessage;
	
	public function __construct( Registry $registry, $directCall )
	{
		global $caption;

		$this->registry = $registry;

		// Chek existing permission Sets
		$this->registry->getObject('db')->initQuery('permissionset');
		if ($this->registry->getObject('db')->isEmpty())
		{
			$this->createPermissionSetTable();
		}

		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();    
			$params = $this->registry->getURLParam();

			if( isset( $urlBits[1] ) )
			{		
				switch( $urlBits[1] )
				{				
					case 'update':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->updateDmsStore();
						}
						break;
					case 'users':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->listUsers();
						}
						break;
					case 'user':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{							
							$action = isset($urlBits[2]) ? $urlBits[2] : '';
							$action = isset($_POST["action"]) ? $_POST["action"] : $action;
							switch ($action) {
								case 'add':
									$this->addUser();
									break;
								case 'modify':
									$UserID = isset($_POST["ID"]) ? $_POST["ID"] : '';
									$this->modifyUser($UserID);
									break;								
								case 'delete':
									$UserID = isset($urlBits[3]) ? $urlBits[3] : '';
									$this->deleteUser($UserID);
									break;								
								default:
							}
						}
						break;
					case 'log':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->showLog();
						}
						break;
					case 'listPortal':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->listPortal();
						}
						break;
					case 'setup':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->listSetup();
						}
						break;
					case 'setPortal':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							if( isset( $urlBits[2] ) ){
								$this->setPortal($urlBits[2]);
							}								
						}
						break;
					default:
						$this->error($caption['msg_unauthorized']);		
						break;
				}
			}else{
				$this->build('admin.tpl.php');
			}
		}
	}

    /**
     * Sestavení stránky
     * @return void
     */
	private function build( $template = 'page.tpl.php')
	{
		// Category Menu
		$this->createCategoryMenu();

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

		// Build page
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-admin.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');
	}

    /**
     * Zobrazení chybové stránky, pokud kontakt nebyl nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		$this->error("Stránka nenalezena");
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param string $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		$this->errorMessage = $message;
		$this->build();
	}

    /**
	 * Generování menu
	 * @return void
	 */
	public function createCategoryMenu()
    {
		global $config;
		$urlBits = $this->registry->getURLBits();
		$typeID = isset( $urlBits[1]) ? $urlBits[1] : '';

		$rec['idCat'] = 'setup';
		$rec['titleCat'] = 'Nastavení';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$rec['script'] = '';
		$table[] = $rec;

		$rec['idCat'] = 'users';
		$rec['titleCat'] = 'Uživatelé';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$rec['script'] = '';
		$table[] = $rec;

        $rec['idCat'] = 'update';
		$rec['titleCat'] = 'Synchronizace';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$rec['script'] = "onclick='return ConfirmAction();'";
		$table[] = $rec;
		$rec['script'] = '';

        $rec['idCat'] = 'log';
		$rec['titleCat'] = 'Log';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$rec['script'] = '';
		$table[] = $rec;

        $rec['idCat'] = 'listPortal';
		$rec['titleCat'] = 'Portál';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$rec['script'] = '';
		$table[] = $rec;

		$cache = $this->registry->getObject('db')->cacheData( $table );
		$this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'DATA', $cache ) );
    }

	/**
	 * Akce volaná z webové stránky,
	 * která spustí synchronizaci celé struktury DMS.
	 * @return void
	 */
	private function updateDmsStore()
	{
		global $caption, $config;
	    $this->urlBits = $this->registry->getURLBits();
		$files = $this->registry->getObject('file')->synchroRoot();

		$this->message = $caption['msg_updateFinished'];
		$this->build();
	}

	/**
	 * Zobrazení nastavení
	 * @return void
	 */
	private function listSetup()
	{
		global $config;
		$pref = $config['dbPrefix'];

		$this->registry->getObject('db')->initQuery('setup');
		$this->registry->getObject('db')->findFirst();
		$setup = $this->registry->getObject('db')->getResult();
		$setup['Separator'] = DIRECTORY_SEPARATOR;
		if ($config['synchroFolderonOpen'] === true){
			$setup['synchroFolderonOpen'] = 'ano';
		}else{
			$setup['synchroFolderonOpen'] = 'ne';
		};
		$setup['CurrentUser'] = get_current_user();
		$this->registry->getObject('template')->dataToTags( $setup, 's_' );

		$this->registry->getObject('db')->initQuery('source','*',false);
		$this->registry->getObject('db')->findSet();
		$source = $this->registry->getObject('db')->getResult();
		$cache = $this->registry->getObject('db')->cacheData($source);
		
		$this->registry->getObject('template')->getPage()->addTag( 'portalList', array('DATA', $cache));

		$this->build('admin-setup.tpl.php');		
	}

	/**
	 * Zobrazení seznamu uživatelů
	 * @return void
	 */
	private function listUsers()
	{
		global $config;
		$pref = $config['dbPrefix'];

		// List users
		$sql = "SELECT u.ID, u.Name, u.FullName, u.PermissionSet, p.Name as Role, u.Close ".
		       "FROM ".$pref."user u, ".$pref."permissionset p ".
		       "WHERE u.PermissionSet = p.Level and u.Close = 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache ))
		{
			$this->registry->getObject('template')->getPage()->addTag( 'UserList', array( 'SQL', $cache ) );   
		}

		// Form add user
		$sql = "SELECT * FROM ".$pref."permissionset";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		
		$this->registry->getObject('template')->getPage()->addTag( 'PermissionSet', array( 'SQL', $cache ) ); 
		$this->build('admin-users.tpl.php');
	}

	/**
	 * Zobrazení seznamu portálů jako menu pro výběr
	 * @return void
	 */
	private function listPortal()
	{
		$sql = "SELECT * FROM source";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache ))
		{
			$this->registry->getObject('template')->getPage()->addTag( 'PortalItems', array( 'SQL', $cache ) );   
			$this->build('portal-list.tpl.php');
		}
		else
		{
			$this->pageNotFound();
		}		
	}


	/**
	 * Zobrazení všech položek logu
	 * @return void
	 */
	private function showLog()
	{
		global $config;
        $pref = $config['dbPrefix'];

		$sql = "SELECT * ".
		       "FROM ".$pref."log ".
		       "ORDER BY EntryNo DESC";
		
		// Group records by Page
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		// Save SQL result to $cache (array type) AND modify record
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			   
		if ($this->registry->getObject('db')->isEmpty( $cache )){
			$this->pageNotFound();
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'LogList', array( 'SQL', $cache ) );        						
			$this->build( 'log-list.tpl.php' );
		}
	}

	/**
	 * Akce volaná z webového formuláře jako založení nového uživatele do databáze
	 * @return void
	 */
	private function addUser()
	{
		global $caption;
		
		$this->errorMessage = $caption['new_user_failed'];

		if (isset($_POST['Name']) && isset($_POST['PerSet']) && isset($_POST['Psw1']) && isset($_POST['Psw2']))
		{
			$name = $_POST['Name'];
			$fullname = $_POST['FullName'];
			$name = $this->registry->getObject('db')->sanitizeData($name);
			$fullname = $this->registry->getObject('db')->sanitizeData($fullname);
			$perset = $_POST['PerSet'];
			$psw = ($_POST['Psw1'] === $_POST['Psw2']) ? $_POST['Psw1'] : '';
			if ($psw != '')
			{
				$psw = md5($psw);
				
				// Check if not exists users yet
				$this->registry->getObject('db')->initQuery('user');
				$isFirst = $this->registry->getObject('db')->isEmpty();

				$this->registry->getObject('db')->initQuery('user');
				$this->registry->getObject('db')->setFilter('Name',$name);
				if ($this->registry->getObject('db')->isEmpty()){

					// Insert to Databáze
					$data['ID'] = $this->registry->getObject('fce')->GUID();
					$data['Name'] = $name;
					$data['FullName'] = $fullname;
					$data['Password'] = $psw;
					$data['PermissionSet'] = $isFirst ? 9 : $perset;
					if ($this->registry->getObject('db')->insertRecords('user',$data))
					{
						$this->message = $caption['new_user_created'];
					}
				}else{
					$this->errorMessage = "Uživatel $name již existuje";
				}
			}
		}
		$this->listUsers();
	}

	/**
	 * Editace uživatele
	 * @return void
	 */
	private function modifyUser( $ID )
	{
		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Login musí být vyplněn!');
			return;
		};
		$fullname = isset($_POST['FullName']) ? $_POST['FullName'] : '';
		if ($Name == ''){
			$this->error('Jnémo uživatele  musí být vyplněno!');
			return;
		};
		if ($_POST['Psw1'] === $_POST['Psw2']){
			$psw = ($_POST['Psw1'] === $_POST['Psw2']) ? $_POST['Psw1'] : '';
		}else{
			$this->error('Kontrola hesla neodpovídá zadanému heslu.!');
			return;
		}

		// Update
		$changes = array();
		$changes['Name'] = $Name;
		$changes['FullName'] = $fullname;
		$changes['Password'] = $psw;
		$changes['PermissionSet'] =  $_POST['PerSet'];
		$condition = "ID = '$ID'";
		$this->registry->getObject('db')->updateRecords('user',$changes, $condition);
		$this->listUsers();
	}

	/**
	 * Akce volaná z webové stránky (např. jako OnClick)
	 * pro odstranění (=deaktivace) uživatele.
	 * Po provedneí akce se zobrazí seznam uživatelů
	 * @param string $ID = ID uživatele
	 * @return void
	 */
	private function deleteUser( $ID )
	{		
		global $config, $caption;
        $pref = $config['dbPrefix'];

		if ($ID == "")
			return;

		$UserID = $this->registry->getObject('authenticate')->getUserID();
		$userName = $this->registry->getObject('authenticate')->getUserName();

		switch (true) {
			case ($UserID == $ID):
				$this->message = 'Nelze odstranit aktuálně přihlášeného uživatele.';		
				break;
			case $this->isUserUsed($ID):
				$this->message = 'Uživatel se již přihlásil, nelze jej odstranit.';
				break;
			default:
				$condition = "ID = '$ID'";
				$data['Close'] = 1;
				$this->registry->getObject('log')->addMessage("Uzavření uživatele ID = $ID",'user',$ID);
				$this->registry->getObject('db')->updateRecords('user',$data,$condition);			
		}
		$this->listUsers();
	}

	/**
	 * Kontrola, zde byl již použit LOGIN uživatele
	 * @param	$UserID
	 * @return	$isUsed - false/true
	 */
	function isUserUsed( $UserID )
	{
		$this->registry->getObject('db')->initQuery('log');
		$this->registry->getObject('db')->setFilter('UserID',$UserID);
		return ($this->registry->getObject('db')->findFirst());
	}

	/**
	 * Interní funkce pro generování tabulky úrovní oprávnění
	 * @return void
	 */
	private function createPermissionSetTable()
	{
		$data = array('Level'=>'0','Name'=>'veřejnost');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
		$data = array('Level'=>'1','Name'=>'zaměstnanec');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
		$data = array('Level'=>'2','Name'=>'člen výboru');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
		$data = array('Level'=>'3','Name'=>'zastupitel');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
		$data = array('Level'=>'4','Name'=>'radní');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
		$data = array('Level'=>'5','Name'=>'starosta');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
		$data = array('Level'=>'9','Name'=>'administrátor');
		$this->registry->getObject('db')->insertRecords('permissionset',$data);
	}

	/**
	 * Nastavení portálu dle výběru
	 * @param mixed $EntryNo = číslo pložky portálu
	 * @return void
	 */
	private function setPortal( $EntryNo )
	{
		$this->registry->getObject('upgrade')->setPortal( $EntryNo );
		$this->listPortal();
	}
}
?>