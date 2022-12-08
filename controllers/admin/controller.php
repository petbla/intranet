<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Admincontroller {

	private $registry;
	private $urlBits;
	private $pageMessage;
	
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
			$ID = '';
			if ($params !== '')
			{
				$params = explode('&',$params);
				if (isset($params[1]))
				{
					$arr = explode('=',$params[1]);
					if($arr[0] == 'ID')
					{
						$ID = $arr[1];
					}
				}
			}

			if( isset( $urlBits[1] ) )
			{		
				switch( $urlBits[1] )
				{				
					case 'update':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->updateDmsStore();
							return;
						}
					case 'users':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->listUsers();
							return;
						}
					case 'adduser':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->addUser();
							return;
						}
					case 'deleteuser':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->deleteUser($ID);
							return;
						}
					case 'modifyuser':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->modifyUser($ID);
							return;
						}
					case 'log':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->showLog();
							return;
						}
					case 'listPortal':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->listPortal();
							return;
						}
					case 'setup':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->listSetup();
							return;
						}
					case 'setPortal':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							if( isset( $urlBits[2] ) ){
								$this->setPortal($urlBits[2]);
							}								
							return;
						}
				}
			}
			else
			{
				if ( $this->registry->getObject('authenticate')->isAdmin())
				{
					$this->registry->getObject('template')->getPage()->addTag('message','Administrace');
					$this->build();		
					return;
				}
			}
		}
		$message = $caption['msg_unauthorized'];
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		$this->build();		
	}

    /**
     * Sestavení stránky
     * @return void
     */
	private function build( $template = 'page.tpl.php')
	{
		// Category Menu
		$this->createCategoryMenu();

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
		$this->build('invalid-contact.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
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
		$table[] = $rec;

		$rec['idCat'] = 'users';
		$rec['titleCat'] = 'Uživatelé';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;

        $rec['idCat'] = 'update';
		$rec['titleCat'] = 'Synchronizace';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;

        $rec['idCat'] = 'log';
		$rec['titleCat'] = 'Log';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;

        $rec['idCat'] = 'listPortal';
		$rec['titleCat'] = 'Portál';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
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

		$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_updateFinished']);
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
		$this->registry->getObject('template')->dataToTags( $setup, 's_' );
		

		$this->build('admin.tpl.php');		
	}

	/**
	 * Zobrazení seznamu uživatelů
	 * @return void
	 */
	private function listUsers()
	{
		global $config;
		$pref = $config['dbPrefix'];

		// Message
		$this->registry->getObject('template')->getPage()->AddTag('message',$this->pageMessage);

		// List users
		$sql = "SELECT u.ID, u.Name, u.FullName, u.PermissionSet, p.Name as Role, u.Close ".
		       "FROM ".$pref."user u, ".$pref."permissionset p ".
		       "WHERE u.PermissionSet = p.Level and u.Close = 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache ))
		{
			$this->registry->getObject('template')->getPage()->addTag( 'UserList', array( 'SQL', $cache ) );   
		}
		else
		{
			$this->registry->getObject('template')->getPage()->AddTag('ID','');
			$this->registry->getObject('template')->getPage()->AddTag('Name','');
			$this->registry->getObject('template')->getPage()->AddTag('FullName','');
			$this->registry->getObject('template')->getPage()->AddTag('PermissionSet','');
		}

		// Form add user
		$sql = "SELECT * FROM ".$pref."permissionset";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		
		$this->registry->getObject('template')->getPage()->addTag( 'PermissionSet', array( 'SQL', $cache ) ); 
		$this->build('admin-users.tpl.php');
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
		
		$message = $caption['new_user_failed'];

		if (isset($_POST['usr_name']) && isset($_POST['usr_perset']) && isset($_POST['usr_psw1']) && isset($_POST['usr_psw2']))
		{
			$name = $_POST['usr_name'];
			$fullname = $_POST['usr_fullname'];
			$name = $this->registry->getObject('db')->sanitizeData($name);
			$fullname = $this->registry->getObject('db')->sanitizeData($fullname);
			$perset = $_POST['usr_perset'];
			$psw = ($_POST['usr_psw1'] === $_POST['usr_psw2']) ? $_POST['usr_psw1'] : '';
			if ($psw != '')
			{
				$psw = md5($psw);
				
				// Check if not exists users yet
				$isFirst = false;
				$this->registry->getObject('db')->initQuery('user');
				if ($this->registry->getObject('db')->isEmpty()){
					$isFirst = true;
				}

				// Insert to Databáze
				$data['ID'] = $this->registry->getObject('fce')->GUID();
				$data['Name'] = $name;
				$data['FullName'] = $fullname;
				$data['Password'] = $psw;
				$data['PermissionSet'] = $isFirst ? 9 : $perset;
				if ($this->registry->getObject('db')->insertRecords('user',$data))
				{
					$message = $caption['new_user_created'];
				}

			}
		}
		$this->listUsers();
	}

	/**
	 * Akce volaná z webové stránky (např. jako OnClick)
	 * pro odstranění (=deaktivace) uživatele.
	 * Po provedneí akce se zobrazí seznam uživatelů
	 * @param String $ID = ID uživatele
	 * @return void
	 */
	private function deleteUser( $ID )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
		$this->pageMessage = '';

		$UserID = $this->registry->getObject('authenticate')->getUserID();
		$userName = $this->registry->getObject('authenticate')->getUserName();

		switch (true) {
			case ($UserID == $ID):
				$this->pageMessage = 'Nelze odstranit aktuálně přihlášeného uživatele.';		
				break;
			case $this->isUserUsed($ID):
				$this->pageMessage = 'Uživatel se již přihlásil, nelze jej odstranit.';
				break;
		};
	
		
		if ($this->pageMessage == "")
		{
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
	 * Nastavení portálu dle výběru
	 * @param Integer $EntryNo = číslo pložky portálu
	 * @return void
	 */
	private function setPortal( $EntryNo )
	{
		$this->registry->getObject('db')->setPortal( $EntryNo );
		$this->listPortal();
	}
}
?>