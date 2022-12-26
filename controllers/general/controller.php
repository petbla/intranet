<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Generalcontroller {

	private $registry;
	private $urlBits;
	private $message;
	private $errorMessage;
	
	public function __construct( Registry $registry, $directCall )
	{
		global $caption;

		$this->registry = $registry;
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		
		$perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if( $directCall == true )
		{
      		$urlBits = $this->registry->getURLBits();     
			
			if($perSet == 0)
			{
				$this->registry->getObject('log')->addMessage($caption['msg_unauthorized'],'contact','');
				
				$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
				$this->build();
				return;
			}

			if( isset( $urlBits[1] ) )
			{
				switch( $urlBits[1] )
				{				
					case 'searchGlobal':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchGlobal($searchText);
							return;
						}
						break;
					case 'searchContact':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchGlobal($searchText,'contact');
							return;
						}
						break;
					case 'searchDocument':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchGlobal($searchText,'dmsentry');
							return;
						}
						break;
					case 'searchAgenda':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchGlobal($searchText,'agenda');
							return;
						}
						break;
				}
			}
			$this->pageNotFound();
		}
	}

    /**
     * Sestavení stránky
     * @return void
     */
	private function build( $template = 'page.tpl.php' )
	{
		// Build page
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-empty.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template , 'footer.tpl.php');
	}

    /**
     * Zobrazení chybové stránky, pokud dokument nebyl nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámého obsahu",'dmsentry','');		
		$this->build('invalid-page.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'contact','');
		
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		$this->build();
	}

    /**
     * Zobrazení seznamu vyhledaných položek dle hledaného řetězce
	 * Vyhledání je ve vše relevantních tabulkách
	 *   - Agenda   : Description, DocumentNo
	 *   - Contact  : FullName, Function, Company, Address, Note, Phone, Email, ContactGroups
	 *   - Dmsentry : Title, Content
 	 * 	   	 20 - Folder 	obal (10,30,35,40) .... fyzický (soubory) i virtuální obsah
 	 * 	 	 25 - Block		obal (10,35,40)    .... virtuální obsah
	 * 		 30 - File		položka            .... fyzický soubor
	 * 		 35 - Note		položka            .... virtuální, jako odkaz, text, poznámka

	 * @param String $searchText = maska hledaných položek
	 * @return void
     */
	function searchGlobal( $searchText , $table = '')
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$this->initSearch();

		$searchText = htmlspecialchars($searchText);
		$searchText = str_replace('*','',$searchText);
		
		$batchID = $this->nextBatchID();
		$createDate = $this->registry->getObject('core')->now();
		$url = "general/view/";

		// Search in dmsentry	
		if (($table == '') || ($table == 'dmsentry')){
			$sql = "INSERT INTO ".$pref."resultsearch (BatchID,CreateDate,Type,Description,ID) ".
					"SELECT $batchID as BatchID, '$createDate' as CreateDate, ".
						"CASE WHEN Type=20 THEN 'Folder' WHEN Type=25 THEN 'Block' WHEN Type=30 THEN 'File'WHEN Type=35 THEN 'Note' ELSE Type END as Type, ".
						"Title as Description ,ID ".
					"FROM ".$pref."dmsentry ".
					"WHERE Archived = 0 AND Type IN (20,25,30,35) ".
						"AND ((Title like '%$searchText%') OR (Content like '%$searchText%')) ".
						"AND PermissionSet <= $perSet ";
			$this->registry->getObject('db')->executeQuery($sql);
		}

		// Search in agenda
		if (($table == '') || ($table == 'agenda')){
				$sql = "INSERT INTO ".$pref."resultsearch (BatchID,CreateDate,Type,Description,ID) ".
					"SELECT $batchID as BatchID, '$createDate' as CreateDate, ".
						"'Agenda' as Type, CONCAT (IFNULL(DocumentNo,''),' ', IFNULL(Description,'')) as Description,ID ".
					"FROM ".$pref."agenda ".
					"WHERE (DocumentNo like '%$searchText%') OR (Description like '%$searchText%')";
			$this->registry->getObject('db')->executeQuery($sql);
		}

		// Search in contact
		if (($table == '') || ($table == 'contact')){
			$sql = "INSERT INTO ".$pref."resultsearch (BatchID,CreateDate,Type,Description,ID) ".
					"SELECT $batchID as BatchID, '$createDate' as CreateDate, ".
						"'Contact' as Type, CONCAT (IFNULL(FullName,''),', Tel.:',IFNULL(Phone,''),', Email:',IFNULL(Email,'')) as Description ,ID ".
					"FROM ".$pref."contact ".
					"WHERE (Close = 0) ".
						"AND (".
							"(FullName like '%$searchText%') OR ".
							"(`Function` like '%$searchText%') OR ".
							"(Company like '%$searchText%') OR ".
							"(Address like '%$searchText%') OR ".
							"(Note like '%$searchText%') OR ".
							"(Phone like '%$searchText%') OR ".
							"(Email like '%$searchText%') OR ".
							"(ContactGroups like '%$searchText%'))";
			$this->registry->getObject('db')->executeQuery($sql);
		}

		// Set order key
		$sql = "SELECT * FROM ".$pref."resultsearch WHERE BatchID like $batchID ORDER BY Type,Description";

		// Group records by Page
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );

		// Save SQL result to $cache (array type) AND modify record
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache ))
		{
			while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
			{
				// New fields
				$rec['Url'] = '';
				$rec['Target'] = '';
				$rec['cardID'] = '';
				$rec['card'] = '';
				$rec['Remind'] = '0';
				$rec['RemindClose'] = '0';
				$rec['FileExtension'] = '';
				$rec['ADocumentNo'] = '---';


				switch ($rec['Type']) {
					case 'Folder':
						$rec['Url'] = 'index.php?page=document/list/' . $rec['ID'];
						$rec['deleteLink'] = "entry/deleteFolder";

						$model = new Entry( $this->registry, $rec['ID'] );
						$entry = $model->getData();
						if( $model->isValid() )
						{
							$rec['Title'] = $entry['Title'];
							$rec['Content'] = $entry['Content'];
							$rec['ModifyDateTime'] = $entry['ModifyDateTime'];
						};					

						$rec['dmsClassName'] = 'item';
						$rec['editFolderCardID'] = 'editFolderCard'.$rec['ID'];					
					
						break;
					case 'Block':
						$rec['Url'] = 'index.php?page=document/list/' . $rec['ID'];
						break;
					case 'File':
						$model = new Entry( $this->registry, $rec['ID'] );
						$entry = $model->getData();
						if( $model->isValid() )
						{
							if ($entry['FileExtension'] <> ''){
								$app = $this->getApplication($entry['FileExtension']);
								$rec['Url'] = $app.$config['webroot'].$entry['Name'];
								if ($app == '')
									$rec['Target'] = '_blank';
							}else{
								$rec['Url'] = 'index.php?page=document/list/' . $rec['ID'];
							}
						};					

						$rec['Title'] = $entry['Title'];
						$rec['FileExtension'] = $entry['FileExtension'];						
						$rec['Content'] = $entry['Content'];
						$rec['RemindResponsiblePerson'] = $entry['RemindResponsiblePerson'];
						$rec['ADocumentNo'] = $entry['ADocumentNo'];
						$rec['CreateDate'] = $entry['CreateDate'];
						$rec['ModifyDateTime'] = $entry['ModifyDateTime'];
						$rec['Remind'] = $entry['Remind'] == "" ? "0" : $entry['Remind'];
						$rec['RemindClose'] = $entry['RemindClose'] == "" ? "0" : $entry['RemindClose'];
						$rec['FileExtension'] = $entry['FileExtension'];

						$rec['dmsClassName'] = 'item';
						$rec['viewFileCardID'] = 'viewFileCard'.$rec['ID'];					
						$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
						$rec['deleteLink'] = "entry/deleteFile";

						break;
					case 'Note':
						$rec['Url'] = 'index.php?page=document/list/' . $rec['ID'];
						$model = new Entry( $this->registry, $rec['ID'] );
						$entry = $model->getData();
						if( $model->isValid() )
						{
							$rec['Title'] = $entry['Title'];
							$rec['FileExtension'] = $entry['FileExtension'];						
							$rec['Content'] = $entry['Content'];
							$rec['RemindResponsiblePerson'] = $entry['RemindResponsiblePerson'];
							$rec['CreateDate'] = $entry['CreateDate'];
							$rec['ModifyDateTime'] = $entry['ModifyDateTime'];
							$rec['Remind'] = $entry['Remind'] == "" ? "0" : $entry['Remind'];
							$rec['RemindClose'] = $entry['RemindClose'] == "" ? "0" : $entry['RemindClose'];
			
							$rec['dmsClassName'] = 'item';
							$rec['viewFileCardID'] = 'viewFileCard'.$rec['ID'];					
							$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
						}
						break;
					case 'Agenda':
						$rec['Url'] = 'Poznámka';
						break;
					case 'Contact':
						$model = new Contact( $this->registry, $rec['ID'] );
						$contact = $model->getData();						

						$rec['Title'] = ($contact['Title'] <> "")? $contact['Title'].'&nbsp;' : '';
						$rec['FirstName'] = $contact['FirstName'];
						$rec['FullName'] = $contact['FullName'];
						$rec['LastName'] = $contact['LastName'];
						$rec['Function'] = $contact['Function'];
						$rec['Company'] = $contact['Company'];
						$rec['Address'] = $contact['Address'];
						$rec['Note'] = $contact['Note'];
						$rec['Phone'] = $contact['Phone'];
						$rec['Email'] = $contact['Email'];
						$rec['Web'] = $contact['Web'];
						$rec['ContactGroups'] = $contact['ContactGroups'];
	
						$rec['dmsClassName'] = 'contact';
						$rec['viewContactCardID'] = 'viewContactCard'.$rec['ID'];					
						$rec['editContactCardID'] = 'editContactCard'.$rec['ID'];					
						$rec['deleteLink'] = "contact/delete";

						// Select Free Agenda Document No.
						$sql = "SELECT ID as AID, DocumentNo, Description FROM ".$pref."agenda ".
							"WHERE `EntryID` = '' ".
							"ORDER BY TypeID,DocumentNo";
						$cache2 = $this->registry->getObject('db')->cacheQuery( $sql );
						$this->registry->getObject('template')->getPage()->addTag( 'documentList', array( 'SQL', $cache2 ) );

						break;
					
					default:
						# code...
						break;
				}
				$result[] = $rec;
		    }			
		}else{
			$message = 'Nenalezeno';
			$this->registry->getObject('template')->getPage()->addTag('message',$message);
			$this->build();
			return;
		}	
		$cache = $this->registry->getObject('db')->cacheData( $result );

		$this->registry->getObject('template')->getPage()->addTag( 'searchText', $searchText );
		$this->registry->getObject('template')->getPage()->addTag( 'ResultItems', array( 'DATA', $cache ) );
		
		// Card subpages
		$this->registry->getObject('template')->getPage()->addTag('viewcardFile','');
		$this->registry->getObject('template')->addTemplateBit('editcardFile', 'document-entry-editcard.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('editcardFolder', 'document-entry-editfolder.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('viewcardContact', 'contact-view.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('editcardContact', 'contact-edit.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('SelectedDocumentNo', 'document-edit-selectADocumentNo.tpl.php');        
		$this->registry->getObject('template')->addTemplateBit('remindIcon','document-list-remindicon.tpl.php');

		// Add icons
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
			$this->registry->getObject('template')->getPage()->addTag( "iconFile$ext", $icon );
		}
		$this->registry->getObject('template')->getPage()->addTag( "iconContact", "<img src='views/classic/images/icon/contact.png' />" );
		$this->registry->getObject('template')->getPage()->addTag( "iconFolder", "<img src='views/classic/images/icon/folder.png' />" );
		$this->registry->getObject('template')->getPage()->addTag( "iconNote", "<img src='views/classic/images/icon/note.png' />" );
		$this->registry->getObject('template')->getPage()->addTag( "iconBlock", "<img src='views/classic/images/icon/block.png' />" );
		$this->registry->getObject('template')->getPage()->addTag( "iconAgenda", "<img src='views/classic/images/icon/agenda.png' />" );

		$this->build('search-list-result.tpl.php');

	}	

	/**
	 * Vrací následující číslo dávky
	 * @retur new BatchID
	 */
	private function nextBatchID()
	{
		global $config;
		$prefix = $config['dbPrefix'];

		$sql = "SELECT MAX(BatchID) as BatchID FROM ".$prefix."resultsearch";
		$this->registry->getObject('db')->executeQuery($sql);

		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			$data = $this->registry->getObject('db')->getRows();
			return ($data['BatchID'] + 1);
		}
		return 1;
	}

	/**
	 * Výmaz expirovaných záznamů v tabulce výsledků hledání
	 */
	private function initSearch()
	{
		global $config;
		$prefix = $config['dbPrefix'];
		$ExpirateDate = date_create('-1 hour')->format('Y-m-d H:i:s'); 

		$condition = "CreateDate < '$ExpirateDate'";
		$this->registry->getObject('db')->deleteRecords( 'resultsearch', $condition); 

	}

	private function getApplication($extension)
	{
		/**
		 * HELP
		 * https://docs.microsoft.com/en-us/office/client-developer/office-uri-schemes#sectionSection9
		 */
		$app = '';
		switch ($extension) {
			case 'xls':
			case 'xlsx':
			case 'csv':
				$app = "ms-excel:ofe|u|";
				break;
			case 'doc':
			case 'docx':
			case 'rtf':
				$app = "ms-word:ofe|u|";
				break;
			case 'ppt':
			case 'pptx':
				$app = "ms-powerpoint:ofv|u|";
				break;
		}
		return $app;
	}

}
?>