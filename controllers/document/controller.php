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
	

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

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
	
	public function getBreads ($ID)
	{
		global $config, $caption;

		$title = $caption['home_page'];
		$href = "index.php?page=document/list";
		$breads = "<a href='$href'>$title</a>";
		
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$names = explode(DIRECTORY_SEPARATOR,$entry['Name']);
			$name = '';
			foreach ($names as $idx => $title) {
				$name .= ($name != '') ? DIRECTORY_SEPARATOR:'';
				$name .= $title;
				$breads .= ($breads != '') ? ' > ':'';
				$ID = $this->registry->GetObject('file')->getIdByName($name);
				$breads .= "<a href='$href/$ID'>$title</a> ";
			}
		}
		return $breads;
	}


	private function listDocuments( $ID )
	{
		global $config, $caption;
		
		$perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$level = $entry['Level'];
			$entryNo = $entry['EntryNo'];
			$parent = $entry['Parent'];
			$name = $entry['Name'];
			$this->registry->setLevel($level);
			$this->registry->setEntryNo($entryNo);		
		}
		else
		{
			$parent = 0;
			$entryNo = 0;
		}
		// Folders
		$sql = "SELECT ID,title,Name,type,Parent,ModifyDateTime FROM DmsEntry ".
					"WHERE Archived = 0 AND parent={$entryNo} AND Type IN (20,25) ".
					"AND PermissionSet <= $perSet ".
					"ORDER BY Type,Title";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$isFolder = ($this->registry->getObject('db')->isEmpty($cache) == false);
		if ($isFolder){
			$this->registry->getObject('template')->getPage()->addTag( 'FolderItems', array( 'SQL', $cache ) );
		}

		$breads = $this->getBreads($ID);
		$isHeader = true;
		$isFiles = true;
		$isFooter = true;

		// Files (and Comment, Headers, Footers)
		$sql = "SELECT ID,title,Name,type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry ".
				  "WHERE Archived = 0 AND parent={$entryNo} AND Type IN (10,30,35,40) ".
				  "AND PermissionSet <= $perSet ".
				  "ORDER BY Type,Title";
		$this->registry->getObject('document')->listDocuments($sql, $entryNo,'',$isHeader, $isFolder, $isFiles, $isFooter,$breads);
	}	

	private function searchDocuments( $searchText )
	{
		global $config, $caption;
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$searchText = htmlspecialchars($searchText);
		$sqlFiles = "SELECT ID,title,Name,type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry ".
					"WHERE Archived = 0 AND Type IN (20,25,30,35) AND MATCH(Title) AGAINST ('*$searchText*' IN BOOLEAN MODE) ".
					"AND PermissionSet <= $perSet ".
					"ORDER BY Name";
		$isHeader = true;
		$isFolder = false;
		$isFiles = true;
		$isFooter = true;
		$this->registry->getObject('document')->listDocuments($sqlFiles,null,'',$isHeader, $isFolder, $isFiles, $isFooter,'','list-entry-resultsearch.tpl.php');
	}	

	private function viewDocument( $ID )
	{
		global $config, $caption;
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

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
		global $config, $caption;
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($perSet > 0) AND $this->model->isValid() )
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