<?php
/**
 * Logovani 
 * 
 * @author  Petr Blazek
 * @version 2.1
 * @date    15.8.2021 
 */
class debug
{ /* Trace :
  $levelDebug = 0 ... no trace
                1 ... trace INFO
                4 ... trace INFO,TRACE
                7 ... trace INFO,TRACE,ERROR
	*/ 
	var $file;
	var $fileName;
	var $levelText = '';
	var $levelDebug = 0;
	var $levelNo = 0;
	var $help = "Value for setup debug: info,trace,error";
	var $sIP;
	var $sSession;
  var $isActive = false;

	// constructor
	function __construct($lt = '', $logFile = "logFile.txt") {
    $this->fileName = $logFile;
    $this->levelDebug = $lt;
    $this->convertLevel($lt);       
	}

	public function __tostring() {
    return ("<p>".$this->help."</p>");
  }
    
  public function info( $debugText) {
    if (!$this->isActive)
      exit;
    // level 1 = INFO      
    if ($this->levelNo >= 1) {      
  		if (is_array($debugText)){
        $this->writeText("INFO(".$this->getID().")". $this->print_var_name('debugText') ."\n");
        $this->writeText("  --- Obsah pole: ------------------------------\n");
        foreach ($debugText as $key=>$value){
          $this->writeText("  > ".$key.' = '.$value."\n");
        }
        $this->writeText("  ----------------------------------------------\n");
      } else
        $this->writeText("INFO(".$this->getID()."): ".$debugText."\n");      
    }	
	} 
	
	public function trace( $debugText) { 
    if (!$this->isActive)
      exit;
    // level 4 = TRACE 
		if ($this->levelNo >= 4) {
  		if (is_array($debugText)){
        $this->writeText("TRACE(".$this->getID().")". $this->print_var_name($debugText) ."\n");
        $this->writeText("  --- Obsah pole: ------------------------------\n");
        foreach ($debugText as $key=>$value){
          $this->writeText("  > ".$key.' = '.$value."\n");
        }
        $this->writeText("  ----------------------------------------------\n");
      } else
        $this->writeText("TRACE(".$this->getID()."): ".$debugText."\n");
    }	
	} 
	
  public function error( $debugText) { 
    if (!$this->isActive)
      exit;
    // level 7 = ERROR 
		if ($this->levelNo >= 7) {
  		if (is_array($debugText)){
        $this->writeText("ERROR(".$this->getID().")". $this->print_var_name('debugText') ."\n");
        $this->writeText("  --- Obsah pole: ------------------------------\n");
        foreach ($debugText as $key=>$value){
          $this->writeText("  > ".$key.' = '.$value."\n");
        }
        $this->writeText("  ----------------------------------------------\n");
      } else
    		$this->writeText("ERROR(".$this->getID()."): ".$debugText."\n");	
    }	
  } 

  public function active( $state = true ){
    $this->isActive = $state;
  }

  public function show() {
    if (!$this->isActive)
      exit;
    if ($this->levelNo > 0) {
      if (rewind($this->file)){
        while (!feof($this->file)){
          printf ("%s<br />\n",fgets($this->file));
        }
      }
    }  
  }
  
	public function alert( $debugText) { 
    if (!$this->isActive)
      exit;
    // Only for level 4 = TRACE and higher 
		//if ($this->levelNo >= 4) {
      echo "<script language=javascript>alert('" . $debugText . "')</script>";	  
    //}
  }   
  
  public function clear() {
    if (!$this->isActive)
      exit;
    fclose($this->file);
    unlink($this->fileName);
  }

  function print_var_name($var) {
    foreach($GLOBALS as $var_name => $value) {
        if ($value === $var) {
            return $var_name;
        }
    }
    return 'Variable';
  }
 
  function getID(){
    date_default_timezone_set("Europe/Prague");
    return "(".$_SERVER['REMOTE_ADDR']." / ".date("d.m.y H:i:s").")";
  }

  function writeText( $sText ){
    $rFile  = fopen( $this->fileName, 'a' );
    flock( $rFile, LOCK_EX );
    fwrite( $rFile, $sText );    
    flock( $rFile, LOCK_UN );
    fclose( $rFile );  
  }
  
  private function convertLevel($lt) {
    switch ($lt) {
      case 'info':
        $this->levelNo = 1; 
        break;
      case 'trace':
        $this->levelNo = 4;
        break;
      case 'error':
        $this->levelNo = 9;
        break;
    }
  }
  

} // END class debeg
?>
