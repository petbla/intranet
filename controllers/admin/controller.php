<?php

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
					case 'addfolder':
						if ( $this->registry->getObject('authenticate')->getPermissionSet() > 0 )
						{
							$this->addfolder();
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

	
	private function updateDmsStore()
	{
		global $caption, $config;
	    $this->urlBits = $this->registry->getURLBits();
		$files = $this->registry->getObject('file')->synchroPath($config['fileserver']);
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_updateFinished']);
	}

	private function listUsers()
	{
		$sql = "SELECT u.ID, u.Name, u.PermissionSet, p.Name as Role ".
		       "FROM user u, permissionset p ".
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
			$this->registry->getObject('template')->getPage()->AddTag('PermissionSet','');
		}
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin-users.tpl.php', 'footer.tpl.php');		
	}

	private function newUser()
	{
		$sql = "SELECT * FROM permissionset";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag( 'PermissionSet', array( 'SQL', $cache ) ); 
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin-users-new.tpl.php', 'footer.tpl.php');		
	}

	private function addUser()
	{
		global $caption;
		
		$message = $caption['new_user_failed'];

		if (isset($_POST['usr_name']) && isset($_POST['usr_perset']) && isset($_POST['usr_psw1']) && isset($_POST['usr_psw2']))
		{
			$name = $_POST['usr_name'];
			$name = $this->registry->getObject('db')->sanitizeData($name);
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

	private function addfolder()
	{
		global $caption;
		
		if(! $this->registry->getObject('authenticate')->isAdmin())
		{
			$message = $caption['new_user_failed'];
		}
		else
		{
			$message = $caption['msg_folderNotCreated'];
			if (isset($_POST['fld_name']) && isset($_POST['root']))
			{
				$fullName = $_POST['root'];
				if ($fullName[strlen($fullName)-1] != DIRECTORY_SEPARATOR)
				{
					$fullName .= DIRECTORY_SEPARATOR;
				}
				$fullName .= $_POST['fld_name'];
				$fullName = iconv("utf-8","windows-1250",$fullName);

				if(!file_exists($fullName))
				{
					if(mkdir($fullName, 0777, true))
					{
						// create succes
						$EntryNo = $this->registry->getObject('file')->findItem($fullName);
						
						$message = 'složka založena';
					}
				}
				else
				{
					$message = $caption['msg_folderExists'];
				}
			}
		}

		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
	}

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