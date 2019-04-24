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
    
    //public function listDocuments( $sql, $entryNo, $pageLink , $isHeader, $isFolder, $isFiles, $isFooter, $breads, $template = 'list-entry.tpl.php')
    public function listDocuments( $entry, $showFolder, $sql, $showBreads, $pageTitle, $template )
	{
		global $config, $caption;
        
        $template = ($template === '' ? 'list-entry.tpl.php' : $template);
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

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
        $isEntries = ($this->registry->getObject('db')->isEmpty( $cache )) ? false : true;
        if($isEntries)
        {
            $this->registry->getObject('template')->getPage()->addTag( 'DocumentItems', array( 'SQL', $cache ) );
        }
        
        $this->registry->getObject('template')->getPage()->addTag( 'pageTitle', $pageTitle );
        //
        // Show icons
        $this->addIcons();

        // Add RemindState Caption
        $this->addRemindState();
        
        // Breds navigation
        if ($entry !== null)
            $breads = $showBreads ? $entry['breads'] : '';
        else
            $breads = $showBreads;
        $this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );

        // Search BOX
        $this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');

        // Show Folders
        if ($showFolder)
        {
            $this->registry->getObject('template')->addTemplateBit('folders', 'list-entry-folders.tpl.php');
        }
        else
        {
            $this->registry->getObject('template')->getPage()->addTag( 'folders', '' );
        }

        // Show result of SQL request
        if ($isEntries)
        {
            $this->registry->getObject('template')->addTemplateBit('documents', 'list-entry-documents.tpl.php');
        }
        else
        {
            $this->registry->getObject('template')->addTemplateBit('documents', 'list-entry-addfiles.tpl.php');
            if (!$showFolder){
                $this->registry->getObject('template')->getPage()->addTag( 'message', '' );
                $template = 'list-entry-nodocuments.tpl.php';
            }
        }
        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');
        
        if ($perSet > 0)
        {
            $this->registry->getObject('template')->addTemplateBit('actionpanel', 'list-entry-actionpanel.tpl.php');
            if($template == 'list-entry-resultsearch.tpl.php')
            {
                $this->registry->getObject('template')->getPage()->addTag( 'addFiles', '' );
            }
            else
            {
                $this->registry->getObject('template')->addTemplateBit('addFiles', 'list-entry-addfiles.tpl.php');
            };
            $this->registry->getObject('template')->addTemplateBit('editcard', 'list-entry-editcard.tpl.php');
            $this->registry->getObject('template')->addTemplateBit('editIcon', 'list-entry-editicon.tpl.php');
            $this->registry->getObject('template')->getPage()->addTag( 'dmsClassName', 'item' );
            if(($entry !== null) && ($entry['Type'] == 20))
                $this->registry->getObject('template')->addTemplateBit('addFolder', 'list-entry-actionpanel-addFolder.tpl.php');
            else
                $this->registry->getObject('template')->getPage()->addTag( 'addFolder', '' );
            if(($entry !== null) && ($entry['isImage'] == true))
                $this->registry->getObject('template')->addTemplateBit('slideshow', 'list-entry-actionpanel-slideshow.tpl.php');
            else
                $this->registry->getObject('template')->getPage()->addTag( 'slideshow', '' );
        }
        else
        {
            $this->registry->getObject('template')->getPage()->addTag( 'actionpanel', '' );
            $this->registry->getObject('template')->getPage()->addTag( 'addFiles', '' );
            $this->registry->getObject('template')->getPage()->addTag( 'editcard', '' );
            $this->registry->getObject('template')->getPage()->addTag( 'editIcon', '' );
        }
        $BaseUrl = $this->registry->getURLPath();
        $this->registry->getObject('template')->getPage()->addTag( 'BaseUrl', $BaseUrl );
        $this->registry->getObject('template')->addTemplateBit('remindIcon','list-entry-remindicon.tpl.php');
        
        
        // Parent folder
        if($entry !== null)
        {
            $entryNo = $entry['EntryNo'];
            if (isset($entryNo) != null)
            {
                $parentPath = $config['fileroot'];
                $parentID = '';
                $this->registry->getObject('db')->initQuery('dmsentry');
                $this->registry->getObject('db')->setFilter('EntryNo',$entryNo);
                if (($entryNo > 0) && $this->registry->getObject('db')->findFirst())
                {
                    $data = $this->registry->getObject('db')->getResult();
                    $parentPath .=  $data['Name'];
                    $parentID = $data['ID'];
                }
                $parentPath = $this->registry->getObject('fce')->ConvertToSharePathName( $parentPath );
                $this->registry->getObject('template')->getPage()->addTag('parentfoldername', $parentPath );
                $this->registry->getObject('template')->getPage()->addTag('parentID', $parentID );            
            }
        }
    }	

	public function viewDocument( $entry, $filePath)
	{
        $breads = $entry['breads'];

        $this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );
        $this->registry->getObject('template')->getPage()->addTag( 'filePath', $filePath );
        $this->registry->getObject('template')->dataToTags( $entry, '' );
        $this->registry->getObject('template')->addTemplateBit('editcard', 'list-entry-editcard.tpl.php');
        
        // Add RemindState Caption
        $this->addRemindState();

        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'view-entry-document.tpl.php', 'footer.tpl.php');
    }

	public function editDocument( $entry, $filePath)
	{
        $breads = $entry['breads'];

        $this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );
        $this->registry->getObject('template')->getPage()->addTag( 'filePath', $filePath );
        $this->registry->getObject('template')->dataToTags( $entry, '' );
        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'edit-entry-document.tpl.php', 'footer.tpl.php');
    }

    public function createCategoryMenu()
    {
		global $config;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

        $entryNo = $this->registry->getEntryNo();
        if (!isset($entryNo))
        {
            $entryNo = 0;
        }
        $sql = "SELECT id as idCat,title as titleCat ,name,path as pathCat,Type,
                       IF(EntryNo = $entryNo,'active','') as activeCat 
                       FROM ".$pref."dmsentry 
                       WHERE `level` = 0 AND Archived = 0 AND `Type` BETWEEN 20 AND 25 AND PermissionSet <= $perSet
                       ORDER BY Type,Title";
        
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        $cacheCategory = $this->registry->getObject('db')->cacheQuery( $sql );
        $this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'SQL', $cache ) );
    }

    public function addIcons()
    {
        global $config;
        $pref = $config['dbPrefix'];

        $sql = "SELECT DISTINCT FileExtension FROM ".$pref."dmsentry WHERE FileExtension <> ''";
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
            $this->registry->getObject('template')->getPage()->addTag( "icon30$ext", $icon );
        }
        
        $icon20 = "<img src='views/classic/images/icon/folder.png' />";
        $icon30 = "<img src='views/classic/images/icon/file.png' />";
        $icon25 = "<img src='views/classic/images/icon/block.png' />";
        $icon35 = "<img src='views/classic/images/icon/note.png' />";
        $this->registry->getObject('template')->getPage()->addTag( 'icon20', $icon20 );
        $this->registry->getObject('template')->getPage()->addTag( 'icon', $icon20 );
        $this->registry->getObject('template')->getPage()->addTag( 'icon30', $icon30 );
        $this->registry->getObject('template')->getPage()->addTag( 'icon25', $icon25 );
        $this->registry->getObject('template')->getPage()->addTag( 'icon35', $icon35 );
    }    

    public function addRemindState()
    {
        global $caption;
        
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_', '' );
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_00_new', $caption['RemindState00'] );
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_10_process', $caption['RemindState10'] );
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_20_wait', $caption['RemindState20'] );
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_30_aprowed', $caption['RemindState30'] );
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_40_storno', $caption['RemindState40'] );
        $this->registry->getObject('template')->getPage()->addTag( 'RemindState_50_finish', $caption['RemindState50'] );
    }
}
?>