<?php
/**
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 */
class Documentcontroller{
	
	private $registry;
	private $model;
	private $perSet;
	private $prefDb;

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		global $config, $caption;
		$this->registry = $registry;
		$this->perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        $this->prefDb = $config['dbPrefix'];
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     
			if($this->perSet == 0)
			{
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
				return;
			}

			if( !isset( $urlBits[1] ) )
			{		
        		$this->listDocuments('');
			}
			else
			{
				$ID = '';
				$searchText = '';
				switch ($urlBits[1]) {
					case 'list':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->listDocuments($ID);
						break;
					case 'listNew':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->listNewDocuments($ID);
						break;
					case 'listArchive':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->listArchiveDocuments($ID);
						break;
					case 'view':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->viewDocument($ID);
						break;
					case 'search':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchDocuments($searchText);
						}
						break;
					case 'addFiles':
						$this->addFiles();
						break;
					case 'addfolder':
						if ( $this->registry->getObject('authenticate')->getPermissionSet() > 0 )
						{
							$this->addfolder();
						}
						break;
					case 'modify':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->modifyDocument($ID);
						break;
					default:
						break;
				}
			}
		}
	}
	
	/**
	 * @return void
	 */
	private function documentNotFound()
	{
		//TOTO: doplnit šablonu
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-document.tpl.php', 'footer.tpl.php');
	}
	
	private function listDocuments( $ID )
	{
		global $caption;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		$entry = $this->model->getData();
		if( $this->model->isValid() )
		{
			$this->registry->setLevel($entry['Level']);
			$this->registry->setEntryNo($entry['EntryNo']);		
		}
		// Folders
		$sql = "SELECT ID,Title,Name,Type,Parent,ModifyDateTime FROM ".$this->prefDb."DmsEntry ".
					"WHERE Archived = 0 AND parent=".$entry['EntryNo']." AND Type IN (20,25) ".
					"AND PermissionSet <= $this->perSet ".
					"ORDER BY Type,Title";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$showFolder = ($this->registry->getObject('db')->isEmpty($cache) == false);
		if ($showFolder){
			$this->registry->getObject('template')->getPage()->addTag( 'FolderItems', array( 'SQL', $cache ) );			
		}
		$sql = "SELECT ID,Title,Name,Type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
		  		  "FROM ".$this->prefDb."DmsEntry ".
				  "WHERE Archived = 0 AND parent=".$entry['EntryNo']." AND Type IN (10,30,35,40) ".
				  "AND PermissionSet <= $this->perSet ".
				  "ORDER BY Type,Title";
		$showBreads = true;
		$template = '';
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$template);
	}	
	
	private function listNewDocuments( $ID )
	{
    	$sql = "SELECT ID,Title,Name,Type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
			   "FROM ".$this->pref."DmsEntry ".
			   "WHERE Archived = 0 AND NewEntry = 1 AND Type = 30  ".
			   "AND PermissionSet <= $this->perSet ".
			   "ORDER BY Level,Parent,Type,Title" ;
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$this->registry->getObject('document')->listDocuments($sql,null,'<h3>Nové dokumenty</h3>',false,false,true,false, '');
	}	

	private function listArchiveDocuments( $ID )
	{
    	$sql = "SELECT ID,Title,Name,Type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
			   "FROM ".$this->pref."DmsEntry AS d ".
			   "WHERE Archived = true AND NewEntry = 0 AND Type = 30 ".
			   "AND PermissionSet <= $this->perSet ".
			   "ORDER BY Level,Parent,Type,LineNo" ;
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$this->registry->getObject('document')->listDocuments($sql,null,'<h3>Archív dokumentů</h3>',false,false,true,false, '', 'list-entry-archive.tpl.php');
	}	

	private function searchDocuments( $searchText )
	{
		global $caption;

		$searchText = htmlspecialchars($searchText);
		$sqlFiles = "SELECT ID,Title,Name,Type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
		     	    "FROM ".$this->prefDb."DmsEntry ".
					"WHERE Archived = 0 AND Type IN (20,25,30,35) ".
					//"AND MATCH(Title) AGAINST ('*".$searchText."*' IN BOOLEAN MODE) ".
					"AND Title like '%".$searchText."%' ".
					"AND PermissionSet <= $this->perSet ".
					"ORDER BY Title";
		$isHeader = true;
		$isFolder = false;
		$isFiles = true;
		$isFooter = true;
		$this->registry->getObject('document')->listDocuments($sqlFiles,null,'',$isHeader, $isFolder, $isFiles, $isFooter,'','list-entry-resultsearch.tpl.php');
	}	

	private function viewDocument( $ID )
	{
		global $caption;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$document = $this->model->getData();
			$breads = $this->getBreads($ID);			
			$filePath = $this->model->getlinkToFile();
			$filePath = iconv("utf-8","windows-1250",$filePath);
			if ($document['Type'] == 20)
			{
				$this->listDocuments($ID);
			}
			else
			{
				$this->registry->getObject('document')->viewDocument($document,$breads,$filePath);
			}
		}
		else
		{
			// File Not Found
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		}
	}	

	private function modifyDocument( $ID )
	{
		global $caption;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($this->perSet > 0) AND $this->model->isValid() )
		{
			$document = $this->model->getData();
			$newTitle = ($_POST['newTitle'] !== null) ? $_POST['newTitle'] : '';
			if ($newTitle)
			{
				$newTitle = $this->registry->getObject('db')->sanitizeData($newTitle);
				
				// Update
				$changes['Title'] = $newTitle;
				$condition = "ID = '$ID'";
				$this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);
			}
			$ID = '';
			$this->registry->getObject('db')->initQuery('dmsentry');
			$this->registry->getObject('db')->setFilter('EntryNo',$document['Parent'] );
			if ($this->registry->getObject('db')->findFirst())
			{
				$document = $this->registry->getObject('db')->getResult();
				$ID = $document['ID'];
			}
		}
		else
		{
			$ID = '';
		}
		$this->listDocuments($ID);
	}	

	private function addfolder()
	{
		global $caption;
		
		if(! $this->registry->getObject('authenticate')->isAdmin())
		{
			$message = $caption['new_user_failed'];
		}
		else
		{
			$message = $caption['msg_folderNotCreated'];
			$action = '';
			foreach ($_POST as $key => $value) {
				$pos = strpos($key,'Folder');
				if($pos !== false)
					$action = 'addFolder';
				$pos = strpos($key,'Block');
				if($pos !== false )
					$action = 'addBlock';
			}
			if (isset($_POST['fld_name']) && isset($_POST['root']))
			{
				$fullName = $_POST['root'];
				if($action == 'addBlock')
				{
					$item = $_POST['fld_name'];
					$EntryNo = $this->registry->getObject('file')->findBlock($fullName,$item);
					if($EntryNo === -1)
					{
						$message = $caption['msg_blockExist'];
					}
					else if($EntryNo !== 0)
					{
						$message = $caption['NewBlockCreated'];
					}
					// toto
				}
				if($action == 'addFolder')
				{
					if ($fullName[strlen($fullName)-1] != DIRECTORY_SEPARATOR)
					{
						$fullName .= DIRECTORY_SEPARATOR;
					}
					$fullName .= $_POST['fld_name'];
					$fullName = iconv("utf-8","windows-1250",$fullName);
					if(!file_exists($fullName))
					{
						if(mkdir($fullName, 0777, true))
						{
							// create succes
							$EntryNo = $this->registry->getObject('file')->findItem($fullName);
							
							$message = $caption['NewFolderCreated'];
						}
					}
					else
					{
						$message = $caption['msg_folderExists'];
					}
				}
			}
		}

		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
	}

	private function addFiles( )
	{
		//$files = ($_POST('files') !== null) ? $_POST('files') : null;
		//328B694F-229F-4D80-8730-F171513611DD

		if(isset($_FILES["fileToUpload"]) && isset($_POST['path']) && isset($_POST["submit_x"]) && isset($_POST['ID']) ) {
			$ID = $_POST['ID'];
			$path = $_POST['path'];
			$path .= ($path[strlen($path)-1] != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';
			$files = $_FILES['fileToUpload'];
			if(!empty($files))
			{
				$files = $this->reArrayFiles($files);
				foreach($files as $file)
				{
					$target_file = $path . basename($file["name"]);
					$target_file = iconv("utf-8","windows-1250",$target_file);
					move_uploaded_file($file['tmp_name'],$target_file);
					$EntryNo = $this->registry->getObject('file')->findItem($target_file);
				}
			}	
		}
		$this->listDocuments($ID);
	}

	private function reArrayFiles($file)
	{
		$file_ary = array();
		$file_count = count($file['name']);
		$file_key = array_keys($file);
		
		for($i=0;$i<$file_count;$i++)
		{
			foreach($file_key as $val)
			{
				$file_ary[$i][$val] = $file[$val][$i];
			}
		}
		return $file_ary;
	}
}
?>