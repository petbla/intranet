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
							$this->searchDocument($searchText);
						}
						break;
					default:
						break;
				}
			}
			$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'Document/search');
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
			$sqlFolders = "SELECT ID,title,type,ModifyDateTime FROM DmsEntry ".
						  "WHERE Archived = 0 AND parent={$entryNo} AND Type = 20 ".
						  "AND PermissionSet <= $perSet ".
			              "ORDER BY Type,Title";
			$sqlFiles = "SELECT ID,title,Name,type,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry ".
						"WHERE Archived = 0 AND parent={$entryNo} AND Type = 30 ".
						"AND PermissionSet <= $perSet ".
			            "ORDER BY Type,Title";
		}
		else
		{
			$sqlFolders = "SELECT ID,title,type,Parent,ModifyDateTime FROM DmsEntry ".
						  "WHERE Archived = 0 AND parent=0 AND Type = 20 ".
						  "AND PermissionSet <= $perSet ".
				          "ORDER BY Type,Title ";
			$sqlFiles = "SELECT ID,title,Name,type,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry  ".
						"WHERE Archived = 0 AND parent=0 AND Type = 30 ".
						"AND PermissionSet <= $perSet ".
						"ORDER BY Type,Title ";
			$entryNo = 0;
		}
		$breads = $this->getBreads($ID);
		$cache = $this->registry->getObject('db')->cacheQuery( $sqlFolders );
		$isHeader = true;
		$isFolder = ($this->registry->getObject('db')->isEmpty($cache) == false);
		$isFiles = true;
		$isFooter = true;
		if ($isFolder){
			$this->registry->getObject('template')->getPage()->addTag( 'FolderItems', array( 'SQL', $cache ) );
		}
		$this->registry->getObject('document')->listDocuments($sqlFiles, $entryNo,'',$isHeader, $isFolder, $isFiles, $isFooter,$breads);
	}	

	private function searchDocument( $searchText )
	{
		global $config, $caption;
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$searchText = htmlspecialchars($searchText);
		$sqlFiles = "SELECT ID,title,Name,type,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry ".
					"WHERE Archived = 0 AND Type BETWEEN 20 AND 30 AND MATCH(Title) AGAINST ('*$searchText*' IN BOOLEAN MODE) ".
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
			$filePath = $this->model->getLink();
			$filePath = '//petblanb/Users/petbla/Desktop/FileServer/Korespondence/Doležal - doklad o platbě pronájmu plochy pro kolotoče.pdf';		
			$filePath = iconv("utf-8","windows-1250",$filePath);
			$filePath = "file:///C:/Users/petbla/Desktop/FileServer/_Zkratky.txt";
			$filePath = "file:///C:/Users/petbla/Desktop/FileServer/Korespondence/Doležal%20-%20doklad%20o%20platbě%20pronájmu%20plochy%20pro%20kolotoče.pdf";
			$filePath = "\\\\petblanb\\c$\Users\\petbla\\Desktop\\FileServer\\_ZkratkyXXXXX.txt";

			$this->registry->getObject('document')->viewDocument($document,$breads,$filePath);
		}
		else
		{
			// File Not Found
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		}
	}	

}
?>