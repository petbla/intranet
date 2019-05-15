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
  public function synchroRoot(){ 
    /*
     * Find deleted OR renamed documents
     */
    global $config;
    
    ini_set('max_execution_time', 600);
    
    $this->deaktiveUnvalidEntries();

    $directories[]  = $this->getRoot(); 

    
    $paret = 0;
    $level = 0;
    while (count($directories)) { 
      $dir  = array_pop($directories); 
      $dir  = $this->registry->getObject('fce')->ConvertToSharePathName( $dir,false);

      if ($handle = opendir($dir)) { 
        while (false !== ($file = readdir($handle))) 
        { 
          if ($file == '.' |0| $file == '..') 
          { 
            continue; 
          };
          $fullItemPath = $dir.$file;
          $winFullItemPath = $this->Convert2SystemCodePage($fullItemPath);
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

  function synchoroDirectory($entry)
  {
    $dir = $this->getRoot() . $entry['Name'];
    $dir  = $this->registry->getObject('fce')->ConvertToSharePathName( $dir,false);
    if ($handle = opendir($dir)) { 
      while (false !== ($file = readdir($handle))) 
      { 
        if ($file == '.' |0| $file == '..') 
        { 
          continue; 
        };
        $fullItemPath = $dir.$file;
        $name  = $this->registry->getObject('fce')->ConvertToDirectoryPathName( $fullItemPath,false );   
        $name = str_replace(str_replace('http:','',$this->getRoot()),'',$name);      

        $this->registry->getObject('db')->initQuery('DmsEntry','ID');
        $sanitizename = $this->registry->getObject('db')->sanitizeData($name);
        $this->registry->getObject('db')->setCondition( 'Name="'.$sanitizename.'" AND Archived=0' );
        if($this->registry->getObject('db')->findFirst() == false)
        {
          $winFullItemPath = $this->Convert2SystemCodePage($fullItemPath);
          $entryNo = $this->findItem($winFullItemPath);
        }    
      }
      closedir($handle); 
      $this->deaktiveUnvalidEntries($entry['EntryNo']);
    } 
}

  function deaktiveUnvalidEntries($Parent = null)
  {
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,ID,Name,Type');
    $this->registry->getObject('db')->setCondition('Archived = 0 AND Type IN (20,30)');
    if (isset($Parent))
      $this->registry->getObject('db')->setFilter('Parent',$Parent);
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
					$this->registry->getObject('log')->addMessage("Zobrazení a aktualizace dokumentu",'contact',$ID);
          $this->registry->getObject('db')->updateRecords('DmsEntry',$changes,$condition);
        }        
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
  public function findItem( $winFullItemPath , $isDir = false )
  {
    $fullItemPath = $this->Convert2CoreCodePage($winFullItemPath);
    $name  = $this->registry->getObject('fce')->ConvertToDirectoryPathName( $fullItemPath,false );    // formát Xxxxx\Ddddd\Aaaaa
    $name = str_replace(str_replace('http:','',$this->getRoot()),'',$name);
    if ($name === '')
    {
      return 0;
    }
    $item = $this->getItem($name, $isDir);
    
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,ID,Name,Title,FileExtension,Multimedia');
    $sanitizename = $this->registry->getObject('db')->sanitizeData($name);
    $this->registry->getObject('db')->setCondition( 'Name="'.$sanitizename.'" AND Archived=0' );
    if( $this->registry->getObject('db')->findFirst())
    {
       $entry = $this->registry->getObject('db')->getResult();
       $ext = pathinfo($item['WinFullName'],PATHINFO_EXTENSION);
       $multimedia = $this->getMultimediaType($ext);       
       if((($entry['FileExtension'] != $ext) || ($entry['Multimedia'] != $multimedia)) && (strlen($ext) <= 10))
       {
         $ID = $entry['ID'];
         $changes = array();
         $changes['FileExtension'] = $ext;
         $changes['Multimedia'] = $multimedia;
         $condition = "ID = '$ID'";
         $this->registry->getObject('log')->addMessage("Doplnění přípony souboru",'dmsentry',$ID);
         $this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);
       };
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
    $data['Multimedia'] = $this->getMultimediaType(strtolower($data['FileExtension']));
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
    $fullParent  = $this->registry->getObject('fce')->ConvertToDirectoryPathName( $fullParent,false );    
    $parentPath = str_replace($this->getRoot(),'',$fullParent);
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
    $this->model = new Entry( $this->registry, '' );
    $data = $this->model->getData( true );

    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $item['Level'] + 1;
    $data['Parent'] = $item['EntryNo'];
    $data['Path'] = $this->registry->getObject('db')->sanitizeData($parentPath);
    $data['Type'] = 25;
    $data['LineNo'] = $this->getNextLineNo($data['Parent']);
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($fullName);
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($name); 
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
		$data['Name'] = $this->registry->getObject('db')->sanitizeData($data['Path'].DIRECTORY_SEPARATOR.$data['ID']);
		$data['PermissionSet'] = $parentEntry['PermissionSet'];
    $data['Url'] = '';
		$this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
		return $data['ID'];
	}

  public function addNote ( $data, $parentID )
	{
    $parentEntry = $this->getEntryById($parentID);

    // Insert Note
		$data['ID'] = $this->registry->getObject('fce')->GUID();
		$data['Level'] = $parentEntry['Level'] + 1;
		$data['Parent'] = $parentEntry['EntryNo'];
		$data['Path'] = $this->registry->getObject('db')->sanitizeData($parentEntry['Name']);
		$data['Type'] = 35;
		$data['LineNo'] = $this->getNextLineNo($data['Parent']);
		$data['Name'] = $this->registry->getObject('db')->sanitizeData($data['Path'].DIRECTORY_SEPARATOR.$data['ID']);
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

  public function getItem( $name , $isDir = false)
  {
    global $config;
    $root = str_replace('http:','',$this->getRoot());

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

    $item['Name'] = $this->registry->getObject('fce')->ConvertToDirectoryPathName( $name,false );    

    $item['FullName'] =  $root.$item['Name'];
    $item['WinFullName'] =  $this->Convert2SystemCodePage($item['FullName']);

    if (!$isDir)
    {
      if(!is_file($item['WinFullName']))
      {
        $isDir = true;
      }
    }


    $arr = explode(DIRECTORY_SEPARATOR,$item['Name']);
    $item['Item'] = (count($arr) > 0) ? $arr[count($arr) - 1] : '';
    if(count($arr) > 0)
    {
      array_pop($arr);
      $item['Parent'] = implode(DIRECTORY_SEPARATOR,$arr);
    }
    $item['WinItem'] =  $this->Convert2SystemCodePage($item['Item']);
    $item['WinParent'] =  $this->Convert2SystemCodePage($item['Parent']);
    $item['Exist'] = (is_dir($item['WinFullName']) || is_file($item['WinFullName']));

    $item['Level'] = substr_count($item['Name'],DIRECTORY_SEPARATOR);
    if ($isDir || (is_dir($item['WinFullName']))) 
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
    $name = $this->registry->getObject('fce')->ConvertToDirectoryPathName( $name ,false);    

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

  public function getEntryById( $ID )
  {
    $this->registry->getObject('db')->initQuery('DmsEntry','');
    $this->registry->getObject('db')->setCondition("ID='$ID'");
    if( $this->registry->getObject('db')->findFirst())
    {
      $entry = $this->registry->getObject('db')->getResult();
      return $entry;
    }
    else
    {
      return null;
    }
  }

  public function Convert2SystemCodePage( $sourceText )
  {
    global $config;

    if ($config['coreEncoding'] == $config['systemEncoding'])
      return($sourceText);
    return(iconv($config['coreEncoding'],$config['systemEncoding'],$sourceText));
  }

  public function Convert2CoreCodePage( $sourceText )
  {
    global $config;

    if ($config['coreEncoding'] == $config['systemEncoding'])
      return($sourceText);
    return(iconv($config['systemEncoding'],$config['coreEncoding'],$sourceText));
  }

  function getMultimediaType($FileExt)
  {
    switch ($FileExt) {
      case 'bmp':
      case 'jpg':
      case 'gif':
      case 'png':
          return 'image';
      case 'mp3':
      case 'wav':
      case 'mid':
          return 'audio';
      case 'mp4':
          return 'video';
    }
    return '';
  }
  
  /**
   * Funkce vrací root složku dle nastavení z tabulky source
   * @return String $root
   */
  private function getRoot()
  {
    global $config;
    $root = $config['fileroot'];

    $root  = $this->registry->getObject('fce')->ConvertToDirectoryPathName( $root );
    return $root;
  }
}