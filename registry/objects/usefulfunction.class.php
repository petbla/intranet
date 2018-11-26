<?php
/**
 * Useful function 
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    1.7.2011 
 */
class UsefulFunction {

	
  public function __construct( Registry $registry )
	{
		$this->registry = $registry;
	}    

  /**
   * Return aktual date
   * @param NONE 
   * @return string (DD.MM.YYYY)
   */
  public function Today( )
  {
		$today = date("Y.m.d");
		return $today ;
  } // end function Today

  /**
   * Return aktual date as text
   * @param int $ts (timestamp) 
   * @return string
   */
  public function Date2FullText( $ts = null )
  {
		if (!isset( $ts ))
		  $ts = time();
		
		$d = preg_replace('/\b(0)(.+)\b/', '\2', date("d", $ts));
    
		$w = date(" w " , $ts);
		
    switch ($w)
		{
      case 0: 
        $w = 'neděle';
        break;
      case 1: 
        $w = 'pondělí';
        break;
      case 2: 
        $w = 'úterý';
        break;
      case 3: 
        $w = 'středa';
        break;
      case 4: 
        $w = 'čtvrtek';
        break;
      case 5: 
        $w = 'pátek';
        break;
      case 6: 
        $w = 'sobota';
        break;
    }
    
		$m = date("m" , $ts);
    switch ($m)
		{
      case 1: 
        $m = 'ledna';
        break;
      case 2: 
        $m = 'února';
        break;
      case 3: 
        $m = 'března';
        break;
      case 4: 
        $m = 'dubna';
        break;
      case 5: 
        $m = 'května';
        break;
      case 6: 
        $m = 'června';
        break;
      case 7: 
        $m = 'července';
        break;
      case 8: 
        $m = 'srpna';
        break;
      case 9: 
        $m = 'září';
        break;
      case 10: 
        $m = 'října';
        break;
      case 11: 
        $m = 'listopadu';
        break;
      case 12: 
        $m = 'prosince';
        break;
    }
		$Y = date("Y" , $ts);
		
		return "$w $d.$m $Y" ;
  } // end function Today

  /**
  * Return price format
  * @return float
  * @param float  $fPrice
  */
  function tPrice( $fPrice ){
    return sprintf( '%01.2f', $fPrice );
  } // end function tPrice

	public function nextSeriesNo( $type, $isoDT )
	{
    $docNo = 0;

    $date = date('Y-m-d', strtotime($isoDT));
    $sql = "SELECT ID,valid_from,first_no,last_no FROM no_series WHERE type='$type'";
    if (isset($date) && ($date != null) )
      $sql .= " AND valid_from <= '" . $date . "' ORDER BY valid_from DESC";
    else
      $sql .= " ORDER BY valid_from"; 
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			$data = $this->registry->getObject('db')->getRows();
		  if ($data['last_no'] == null)
		    $docNo = $data['first_no'];
      else
        $docNo = $data['last_no'] + 1;
			
      $update['last_no'] = $docNo; 
      $this->registry->getObject('db')->updateRecords( 'no_series', $update, 'ID=' . $data['ID']);
    }           
    return $docNo;
  }

	public function randomString( $length=8 )
	{
	    $characters = '0123456789QWERTZUIOPLKJHGFDSAYXCVBNMabcdefghijklmnopqrstuvwxyz';
	    $string = '';    
	    for ($i = 0; $i < $length; $i++ ) 
	    {
	        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
	    }
	    return $string;
	}
	
  public function randomPicture( $fileName, $text, $fontSize)
	{
    $angle = 0;
    $fontFile = 'fonts/ArialUni.ttf';
    $dimensions = imagettfbbox($fontSize, $angle, $fontFile, $text ); 
    $w = $dimensions[2] + 10;
    $h = $fontSize + 10; 

    $obrazek = imagecreatetruecolor($w,$h);
    $background = imagecolorallocate($obrazek,255,239,246);
    $color = imagecolorallocate($obrazek,0,0,255);

    imagefilledrectangle($obrazek,0,0,$w,$h,$background);
    imagettftext($obrazek, $fontSize, $angle, 5, $fontSize + 6, $color, $fontFile, $text);      
    imagepng($obrazek,$fileName);
    imagedestroy($obrazek);
	}

  public function AmmountWords( $amount )
  {                                     
    global $WordsSeparator, $namesCrowns, $namesMillions, $namesThousands, $namesHundreds, 
           $namesTens, $namesSmall, $two, $zero;	
    
    $zero = "nula"; 
    $two = "dvě"; 
    $namesSmall =array("jedna", "dva", "tři", "čtyři", "pět", "šest", "sedm", "osm", "devět", "deset", 
                       "jedenáct", "dvanáct", "třináct", "čtrnáct", "patnáct", "šestnáct", "sedmnáct", "osmnáct", "devatenáct"); 
    $namesTens =array( "dvacet", "třicet", "čtyřicet", "padesát", "šedesát", "sedmdesát", "osmdesát", "devadesát" ); 
    $namesHundreds =array( "jedno sto", "dvě stě", "tři sta", "čtyři sta", "pět set", "šest set", "sedm set", "osm set", "devět set" ); 
    $namesThousands =array( "jeden tisíc", "dva tisíce", "tři tisíce", "čtyři tisíce", "tisíc" ); 
    $namesMillions =array( "jeden milion", "dva miliony", "tři miliony", "čtyři miliony", "milionů" ); 
    $namesCrowns =array( "korun", "koruna", "koruny", "koruny", "koruny", "korun" ); 
    $WordsSeparator = ""; 

    if ($amount == 0) 
      return $zero; 
    if ($amount == 2) 
      return $two; // aby to vrátilo "dvě" místo "dva" 

    //string $amountString = $amount.ToString("##########0"); 
    $result = ""; 

    if ($amount >= 1000000) { 
      $result .= $this->GetAmountPart_Millions($amount); 
    } 
    if ($amount >= 1000) { 
      $result .= $this->GetAmountPart_Thousands($amount); 
    } 
    if ($amount >= 100) { 
      $result .= $this->GetAmountPart_Hundreds($amount); 
    } 
    $result .= $this->GetAmountPart_BelowHundred($amount); 

    $result = $this->ApplySeparator($result); 

    return $result; 
  } 

  function ApplySeparator($amountInWords) 
  { 
    global $WordsSeparator, $namesCrowns, $namesMillions, $namesThousands, $namesHundreds, 
           $namesTens, $namesSmall, $two, $zero;	
    return str_Replace(" ", $WordsSeparator, $amountInWords); 
  } 

  function GetAmountPart_BelowHundred($amount) 
  { 
    global $WordsSeparator, $namesCrowns, $namesMillions, $namesThousands, $namesHundreds, 
           $namesTens, $namesSmall, $two, $zero;	
    if ($amount == 0) 
      return ""; 
    
    $hundreds = floor($amount / 100); 
    $amountBelowHundred = $amount - ($hundreds * 100); 

    //echo "x".$amountBelowHundred."x"; 

    if ($amountBelowHundred == 0) 
      return ""; 
    $tens = floor($amountBelowHundred / 10); 
    $units = $amountBelowHundred - ($tens * 10); 
    $result = ""; 
    
    if ($tens >= 2) { 
      $result .= $namesTens[$tens - 2].$WordsSeparator; 
    } 

    if ($amount >= 1 && $amountBelowHundred < 20) { 
      $result .= $namesSmall[$amountBelowHundred - 1].$WordsSeparator; 
    } else if ($units > 0) { 
      $result .= $namesSmall[$units - 1].$WordsSeparator; 
      //echo "x".$result."x"; //MessageError($units); 
    } 
    return $result; 
  } 

  private function GetAmountPart_Hundreds($amount) 
  { 
    global $WordsSeparator, $namesCrowns, $namesMillions, $namesThousands, $namesHundreds, 
           $namesTens, $namesSmall, $two, $zero;	

    if ($amount < 100) 
      return ""; 

    $thousands = floor($amount / 1000); 
    $amountBelowThousand = $amount - ($thousands * 1000); 
    if ($amountBelowThousand < 100) 
      return ""; 
    $hundreds = floor($amountBelowThousand / 100); 

    $result = ""; 
    $result .= $namesHundreds[$hundreds - 1] . $WordsSeparator; 

    return $result; 
  } 

  private function GetAmountPart_Thousands($amount) 
  { 
    global $WordsSeparator, $namesCrowns, $namesMillions, $namesThousands, $namesHundreds, 
           $namesTens, $namesSmall, $two, $zero;	

    if ($amount < 1000) 
      return ""; 

    $millions = floor($amount / 1000000); 
    $amountBelowMillion = $amount - ($millions * 1000000); 
    if ($amountBelowMillion < 100) 
      return ""; 

    $thousands = $amountBelowMillion / 1000; 
    $result = ""; 

    if ($thousands <= 4) { 
      $result .= $namesThousands[$thousands - 1] . $WordsSeparator; 
    } else { 
      $result .= AmmountWords($thousands) . $WordsSeparator.$namesThousands[strlen($namesThousands) - 1] . $WordsSeparator; 
    } 
    return $result; 
  } 

  private function GetAmountPart_Millions($amount) 
  { 
    global $WordsSeparator, $namesCrowns, $namesMillions, $namesThousands, $namesHundreds, 
           $namesTens, $namesSmall, $two, $zero;	

    if ($amount < 1000000) 
      return ""; 

    $millions = floor($amount / 1000000); 
    $result = ""; 

    if ($millions <= 4) { 
      $result .= $namesMillions[$millions - 1] . $WordsSeparator; 
    } else { 
      $result .= AmmountWords($millions).$WordsSeparator.$namesMillions[strlen($namesMillions) - 1].$WordsSeparator; 
    } 
    return $result; 
  } 
  
  public function GUID()
	{
    	if (function_exists('com_create_guid') === true)
    	{
        	return trim(com_create_guid(), '{}');
	    }
    	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

}
?>