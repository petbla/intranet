<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Generalcontroller {

	private $registry;
	private $urlBits;
	
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
				// Search BOX
				$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
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
			$this->notFound();
		}
	}

    /**
     * Zobrazení chybové stránky, pokud dokument nebyl nalezem 
     * @return void
     */
	private function notFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámého obsahu",'dmsentry','');
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-page.tpl.php', 'footer.tpl.php');
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
		// Nastavení parametrů
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
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
						"'Agenda' as Type, CONCAT (DocumentNo,' ', Description) as Description,ID ".
					"FROM ".$pref."agenda ".
					"WHERE (DocumentNo like '%$searchText%') OR (Description like '%$searchText%')";
			$this->registry->getObject('db')->executeQuery($sql);
		}

		// Search in contact
		if (($table == '') || ($table == 'contact')){
			$sql = "INSERT INTO ".$pref."resultsearch (BatchID,CreateDate,Type,Description,ID) ".
					"SELECT $batchID as BatchID, '$createDate' as CreateDate, ".
						"'Contact' as Type, CONCAT (FullName,', Tel.:',Phone,', Email:',Email) as Description ,ID ".
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

				switch ($rec['Type']) {
					case 'Folder':
						$rec['Url'] = 'index.php?page=document/list/' . $rec['ID'];
						$rec['deleteLink'] = "entry/deleteFolder";
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
						$rec['viewcardID'] = '';
						$rec['editcardID'] = 'editentry'.$rec['ID'];
						$rec['deleteLink'] = "entry/deleteFile";

						$this->registry->getObject('template')->getPage()->addTag('viewcard','');
						$this->registry->getObject('template')->addTemplateBit('editcard', 'document-entry-editcard.tpl.php');
						break;
					case 'Note':
						$rec['Url'] = 'index.php?page=document/list/' . $rec['ID'];
						break;
					case 'Agenda':
						$rec['Url'] = 'Poznámka';
						break;
					case 'Contact':
						$model = new Contact( $this->registry, $rec['ID'] );
						$contact = $model->getData();
						$rec['Title'] = ($contact['Title'] <> "")? $contact['Title'].'&nbsp;' : '';
						$rec['FirstName'] = $contact['FirstName'];
						$rec['LastName'] = $contact['LastName'];
						$rec['Function'] = $contact['Function'];
						$rec['Company'] = $contact['Company'];
						$rec['Address'] = $contact['Address'];
						$rec['Note'] = $contact['Note'];
						$rec['Phone'] = $contact['Phone'];
						$rec['Email'] = $contact['Email'];
						$rec['Web'] = $contact['Web'];
						$rec['ContactGroups'] = $contact['ContactGroups'];

						$rec['viewcardID'] = 'viewcontact'.$rec['ID'];
						$rec['editcardID'] = 'editcontact'.$rec['ID'];
						$rec['deleteLink'] = "contact/delete";

						$this->registry->getObject('template')->addTemplateBit('viewcard', 'contact-view.tpl.php');
						$this->registry->getObject('template')->addTemplateBit('editcard', 'contact-edit.tpl.php');
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
			$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
			return;
		}	
		$cache = $this->registry->getObject('db')->cacheData( $result );
	
		// Build page 
		$this->registry->getObject('template')->getPage()->addTag( 'searchText', $searchText );
		$this->registry->getObject('template')->getPage()->addTag( 'ResultItems', array( 'DATA', $cache ) );
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'search-list-result.tpl.php', 'footer.tpl.php');						
		
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		  
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