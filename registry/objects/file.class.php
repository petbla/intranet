<?php
/**
 * Správce souborů
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    27.11.2018 
 */

class file {

  public function __construct( $registry ) 
  {
    $this->registry = $registry;
  }


  /**
   * Funkce pro aktualizaci databáze, tj. založení složek a jijich podsložek a souborů 
   * 
	 * @param string $root
	 * @return void
	 */
  public function updateFiles($root = '.'){ 
    $last_letter  = $root[strlen($root)-1]; 
    $root = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR; 

    /*
     * Find deleted OR renamed documents
     */
    $slozka = 0;
    $soubor = 0;
    $neni = 0;
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,ID,Name,Type');
    $this->registry->getObject('db')->setCondition('Archived = false');
    if( $this->registry->getObject('db')->findSet())
    {
      $data = $this->registry->getObject('db')->getResult();
      foreach ($data as $key => $entry) {
        $ID = $entry['ID'];
        switch ($entry['Type']) {
          case 20:
            # Folder
            $slozka += 1;
            break;
          case 30:
            # File
            $fullname =  iconv("utf-8","windows-1250",$root.$entry['Name']);
            $soubor += 1;
            if (!is_file($fullname))
            {
              $neni += 1;
              $changes['Archived'] = true;
              $condition = "ID = '$ID'";
              $this->registry->getObject('db')->updateRecords('DmsEntry',$changes,$condition);
            }
            break;
        }        
      }
    }

    $directories[]  = $root; 
    $paret = 0;
    $level = 0;
    while (sizeof($directories)) { 
      $dir  = array_pop($directories); 
      if ($handle = opendir($dir)) { 
        while (false !== ($file = readdir($handle))) 
        { 
          if ($file == '.' || $file == '..') 
          { 
            continue; 
          };
          $fullName = $dir.$file;
          $entryNo = $this->findItem($fullName);
          $type = $this->getFileType($fullName);
          if ($type == 20) 
          { 
            $directory_path = $fullName.DIRECTORY_SEPARATOR; 
            array_push($directories, $directory_path); 
          } 
        } 
        closedir($handle); 
      } 
    } 

    
  } 


	/**
   * Funkce pro vyhledání položky (soubor/složka) v databázi a pokud neexistuje, tak dojde k založení včetně všech 
   * nadřazených složek
   * 
	 * @param string $fullName
	 * @return bool $EntryNo  
	 */
  public function findItem( $fullName )
  {
    global $config;
    $root = $config['fileserver'];
    $name = str_replace($root,'',$fullName);
    $type = $this->getFileType($fullName);
    if ($name == '')
    {
      return 0;
    }
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,Name');
    $sanitize_name = iconv("windows-1250","utf-8",$name);
    $sanitize_name = $this->registry->getObject('db')->sanitizeData($sanitize_name);
    $this->registry->getObject('db')->setCondition( "Name='$sanitize_name'" );
    if( $this->registry->getObject('db')->findFirst())
    {
       $parentEntry = $this->registry->getObject('db')->getResult();
       return $parentEntry['EntryNo'];
    }
    $level = substr_count($name,DIRECTORY_SEPARATOR);
    $pathArr = explode(DIRECTORY_SEPARATOR, $name);
    if ($name[strlen($name)-1] == DIRECTORY_SEPARATOR)
    {
      $name = substr($name,0,strlen($name) - 1);
      array_pop($pathArr);
    }
    if(count($pathArr) > 1)
    {
      $title = $pathArr[count($pathArr) - 1];
      array_pop($pathArr);        
      $path = implode(DIRECTORY_SEPARATOR, $pathArr);
    }
    else
    {
      $title = $name;
      $path = '';
    }
    $title = iconv("windows-1250","utf-8",$title);
    $name = iconv("windows-1250","utf-8",$name);
    if ($type == 30)
    {
      $fileExtension = pathinfo($title,PATHINFO_EXTENSION);
      if ($fileExtension != '')
      {
        $title = substr($title,0,strlen($title) - strlen($fileExtension) - 1);            
      }
      if($title[strlen($title)-1] == '.')
      {
        $title = substr($title,0,strlen($title) );            
      }
    }
    else
    {
      $fileExtension = '';
    }
    // Insert NEW folder
    $data = array();
    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $level;
    $data['Parent'] = $this->findItem($root.$path);
    $data['Type'] = $type;
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($title); 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($name);
    $data['FileExtension'] = $fileExtension;
    $data['ModifyDateTime'] = date("Y-m-d H:i:s", filemtime($fullName)); // datum a čas změny
    $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
    $this->registry->getObject('db')->findFirst();
    $parentEntry = $this->registry->getObject('db')->getResult();
    return $parentEntry['EntryNo'];
  }

  
  private function getFileType ($file)
  {
    if (is_dir($file)) 
    { 
      return 20;
    } 
    elseif (is_file($file)) 
    { 
      return 30;
    }
    else
    {
      return (substr_count($file,'.') > 0 ? 30 : 20);  
    }
  }

  private function getNextLineNo ($Parent)
  {
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,LineNo');
    $this->registry->getObject('db')->setCondition("Parent=$Parent");
    $this->registry->getObject('db')->setOrderBy('Parent,LineNo');
    if( $this->registry->getObject('db')->findLast())
    {
      $Entry = $this->registry->getObject('db')->getResult();
      $lineNo = $Entry['LineNo'];
    }
    else
    {
      $lineNo = 0;
    }
    $lineNo += 100;
    return $lineNo;
  }

  public function getIdByName( $name )
  {
    $name = $this->registry->getObject('db')->sanitizeData($name);
    $this->registry->getObject('db')->initQuery('DmsEntry','ID');
    $this->registry->getObject('db')->setCondition("Name='$name'");
    if( $this->registry->getObject('db')->findFirst())
    {
      $Entry = $this->registry->getObject('db')->getResult();
      return $Entry['ID'];
    }
    else
    {
      return ('');
    }
    
  }
}