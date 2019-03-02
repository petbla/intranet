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

    $this->registry = $registry;
    $root = str_replace('\\',DIRECTORY_SEPARATOR,$root);
    $root = str_replace('/',DIRECTORY_SEPARATOR,$root);
    $last_letter  = $root[strlen($root)-1]; 
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
    global $config;
    
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
          $changes['Archived'] = 1;
          $changes['LastChange'] = date("Y-m-d H:i:s");
          $condition = "ID = '$ID'";
          $item = $this->getItem($entry['Name']);         
					$this->registry->getObject('log')->addMessage("Zobrazení a aktualizace dokumentu",'contact',$ID);
          $this->registry->getObject('db')->updateRecords('DmsEntry',$changes,$condition);
        }        
      }
    }

    $directories[]  = $this->root; 
    
    $paret = 0;
    $level = 0;
    while (count($directories)) { 
      $dir  = array_pop($directories); 
      $dir = str_replace('\\','/',$dir);
      $dir = str_replace('http:','',$dir);

      if ($handle = opendir($dir)) { 
        while (false !== ($file = readdir($handle))) 
        { 
          if ($file == '.' |0| $file == '..') 
          { 
            continue; 
          };
          $fullItemPath = $dir.$file;
          $winFullItemPath = iconv("utf-8","windows-1250",$fullItemPath);
          $entryNo = $this->findItem($winFullItemPath);
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
    $name = str_replace('\\',DIRECTORY_SEPARATOR,$fullItemPath);
    $name = str_replace('/',DIRECTORY_SEPARATOR,$name);
    $name = str_replace(str_replace('http:','',$this->root),'',$name);
    if ($name === '')
    {
      return 0;
    }
    $item = $this->getItem($name);
    if(! $item['Exist'])
    {
      return 0;
    }
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
    $data['Path'] = $this->registry->getObject('db')->sanitizeData($item['Parent']);
    $data['Type'] = $item['Type'];
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($item['Title']); 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($item['Name']);
    $data['FileExtension'] = $item['Extension'];
    $data['ModifyDateTime'] = date("Y-m-d H:i:s", filemtime($item['WinFullName'])); // datum a čas změny
    $data['PermissionSet'] = 1;
    $data['Url'] = '';
    switch (strtolower($data['FileExtension'])) {
      case 'bmp':
      case 'jpg':
      case 'gif':
      case 'png':
          $data['Multimedia'] = 'image';
          break;
      case 'mp3':
          $data['Multimedia'] = 'audio';
          break;
      case 'mp4':
          $data['Multimedia'] = 'video';
          break;
    }
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
  public function addBlock( $fullParent,$name )
  {
    $fullParent = str_replace('\\',DIRECTORY_SEPARATOR,$fullParent);
    $fullParent = str_replace('/',DIRECTORY_SEPARATOR,$fullParent);
    $parentPath = str_replace($this->root,'',$fullParent);
    $parentID = $this->getIdByName($parentPath);

    $fullName = $parentPath !== '' ? $parentPath.DIRECTORY_SEPARATOR.$name : $name;
    $entryID = $this->getIdByName($fullName);

    if ($entryID !== '')
      return (-1);

    require_once( FRAMEWORK_PATH . 'models/entry/model.php');
    if ($parentID !== '')
    {
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
    $this->model->initNew();
    $data = $this->model->getData();

    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $item['Level'] + 1;
    $data['Parent'] = $item['EntryNo'];
    $data['Path'] = $this->registry->getObject('db')->sanitizeData($parentPath);
    $data['Type'] = 25;
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($fullName);
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($name); 
    $data['PermissionSet'] = 1;
    $data['Url'] = '';
    $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
    $this->registry->getObject('db')->findFirst();
    $entry = $this->registry->getObject('db')->getResult();
    return $data['ID'];
  }
  
  public function newNote ( $parentEntry )
	{
		// Insert NEW Note
		$data = array();
		$data['ID'] = $this->registry->getObject('fce')->GUID();
		$data['Level'] = $parentEntry['Level'] + 1;
		$data['Parent'] = $parentEntry['EntryNo'];
		$data['Path'] = $this->registry->getObject('db')->sanitizeData($parentEntry['Name']);
		$data['Type'] = 35;
		$data['LineNo'] = $this->getNextLineNo($data['Parent']);
		$data['Title'] = 'Nová poznámka'; 
		$data['Name'] = $data['Path'].DIRECTORY_SEPARATOR.$data['ID'];
		$data['PermissionSet'] = $parentEntry['PermissionSet'];
    $data['Url'] = '';
		$this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
		return $data['ID'];
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
    global $config;
    $root = str_replace('http:','',$this->root);

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
    $item['FullName'] =  $root.$item['Name'];

    if(!is_file($item['FullName']) && !is_dir($item['FullName']))
    {
      return $item;
    }


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
    
    $parentItems = scandir($root.$item['WinParent']);
    for ($i=0; $i < count($parentItems); $i++) { 
      $parentItems[$i] = strtoupper($parentItems[$i]);
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
    $name = str_replace('\\',DIRECTORY_SEPARATOR,$name);
    $name = str_replace('/',DIRECTORY_SEPARATOR,$name);
    $name = $this->registry->getObject('db')->sanitizeData($name);
    $this->registry->getObject('db')->initQuery('DmsEntry','ID');
    $this->registry->getObject('db')->setCondition("Name='$name' AND Archived=0");
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

  public function getIdByEntryNo( $entryNo )
  {
    $this->registry->getObject('db')->initQuery('DmsEntry','ID');
    $this->registry->getObject('db')->setCondition("EntryNo='$entryNo'");
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