<?php
/**
 * Správce emailů
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    31.7.2011 
 */
class email
{
  public $to;
  public $from;
  public $subject;
  public $body;
  public $resulSent;
  
  private $encode = "utf-8";  
  private $parts;
  private $headers;
  private $receivers = array();
  
  private $template;
  private $block;
  private $fieldSets = array();
	
  private $startBlock = '<!-- BEGIN ';
	private $endBlock = '<!-- END ';
  private $endBlockLine = ' -->';
  

  /*
   *  Class constructor 
   *     
   */
  public function __construct( $registry ) 
  {
		$this->registry = $registry;

  }

  public function init( $template, $block ){
    $this->parts = array();
    $this->to =  "";
    $this->from =  "";
    $this->subject =  "";
    $this->body =  "";
    $this->headers =  "";
    $this->template = $template; 
    $this->block = $block;
    $this->resulSent = false;
  }

  public function addReceiver ( $email )
  {
    $this->receivers[] = $email;
  }
  
  
  public function addFields ( $data , $pref='')
  {
    global $deb;
    
    if( $pref !=''){
      $newData = array();
      foreach($data as $key => $value)
      {
        $newData[$pref.$key] = $value; 
      }
      $data = $newData;
    }
    $this->fieldSets[] = $data;
  }
  
  public function setSubject ( $block )
  {
    $this->subject = $this->getBlock( $block );
  }
  
  public function result ()
  {
    return $this->resulSent;  
  }
  
  public function send() {
    global $deb;

    $body = $this->getBlock('header');
    $body .= $this->getBlock( $this->block );
    $body .= $this->getBlock('footer');
    if (isset($this->fieldSets)){
      foreach($this->fieldSets as $fields)
      {
        foreach($fields as $key => $value)
        {
          $body = preg_replace("/\{$key}/", $value, $body);
          $this->subject = preg_replace("/\{$key}/", $value, $this->subject);
        }
      }
    }
    // Convert BODY to ISO-8859-2    
    $this->body = $body;

    $this->resulSent = false;
    $mime = $this->get_mail(false);
    $this->resulSent = mail($this->to, $this->subject_encode($this->subject), $this->body, $mime);
    $this->registry->getObject('log')->logEmail( $this->to, $this->subject, $this->body);
  }

  public function bulkSend() {
    global $deb;

    $body = $this->getBlock('header');
    $body .= $this->getBlock( $this->block );
    $body .= $this->getBlock('footer');
    if (isset($this->fieldSets)){
      foreach($this->fieldSets as $fields)
      {
        foreach($fields as $key => $value)
        {
          $body = preg_replace("/\{$key}/", $value, $body);
          $this->subject = preg_replace("/\{$key}/", $value, $this->subject);
        }
      }
    }
    // Convert BODY to ISO-8859-2 
    $this->body = $body;
    
    $this->resulSent = false;
    if( count($this->receivers)>0 ){
    
      $this->resulSent = true;
      foreach( $this->receivers as $to ){
        $this->to = $to;
        $mime = $this->get_mail(false);

        $resulSent = mail($to, $this->subject_encode($this->subject), $this->body, $mime);
        $this->registry->getObject('log')->logEmail( $to, $this->subject, $this->body);
        if(!$resulSent)
          $this->resulSent = false;
      }
    }
  }
    
      
  private function getBlock( $block ){
    global $deb;
   
    $fileStream = '';
    if( isset( $this->template ) && isset( $block ) ){
      $fileStream = $this->getFile( $this->template );
    }
    $iStart = strpos( $fileStream, $this->startBlock.$block.$this->endBlockLine );
    $iEnd =   strpos( $fileStream, $this->endBlock.$block.$this->endBlockLine );

    if( is_int( $iStart ) && is_int( $iEnd ) ){
      $iStart += strlen( $this->startBlock.$block.$this->endBlockLine );
      return substr( $fileStream, $iStart, $iEnd - $iStart );
    }
    else {
      echo 'No block: <i>'.$block.'</i> in file: '.$this->template.' <br />';
      return null;
    }
  } // end function getFileBlock

  private function mime_header_encode($text, $encoding = "utf-8") {
    return "=?$encoding?Q?" . imap_8bit($text) . "?=";
  }

  private function subject_encode($subject, $encoding = "utf-8") {
    iconv_set_encoding("internal_encoding",$encoding);
    if( $subject <> ''){
      $subject = iconv_mime_encode("Subject",$subject);
      $subject = substr($subject, strlen("Subject: "));    
    }
    return $subject;
  }

  /*
   *  returns the constructed mail
   *  string get_mail()      
   */
  private function get_mail($complete = true) {
    global $deb;
     
    $mime =  "";
    if (!empty($this->from))
      $mime .=  "From: ".$this->from.PHP_EOL;
    if (!empty($this->headers))
      $mime .= $this->headers.PHP_EOL;

    if ($complete) {
      if (!empty($this->to)) {
        $mime .= "To: $this->to".PHP_EOL;
      }
      if (!empty($this->subject)) {
        $mime .= "Subject: $this->subject".PHP_EOL;
      }
    }
    //$mime .= "Content-Type: text/html; charset=".$this->encode.PHP_EOL;
    $mime .= "MIME-Version: 1.0"
              . PHP_EOL . "Content-Type: text/html; charset=".$this->encode
              . PHP_EOL . "Content-Transfer-Encoding: 8bit";

    return $mime;
  }

  /*
   *  Send the mail (last class-function to be called)
   *  void send()    
   */


  private function getFile( $sFile ){
    global $deb;
    
    $rFile =  fopen( $sFile, 'r' );
    $iSize =  filesize( $sFile );
    if( $iSize > 0 ){
      $sContent = fread( $rFile, $iSize );
    }else{
      $sContent = null;
    }
    fclose( $rFile );
    return $sContent;
  }
}
?>
