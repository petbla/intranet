<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Admincontroller {

	private $registry;
	private $urlBits;
	
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
					case 'newuser':
						if ( $this->registry->getObject('authenticate')->isAdmin())
						{
							$this->newUser();
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
				}
			}
			else
			{
				if ( $this->registry->getObject('authenticate')->isAdmin())
				{
					$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin.tpl.php', 'footer.tpl.php');
					return;
				}
			}
		}
		$message = $caption['msg_unauthorized'];
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
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
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_updateFinished']);
	}

	/**
	 * Zobrazení seznamu uživatelů
	 * @return void
	 */
	private function listUsers()
	{
		global $config;
        $pref = $config['dbPrefix'];

		$sql = "SELECT u.ID, u.Name, u.FullName, u.PermissionSet, p.Name as Role, u.Close ".
		       "FROM ".$pref."user u, ".$pref."permissionset p ".
		       "WHERE u.PermissionSet = p.Level";
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
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin-users.tpl.php', 'footer.tpl.php');		
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
		// Zobrazení seznamu
		$this->listResult($sql, '', 'LogList', 'log-list.tpl.php');		
	}

	/**
	 * Zobrazení požadovaního seznamu které současně 
	 * zajistí zobrazení stránkování s možností výběru stránek a listování v nich
	 * @param String $sql = sestavený kompletní SQL dotaz
	 * @param String $pageLink
	 * @param String $SQLDataElement
	 * @param String $template
	 * @return void
	 */
	private function listResult( $sql, $pageLink , $SQLDataElement, $template )
	{
		global $config;

		// Stránkování
		$cacheFull = $this->registry->getObject('db')->cacheQuery( $sql );
		$records = $this->registry->getObject('db')->numRowsFromCache( $cacheFull );
		$pageCount = (int) ($records / $config['maxVisibleItem']);
		$pageCount = ($records > $pageCount * $config['maxVisibleItem']) ? $pageCount + 1 : $pageCount;  
		$pageNo = ( isset($_GET['p'])) ? $_GET['p'] : 1;
		$pageNo = ($pageNo > $pageCount) ? $pageCount : $pageNo;
		$pageNo = ($pageNo < 1) ? 1 : $pageNo;
		$fromItem = (($pageNo - 1) * $config['maxVisibleItem']);    
		$navigate = $this->registry->getObject('template')->NavigateElement( $pageNo, $pageCount ); 
		$this->registry->getObject('template')->getPage()->addTag( 'navigate_menu', $navigate );
		$sql .= " LIMIT $fromItem," . $config['maxVisibleItem']; 
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache )){
			$this->registry->getObject('template')->getPage()->addTag( $SQLDataElement, array( 'SQL', $cache ) );
			$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page-notfound.tpl.php', 'footer.tpl.php');			
		}
    }	

	/**
	 * Zobrazení stránky s webovým formulářem pro založení nového uživatele
	 * @return void
	 */
	private function newUser()
	{
		global $config;
		$pref = $config['dbPrefix'];
		
		$sql = "SELECT * FROM ".$pref."permissionset";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag( 'PermissionSet', array( 'SQL', $cache ) ); 
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin-users-new.tpl.php', 'footer.tpl.php');		
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
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
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

		$UserID = $this->registry->getObject('authenticate')->getUserID();
		$userName = $this->registry->getObject('authenticate')->getUserName();
		if ($UserID == $ID)
		{
			$message = 'Nelze odstranit Sám Sebe - aktuálně přihlášeného uživatele.';
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('message',$message);
			}
		else
		{
			$condition = "ID = '$ID'";
			$data['Close'] = 1;
			$this->registry->getObject('log')->addMessage("Uzavření uživatele",'user',$ID);
			$this->registry->getObject('db')->updateRecords('user',$data,$condition);			
			$this->listUsers();
		}
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
}
?>