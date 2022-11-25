<?php

/**
 * Core functions
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    19.12.2020
 */
class Core
{


  public function __construct(Registry $registry)
  {
    $this->registry = $registry;
  }

  /**
   * Return aktual date
   * @param NONE 
   * @return string (DD.MM.YYYY)
   */
  public function today()
  {
    $today = date("Y.m.d");
    return $today;
  } // end function Today
  
  /**
   * Return actual datetime as ISO format
   * @param NONE 
   * @return string (YYYY-MM-DD HH:MM:SS)
   */
  public function now()
  {
    $today = date("Y-m-d H:i:s");
    return $today;
  } // end function Today
  
  /**
   * Return formateed date
   * @param $date - ISO format '2022-07-23T05:10:30+0200'
   * @param $mask - mask format date (examle: "d.m.Y", "h:m"
   * 
   * @return $fdate - formated date
   */
  public function formatDate( $date, $mask = "d.m.Y")
  {
    $fdate = date($mask,strtotime($date));
    return $fdate;
  } 

  /**
   * Return day of week as text
   * @param int $w 
   * @return string
   */
  public function dayOfWeekText($w = 0)
  {
    switch ($w-1) {
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
      default:
        $w = 'neděle';
        break;
    }
    return $w;
  }

  /**
   * Return day of week as short text
   * @param int $w 
   * @return string
   */
  public function dayOfWeekTextShort($w = 0)
  {
    switch ($w-1) {
      case 1:
        $w = 'PO';
        break;
      case 2:
        $w = 'UT';
        break;
      case 3:
        $w = 'ST';
        break;
      case 4:
        $w = 'CT';
        break;
      case 5:
        $w = 'PA';
        break;
      case 6:
        $w = 'SO';
        break;
      default:
        $w = 'NE';
        break;
    }
    return $w;
  }

  /**
   * Return aktual date as text
   * @param int $ts (timestamp) 
   * @return string
   */
  public function date2FullText($ts = null)
  {
    if (!isset($ts))
      $ts = time();

    $d = preg_replace('/\b(0)(.+)\b/', '\2', date("d", $ts));

    $w = date(" w ", $ts);

    switch ($w) {
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

    $m = date("m", $ts);
    switch ($m) {
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
    $Y = date("Y", $ts);

    return "$w $d.$m $Y";
  } // end function Today


  public function randomString($length = 8)
  {
    $characters = '0123456789QWERTZUIOPLKJHGFDSAYXCVBNMabcdefghijklmnopqrstuvwxyz';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
      $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
  }

  public function randomPicture($fileName, $text, $fontSize)
  {
    $angle = 0;
    $fontFile = 'fonts/ArialUni.ttf';
    $dimensions = imagettfbbox($fontSize, $angle, $fontFile, $text);
    $w = $dimensions[2] + 10;
    $h = $fontSize + 10;

    $obrazek = imagecreatetruecolor($w, $h);
    $background = imagecolorallocate($obrazek, 255, 239, 246);
    $color = imagecolorallocate($obrazek, 0, 0, 255);

    imagefilledrectangle($obrazek, 0, 0, $w, $h, $background);
    imagettftext($obrazek, $fontSize, $angle, 5, $fontSize + 6, $color, $fontFile, $text);
    imagepng($obrazek, $fileName);
    imagedestroy($obrazek);
  }

  public function createGUID()
  {
    if (function_exists('com_create_guid') === true) {
      return trim(com_create_guid(), '{}');
    }
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
  }


    /**
   * Convert array to HTML table text
   * @param array $input - data
   * @return string HTML text formated as TABLE
   */
  public function array2table($input)
  {
      $result = '<table>';
      foreach ($input as $key => $value) {
          $result .= '<tr>';
          $result .= "<td>$key</td><td>";
          if (gettype($value) == "array") {
              $result .= $this->array2table($value);
          } else
              $result .= $value;
          $result .= '</td></tr>';
      }
      $result .= '</table>';
      return $result;
  }

  /**
   * Convert array to HTML table List
   * @param array $table - tabulka[zaznam[hodnoty]] ;
   * @param string $header - seznam polí pro zobrazení
   * @param string $tableCardLink - url odkazu pro zobrazení karty/detailu
   * @param string $keyName - kliíčové pole pro zobrazení karty/detailu
   * @return string HTML text formated as TABLE
   */
  public function array2tableList($table, $header = '', $tableCardLink = '', $keyName = '')
  {
    $result = '';
      if (gettype($table) == "array") {
          $result = "<table class='w3-table w3-bordered w3-centered w3-tiny'>";
          $countRec = count($table);
          if ($countRec > 0) {
              $rec = $table[0];

              if ($header !== '') {
                  $colName = explode(',', $header);
              } else {
                  $i = 0;
                  foreach ($rec as $key => $value) {
                      $colName[$i] = $key;
                      $i++;
                  }
              }

              // Table Header
              $result .= '<tr>';
              foreach ($colName as $key => $value) {
                  $result .= "<th>$value</th>";
              }
              $result .= '</tr>';
              for ($i = 0; $i < $countRec; $i++) {
                  $rec = $table[$i];

                  $id = '';
                  if ($tableCardLink !== '')
                    $id = isset($rec[$keyName]) ? $rec[$keyName] : '';

                  if ($id !== ''){
                    $ondblclick = 'openCardPage('.$id.',"'.$tableCardLink.'");';
                    $result .= "<tr ondblclick='$ondblclick'>";
                  }
                  else
                    $result .= "<tr>";
                  foreach ($colName as $key => $value) {
                      $result .= "<td>$rec[$value]</td>";
                  }
                  $result .= '</tr>';
              }
          }
      }
      $result .= '</table>';
      return $result;
  }
  /**
   * Convert array to HTML table Card
   * @param array $table - tabulka[zaznam[hodnoty]] ;
   * @param string $header - seznam polí pro zobrazení
   * @return string HTML text formated as TABLE
   */
  public function array2tableCard($rec, $header = '')
  {
      $result = '';
      if (gettype($rec) == "array") {
        
          if ($header !== '') {
              $result .= "<h1>$header</h1>";
          };
          $countField = count($rec);
          if ($countField > 0) {
              $result .= '<table>';
              foreach ($rec as $column => $value) {
                  $result .= '<tr>';
                  $result .= "<td>$column</td>";
                  
                  if (gettype($value) == "array") {
                    $value = $this->array2tableCard($value);
                  };
                  $result .= "<td>$value</td>";
                  $result .= '</tr>';
              }
              $result .= '</table>';
          }
      }
      return $result;
  }


  /**
   * Get current IP address
   * @return $ip - IP address
   */
  public function getUserIpAddr(){ 
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){ 
        $ip = $_SERVER['HTTP_CLIENT_IP']; 
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ 
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
    }else{
        $ip = $_SERVER['REMOTE_ADDR']; 
    } 
    return $ip;
  }     
    
}
