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
				$this->error($caption['msg_unauthorized']);
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
						$this->listNewDocuments();
						break;
					case 'listArchive':
						$this->listArchiveDocuments();
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
						$this->addfolder();
						break;
					case 'deleteFolder':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->deleteFolder( $ID );
						break;
					case 'modify':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->modifyDocument($ID);
						break;
					default:
						$this->documentNotFound();
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
	private function error( $message )
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
	}

	private function listDocuments( $ID )
	{
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
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
		  		  "FROM ".$this->prefDb."DmsEntry ".
				  "WHERE Archived = 0 AND parent=".$entry['EntryNo']." AND Type IN (10,30,35,40) ".
				  "AND PermissionSet <= $this->perSet ".
				  "ORDER BY Type,Title";
		$showBreads = true;
		$pageTitle = '';
		$template = '';
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	
	
	private function listNewDocuments( )
	{
		global $caption;		
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry,'' );
		$entry = $this->model->getData();
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$showFolder = false;
    	$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
			   "FROM ".$this->pref."DmsEntry ".
			   "WHERE Archived = 0 AND NewEntry = 1 AND Type = 30  ".
			   "AND PermissionSet <= $this->perSet ".
			   "ORDER BY Level,Parent,Type,Title" ;
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['NewDocument'].'</h3>';
		$template = '';
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	

	private function listArchiveDocuments()
	{
		global $caption;		
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry,'' );
		$entry = $this->model->getData();
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$showFolder = false;
    	$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
			   "FROM ".$this->pref."DmsEntry AS d ".
			   "WHERE Archived = true AND NewEntry = 0 AND Type = 30 ".
			   "AND PermissionSet <= $this->perSet ".
			   "ORDER BY Level,Parent,Type,LineNo" ;
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['Archive'].'</h3>';
		$template = 'list-entry-archive.tpl.php';
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	

	private function searchDocuments( $searchText )
	{
		global $caption;		
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry,'' );
		$entry = $this->model->getData();
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$showFolder = false;
		$searchText = htmlspecialchars($searchText);
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
		     	    "FROM ".$this->prefDb."DmsEntry ".
					"WHERE Archived = 0 AND Type IN (20,25,30,35) ".
					//"AND MATCH(Title) AGAINST ('*".$searchText."*' IN BOOLEAN MODE) ".
					"AND Title like '%".$searchText."%' ".
					"AND PermissionSet <= $this->perSet ".
					"ORDER BY Title";
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['Archive'].'</h3>';
		$template = 'list-entry-resultsearch.tpl.php';
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	

	private function viewDocument( $ID )
	{
		global $caption;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$filePath = $this->model->getlinkToFile();
			$filePath = iconv("utf-8","windows-1250",$filePath);
			if (($entry['Type'] == 20) || ($entry['Type'] == 25))
			{
				$this->listDocuments($ID);
			}
			else
			{
				$this->registry->getObject('document')->viewDocument($entry,$filePath);
			}
		}
		else
		{
			// File Not Found
			$this->documentNotFound();
		}
	}	

	private function modifyDocument( $ID )
	{
		global $caption;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($this->perSet > 0) AND $this->model->isValid() )
		{
			$entry = $this->model->getData();
			
			if(isset($_POST['save']))
			{
				$newTitle = ($_POST['newTitle'] !== null) ? $_POST['newTitle'] : '';
				if ($newTitle)
				{
					$newTitle = $this->registry->getObject('db')->sanitizeData($newTitle);
					
					// Update
					$changes['Title'] = $newTitle;
					$condition = "ID = '$ID'";
					$this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);
				}
				$ID = $entry['parentID'];
			}
			elseif(isset($_POST['back']))			
			{
				$ID = $entry['parentID'];
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
				$pos = strpos($key,'Note');
				if($pos !== false )
					$action = 'addNote';
			}
			if (isset($_POST['fld_name']) && ($_POST['fld_name'] !== "") && isset($_POST['root']))
			{
				$fullName = $_POST['root'];
				if($action == 'addBlock')
				{
					$item = $_POST['fld_name'];
					$ID = $this->registry->getObject('file')->addBlock($fullName,$item);
					if($ID !== '')
					{
						$this->listDocuments($ID);
						return;
					}
					else
					{
						$message = $caption['NewBlockCreated'];
					}
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
							$ID = $this->registry->getObject('file')->getIdByEntryNo($EntryNo);
							$this->listDocuments($ID);
							return;
						}
					}
					else
					{
						$message = $caption['msg_folderExists'];
					}
				}
			}
			else
			{
				if($action == 'addNote')
				{
					// Create New Note
					$parentID = isset($_POST['parentID']) ? $_POST['parentID'] : '';
					if ($parentID !== '')
					{
						require_once( FRAMEWORK_PATH . 'models/entry/model.php');
						$this->model = new Entry( $this->registry, $parentID );
						if( ($this->perSet > 0) AND $this->model->isValid() )
						{
							$parentEntry = $this->model->getData();
							$ID = $this->registry->getObject('file')->newNote( $parentEntry );
							$this->listDocuments($parentID);
							return;
						}				
					}
				}

			}
		}
		$this->error($message);
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

	private function deletefolder( $ID )
	{
		global $caption;

		$message = $caption['msg_noalloved'];
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($this->perSet > 0) AND $this->model->isValid() )
		{
			$entry = $this->model->getData();
			if($entry['Type'] != 25)
			{
				$message = 'Odstranit lze pouze prázdný blok.';
			}
			else
			{
				if($entry['isNote'])
				{
					$message = 'Nelze odstranit blok s poznámkami.';
				}
				else
				{
					$this->listDocuments($entry['parentID']);
					return;
				}
			}
		}				
		$this->error($message);
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