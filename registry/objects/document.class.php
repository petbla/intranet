<?php
/**
 * Správce dokumentů
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    27.11.2018 
 */

class document {
    
    # Any private var
    # private $justProcessed = false;
	
    public function __construct( $registry ) 
    {
        $this->registry = $registry;
    }
    
	public function listDocuments( $sql, $pageLink , $isHeader, $isFolder, $isFiles, $isFooter, $breads)
	{
		global $config, $caption;

        // Stránkování
        $cacheFull = $this->registry->getObject('db')->cacheQuery( $sql );
        $records = $this->registry->getObject('db')->numRowsFromCache( $cacheFull );
        $pageCount = (int) ($records / $config['maxVisibleItem']);
        $pageCount = ($records > $pageCount * $config['maxVisibleItem']) ? $pageCount + 1 : $pageCount;  
        $pageNo = ( isset($_GET['p'])) ? $_GET['p'] : 1;
        $pageNo = ($pageNo > $pageCount) ? $pageCount : $pageNo;
        $pageNo = ($pageNo < 1) ? 1 : $pageNo;
        $fromItem = (($pageNo - 1) * $config['maxVisibleItem']);    
        $navigate = $this->registry->getObject('template')->NavigateElement( $pageNo, $pageCount ); 
        $this->registry->getObject('template')->getPage()->addTag( 'navigate_menu', $navigate );
        $sql .= " LIMIT $fromItem," . $config['maxVisibleItem']; 
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        
        $this->registry->getObject('template')->getPage()->addTag( 'DocumentItems', array( 'SQL', $cache ) );
        $this->addIcons();
		$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
        $this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );
        
        if ($isFolder)
        {
            $this->registry->getObject('template')->addTemplateBit('folderitems', 'list-entry-folders.tpl.php');
        }
        else
        {
            $this->registry->getObject('template')->getPage()->addTag( 'folderitems', '' );
        }
        if ($isFiles)
        {
            $this->registry->getObject('template')->addTemplateBit('documentitems', 'list-entry-documents.tpl.php');
        }
        else
        {
            $this->registry->getObject('template')->getPage()->addTag( 'documentitems', '' );
        }
        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-document.tpl.php', 'footer.tpl.php');
    }	

    public function createCategoryMenu()
    {
        $entryNo = $this->registry->getEntryNo();
        if (!isset($entryNo))
        {
            $entryNo = 0;
        }
        $sql = "SELECT id as idCat,title as titleCat ,name,path as pathCat,
                       IF(EntryNo = $entryNo,'active','') as activeCat 
                       FROM dmsentry 
                       WHERE `level` = 0 AND `Type` = 20
                       ORDER BY Title";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        $cacheCategory = $this->registry->getObject('db')->cacheQuery( $sql );
        $this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'SQL', $cache ) );
    }

    public function addIcons()
    {
        global $config;
        $sql = "SELECT DISTINCT FileExtension FROM dmsentry WHERE FileExtension <> ''";
        $this->registry->getObject('db')->executeQuery( $sql );
        while( $data = $this->registry->getObject('db')->getRows() )
        {
            $ext = strtolower($data['FileExtension']);
            $img = substr($ext,0,3);
            $filename = "views/classic/images/icon/$img.png";
            $fullFilename = $_SERVER['DOCUMENT_ROOT'].'/intranet/'.$filename;
            if (!(file_exists($fullFilename)))
            {
                $filename = 'views/classic/images/icon/file.png';
            }
            $icon = "<img src='$filename' />";
            $this->registry->getObject('template')->getPage()->addTag( "icon$ext", $icon );
        }
        
        $icon20 = "<img src='views/classic/images/icon/folder.png' />";
        $icon30 = "<img src='views/classic/images/icon/file.png' />";
        $this->registry->getObject('template')->getPage()->addTag( 'icon20', $icon20 );
        $this->registry->getObject('template')->getPage()->addTag( 'icon30', $icon30 );
    }    
}
?>