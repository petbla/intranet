<?php
/**
 * Správce logování aktivit
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    19.1.2019
 */
class login
{
  private $log;
  private $prefDb = '';
    
  /*
   *  Class constructor 
   *     
   */
  public function __construct( $registry )
  { 
    global $config;
    $this->registry = $registry;
    $this->prefDb = $config['dbPrefix'];
  }
                                                    
  public function addMessage( $message, $table = '', $ID = '' )
  {
    if($table !== '')
    {
      $table = $this->prefDb.$table;
    }
    $this->init();
    $this->log['Description'] = $this->registry->getObject('db')->sanitizeData( $message );
    $this->log['Table'] = $table;
    $this->log['ID'] = $ID;
    $this->write();
  }
  
  private function init( )
  {
    $log = array();
    $log['IP'] = $_SERVER['REMOTE_ADDR'];
    if($this->registry->getObject('authenticate')->isLoggedIn())
    {
      $log['UserID'] = $this->registry->getObject('authenticate')->getUserID();    
      $log['UserName'] = $this->registry->getObject('authenticate')->getUserName();    
    }
    else
    {
      $log['UserID'] = 'anonymous';    
      $log['UserName'] = 'Session: '.session_id();
    }
    $this->log = $log;
  }

  private function write( )
  {
    $this->registry->getObject('db')->insertRecords( 'log', $this->log );    
  }
}
?>
