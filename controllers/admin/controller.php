<?php

class Admincontroller {

	private $registry;
	private $urlBits;
	
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;

		if ( !$this->registry->getObject('authenticate')->isAdmin())
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin-error404.tpl.php', 'footer.tpl.php');	
			return;
		}

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
						$this->updateDmsStore();
						return;
					case 'users':
						$this->listUsers();
						return;
					case 'newuser':
						$this->newUser();
						return;
					case 'adduser':
						$this->addUser();
						return;
				}
			}
		}
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin.tpl.php', 'footer.tpl.php');
	}

	
	private function updateDmsStore()
	{
		global $caption, $config;
	    $this->urlBits = $this->registry->getURLBits();
		$files = $this->registry->getObject('file')->updateFiles($config['fileserver']);
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_updateFinished']);
	}

	private function listUsers()
	{
		$sql = "SELECT ID, Name, PermissionSet FROM user";
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