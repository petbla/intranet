<?php
/**
 * Správce souborů
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    27.11.2018 
 */

class file {
  
  private $fullName;
  private $root;
  private $itemName;
  private $parent;
  private $item;
  private $itemTitle;
  private $itemExists;
  private $itemType;
  private $itemLevel;
  private $winFullName;
  private $winItem;
  private $winParent;
  private $fileExtension;

  public function __construct( $registry ) 
  {
    global $config;
    $root = $config['fileserver'];
    $last_letter  = $root[strlen($root)-1]; 

    $this->registry = $registry;
    $root = str_replace('\\',DIRECTORY_SEPARATOR,$root);
    $root = str_replace('/',DIRECTORY_SEPARATOR,$root);
    $root = ($last_letter == DIRECTORY_SEPARATOR) ? $root : $root.DIRECTORY_SEPARATOR; 
    $this->root = $root;
  }
  /**
   * Funkce pro aktualizaci databáze, tj. založení složek a jijich podsložek a souborů 
   * 
	 * @param string $root
	 * @return void
	 */
  public function synchroRoot(){ 
    /*
     * Find deleted OR renamed documents
     */
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,ID,Name,Type');
    $this->registry->getObject('db')->setCondition('Archived = false AND Type IN (20,30)');
    if( $this->registry->getObject('db')->findSet())
    {
      $data = $this->registry->getObject('db')->getResult();
      $counter = 0;
      foreach ($data as $key => $entry) {
        $ID = $entry['ID'];
        $counter += 1;
        $this->setName($entry['Name']);
        if(! $this->itemExists)
        {
          $changes['Archived'] = true;
          $changes['LastChange'] = date("Y-m-d H:i:s");
          $condition = "ID = '$ID'";
          $this->registry->getObject('db')->updateRecords('DmsEntry',$changes,$condition);
        }        
      }
    }
    $directories[]  = $this->root; 
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
          $fullItemPath = $dir.$file;
          $entryNo = $this->findItem($fullItemPath);
          if(is_dir($fullItemPath))
          { 
            $directory_path = $fullItemPath.DIRECTORY_SEPARATOR; 
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
	 * @param string $fullItemPath
	 * @return bool $EntryNo  
	 */
  public function findItem( $winFullItemPath )
  {
    $fullItemPath = iconv("windows-1250","utf-8",$winFullItemPath);
    $name = str_replace($this->root,'',$fullItemPath);
    if ($name == '')
    {
      return 0;
    }
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,Name');
    $name = $this->registry->getObject('db')->sanitizeData($name);
    $this->registry->getObject('db')->setCondition( "Name='$name'" );
    if( $this->registry->getObject('db')->findFirst())
    {
       $entry = $this->registry->getObject('db')->getResult();
       return $entry['EntryNo'];
    }
    $this->setName($name);
    
    // Insert NEW folder
    $data = array();
    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $this->itemLevel;
    $data['Parent'] = $this->findItem($this->itemParent);
    $data['Path'] = $this->itemParent;
    $data['Type'] = $this->itemType;
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($this->itemTitle); 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($this->itemName);
    $data['FileExtension'] = $this->fileExtension;
    $data['ModifyDateTime'] = date("Y-m-d H:i:s", filemtime($this->winFullName)); // datum a čas změny
    $data['PermissionSet'] = 1;
    $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
    $this->registry->getObject('db')->findFirst();
    $entry = $this->registry->getObject('db')->getResult();
    return $entry['EntryNo'];
  }

  
  private function getNextLineNo ($Parent)
  {
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,LineNo');
    $this->registry->getObject('db')->setCondition("Parent=$Parent");
    $this->registry->getObject('db')->setOrderBy('Parent,LineNo');
    if( $this->registry->getObject('db')->findLast())
    {
      $entry = $this->registry->getObject('db')->getResult();
      $lineNo = $entry['LineNo'];
    }
    else
    {
      $lineNo = 0;
    }
    $lineNo += 100;
    return $lineNo;
  }

  public function setName( $name )
  {
    $this->fullName = '';
    $this->itemName = '';
    $this->parent = '';
    $this->item = '';
    $this->itemTitle = '';
    $this->itemExists = false;
    $this->itemType = 0;
    $this->itemLevel = 0;
    $this->winFullName = '';
    $this->winItem = '';
    $this->winParent = '';
    $this->fileExtension = '';

    $this->itemName = str_replace('\\',DIRECTORY_SEPARATOR,$name);
    $this->itemName = str_replace('/',DIRECTORY_SEPARATOR,$this->itemName);
    $this->fullName =  $this->root.$this->itemName;
    $this->winFullName =  iconv("utf-8","windows-1250",$this->fullName);
    $arr = explode(DIRECTORY_SEPARATOR,$this->itemName);
    $this->item = (count($arr) > 0) ? $arr[count($arr) - 1] : '';
    $this->winItem =  iconv("utf-8","windows-1250",$this->item);
    if(count($arr) > 0)
    {
      array_pop($arr);
      $this->parent = implode(DIRECTORY_SEPARATOR,$arr);
    }
    else
    {
      $this->parent = '';
    }
    $this->winParent =  iconv("utf-8","windows-1250",$this->parent);
    $parentItems = scandir($this->root.$this->winParent);
    for ($i=0; $i < count($parentItems); $i++) { 
      $parentItems[$i] = strtoupper(iconv("windows-1250","utf-8",$parentItems[$i]));
    }
    $this->itemExists = in_array(strtoupper($this->item),$parentItems);

    $this->itemLevel = substr_count($this->itemName,DIRECTORY_SEPARATOR);
    $this->fileExtension = pathinfo($this->winFullName,PATHINFO_EXTENSION);
    if (is_dir($this->winFullName)) 
    { 
      $this->itemType = 20;
    } 
    elseif (is_file($this->winFullName)) 
    { 
      $this->itemType = 30;
    }
    else
    {
      $this->itemType = 30;
    }
    $this->itemTitle = $this->item;
    if ($this->itemTitle !== '')
    {
      if ($this->fileExtension !== '')
      {
        $this->itemTitle = substr($this->itemTitle,0,strlen($this->itemTitle) - strlen($this->fileExtension) - 1);            
      }
      if($this->itemTitle[strlen($this->itemTitle)-1] == '.')
      {
        $this->itemTitle = substr($this->itemTitle,0,strlen($this->itemTitle) );            
      }
    }
  }

  public function getIdByName( $name )
  {
    $name = $this->registry->getObject('db')->sanitizeData($name);
    $this->registry->getObject('db')->initQuery('DmsEntry','ID');
    $this->registry->getObject('db')->setCondition("Name='$name'");
    if( $this->registry->getObject('db')->findFirst())
    {
      $entry = $this->registry->getObject('db')->getResult();
      return $entry['ID'];
    }
    else
    {
      return ('');
    }
    
  }
}