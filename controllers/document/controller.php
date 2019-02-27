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
					case 'listTodo':
						$this->listTodo();
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
					case 'addFolder':
						$this->addFolder();
						break;
					case 'deleteFolder':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->deleteFolder( $ID );
						break;
					case 'deleteFile':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->deleteFile( $ID );
						break;
					case 'modify':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->modifyDocument($ID);
						break;
					case 'slideshow':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->slideshow($ID);
						break;
					case 'WS':
						// Je voláno jako XMLHttpRequest (function.js) a pouze loguje zobrazené položky
						switch ($urlBits[2]) {
							case 'logView':
								$ID = isset($urlBits[3]) ? $urlBits[3] : '';
								$result = $this->wsLogDocumentView($ID);
								exit($result);		
								break;
							case 'setRemind':
								$ID = isset($urlBits[3]) ? $urlBits[3] : '';
								if($this->perSet > 0)
									$result = $this->wsSetRemindEntry($ID);
								else
									$result = 'Error';
								exit($result);
								break;
						}
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
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámého dokumentu",'dmsentry','');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-document.tpl.php', 'footer.tpl.php');
	}
	private function error( $message )
	{
		$this->registry->getObject('log')->addMessage("Chyba: $message",'dmsentry','');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
	}

	private function slideshow( $ID )
	{
		global $config;
		$root = $config['webserver'];

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		$entry = $this->model->getData();
		if( $this->model->isValid() )
		{
			if($entry['isImage'] == true)
			{
				$this->registry->getObject('db')->initQuery('dmsentry');
				$this->registry->getObject('db')->setFilter('Parent',$entry['EntryNo']);
				$this->registry->getObject('db')->setFilter('Type',30);
				$this->registry->getObject('db')->setFilter('Multimedia','image');
				if ($this->registry->getObject('db')->findSet())
				{
					$images = $this->registry->getObject('db')->getResult();
					$i = 0;
					foreach ($images as $image) {
						$i++;
						$ID = $image['ID'];
						$data[] = array('index' => $i);
						$link = $root.$image['Name'];
						$link = str_replace(DIRECTORY_SEPARATOR,'/', $link); 

						$img[] = array('imagepath' => $link, 'Title' => $image['Title']);
					}
					$CacheId = $this->registry->getObject('db')->cacheData($img);
					$this->registry->getObject('template')->getPage()->addTag( 'ImageList', array('DATA', $CacheId));
					$CacheId = $this->registry->getObject('db')->cacheData($data);
					$this->registry->getObject('template')->getPage()->addTag( 'IndexList', array('DATA', $CacheId));

					// Breds navigation
					$breads = $entry['breads'];
					$this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );

					$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'slideshow.tpl.php', 'footer.tpl.php');
					return;
				}
			}
		}
		$this->documentNotFound();	
	}

	private function listDocuments( $ID )
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		$entry = $this->model->getData();
		if( $this->model->isValid() )
		{
			if(($entry['Type'] == 30) || ($entry['Type'] == 35)){
				$this->model = new Entry( $this->registry, $entry['parentID'] );
				$entry = $this->model->getData();
			}
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
					",Remind,RemindClose,RemindFromDate,RemindLastDate,Content,RemindResponsiblePerson,RemindUserID,RemindContactID ".	
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 AND parent=".$entry['EntryNo']." AND Type IN (10,30,35,40) ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"ORDER BY Remind DESC,Type,Title ";
		$showBreads = true;
		$pageTitle = '';
		$template = '';
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu souborů a složek','DmsEntry',$ID);
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	
	
	private function listTodo( )
	{
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",Remind,RemindClose,RemindFromDate,RemindLastDate,Content,RemindResponsiblePerson,RemindUserID,RemindContactID ".	
					",(DATE_FORMAT(RemindLastDate,'%Y-%m-%d') < CURDATE()) as term ".
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 AND Remind = 1 ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"ORDER BY RemindLastDate,Title ";
		$entry = null;
		$showFolder = '';
		$showBreads = false;
		$pageTitle = '';
		$template = 'list-entry-todo.tpl.php';
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu úkolů.','DmsEntry','');
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
				",Remind,RemindClose,RemindFromDate,RemindLastDate,Content,RemindResponsiblePerson,RemindUserID,RemindContactID ".	
				"FROM ".$this->prefDb."DmsEntry ".
			   	"WHERE Archived = 0 AND NewEntry = 1 AND Type = 30  ".
			   	"AND PermissionSet <= $this->perSet ".
			   	"ORDER BY Level,Parent,Type,Title" ;
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['NewDocument'].'</h3>';
		$template = '';
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu souborů a složek','DmsEntry','');
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
				",Remind,RemindClose,RemindFromDate,RemindLastDate,Content,RemindResponsiblePerson,RemindUserID,RemindContactID ".
				"FROM ".$this->prefDb."DmsEntry ".
			   	"WHERE Archived = 1 AND Type = 30 ".
			   	"AND PermissionSet <= $this->perSet ".
			   	"ORDER BY Level,Parent,Type,LineNo" ;
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['Archive'].'</h3>';
		$template = 'list-entry-archive.tpl.php';
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu souborů a složek','DmsEntry','');
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
		$searchText = str_replace('*','',$searchText);
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",Remind,RemindClose,RemindFromDate,RemindLastDate,Content,RemindResponsiblePerson,RemindUserID,RemindContactID ".
					"FROM ".$this->prefDb."DmsEntry ".
					"WHERE Archived = 0 AND Type IN (20,25,30,35) ".
					//"AND MATCH(Title) AGAINST ('*".$searchText."*' IN BOOLEAN MODE) ".
					"AND Title like '%".$searchText."%' ".
					"AND PermissionSet <= $this->perSet ".
					"ORDER BY Remind DESC,Title ";
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['Archive'].'</h3>';
		$template = 'list-entry-resultsearch.tpl.php';
		$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', $searchText );
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu souborů a složek','DmsEntry','');
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	
	
	private function viewDocument( $ID )
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$this->registry->getObject('log')->addMessage("Zobrazení ".$entry['Name'],'DmsEntry',$ID);
		}
		else
		{
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
				$newUrl = ($_POST['newUrl'] !== null) ? $_POST['newUrl'] : '';
				$newRemind = ($_POST['newRemind'] !== null) ? ($_POST['newRemind'] === '') ? 0 : 1 : 0;
				$newRemindFromDate = ($_POST['newRemindFromDate'] !== '') ? $_POST['newRemindFromDate'] : 'NULL';
				$newRemindLastDate = ($_POST['newRemindLastDate'] !== '') ? $_POST['newRemindLastDate'] : 'NULL';
				$newRemindResponsiblePerson = ($_POST['newRemindResponsiblePerson'] !== '') ? $_POST['newRemindResponsiblePerson'] : '';
				if ($newTitle)
				{
					$newTitle = $this->registry->getObject('db')->sanitizeData($newTitle);
					
					// Update
					$changes['Title'] = $newTitle;
					$changes['Url'] = $newUrl;
					$changes['Remind'] = $newRemind;
					if($newRemind == 1)
						$changes['RemindClose'] = 0;
					$changes['RemindFromDate'] = $newRemindFromDate;
					$changes['RemindLastDate'] = $newRemindLastDate;
					$changes['RemindResponsiblePerson'] = $newRemindResponsiblePerson;
					$condition = "ID = '$ID'";
					$this->registry->getObject('log')->addMessage("Zobrazení a aktualizace dokumentu",'contact',$ID);
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

	private function addFolder()
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

	private function deleteFolder( $ID )
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
				if($entry['isNote'] || $entry['isBlock'])
				{
					$message = 'Nelze odstranit blok s poznámkami.';
				}
				else
				{
					$condition = "ID = '$ID'";
					$data = array();
					$data['Archived'] = 1;
					$this->registry->getObject('log')->addMessage("Uzavření bloku",'dmsentry',$ID);
					$this->registry->getObject('db')->updateRecords('dmsentry',$data,$condition);			
					$this->listDocuments($entry['parentID']);
					return;
				}
			}
		}				
		$this->error($message);
	}
		
	private function deleteFile( $ID )
	{
		global $caption;

		$message = $caption['msg_noalloved'];
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($this->perSet > 0) AND $this->model->isValid() )
		{
			$entry = $this->model->getData();
			if($entry['Type'] != 35)
			{
				$message = 'Odstranit lze pouze prázdnou poznámku.';
			}
			else
			{
				$condition = "ID = '$ID'";
				$data['Archived'] = 1;
				$this->registry->getObject('log')->addMessage("Výmaz poznámky",'dmsentry',$ID);
				$this->registry->getObject('db')->updateRecords('dmsentry',$data,$condition);			
					
				$this->listDocuments($entry['parentID']);
				return;
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

	private function wsLogDocumentView( $ID )
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$this->registry->getObject('log')->addMessage("Zobrazení ".$entry['Name'],'DmsEntry',$ID);
			return 'OK';
		}
		return 'Error';
	}	

	private function wsSetRemindEntry( $ID )
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			if ($entry['Remind'] == 1)
			{
				$changes['Remind'] = 0;
				$changes['RemindClose'] = 1;
				$this->registry->getObject('log')->addMessage("Úkol vyřízen",'dmsentry',$ID);
			}
			// Update
			$condition = "ID = '$ID'";
			$this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);
			return 'OK';
		}
		return 'Error';
	}	
}
?>