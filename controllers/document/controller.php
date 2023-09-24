<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Documentcontroller{
	
	private $registry;
	private $model;
	private $perSet;
	private $prefDb;
	private $message;
	private $errorMessage;
	private $zob;

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		global $config, $caption, $deb;
		$this->registry = $registry;
		$this->perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        $this->prefDb = $config['dbPrefix'];


		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     
			
			if($this->perSet == 10)
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
					case 'view':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->viewDocument($ID);
						break;
					case 'addFiles':
						$this->addFiles( true );
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
					case 'importCsv':
						if($this->perSet >= 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$ID = isset($urlBits[2]) ? $urlBits[2] : '';
							$this->importCsv( $ID );
							$this->listDocuments($ID);
						}
						else
							$this->error($caption['Error'].' - '.$caption['msg_unauthorized']);
						break;
					case 'ws':
						switch ($urlBits[2]) {
							case 'newDmsentry':
								$ID = $this->newDmsentry();						
								if($ID)
									exit('OK');
								else
									exit($this->errorMessage);
						}
						break;
					case 'choice':
						// document/choice/list/{Type}/{TypeID}
						// document/choice/<$action>/<$Type>/<$TypeID>
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$Type = isset($urlBits[3]) ? $urlBits[3] : '';
						if($Type == '')
							$Type = isset($_POST['Type']) ? $_POST['Type'] :'';
						$action = isset($_POST['storno']) ? 'storno' : $action;
						$action = isset($_POST['add']) ? 'add' : $action;
						$TypeID = isset($urlBits[4]) ? $urlBits[4] : 0;
						if($TypeID == 0)
							$TypeID = isset($_POST['TypeID']) ? $_POST['TypeID'] :0;
						$ParentID = isset($urlBits[5]) ? $urlBits[5] : '';
						$ParentID = isset($_POST['ID']) ? $_POST['ID'] : $ParentID;
						$parententry = $this->getDmsentryByID($ParentID);
						if($parententry)
							$DmsParentEntryNo = $parententry['EntryNo'];
						else
							$DmsParentEntryNo = 0;
						$this->choiceDocument($action, $Type, $TypeID, $DmsParentEntryNo);
						break;						
					default:
						$this->pageNotFound();
						break;
				}
			}
		}
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
    
	/**
     * Zobrazení chybové stránky, pokud dokument nebyl nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámého dokumentu",'dmsentry','');
		$this->build('invalid-document.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param string $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'dmsentry','');
		$this->errorMessage = $message;
		$this->build();
	}

    /**
     * Pokud položka je typu složka a tato obsahuje položky typu soubor = media = obrázky
	 * pak se zobrazí stránka galerie pro listování mezi obrázky 
	 * @param string $ID = ID položky typu složka
     * @return void
     */
	private function slideshow( $ID )
	{
		global $config;
		$webroot = $config['webroot'];

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
						$imagepath = $webroot.$image['Name'];
						$imagepath = str_replace(DIRECTORY_SEPARATOR,'/', $imagepath); 
						$img[] = array('imagepath' => $imagepath, 'Title' => $image['Title']);
					}
					$CacheId = $this->registry->getObject('db')->cacheData($img);
					$this->registry->getObject('template')->getPage()->addTag( 'ImageList', array('DATA', $CacheId));
					$CacheId = $this->registry->getObject('db')->cacheData($data);
					$this->registry->getObject('template')->getPage()->addTag( 'IndexList', array('DATA', $CacheId));
					// Vložení Breds navigace na stránku
					$breads = $entry['breads'];
					$this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );
					// Logování 
					$this->registry->getObject('log')->addMessage('Zobrazení galerie obrázků','DmsEntry',$ID);					
					$this->build('slideshow.tpl.php');
				}
				else
				{
					$this->pageNotFound();	
				}
			}
			else
			{
				$this->pageNotFound();	
			}
		}
		else
		{
			$this->pageNotFound();	
		}
	}

    /**
     * Zobrazení seznamu položek ze složky
	 * @param string $ID = ID položky typu složka
     * @return void
     */
	private function listDocuments( $ID )
	{
		global $config;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		$entry = $this->model->getData();
		if( $this->model->isValid() )
		{
			if(($entry['Type'] == 30) || ($entry['Type'] == 35)){
				$this->model = new Entry( $this->registry, $entry['Parent'] );
				$entry = $this->model->getData();
			}
			$this->registry->setLevel($entry['Level']);
			$this->registry->setEntryNo($entry['EntryNo']);		

			// Synchronizace složky
			if($entry['Type'] == 20)
			{
				if ($config['synchroFolderonOpen'])
					$this->registry->getObject('file')->synchoroDirectory($entry);
			}
		}
		// Zobrazení podsložek
		$sql = "SELECT ID,Title,Name,Type,Parent,ModifyDateTime FROM ".$this->prefDb."DmsEntry ".
					"WHERE Archived = 0 AND parent=".$entry['EntryNo']." AND Type IN (20,25) ".
					"AND PermissionSet <= $this->perSet ".
					"ORDER BY Type,Title";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$showFolder = ($this->registry->getObject('db')->isEmpty($cache) == false);
		if ($showFolder){
			$this->registry->getObject('template')->getPage()->addTag( 'FolderItems', array( 'SQL', $cache ) );			
		}
		
		// Zobrazení dokumentů		
		if(!isset($config['HideHandledNote'])){
			$config['HideHandledNote'] = "";
		};
		if($config['HideHandledNote'] == "checked")
			$HideRemindClose = "AND RemindClose = 0 ";
		else
			$HideRemindClose = "";

		$sql = "SELECT e.ID,e.Title,e.Name,e.Type,e.Url,e.Parent,e.ModifyDateTime,LOWER(e.FileExtension) as FileExtension ".
						",IF(e.Remind=0,'0','1') as Remind,IF(e.RemindClose=0,'0','1') as RemindClose,e.RemindFromDate,e.RemindLastDate".
						",e.Content,e.RemindResponsiblePerson,e.RemindUserID,e.RemindContactID,e.RemindState ".	
						",a.DocumentNo, a.ExecuteDate, a.ID as AgendaID ".
					"FROM ".$this->prefDb."DmsEntry as e ".
				  	"LEFT JOIN ".$this->prefDb."agenda as a ON e.ID = a.EntryID ".
				  	"WHERE e.Archived = 0 AND e.parent=".$entry['EntryNo']." AND e.Type IN (10,30,35,40) ".
						"AND e.PermissionSet <= $this->perSet ".
						$HideRemindClose.
				  	"ORDER BY e.Remind DESC,e.Type,e.Title DESC ";
		$showBreads = true;
		$template = '';
		
		// Logování akce
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu souborů a složek','DmsEntry',$ID);
		// Zobrazení výsledku
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$template);
	}	

	
    /**
     * Zobrazení obsahu položky (souboru, složky, poznámky, apod.)
	 * @param string $ID = ID položky, jejiž obsah se má zobrazit
	 * @return void
     */
	private function viewDocument( $ID )
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
			// Logování
			$this->registry->getObject('log')->addMessage("Zobrazení ".$entry['Name'],'DmsEntry',$ID);
			// Zobrazení obsahu položky
			$this->registry->getObject('document')->viewDocument($entry,'');
		}
		else
		{
			$this->pageNotFound();
		}
	}	


    /**
     * Akce vyvolaná webovým formulářem, která uloží editované hodnoty položky a nastavuje připomenutí
	 * Očekávané akce jsou identifikovány v proměné $_POST[]
	 * 	 'stornoRemind' = na položce budou zrušeny přínaky připomínky
	 *   'save'         = uloží se editovaný obsah
	 *   'back'         = žádná změna se neuloží
	 * Po dokončení se zobrazí seznam položek podle editované položky
	 * @param string $ID = ID editované položky
	 * @return void
     */
	private function modifyDocument( $ID )
	{
		global $caption;

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($this->perSet > 0) AND $this->model->isValid() )
		{
			$entry = $this->model->getData();
			
			$post = $_POST;
			
			if(isset($_POST['stornoRemind']))
			{
				$changes['Remind'] = '0';
				$changes['RemindClose'] = '0';
				$condition = "ID = '$ID'";
				$this->registry->getObject('log')->addMessage("Zobrazení a aktualizace dokumentu",'dmsentry',$ID);
				$this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);
				$ID = $entry['Parent'];
			}
			elseif(isset($_POST['save']))
			{

				$Title = isset($_POST['Title']) ? $_POST['Title'] : '';
				$Url = isset($_POST['Url'] ) ? $_POST['Url'] : '';
				$Content = isset($_POST['Content']) ? $_POST['Content'] : '';
				
				$Remind = (isset($_POST['Remind'])) ? (($_POST['Remind'] === '') ? '0' : '1') : '0';
				$RemindClose = (isset($_POST['RemindClose'])) ? (($_POST['RemindClose'] === '') ? '0' : '1') : '0';

				$RemindFromDate = isset($_POST['RemindFromDate']) ? ($_POST['RemindFromDate'] === '' ? 'NULL' : $_POST['RemindFromDate']): 'NULL';
				$RemindLastDate = isset($_POST['RemindLastDate'] ) ? ($_POST['RemindLastDate'] === '' ? 'NULL' : $_POST['RemindFromDate']) : 'NULL';
				$RemindState = isset($_POST['RemindState']) ? $_POST['RemindState'] : 'NULL';
				$RemindResponsiblePerson = isset($_POST['RemindResponsiblePerson']) ? $_POST['RemindResponsiblePerson'] : '';
				
				$RemindContactID = isset($_POST['RemindContactID']) ? $_POST['RemindContactID'] : '';
				$RemindResponsiblePerson = isset($_POST['RemindResponsiblePerson']) ? $_POST['RemindResponsiblePerson'] : '';
				$RemindUserID = isset($_POST['RemindUserID']) ? $_POST['RemindUserID'] : '';
				$RemindUserGroup = isset($_POST['RemindUserGroup']) ? $_POST['RemindUserGroup'] : 0;

				if ($Title)
				{
					$Title = $this->registry->getObject('db')->sanitizeData($Title);
					
					// Update
					$changes['Title'] = $Title;
					$changes['Url'] = $Url;
					$changes['Content'] = $Content;
					$changes['Remind'] = $Remind;
					$changes['RemindClose'] = $RemindClose;
					if ($changes['RemindClose'] == '1'){
						$changes['Remind'] = '0';
					}else{
						if($Remind == '1')
							$changes['RemindClose'] = '0';
						if ($RemindFromDate != 'NULL'){
							$changes['RemindFromDate'] = $RemindFromDate;
							$changes['Remind'] = '1';
						}
						if ($RemindLastDate != 'NULL')
							$changes['RemindLastDate'] = $RemindLastDate;
					}
					if ($RemindResponsiblePerson != '')
						$changes['RemindResponsiblePerson'] = $RemindResponsiblePerson;
					if ($RemindContactID != '')
						$changes['RemindContactID'] = $RemindContactID;
					if ($RemindUserID != '')
						$changes['RemindUserID'] = $RemindUserID;
					if ($RemindUserGroup > 0)
						$changes['RemindUserGroup'] = $RemindUserGroup;

					$changes['RemindState'] = $RemindState;
					$condition = "ID = '$ID'";
					$this->registry->getObject('log')->addMessage("Zobrazení a aktualizace dokumentu",'dmsentry',$ID);
					$this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);

					// Číslo jednací
					$DocumentNo = isset($_POST['NewDocumentNo']) ? $_POST['NewDocumentNo'] : 'NONE';
					if($DocumentNo !== 'NONE'){
						$changes = array();
						$changes['EntryID']	= $ID;

						$this->registry->getObject('db')->initQuery('agenda');
						$this->registry->getObject('db')->setFilter('ID',$DocumentNo);
						if ($this->registry->getObject('db')->findFirst())
						{
							$agenda = $this->registry->getObject('db')->getResult();				
							if($agenda['Description'] == '')
								$changes['Description'] = $entry['Title'];
						}
						
						$condition = "ID = '$DocumentNo'";
						$this->registry->getObject('db')->updateRecords('agenda',$changes, $condition);
					};

					$searchText = isset($_POST['searchText'])? $_POST['searchText'] : '';
					$searchType = isset($_POST['searchType'])? ($_POST['searchType'] == 'general' ? '' : 'dmsentry') : 'dmsentry';
					if ($searchText != '{searchText}'){
						require_once( FRAMEWORK_PATH . 'controllers/general/controller.php');
						$general = new Generalcontroller( $this->registry, true );
						$general->searchGlobal($searchText, $searchType);
						return;
					}
				}else{
					$ID = $entry['Parent'];
				}				
			}
			elseif(isset($_POST['back']))			
			{
				$ID = $entry['Parent'];
			}
		}
		else
		{
			$this->pageNotFound();
			return;
		}
		$controller = isset($_POST['controller'])? $_POST['controller'] : 'document';
		switch ($controller) {
			case 'todo':
				require_once( FRAMEWORK_PATH . 'controllers/todo/controller.php');
				$todo = new Todocontroller( $this->registry, true );
				$action = isset($_POST['controllerAction'])? $_POST['controllerAction'] : '';
				switch ($action) {
					case 'listTodo':
						$todo->listTodo();
						break;
					case 'listMyTodo':
						$todo->listMyTodo();
						break;				
					default:
						# code...
						break;
				}				
				break;
			default:
				// document
				$this->listDocuments($ID);
				break;
		}	
	}	


    /**
     * Akce vyvolaná jako API rozhranní volané přes JavaScript a atributy elementu
	 * vytváří novou složku, blok nebo poznámku
	 * 
	 * Api: document/ws/<action>/<type>/<parentID>/<name>
	 * Api: document/ws/newDmsentry/<Block|Folder|Note>/<parentID>/<name>
	 * 
	 * @return $ID or null (and $this->errorMessage)
     */
	private function newDmsentry()
	{
		global $config, $caption, $deb;
		$this->errorMessage = '';
		$ID = null;

		$urlBits = $this->registry->getURLBits();     
		$action = isset($urlBits[3]) ? $urlBits[3] : null;
		if(!$action){
			$this->errorMessage = 'ERROR: No action to create new dmsentry.';
			return $ID;
		}
		$parentID = isset($urlBits[4]) ? $urlBits[4] : 0;
		$parententry = $this->getDmsentryByID($parentID);
		$Name = isset($urlBits[5]) ? $urlBits[5] : null;
		if(!$Name) {
			$this->errorMessage = 'ERROR: Name for new dmsentry must be input.';
			return $ID;
		}
		if(! $this->registry->getObject('authenticate')->isAdmin()){
			$this->errorMessage = 'ERROR: '.$caption['msg_unauthorized'];
			return $ID;
		}

		$parentFullName = $config['fileroot'] . $this->registry->getObject('fce')->ConvertToDirectorySeparator( $parententry['Name'] , true);
		switch ($action){
			case 'Block':
				$ID = $this->registry->getObject('file')->addBlock($parentFullName,$Name);
				if($ID != '')
				{
					$this->registry->getObject('log')->addMessage("Vytvořen nový blok",'dmsentry',$ID);					
				}else{
					$this->errorMessage = 'ERROR: '.$caption['msg_blockNotCreated'];
					$ID = null;
				}
				break;
			case 'Folder':
				$fileFullName = "$parentFullName$Name";
				if(!file_exists($fileFullName))
				{
					try {
						if(mkdir($fileFullName, 0755, true))
						{
							// create succes
							// $fullName = //petbla/c$/Users/petr/Desktop/OBECNTB/FileServer/Stavby RD/RD Šáchovi 313/Šáchovi - Vyjádření k PD.docx
							$EntryNo = $this->registry->getObject('file')->findItem($fileFullName);
							$ID = $this->registry->getObject('file')->getIdByEntryNo($EntryNo);
							$this->registry->getObject('log')->addMessage("Vytvořena nová složka",'dmsentry',$ID);
						}
					} catch (Exception $e) {
						$this->errorMessage = 'ERROR: Složka nebyla vytvořena. Chyba: ' + $e->getMessage();
					}
				}
				break;
			case 'Note':
				$ID = $this->registry->getObject('file')->newNote( $parententry , $Name );
				$this->registry->getObject('log')->addMessage("Vytvořena nová poznámka",'dmsentry',$ID);
				break;
			default:
				$this->errorMessage = $caption['msg_folderExists'];
		}
		return $ID;
	}

    /**
     * Akce vyvolaná webovým formulářem, která provee upload souborů na server 
	 * do aktuální složky
	 * Po provedení akce se zobrazí nový seznam položek složky
	 * @param boolean $listfolder, default value = true
	 * @return array $uploadFiles
     */
	function addFiles( $listfolder = true )
	{
		global $config;
		$ParentID = '';
		$uploadFiles = array();

		if(isset($_FILES["fileToUpload"]) && isset($_POST["submit_x"]) && isset($_POST['ParentID']) ) {
			$ParentID = $_POST['ParentID'];
			$parententry = $this->getDmsentryByID($ParentID);
			if ($parententry){

				$path = $config['fileroot'] . str_replace('\\','/',$parententry['Name']) . '/';
				$files = $_FILES['fileToUpload'];
				if(!empty($files))
				{
					$files = $this->reArrayFiles($files);
					foreach($files as $file)
					{
						$target_file = $path . basename($file["name"]);
						try {
							move_uploaded_file($file['tmp_name'],$target_file);
							$EntryNo = $this->registry->getObject('file')->findItem($target_file);
							$uploadFiles[] = $EntryNo; 
							$this->registry->getObject('log')->addMessage("Upload souboru EntryNo = $EntryNo do aktuální složky (dle ID)",'dmsentry',$ParentID);
						} catch (Exception $e) {
							$this->error('Soubor ' + $file["name"] + ' se nepodařilo načíst. Chyba: ' + $e->getMessage());
						}
					}
				}	
			}	
		}
		if ($listfolder)
			$this->listDocuments($ParentID);
		return $uploadFiles;
	}

	/**
	 * Menu pro výběr dokumentu pro další akci (např. výběr přílohy do zápisu)
	 * 
	 * Sample:  
	 *   document/choice/list/{Type}/{TypeID}
	 *   document/choice/<$action>/<$Type>/<$TypeID>
     *
	 * @param mixed $action - option: list,select,storno,set,search
	 * @param mixed $Type - option: meetingline,
	 * @param mixed $TypeID - record type ID 
	 * @param mixed $DmsParentEntryNo - parent DMS entryNo field
	 * @return void
	 */
	public function choiceDocument($action, $Type, $TypeID, $DmsParentEntryNo)	
	{
		// Najít ParentFolder - pokud byl zadán - a určit level složky pro výběr
		$parentPath = '';
		$ParentID = '';
		if ($DmsParentEntryNo != ''){
			$parententry = $this->getDmsentry( $DmsParentEntryNo );
			if( $parententry ){
				$parentPath = $parentPath . $parententry['Name'];
				$ParentID = $parententry['ID'];
			}				
		}
		$breads = str_replace("\\"," => ",$parentPath);

		// Seznam složek pro výběr
		$searchtext = isset($_POST['searchtext']) ? $_POST['searchtext'] : null;
		if($searchtext) 
			$dmsentry = $this->registry->getObject('document')->readFoldersByTitle($searchtext);
		else
			$dmsentry = $this->registry->getObject('document')->readFolders($DmsParentEntryNo, 0);
		$folders = array();
		if($dmsentry){
			foreach ($dmsentry as $entry){
				$rec['ID'] = $entry['ID'];
				$rec['Name'] = $entry['Name'];
				$rec['Url'] = $entry['Url'];
				$rec['Title'] = $entry['Title'];
				$rec['DocumentType'] = $entry['Type'];
				$rec['FileExtension'] = $entry['FileExtension'];
				if ($entry['Type'] <> 20) {
					$rec['DocumentClass'] = 'blue';
					$rec['Action'] = 'set';
				}else{
					$rec['DocumentClass'] = '';
					$rec['Action'] = 'select';
				}
				$rec['Type'] = $Type;
				$folders[] = $rec;
			}
		}else{
			$rec['ID'] = '';
			$rec['Name'] = '<NONE>';
			$rec['Title'] = '<NONE>';
			$folders[] = $rec;
		}

		// Vložení sezanmu do Page
		$cache =  $this->registry->getObject('db')->cacheData($folders);
		$this->registry->getObject('template')->getPage()->addTag( 'listFolder', array( 'DATA', $cache ) );

		$post = $_POST;
		switch ($action) {
			case 'storno':
				# Bez zápisu
				break;
			case 'select':
				$this->choiceDocument('list', $Type, $TypeID, $DmsParentEntryNo);
				return;
			case 'set':
				require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
				$zob = new Zobcontroller( $this->registry, false );
				require_once( FRAMEWORK_PATH . 'controllers/zob/advance.php');
				$adv = new Zobadvance( $this->registry );					

				switch ($Type){
					case 'meetinglineadv':
						$zob->addMeetingLineAttachment($TypeID, $DmsParentEntryNo);
						$meetingline = $zob->getMeetingline($TypeID);
						$adv->MeetingID = $meetingline['MeetingID'];
						$adv->main($meetingline['MeetingID']);
						return;
					case 'meetingline':
						$zob->addMeetingLineAttachment($TypeID, $DmsParentEntryNo);
						$meetingline = $zob->getMeetingline($TypeID);
						$zob->listMeetingLine($meetingline['MeetingID'],$meetingline['MeetingLineID']);
						return;
				}
				/*


				$inbox = $this->getInbox($InboxID);
				if($inbox){
					$filename = $inbox['Title'];
					$title = $inbox['Title'];
					$entry = $this->registry->getObject('file')->addFile($inbox['SourcePath'], $DmsParentEntryNo, $filename, $title);
					if($entry == null){
						$this->errorMessage = $this->registry->getObject('file')->getLastError();
					}else{
						$change = array();
						$change['DmsEntryID'] = $entry['ID'];
						$change['DestinationPath'] = $entry['DestinationPath'];
						$condition = "InboxID = $InboxID";
						$this->registry->getObject('db')->updateRecords('inbox',$change,$condition);
					}
				}
				*/
				break;			
			default:
				# 'list'
				# Zobrazení stránky pro výběr složky
				$this->registry->getObject('template')->getPage()->addTag( 'Type', $Type );							
				$this->registry->getObject('template')->getPage()->addTag( 'TypeID', $TypeID );							
				$this->registry->getObject('template')->getPage()->addTag( 'ParentID', $ParentID );							
				$this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );							
				$this->registry->getObject('document')->addIcons();
				$this->build('document-choice.tpl.php');
				return;
		}
	}


    /**
     * Akce vyvolaná z webové stránky (např.: OnClick), která se pokusí odstranit
	 * složky blok nebo poznámku. Odstranění v databázi je formnou nastavení pole "Archived = 1"
	 * Po provedení akce se zobrazí nový seznam položek složky
	 * @param string $ID = ID odstraňované položky
	 * @return void
     */
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
					$this->listDocuments($entry['Parent']);
					return;
				}
			}
		}				
		$this->error($message);
	}
		
    /**
     * Akce vyvolaná z webové stránky (např.: OnClick), která pro danou položku 
	 * nastaví pole "Archived = 1"
	 * Po provedení akce se zobrazí seznam položek dle odstraňované položky
	 * @param string $ID = ID odstraňované položky
	 * @return void
     */
	private function deleteFile( $ID )
	{
		global $caption;

		$message = $caption['msg_noalloved'];
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if( ($this->perSet > 0) AND $this->model->isValid() )
		{
			$entry = $this->model->getData();
			$condition = "ID = '$ID'";
			$data['Archived'] = 1;
			$this->registry->getObject('log')->addMessage("Výmaz poznámky",'dmsentry',$ID);
			$this->registry->getObject('db')->updateRecords('dmsentry',$data,$condition);			
				
			$this->listDocuments($entry['Parent']);
			return;
		}				
		$this->error($message);
	}

	/**
	 * Interní funkce, která z pole webového formuláře typu upload filed, 
	 * vytvoří seznam souborů, které se budou importova na server
	 * @param array<mixed><mixed> $file = pole z webového formuláže
	 * @return array<mixed> = pole seznamu souborů
	 */
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

	/**
	 * Akce vyvolaná z webového formuláře, která načte CSV soubor 
	 * ze kterého se pokusí provést import nových poznámek. Jako CSV soubor
	 * je očekávám přesně definovaný obsah a požadí sloupců (viz níže)
	 * Po dokončení se zobrazí seznam poznámek
	 * @return void
	 */
	private function importCsv( $parentID )
	{
		if(isset($_FILES["fileToUpload"]) ) {
			$file = $_FILES['fileToUpload'];
			if(!empty($file))
			{
				$target_file = 'tmp/' . basename($file["name"]);
				move_uploaded_file($file['tmp_name'],$target_file);
			}	
		}

		// If you need to parse XLS files, include php-excel-reader
		require_once( FRAMEWORK_PATH . 'vendor/spreadsheet-reader/php-excel-reader/excel_reader2.php');
		require_once( FRAMEWORK_PATH . 'vendor/spreadsheet-reader/SpreadsheetReader.php');
	
		$Reader = new SpreadsheetReader($target_file);
		$idValidCsv = false;
		$lineno = 0;
		foreach ($Reader as $Row)
		{
			$lineno++;
			if ($lineno == 1){
				if(($Row[0] === 'Název 1') &&
				   ($Row[1] === 'Název 2') &&
				   ($Row[2] === 'Text') &&
				   ($Row[3] === 'Popis') &&
				   ($Row[4] === 'Připomenutí') &&
				   ($Row[5] === 'Datum připomenutí') &&
				   ($Row[6] === 'Termín splnění') &&
				   ($Row[7] === 'Odpovídá'))
				{
					$idValidCsv = true;
				}
			}
			if (($idValidCsv) && ($lineno > 1)){
				$Name = $this->registry->getObject('db')->sanitizeData($Row[0]);
				$Name2 = $this->registry->getObject('db')->sanitizeData($Row[1]);
				$Text = $this->registry->getObject('db')->sanitizeData($Row[2]);
				if($Name2 != '')
				{
					$Name .= ($Name !== '') ? (' '.$Name2) : $Name2;
				}
				if($Text !== '')
				{
					$Name .= ($Name !== '') ? (' - '.$Text) : $Text;
				}
				$Content = $this->registry->getObject('db')->sanitizeData($Row[3]);				
				$Remind = $this->registry->getObject('db')->sanitizeData($Row[4]);				
				$RemindFromDate = $this->registry->getObject('db')->sanitizeData($Row[5]);
				$RemindLastDate = $this->registry->getObject('db')->sanitizeData($Row[6]);
				$RemindResponsiblePerson = $this->registry->getObject('db')->sanitizeData($Row[7]);

				$entry = array();
				$entry['Title'] = $Name; 
				$entry['Content'] = $Content; 
				$entry['Remind'] = (strtolower($Remind) == 'ano') ? 1 : 0; 
				
				if($RemindFromDate !== ''){
					$RemindFromDate = DateTime::createFromFormat('m-d-y', $RemindFromDate);
					$entry['RemindFromDate'] = $RemindFromDate->format('Y-m-d H:i:s');
				}
				if($RemindLastDate !== ''){
					$RemindLastDate = DateTime::createFromFormat('m-d-y', $RemindLastDate);
					$entry['RemindLastDate'] = $RemindLastDate->format('Y-m-d H:i:s');
				}
				$entry['RemindResponsiblePerson'] = $RemindResponsiblePerson;
				
				if($entry['Title'] !== '')
					$this->registry->getObject('file')->addNote($entry,$parentID);

			}
		}
		$this->listDocuments( $parentID );
	}

	private function getDmsentry($EntryNo)
	{
		$entry = null;
        if($EntryNo > 0){
            $this->registry->getObject('db')->initQuery('dmsentry');
            $this->registry->getObject('db')->setFilter('EntryNo',$EntryNo);
            if ($this->registry->getObject('db')->findFirst())
                $entry = $this->registry->getObject('db')->getResult();			
        }
        return $entry;
	}

	private function getDmsentryByID($ID)
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

}
?>