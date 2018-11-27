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
    
	public function listDocuments( $sql, $pageLink)
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
		$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
	
        $this->registry->getObject('db')->executeQuery( $sql );			
        if( $this->registry->getObject('db')->numRows() != 0 )
        {
            $document = $this->registry->getObject('db')->getRows();
            switch ($document['type']) {
                case 20:
                    # Folder
                    $icon = "<img src='views/classic/images/icon/folder.png' />";
                    break;
                case 30:
                    # File
                    $icon = "<img src='views/classic/images/icon/file.png' />";
                    break;
                default:
                    $icon = '';
                    break;
            }
            $this->registry->getObject('template')->getPage()->addTag( 'title', $document['title'] );
            $this->registry->getObject('template')->getPage()->addTag( 'icon', $icon );
        }
                    
        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-document.tpl.php', 'footer.tpl.php');
    }	

    public function createCategoryMenu()
    {
        $entryNo = $this->registry->getEntryNo();

        $sql = "SELECT id as idCat,title as titleCat ,name,path as pathCat,
                       IF(EntryNo = $entryNo,'active','') as activeCat FROM dmsentry WHERE `level` = 0";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        $cacheCategory = $this->registry->getObject('db')->cacheQuery( $sql );
        
        $this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'SQL', $cache ) );
    }
}
?>