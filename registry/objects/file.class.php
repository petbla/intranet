<?php
/**
 * Správce souborů
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    27.11.2018 
 */

class file {

  private $lastError;
  private $registry;
  private $model;
  private $errorMessage;

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

    return;

    $directories[]  = $this->getFileRoot(); 

    
    $paret = 0;
    $level = 0;
    while (count($directories)) { 
      $directoryNamePath  = array_pop($directories); 
      $directoryNamePath  = $this->registry->getObject('fce')->ConvertToSharePath( $directoryNamePath,false);

      if ($handle = opendir($directoryNamePath)) { 
        while (false !== ($fileName = readdir($handle))) 
        { 
          if ($fileName == '.' |0| $fileName == '..') 
          { 
            continue; 
          };
          $fullItemPath = $directoryNamePath.$fileName;
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
    // Fileroot           =  \\petbla\c$\Users\petr\Desktop\OBECNTB\FileServer\
    // $entry['Name']     =  Stavby RD\RD Šáchovi 313
    $directoryNamePath = $this->getFileRoot() . $entry['Name'];
    // $directoryNamePath =  \\petbla\c$\Users\petr\Desktop\OBECNTB\FileServer\Stavby RD\RD Šáchovi 313

    $directoryNamePath  = $this->registry->getObject('fce')->ConvertToSharePath( $directoryNamePath,false);
    // $directoryNamePath =  //petbla/c$/Users/petr/Desktop/OBECNTB/FileServer/Stavby RD/RD Šáchovi 313/

    if ($handle = opendir($directoryNamePath)) { 
      while (false !== ($fileName = readdir($handle))) 
      { 
        // $fileName = 	Šáchovi - Vyjádření k PD.docx
        if ($fileName == '.' |0| $fileName == '..') 
        { 
          continue; 
        };
        
        $fullItemPath = $directoryNamePath.$fileName;
        // $fullItemPath = //petbla/c$/Users/petr/Desktop/OBECNTB/FileServer/Stavby RD/RD Šáchovi 313/Šáchovi - Vyjádření k PD.docx
        
        $name  = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $fullItemPath,false );   
        $name = str_replace(str_replace('http:','',$this->getFileRoot()),'',$name);      
        // $name = Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx

        // Find $name in table 'dnsentry'
        $this->registry->getObject('db')->initQuery('dmsentry','ID');
        $sanitizename = $this->registry->getObject('db')->sanitizeData($name);
        $this->registry->getObject('db')->setCondition( 'Name="'.$sanitizename.'" AND Archived=0' );
        if($this->registry->getObject('db')->findFirst() == false)
        {
          // Convert $fullItemPath to CodePage of application (UTF-8)
          $winFullItemPath = $this->Convert2SystemCodePage($fullItemPath);
          // $winFullItemPath = //petbla/c$/Users/petr/Desktop/OBECNTB/FileServer/Stavby RD/RD Šáchovi 313/Šáchovi - Vyjádření k PD.docx

          // Add entry 
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
        $item = $this->getItemFromName($entry['Name']);
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
    // $winFullItemPath = //petbla/c$/Users/petr/Desktop/OBECNTB/FileServer/Stavby RD/RD Šáchovi 313/Šáchovi - Vyjádření k PD.docx

    $fullItemPath = $this->Convert2CoreCodePage($winFullItemPath);
    // $fullItemPath = //petbla/c$/Users/petr/Desktop/OBECNTB/FileServer/Stavby RD/RD Šáchovi 313/Šáchovi - Vyjádření k PD.docx

    $name  = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $fullItemPath,false );    // formát Xxxxx\Ddddd\Aaaaa
    // $name = \\petbla\c$\Users\petr\Desktop\OBECNTB\FileServer\Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx
    $name = str_replace(str_replace('http:','',$this->getFileRoot()),'',$name);
    // $name = Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx
  
    if ($name === '')
    {
      return 0;
    }
    $item = $this->getItemFromName($name, $isDir);
    
    $this->registry->getObject('db')->initQuery('dmsentry','EntryNo,ID,Name,Title,FileExtension,Multimedia');
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
    
    // Insert NEW 
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
    if(file_exists($item['WinFullName'])){
      $data['ModifyDateTime'] = date("Y-m-d H:i:s", filemtime($item['WinFullName'])); // datum a čas změny
    }else{
      $data['ModifyDateTime'] = date("Y-m-d H:i:s"); 
    }
    $data['PermissionSet'] = 1;
    $data['Url'] = '';
    $data['Multimedia'] = $this->getMultimediaType(strtolower($data['FileExtension']));

    if ($data['Type'] == 20){
      $dirPath = $this->getFileRoot().$item['Name'];
      if(!file_exists($dirPath)){
        mkdir($dirPath);
      }
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
    $fullParent  = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $fullParent,false );    
    $parentPath = str_replace($this->getFileRoot(),'',$fullParent);
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
  
  public function addFile( $SourcePath, $parentEntryNo, $filename, $title )
	{
	  global $config;

    $parentEntry = $this->getEntry($parentEntryNo);
    if ($parentEntry == NULL){
      $this->lastError = "Cílová složka EntryNo='$parentEntryNo' neexistuje.";
      return null;
    }      
    
    if ($parentEntry['Type'] != 20){
      $this->lastError = "Cíl kopie musí být složka.";
      return null;
    }      
    
    if (!file_exists($SourcePath)){
      $this->lastError = "Zdrojový soubor $SourcePath nenalezen.";
      return null;
    }
       
    $SourcePath =  $this->Convert2SystemCodePage($SourcePath);
    $Extension = pathinfo($SourcePath,PATHINFO_EXTENSION);
    
    $UrlParentPath = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $parentEntry['Name'],true );
		$UrlName = $UrlParentPath.$filename;
    
    // Copy file from source to Parentfolder-destination  
    $root = str_replace('http:','',$this->getFileRoot());
    $UrlFullFileName =  $root.$UrlName.'.'.$Extension;
    $UrlFullFileName =  $this->Convert2SystemCodePage($UrlFullFileName);

    if (file_exists($UrlFullFileName)){
      $this->lastError = "Cílový soubor $UrlFullFileName již existuje. Zadejte jinmý název.";
      return null;
    }

    try {
      copy($SourcePath, $UrlFullFileName); 
      if (!file_exists($UrlFullFileName)){
        $this->lastError = "Kopie z $SourcePath do $UrlFullFileName skončil chybou.";
        return null;
      }
      if(!unlink($SourcePath)){ 
        $this->errorMessage = "Původní soubor $SourcePath nelze odstranit. Musí být odstraněn ručně.";
      }

    } catch (Exception $e) {
      $copyError = 'Kopírování skončilo chybou: ' + $e->getMessage();
      $this->lastError = $copyError;
    };
    if($this->lastError != '')
      return null;

    // Insert new entry  
    $UrlFullFileName = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $UrlFullFileName,false );
    $UrlFullFileName = $this->Convert2SystemCodePage($UrlFullFileName);

    $EntryNo = $this->findItem($UrlFullFileName);
    $entry = $this->getEntry($EntryNo); 

    $UrlName = str_replace('\\','/',$UrlName);
    $entry['DestinationPath'] = $this->registry->getObject('db')->sanitizeData($config['webroot'].$UrlName.'.'.$Extension);
  
    return $entry;
	}

  public function newNote ( $parentEntry, $Title )
	{
		// Insert NEW Note
		$data = array();
		$data['ID'] = $this->registry->getObject('fce')->GUID();
		$data['Level'] = $parentEntry['Level'] + 1;
		$data['Parent'] = $parentEntry['EntryNo'];
		$data['Path'] = $this->registry->getObject('db')->sanitizeData($parentEntry['Name']);
		$data['Type'] = 35;
		$data['LineNo'] = $this->getNextLineNo($data['Parent']);
		$data['Title'] = $Title; 
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

  public function getItemFromName( $name , $isDir = false)
  {
    global $config;
    
    // $name = Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx

    $root = str_replace('http:','',$this->getFileRoot());
    // $root = 	\\petbla\c$\Users\petr\Desktop\OBECNTB\FileServer

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
    
    $item['Name'] = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $name,false );    
    // => Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx

    $item['FullName'] =  $root.$item['Name'];
    // => \\petbla\c$\Users\petr\Desktop\OBECNTB\FileServer\Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx

    $item['WinFullName'] =  $this->Convert2SystemCodePage($item['FullName']);

    if (!$isDir)
    {
      if(!is_file($item['WinFullName']))
      {
        $isDir = true;
      }
    }

    $arr = explode(DIRECTORY_SEPARATOR,$item['Name']);
    // $name = Stavby RD\RD Šáchovi 313\Šáchovi - Vyjádření k PD.docx
    // $arr[0] = Stavby RD
    // $arr[1] = RD Šáchovi 313
    // $arr[2] = Šáchovi - Vyjádření k PD.docx

    $item['Item'] = (count($arr) > 0) ? $arr[count($arr) - 1] : '';
    // => Šáchovi - Vyjádření k PD.docx

    if(count($arr) > 0)
    {
      array_pop($arr);
      $item['Parent'] = implode(DIRECTORY_SEPARATOR,$arr);
      // => Stavby RD\RD Šáchovi 313
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
    $name = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $name ,false);    

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

  public function getEntry( $EntryNo )
  {
    $entry = null;
    $this->registry->getObject('db')->initQuery('DmsEntry','');
    $this->registry->getObject('db')->setCondition("EntryNo = '$EntryNo'");
    if( $this->registry->getObject('db')->findFirst())
    {
      $entry = $this->registry->getObject('db')->getResult();
    }
    return $entry;
  }

  public function getEntryById( $ID )
  {
    $entry = null;
    $this->registry->getObject('db')->initQuery('DmsEntry','');
    $this->registry->getObject('db')->setCondition("ID='$ID'");
    if( $this->registry->getObject('db')->findFirst())
    {
      $entry = $this->registry->getObject('db')->getResult();
    }
    return $entry;
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
  public function getFileRoot()
  {
    global $config;
    $root = $config['fileroot'];

    $root  = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $root );
    return $root;
  }

  public function getLastError()
  {
    return $this->lastError;
  }
}