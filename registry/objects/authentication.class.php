<?php
/**
 * Správce autentizace
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    7.7.2011 
 */

class authentication {

	private $userID = 0;
	private $loggedIn = false;
	private $admin = false;
	
	private $groups = array();
	
	private $banned = false;
	private $username;
	private $justProcessed = false;
	private $loginFailureReason = '';
	
  public function __construct( $registry ) 
  {
		$this->registry = $registry;
	
  }
  
  private function sessionAuthenticate( $uid )
  {
  	global $caption;
  	
  	$sql = "SELECT u.ID, u.username, u.active, u.email, u.admin, u.banned, u.name, (SELECT GROUP_CONCAT( g.name SEPARATOR '-groupsep-' ) FROM groups g, group_memberships gm WHERE g.ID = gm.group AND gm.user = u.ID ) AS groupmemberships FROM users u WHERE u.ID={$uid}";
  	$username = '';
    $this->registry->getObject('db')->executeQuery( $sql );
  	if( $this->registry->getObject('db')->numRows() == 1 )
  	{
  		$userData = $this->registry->getObject('db')->getRows();
  		$username = $userData['username']; 
      if( $userData['active'] == 0 )
  		{
  			$this->loggedIn = false;
  			$this->loginFailureReason = $caption['auth_errorlogin_inactive'];
  			$this->active = false;
  		}
  		elseif( $userData['banned'] != 0)
  		{
  			$this->loggedIn = false;
  			$this->loginFailureReason = $caption['auth_errorlogin_banned'];
  			$this->banned = false;
  		}
  		else
  		{ 			
        $this->loggedIn = true;
  			$this->userID = $uid;
  			$this->admin = ( $userData['admin'] == 1 ) ? true : false;
  			$this->username = $userData['username'];
  			$this->name = $userData['name'];
  			$groups = explode( '-groupsep-', $userData['groupmemberships'] );
  			$this->groups = $groups;
  		}
  	}
  	else
  	{
  		$this->loggedIn = false;
  		$this->loginFailureReason = $caption['auth_errorlogin_nouser'];
  	}
  	
  	if($this->loginFailureReason != '') 		  	
      $this->registry->getObject('log')->logMsg("Chyba přihlášení uživatele ID=$uid, name=$username",$this->loginFailureReason);

    if( $this->loggedIn == false )
  	{
  		$this->logout();
  	}
  }
  
  private function postAuthenticate( $u, $p )
  {
  	global $caption, $deb;
    
    $this->justProcessed = true;
  	$sql = "SELECT u.ID, u.username, u.email, u.admin, u.banned, u.active, u.name, 
                   (SELECT GROUP_CONCAT( g.name SEPARATOR '-groupsep-' ) 
                      FROM groups g, group_memberships gm 
                      WHERE g.ID = gm.group AND gm.user = u.ID ) AS groupmemberships 
              FROM users u 
              WHERE u.username='{$u}' AND u.password_hash='{$p}'";

  	$this->registry->getObject('db')->executeQuery( $sql );
  	$uid = 0;
  	$username = $u;
  	
    if( $this->registry->getObject('db')->numRows() == 1 )
  	{
      $userData = $this->registry->getObject('db')->getRows();
    	$uid = $userData['ID'];
    	$username = $userData['username'];
    	
  		if( $userData['active'] == 0 )
  		{
  			$this->loggedIn = false;
  			$this->loginFailureReason = $caption['auth_errorlogin_inactive'];
  			$this->active = false;
  		}
  		elseif( $userData['banned'] != 0)
  		{
  			$this->loggedIn = false;
  			$this->loginFailureReason = $caption['auth_errorlogin_banned'];
  			$this->banned = false;
  		}
  		else
  		{
  			$this->loggedIn = true;
  			$this->userID = $userData['ID'];
  		  $username  = $userData['username'];
        $this->admin = ( $userData['admin'] == 1 ) ? true : false;
  			$_SESSION['bjx_auth_session_uid'] = $userData['ID'];
  			
  			$groups = explode( '-groupsep-', $userData['groupmemberships'] );
  			$this->groups = $groups;
  			$this->loginFailureReason = '';
  			
        if( $this->isAdmin() )
          $this->registry->getObject('log')->logMsg("Přihlášení ADMINSTRÁTORA $username");
        else  
          $this->registry->getObject('log')->logMsg("Přihlášení uživatele $username");
  		}
  	}
  	else
  	{
  		$this->loggedIn = false;
  		$this->loginFailureReason = $caption['auth_errorlogin_credent'];
  	}
    if($this->loginFailureReason != '') 		  	
      $this->registry->getObject('log')->logMsg("Chyba přihlášení uživatele $username",$this->loginFailureReason);
  }

	private function newRegistration()
	{
    global $caption;
    
		$username = $this->registry->getObject('db')->sanitizeData( $_POST['bjx_auth_user'] );  
		
    $sql = "SELECT * FROM users WHERE username='$username'";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 ){
      $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['auth_new_registration'] );
   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_registrationMsg'] );
    }else{
			$psw = $_POST['bjx_auth_pass'];  
   		$psw2 = $_POST['bjx_auth_pass2'];  
	  	$text = $_POST['bjx_auth_text'];  
	  	$email = $_POST['bjx_auth_email'];  
	  	
      if ( $psw == $psw2 ){
        if ( isset($_SESSION['confirmPicture']) ){
          $textConfirm = $_SESSION['confirmPicture'];
          if ($text == $textConfirm){
            
            // New Login
            $user = array();
            $user['username'] = $username;
            $user['password_hash'] = md5($psw);
            $user['email'] = $email;
            $user['active'] = 1;
            $user['admin'] = 0;
            $user['anonymous'] = 0;
            $user['banned'] = 0;
            $user['name'] = '';
            $user['send_news'] = 0;
            $user['news_hash'] = $this->registry->getObject('fce')->randomString( 60 );;
            
      			$this->registry->getObject('db')->insertRecords( 'users', $user );
            
            $uid = $this->registry->getObject('db')->lastInsertID();         			
            $extra['userID'] = $uid;
            $extra['default_shipping_email'] = $email;
            $this->registry->getObject('db')->insertRecords( 'users_extra', $extra );
            
        
          }else{
  	        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['auth_new_registration'] );
    	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_conftext'] );
          }
        }else{
	        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['auth_new_registration'] );
  	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_conftext'] );
        }
      }else{
        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['auth_new_registration'] );
	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_repeatPsw'] );
      }
    }
  }

	private function sendPassword()
	{
    global $config, $caption, $deb;
    
		$username = $this->registry->getObject('db')->sanitizeData( $_POST['bjx_auth_user'] );  
		
    $sql = "SELECT * FROM users WHERE anonymous=0 AND banned=0 AND active=1 AND username='$username'";

		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 ){
      $data = $this->registry->getObject('db')->getRows();
	  	$text = $_POST['bjx_auth_text'];  
	  	
      if ( isset($_SESSION['confirmPicture']) ){
        $textConfirm = $_SESSION['confirmPicture'];
        if ($text == $textConfirm){
            
    	  	$email = $data['email'];
          $this->registry->getObject('log')->setUserID( $data['ID'] );
          $data['newPws'] = $this->registry->getObject('fce')->randomString(10);
          
          // Send Email for Confirm SingOff
          $this->registry->getObject('email')->init( 'template/email_user.tpl.php','changePsw');
          $this->registry->getObject('email')->from = $config['infoEmail'];
          $this->registry->getObject('email')->to = $email;
          $this->registry->getObject('email')->addFields($config,'cfg_');
          $this->registry->getObject('email')->addFields($data);
          $this->registry->getObject('email')->setSubject('subject_changePsw');
          $this->registry->getObject('email')->send();
          
          if ( $this->registry->getObject('email')->result() ){
  					$changes = array();
  					$changes['password_hash'] = md5( trim($data['newPws']) );
  					// aktualizuj současné heslo na nové heslo
  					$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' . $data['ID'] );
  	        
            $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['sendForgetPsw'] );
    	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['passwordSent'] );
          }else{
  	        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['sendForgetPsw'] );
    	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_changePsw'] );
          }
              
          
        }else{
	        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['sendForgetPsw'] );
  	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_conftext'] );
        }
      }else{
        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['sendForgetPsw'] );
	   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_conftext'] );
      }
    }else{
      $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['sendForgetPsw'] );
   	  $this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['auth_error_noValidLogin'] );
    }
  }

  public function checkForAuthentication()
  {
  	global $caption, $deb;
  	
    if( isset( $_SESSION['bjx_auth_session_uid'] ) && intval( $_SESSION['bjx_auth_session_uid'] ) > 0 )
  	{
      $this->sessionAuthenticate( intval( $_SESSION['bjx_auth_session_uid'] ) );
  	}
  	elseif( isset(  $_POST['bjx_auth_user'] ) &&  $_POST['bjx_auth_user'] != '' && isset( $_POST['bjx_auth_pass'] ) && $_POST['bjx_auth_pass'] != '' && isset(  $_POST['login'] ))
  	{
      if( isset( $_POST['bjx_auth_pass2'] ) && $_POST['bjx_auth_pass2'] != '' && isset( $_POST['bjx_auth_text'] ) && $_POST['bjx_auth_text'] != '')
        $this->newRegistration();
      $this->postAuthenticate( $this->registry->getObject('db')->sanitizeData( $_POST['bjx_auth_user'] ), md5( $_POST['bjx_auth_pass'] ) );
  	}
  	elseif( isset(  $_POST['bjx_auth_user'] ) &&  $_POST['bjx_auth_user'] != '' && isset(  $_POST['login'] ) && trim($_POST['login']) == $caption['sendpsw'] )
  	{
      // Send Password      
      $this->sendPassword();
    }
  }

  public function setSendNews( $email , $sing=0)
  {
    $sql = "SELECT * FROM users WHERE email='$email'";
    $this->registry->getObject('db')->executeQuery( $sql );
    if( $this->registry->getObject('db')->numRows() == 1 ){
      $data = $this->registry->getObject('db')->getRows();
      $users = array(); 
      $users['send_news'] = $sing;     
      $this->registry->getObject('db')->updateRecords( 'users', $users, 'ID=' . $data['ID']);
    }else{
      $users = array(); 
      $users['news_hash'] = $this->registry->getObject('fce')->randomString( 60 );
      $users['email'] = $email;
      $users['active'] = $sing;
      $users['admin'] = 0;
      $users['anonymous'] = 1;
      $users['send_news'] = $sing;
			$this->registry->getObject('db')->insertRecords( 'users', $users );
			
      $extra = array();
			$extra['userID'] = $this->registry->getObject('db')->lastInsertID();       
			$extra['default_shipping_email'] = $email;
      $this->registry->getObject('db')->insertRecords( 'users_extra', $extra );
    }
  } 

  public function logout() 
	{
		$_SESSION['bjx_auth_session_uid'] = '';
	}

  public function getLoginFailureReason()
  {
    return $this->loginFailureReason;
  }
  
  public function getUserID()
  {
    return $this->userID;
  }
  
  public function isLoggedIn()
  {
    return $this->loggedIn;
  }
  
  public function isAdmin()
  {
  	return $this->admin;
  }
  
  public function getUsername()
  {
  	return $this->username;
  }
  
  public  function isMemberOfGroup( $group )
  {
    if( in_array( $group, $this->groups ) )
    {
	    return true;
    }
    else
    {
	    return false;
    }
  }
  
  public function justProcessed()
  {
    return $this->justProcessed;
  }

	public function randomString( $length=8 )
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	    $string = '';    
	    for ($i = 0; $i < $length; $i++ ) 
	    {
	        $string .= $characters[mt_rand(0, strlen($characters))];
	    }
	    return $string;
	}

}
?>