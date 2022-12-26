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
	
    private $message = '';
    private $errorMessage = '';

    public function __construct( $registry ) 
    {
        $this->registry = $registry;
    }
    
     /**
     * Sestavení stránky
     * @return void
     */
    private function build( $template = 'page.tpl.php' )
    {
        // Category Menu
	    $this->registry->getObject('document')->createCategoryMenu();

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

        // Build page
        $this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
        $this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-document.tpl.php');
        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template , 'footer.tpl.php');
    }

    //public function listDocuments( $sql, $entryNo, $pageLink , $isHeader, $isFolder, $isFiles, $isFooter, $breads, $template = 'document-list.tpl.php')
    public function listDocuments( $entry, $showFolder, $sql, $showBreads, $template )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
        
        $template = ($template === '' ? 'document-list.tpl.php' : $template);
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		// Group records by Page
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );

		// Save SQL result to $cache (array type) AND modify record
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache ))
		{
            // Select Free Agenda Document No.
            $sql = "SELECT ID as AID, DocumentNo, Description FROM ".$pref."agenda ".
                " WHERE IFNULL(`EntryID`,'') = ''".
                " ORDER BY TypeID,DocumentNo";
            $cache2 = $this->registry->getObject('db')->cacheQuery( $sql );
            $this->registry->getObject('template')->getPage()->addTag( 'documentList', array( 'SQL', $cache2 ) );
            			
            while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
			{			
                $this->model = new Entry( $this->registry, $rec['ID'] );
                $entry = $this->model->getData();
                        
                $rec['ADocumentNo'] = $entry['ADocumentNo'];
                $rec['CreateDate'] = $entry['CreateDate'];

                $rec['dmsClassName'] = 'item';
                
                $rec['DocumentType'] = $entry['DocumentType'];

                $rec['viewFileCardID'] = 'viewFileCard'.$rec['ID'];					
                $rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
            
                $result[] = $rec;
            };

            $cache = $this->registry->getObject('db')->cacheData( $result );
            $this->registry->getObject('template')->getPage()->addTag( 'DocumentItems', array( 'DATA', $cache ) );
            $isEntries = true;            
		}else{            
            $isEntries = false;
        }                
        
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

        // Show Folders
        if ($showFolder)
        {
            $this->registry->getObject('template')->addTemplateBit('folders', 'document-list-folders.tpl.php');
        }
        else
        {
            $this->registry->getObject('template')->getPage()->addTag( 'folders', '' );
        }

        // Show result of SQL request
        if ($isEntries)
        {
            $this->registry->getObject('template')->addTemplateBit('documents', 'document-list-files.tpl.php');
        }
        else
        {
            $this->registry->getObject('template')->addTemplateBit('documents', 'document-list-addfiles.tpl.php');
            if (!$showFolder){
                $this->registry->getObject('template')->getPage()->addTag( 'message', '' );
                $template = 'list-entry-nodocuments.tpl.php';
            }
        }
        
        if ($perSet > 0)
        {
            $this->registry->getObject('template')->addTemplateBit('actionpanel', 'document-list-actions.tpl.php');
            $this->registry->getObject('template')->addTemplateBit('addFiles', 'document-list-addfiles.tpl.php');
            $this->registry->getObject('template')->addTemplateBit('editcardFile', 'document-entry-editcard.tpl.php');
            $this->registry->getObject('template')->addTemplateBit('editIcon', 'document-list-actionicons.tpl.php');
            if(($entry !== null) && ($entry['Type'] == 20))
                $this->registry->getObject('template')->addTemplateBit('addFolder', 'document-list-addFolder.tpl.php');
            else
                $this->registry->getObject('template')->getPage()->addTag( 'addFolder', '' );
            if(($entry !== null) && ($entry['isImage'] == true))
                $this->registry->getObject('template')->addTemplateBit('slideshow', 'document-list-slideshow.tpl.php');
            else
                $this->registry->getObject('template')->getPage()->addTag( 'slideshow', '' );
            $this->registry->getObject('template')->addTemplateBit('SelectedDocumentNo', 'document-edit-selectADocumentNo.tpl.php');        
        }
        else
        {
            $this->registry->getObject('template')->getPage()->addTag( 'actionpanel', '' );
            $this->registry->getObject('template')->getPage()->addTag( 'addFiles', '' );
            $this->registry->getObject('template')->getPage()->addTag( 'editcardFile', '' );
            $this->registry->getObject('template')->getPage()->addTag( 'editIcon', '' );
        }
        
        $mediaplayer = '';
        switch (true) {
            case $entry['isAudio']:
                $this->registry->getObject('template')->addTemplateBit('mediaplayer', 'list-entry-mediaplayer-audio.tpl.php');
                break;
            default:
                $this->registry->getObject('template')->getPage()->addTag( 'mediaplayer', '' );
                break;
        }

        $BaseUrl = $this->registry->getURLPath();
        $this->registry->getObject('template')->getPage()->addTag( 'BaseUrl', $BaseUrl );
        $this->registry->getObject('template')->addTemplateBit('remindIcon','document-list-remindicon.tpl.php');
        
        
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
                $parentPath = $this->registry->getObject('fce')->ConvertToSharePath( $parentPath );
                $this->registry->getObject('template')->getPage()->addTag('parentfoldername', $parentPath );
                $this->registry->getObject('template')->getPage()->addTag('parentID', $parentID );            
            }
        }        
        $this->build( $template );
    }	

	public function viewDocument( $entry, $filePath)
	{
        $breads = $entry['breads'];

        $this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );
        $this->registry->getObject('template')->getPage()->addTag( 'filePath', $filePath );
        $this->registry->getObject('template')->dataToTags( $entry, '' );
        $this->registry->getObject('template')->addTemplateBit('editcard', 'document-entry-editcard.tpl.php');
        
        // Add RemindState Caption
        $this->addRemindState();
        
		$this->build('document-entry-view.tpl.php');
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

    public function readFolders($ID)
    {
        global $config;
        $dmsentry = null;
        $level = 0;
        $parent = 0;
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        

        $entry = $this->getDmsentry($ID);
        if( $entry ){   
            $level = $entry['Level'] + 1;    
            $parent = $entry['EntryNo'];
        }

        $this->registry->getObject('db')->initQuery('dmsentry');
        if($parent > 0)
            $this->registry->getObject('db')->setFilter('Parent',$parent);
        $this->registry->getObject('db')->setFilter('Level',$level);
        $this->registry->getObject('db')->setFilter('Archived',0);
        $this->registry->getObject('db')->setFilter('Type',20);
        $this->registry->getObject('db')->setCondition("PermissionSet <= $perSet");
        $this->registry->getObject('db')->setOrderBy("Name");
        if ($this->registry->getObject('db')->findSet())
            $dmsentry = $this->registry->getObject('db')->getResult();			
        return $dmsentry;
    }

	private function getDmsentry($ID)
	{
		$entry = null;
        if($ID != ''){
            $this->registry->getObject('db')->initQuery('dmsentry');
            $this->registry->getObject('db')->setFilter('ID',$ID);
            if ($this->registry->getObject('db')->findFirst())
                $entry = $this->registry->getObject('db')->getResult();			
        }
        return $entry;
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
        
        $path = '"c:\temp"';
        $icon20 = "<img src='views/classic/images/icon/folder.png' ondblclick='openFolder($path);'/>";
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