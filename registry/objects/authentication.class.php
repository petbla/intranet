<?php
/**
 * Správce autentizace
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    26.12.2018
 */

class authentication {

	private $userID = '';
	private $loggedIn = false;
	private $admin = false;
	
  private $name = '';
  private $permissionSet = 0;
	private $justProcessed = false;
	private $loginFailureReason = '';
	
  public function __construct( $registry ) 
  {
		$this->registry = $registry;
  }
  
  private function sessionAuthenticate( $uid )
  {
  	global $caption;
  	
  	$sql = "SELECT ID, Name, PermissionSet FROM user WHERE ID='$uid'";
  	$username = '';
    $this->registry->getObject('db')->executeQuery( $sql );
  	if( $this->registry->getObject('db')->numRows() == 1 )
  	{
  		$userData = $this->registry->getObject('db')->getRows();
      $this->loggedIn = true;
      $this->userID = $uid;
      $this->permissionSet = $userData['PermissionSet'];
      $this->admin = ( $userData['PermissionSet'] == 9 ) ? true : false;
      $this->name = $userData['Name'];
      $username = $this->name;
      return true;
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
    return false;
  }
  

  public function checkForAuthentication()
  {
  	global $caption, $deb;
  	
    $sql = "SELECT * FROM intranet.user";
    $this->registry->getObject('db')->executeQuery( $sql );
  	if( $this->registry->getObject('db')->numRows() == 0 )
  	{
      $this->loggedIn = false;
      $this->userID = '';
      $this->permissionSet = 9;
      $this->admin = ($this->permissionSet == 9) ? true : false;
      $this->name = 'Sytem Administrator';
      $username = $this->name;
      return true;
    }
    else
    {
      if( isset( $_SESSION['int_auth_session_uid'] ) && intval( $_SESSION['int_auth_session_uid'] ) > 0 )
      {
        return ($this->sessionAuthenticate( $_SESSION['int_auth_session_uid'] ));
      }
      elseif( isset(  $_POST['log_auth_user'] ) &&  $_POST['log_auth_user'] != '' && 
              isset( $_POST['log_auth_pass'] ) && $_POST['log_auth_pass'] != '' && 
              isset(  $_POST['login'] ))
      {
        $name = $_POST['log_auth_user'];
        $psw = $_POST['log_auth_pass'];
        $this->registry->getObject('db')->initQuery('user');
        $this->registry->getObject('db')->setFilter('Name',$name);
        if ($this->registry->getObject('db')->findFirst())
        {
          $data = $this->registry->getObject('db')->getResult();
          $psw = md5($psw);
          if ($psw == $data['Password'])
          {
            // Login succest
            $this->loggedIn = true;
            $this->userID = $data['ID'];
            $this->permissionSet = $data['PermissionSet'];
            $this->admin = ($this->permissionSet == 9) ? true : false;
            $this->name = $data['Name'];
            $username = $this->name;
            $_SESSION['int_auth_session_uid'] = $data['ID']; 
            return true;
          }
        }
      }
    }
    return false;
 }

  public function logout() 
	{
    // Logout succest
    $this->loggedIn = false;
    $this->userID = '';
    $this->permissionSet = 0;
    $this->admin = false;
    $this->name = '';
    $username = $this->name;
		$_SESSION['int_auth_session_uid'] = '';
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
  	return $this->name;
  }
  
  public function getPermissionSet()
  {
  	return $this->permissionSet;
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