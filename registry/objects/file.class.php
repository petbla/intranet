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
    ini_set('max_execution_time', 600);
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,ID,Name,Type');
    $this->registry->getObject('db')->setCondition('Archived = false AND Type IN (20,30)');
    if( $this->registry->getObject('db')->findSet())
    {
      $data = $this->registry->getObject('db')->getResult();
      $counter = 0;
      foreach ($data as $key => $entry) {
        $ID = $entry['ID'];
        $counter += 1;
        $item = $this->getItem($entry['Name']);
        if(! $item['Exist'])
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
    $item = $this->getItem($name);
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,Name');
    $sanitizename = $this->registry->getObject('db')->sanitizeData($name);
    $this->registry->getObject('db')->setCondition( 'Name="'.$sanitizename.'" AND Archived=0' );
    if( $this->registry->getObject('db')->findFirst())
    {
       $entry = $this->registry->getObject('db')->getResult();
       return $entry['EntryNo'];
    }
    
    // Insert NEW folder
    $data = array();
    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $item['Level'];
    $data['Parent'] = $this->findItem($item['WinParent']);
    $data['Path'] = $item['Parent'];
    $data['Type'] = $item['Type'];
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
//    $data['Title'] = $this->registry->getObject('db')->sanitizeData($item['Title']); 
    $data['Title'] = $item['Title']; 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($item['Name']);
    $data['FileExtension'] = $item['Extension'];
    $data['ModifyDateTime'] = date("Y-m-d H:i:s", filemtime($item['WinFullName'])); // datum a čas změny
    $data['PermissionSet'] = 1;
    $data['Url'] = '';
    $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
    $this->registry->getObject('db')->findFirst();
    $entry = $this->registry->getObject('db')->getResult();
    return $entry['EntryNo'];
  }

	/**
   * Funkce pro vyhledání položky (Block) v databázi a pokud neexistuje, tak dojde k založení 
   * 
	 * @param string $fullParent
   * @param string $item
	 * @return bool $EntryNo  
	 */
  public function findBlock( $fullParent,$name )
  {
    $parentPath = str_replace($this->root,'',$fullParent);
    $parentID = $this->getIdByName($parentPath);

    $fullName = $parentPath !== '' ? $parentPath.DIRECTORY_SEPARATOR.$name : $name;
    $entryID = $this->getIdByName($fullName);

    if ($entryID !== '')
      return (-1);

    if ($parentID !== '')
    {
      require_once( FRAMEWORK_PATH . 'models/entry/model.php');
      $this->model = new Entry( $this->registry, $parentID );
      $item = $this->model->getData();
    }else
    {
      $item = array();
      $item['Level'] = -1;
      $item['EntryNo'] = 0;
    }

    // Insert NEW Block to folder
    $data = array();
    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $item['Level'] + 1;
    $data['Parent'] = $item['EntryNo'];
    $data['Path'] = $parentPath;
    $data['Type'] = 25;
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
//    $data['Title'] = $this->registry->getObject('db')->sanitizeData($item['Title']); 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($fullName);
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($name); 
    $data['PermissionSet'] = 1;
    $data['Url'] = '';
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

  public function getItem( $name )
  {
    $item = array();
    $item['FullName'] = '';
    $item['Name'] = '';
    $item['Parent'] = '';
    $item['Item'] = '';
    $item['Title'] = '';
    $item['Exist'] = false;
    $item['Type'] = 0;
    $item['Level'] = 0;
    $item['WinFullName'] = '';
    $item['WinItem'] = '';
    $item['WinParent'] = '';
    $item['Extension'] = '';

    $item['Name'] = str_replace('\\',DIRECTORY_SEPARATOR,$name);
    $item['Name'] = str_replace('/',DIRECTORY_SEPARATOR,$item['Name']);
    $item['FullName'] =  $this->root.$item['Name'];
    $arr = explode(DIRECTORY_SEPARATOR,$item['Name']);
    $item['Item'] = (count($arr) > 0) ? $arr[count($arr) - 1] : '';
    if(count($arr) > 0)
    {
      array_pop($arr);
      $item['Parent'] = implode(DIRECTORY_SEPARATOR,$arr);
    }
    $item['WinFullName'] =  iconv("utf-8","windows-1250",$item['FullName']);
    $item['WinItem'] =  iconv("utf-8","windows-1250",$item['Item']);
    $item['WinParent'] =  iconv("utf-8","windows-1250",$item['Parent']);
    
    $parentItems = scandir($this->root.$item['WinParent']);
    for ($i=0; $i < count($parentItems); $i++) { 
      $parentItems[$i] = strtoupper(iconv("windows-1250","utf-8",$parentItems[$i]));
    }
    $item['Exist'] = in_array(strtoupper($item['Item']),$parentItems);

    $item['Level'] = substr_count($item['Name'],DIRECTORY_SEPARATOR);
    if (is_dir($item['WinFullName'])) 
    { 
      $item['Type'] = 20;
    } 
    elseif (is_file($item['WinFullName'])) 
    { 
      $item['Type'] = 30;
      $item['Extension'] = pathinfo($item['WinFullName'],PATHINFO_EXTENSION);
    }
    else
    {
      $item['Type'] = 30;
    }
    $item['Title'] = $item['Item'];
    if (($item['Title'] !== '') && ($item['Type'] === 30))
    {
      if ($item['Extension'] !== '') 
      {
        $item['Title'] = substr($item['Title'],0,strlen($item['Title']) - strlen($item['Extension']) - 1);            
      }
      if($item['Title'][strlen($item['Title'])-1] == '.')
      {
        $item['Title'] = substr($item['Title'],0,strlen($item['Title']) );            
      }
    }
    return $item;
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