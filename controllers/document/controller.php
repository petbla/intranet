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
				if( !isset( $urlBits[2] ) )
				{		
					$ID = '';
				}
				else
				{
					$ID = $urlBits[2];
				}
				// TODO: edit, search
				switch( $urlBits[1] )
				{				
					case 'list':
						$this->listDocuments($ID);
						break;
					case 'view':
						$this->viewDocument($ID);
						break;
					default:				
						$this->listDocuments('');
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

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$level = $entry['Level'];
			$entryNo = $entry['EntryNo'];
			$name = $entry['Name'];
			$this->registry->setLevel($level);
			$this->registry->setEntryNo($entryNo);		
			$sqlFolders = "SELECT ID,title,type,ModifyDateTime FROM DmsEntry AS d ".
			              "WHERE d.Archived = 0 AND d.parent={$entryNo} AND Type = 20 AND Archived = false ".
			              "ORDER BY Type,Title";
			$sqlFiles = "SELECT ID,title,type,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry AS d ".
			            "WHERE d.Archived = 0 AND d.parent={$entryNo} AND Type = 30 AND Archived = false ".
			            "ORDER BY Type,Title";
		}
		else
		{
			$sqlFolders = "SELECT ID,title,type,ModifyDateTime FROM DmsEntry AS d ".
				          "WHERE d.Archived = 0 AND d.parent=0 AND Type = 20 AND Archived = false ".
				          "ORDER BY Type,Title ";
			$sqlFiles = "SELECT ID,title,type,ModifyDateTime,LOWER(FileExtension) as FileExtension FROM DmsEntry AS d ".
				        "WHERE d.Archived = 0 AND d.parent=0 AND Type = 30 AND Archived = false ".
						"ORDER BY Type,Title ";
		}
		$breads = $this->getBreads($ID);
		$cache = $this->registry->getObject('db')->cacheQuery( $sqlFolders );
		$this->registry->getObject('template')->getPage()->addTag( 'FolderItems', array( 'SQL', $cache ) );
		$this->registry->getObject('document')->listDocuments($sqlFiles,'',true,true,true,true,$breads);
	}	

	private function viewDocument( $ID )
	{
		global $config, $caption;

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
			$filePath = 'index.php';

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