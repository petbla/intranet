<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    04.07.2019
 */
class Agendacontroller{
	
	private $registry;
	private $model;
	private $perSet;
	private $prefDb;
	private $message;
	private $errorMessage;

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		global $config, $caption;
		$this->registry = $registry;
		$this->perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        $this->prefDb = $config['dbPrefix'];
		$templateHeader = '';
		$templateFooter = '';

		$post = $_POST;

		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
				$this->pageNotFound();
			}
			else
			{
				$ID = '';
				switch ($urlBits[1]) {
					case 'list':
						$TypeID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->listAgenda($TypeID);
						break;
					case 'add':
						$TypeID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->addAgenda($TypeID);
						break;
					case 'unlink':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->unlinkAgenda($ID);
						break;
					case 'type':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;
						switch ($action) {
							case 'list':
								$this->listAgendaType();
								break;
							case 'add':
								$this->addAgendaType();
								break;
							case 'modify':
								$TypeID = isset($_POST["TypeID"]) ? $_POST["TypeID"] : '';
								if($TypeID !== ''){
									$this->modifyAgendaType( $TypeID );
								}else{
									$this->pageNotFound();
								}
								break;
							case 'delete':
								$TypeID = isset($urlBits[3]) ? $urlBits[3] : '';
								if($TypeID !== ''){
									$this->deleteAgendaType( $TypeID );
								}else{
									$this->pageNotFound();
								}
								break;
							default:
								$this->pageNotFound();
								break;
						}		
						break;				
					case 'document':
						require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
						$zob = new Zobcontroller( $this->registry , false);					

						$template = 'document-edit.tpl.php';
						$templateHeader = 'document-edit-header.tpl.php';
						$templateFooter = 'document-edit-footer.tpl.php';
						$header = 'Nový dokument';
						$formhref = '';
						$DocumentNo = '';  // číslo jednací
						$AgendaTypeOption = $this->createAgendaTypeOption();
						$AgendaTypeID = 0;
						$ParentID = '';
						$Breads = '';
						$ParentName = '';
						$Today = date('Y-m-d');

						$post = $_POST;
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($post['save']) ? 'save' : $action;
						$action = isset($post['preview']) ? 'preview' : $action;
						$action = isset($post['findcontact']) ? 'findcontact' : $action;

						$Type = isset($urlBits[3]) ? $urlBits[3] : '';
						switch ($action) {
							case 'create':
								$header = 'Příloha ';
								
								switch ($Type) {
									case 'meetingline':
										$MeetinglineID = isset($urlBits[4]) ? $urlBits[4] : 0;
										$meetingline = $zob->getMeetingline($MeetinglineID);
										$meeting = $zob->getMeeting($meetingline['MeetingID']);
										$ParentID = $meeting['ParentID'];
										$header = $meetingline['Title'];
										$formhref = 'zob/meetingline/list/' . $meetingline['MeetingID'];


										require_once( FRAMEWORK_PATH . 'models/entry/model.php');
										$this->model = new Entry( $this->registry, $ParentID );
										$entry = $this->model->getData();
										if ($this->model->isValid()) {
											$Breads = $entry['breads'];
											$ParentName = $entry['Name'];
										}
								
										break;									
									default:
										# code...
										break;
								}
						
								break;
							case 'preview':
								$template = 'document-preview.tpl.php';
								$templateHeader = 'document-preview-header.tpl.php';
								$templateFooter = 'document-preview-footer.tpl.php';

								require_once( FRAMEWORK_PATH . 'controllers/contact/controller.php');
								$contact = new Contactcontroller( $this->registry , false);
								$doc = $contact->readFromData();

								$registry->getObject('template')->dataToTags( $doc, '' );

								break;
							case 'save':
								$this->saveDocument();
								//$this->pageNotFound();
								break;
						}

						$this->registry->getObject('template')->getPage()->addTag( 'formhref', $formhref );						
						$this->registry->getObject('template')->getPage()->addTag( 'AgendaTypeOption', $AgendaTypeOption );						
						$this->registry->getObject('template')->getPage()->addTag( 'AgendaTypeID', $AgendaTypeID );						
						$this->registry->getObject('template')->getPage()->addTag( 'ParentID', $ParentID );						
						$this->registry->getObject('template')->getPage()->addTag( 'Breads', $Breads );						
						$this->registry->getObject('template')->getPage()->addTag( 'ParentName', $ParentName );						
						$this->registry->getObject('template')->getPage()->addTag( 'DocumentNo', $DocumentNo );						
						$this->registry->getObject('template')->getPage()->addTag( 'Today', $Today );						
						$this->registry->getObject('template')->getPage()->addTag( 'Header', $header );						

						$this->build($template,$templateHeader,$templateFooter);		
						break;
					case 'WS':
						// Je voláno jako XMLHttpRequest (function.js) a pouze loguje zobrazené položky
						switch ($urlBits[2]) {
							case 'unlink':
								$ID = isset($urlBits[3]) ? $urlBits[3] : '';
								$result = $this->wsUnlinkAgenda($ID);
								exit($result);
							case 'getContacts':
								$company = isset($urlBits[3]) ? $urlBits[3] : '';
								$firstname = isset($urlBits[4]) ? $urlBits[4] : '';
								$lastname = isset($urlBits[5]) ? $urlBits[5] : '';
								$result = '';

								$this->registry->getObject('db')->initQuery('contact','id,FullName');
								$this->registry->getObject('db')->setOrderBy('FullName');
								if($company)
									$this->registry->getObject('db')->setCondition("Company like '%$company%'");
								if($firstname)
									$this->registry->getObject('db')->setCondition("FirstName like '%$firstname%'");
								if($lastname)
									$this->registry->getObject('db')->setCondition("LastName like '%$lastname%'");
								if ($this->registry->getObject('db')->findSet()) {
									$contacts = $this->registry->getObject('db')->getResult();
									foreach ($contacts as $row) {
										$result .= implode(', ', $row) . "\n";
									}								
								};
								exit($result);
							case 'getnextdocumentno':
								$agendaTypeName = isset($urlBits[3]) ? $urlBits[3] : '';
								$update = isset($urlBits[4]) ? $urlBits[4] : 'false';

								$result = $agendaTypeName;

								$this->registry->getObject('db')->initQuery('agendatype');
								$this->registry->getObject('db')->setFilter('Name',$agendaTypeName);
								if ($this->registry->getObject('db')->findFirst()) {
									$agendatype = $this->registry->getObject('db')->getResult();
									$result = $this->getNextDocumentNo($agendatype['TypeID'],false);
								}
								exit($result);
						}
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
	private function build( $template = 'page.tpl.php' , $templateHeader = 'header.tpl.php' , $templateFooter = 'footer.tpl.php') 
	{
		// Category Menu
		$this->createCategoryMenu();

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

		// Build page
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-agenda.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates($templateHeader, $template, $templateFooter);
	}

	/**
     * Zobrazení chybové stránky, pokud agenda nebyla nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		$this->error("Pokus o zobrazení neznámého dokladu agendy");
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param string $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'agenda','');
			
		$this->errorMessage = $message;
		$this->build();
	}

    /**
	 * Generování menu
	 * @return void
	 */
	public function createCategoryMenu()
    {
		global $config;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$urlBits = $this->registry->getURLBits();
		$typeID = isset( $urlBits[2]) ? ($urlBits[2] != 'list' ? $urlBits[2] : 0) : 0;

        $sql = "SELECT TypeID as idCat, Name as titleCat,
					IF(TypeID = '$typeID','active','') as activeCat 
					FROM ".$pref."agendatype";
        
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        $cacheCategory = $this->registry->getObject('db')->cacheQuery( $sql );
        $this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'SQL', $cache ) );
    }

	public function createAgendaTypeOption()
	{
		$element = "<option TypeID='0'></option>";		
		$this->registry->getObject('db')->initQuery('agendatype');

		if ($this->registry->getObject('db')->findSet()) {
			$agendatypes = $this->registry->getObject('db')->getResult();
			foreach ($agendatypes as $agendatype) {
				$TypeID = $agendatype['TypeID'];
				$Name = $agendatype['Name'];
				$element .= "<option TypeID='$TypeID'>$Name</option>";		
			}
		}		
		return $element;
	}

    /**
     * Zobrazení položek agendy
	 * @param string $TypeID = ID agendy
     * @return void
     */
	private function listAgenda( $TypeID )
	{
		global $caption, $deb;		
		$sql = "SELECT a.ID,a.TypeID,a.DocumentNo,a.Description,a.EntryID,a.CreateDate,a.ExecuteDate,e.Name ".
					 "FROM ".$this->prefDb."agenda as a ".
					 "LEFT JOIN ".$this->prefDb."dmsentry as e ON a.EntryID = e.ID ".
					 "WHERE a.TypeID = $TypeID ".
					 "ORDER BY a.DocumentNo DESC ";

		$this->registry->getObject('db')->initQuery('agendatype');
		$this->registry->getObject('db')->setFilter('TypeID',$TypeID);
		if ($this->registry->getObject('db')->findFirst())
		{
			$agendatype = $this->registry->getObject('db')->getResult();
		}else{
			$this->pageNotFound();
			return;
		}
	 
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if($perSet > 0)
		{
			// Zobrazení výsledku
			$templateList = 'agenda-list.tpl.php';
			$templateCard = 'agenda-edit.tpl.php';

			$cache = $this->listResult( $sql );
			if($this->registry->getObject('db')->isEmpty( $cache )){
				$this->registry->getObject('template')->getPage()->addTag( 'ID', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'DocumentNo', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'Description', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'CreateDate', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'ExecuteDate', '' );				
			}else{
				$this->registry->getObject('template')->getPage()->addTag( 'AgendaList', array( 'SQL', $cache ) );
			}
			$this->registry->getObject('template')->getPage()->addTag( 'TypeID', $TypeID );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditDocumentNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditDescription', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditCreateDate', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditExecuteDate', '' );				

			$this->registry->getObject('template')->addTemplateBit('editcard', $templateCard);
			$this->build($templateList);
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
	}


    /**
     * Zobrazení seznam typů agend
     * @return void
     */
	private function listAgendaType( )
	{
		global $caption,$deb;		
    	$sql = "SELECT * FROM ".$this->prefDb."agendatype ";

		// Zobrazení výsledku
		$templateList = 'agenda-type-list.tpl.php';
		$cache = $this->listResult( $sql );
		if($this->registry->getObject('db')->isEmpty( $cache )){
			$this->registry->getObject('template')->getPage()->addTag( 'TypeID', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Name', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'NoSeries', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'LastNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'editcard', '' );				
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'AgendaTypeList', array( 'SQL', $cache ) );
		}
		$this->build($templateList);
	}

	/**
	 * SQL dotaz přeskládán do listu položek v $cache
	 * @param string $sql - SELECT dotaz
	 * @return int $cache - index výsledku dotazu
	 */
	private function listResult( $sql )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if($perSet > 0)
		{
			// Group records by Page
			$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			return ( $cache );
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
		return -1;
    }	

	/**
	 * Založení nového typu dokumentu
	 * @return void
	 */
	private function addAgenda( $TypeID )
	{
		global $config, $caption;

		require_once( FRAMEWORK_PATH . 'models/agenda/model.php');
		$this->model = new Agenda( $this->registry, '' );
		$this->model->initNew( $TypeID );
		$this->listAgenda( $TypeID );
	}	

	private function saveDocument()
	{
		$urlBits = $this->registry->getURLBits();     

		$Table = isset($_POST["Table"]) ? $_POST["Table"] : 0;
		switch ($Table) {
			case 'meetingline':
				// Form fields				
				$post = $_POST;
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : 0;
				$MeetingLineID = isset($_POST['MeetingLineID']) ? $_POST['MeetingLineID'] : 0;
				
				
				$ContactID = isset($_POST['ContactID']) ? $_POST['ContactID'] : '';
				$ParentID = isset($_POST['ParentID']) ? $_POST['ParentID'] : '';
				$AgendaTypeID = isset($_POST['AgendaTypeID']) ? $_POST['AgendaTypeID'] : '';
				$ParentName = isset($_POST['ParentName']) ? $_POST['ParentName'] : '';
				$FileName = isset($_POST['FileName']) ? $_POST['FileName'] : '';

				require_once( FRAMEWORK_PATH . 'controllers/contact/controller.php');
				$contact = new Contactcontroller( $this->registry , false);
				$doc = $contact->readFromData();
			
				break;
			
			default:
				# code...
				break;
		}

	}

	/**
	 * Založení nového typu dokumentu
	 * @return void
	 */
	private function addAgendaType( )
	{
		global $config, $caption;

		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Název musí být vyplněn!');
			return;
		};
		$NoSeries = isset($_POST['NoSeries']) ? $_POST['NoSeries'] : '';
		if ($Name == ''){
			$this->error('Číselná řada musí být vyplněna!');
			return;
		};

		$this->registry->getObject('db')->initQuery('agendatype');
		$this->registry->getObject('db')->setFilter('NoSeries',$NoSeries);
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->error("Kód číselné řady $NoSeries již existuje!");
			return;
		}

		$data = array();
		$data['Name'] = $Name;
		$data['NoSeries'] = $NoSeries;
		$this->registry->getObject('db')->insertRecords('agendatype',$data);
		$this->listAgendaType();
	}	

	
	/**
	 * Editace typu agendy
	 * @return void
	 */
	private function modifyAgendaType( $TypeID )
	{
		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Název musí být vyplněn!');
			return;
		};
		$NoSeries = isset($_POST['NoSeries']) ? $_POST['NoSeries'] : '';
		if ($Name == ''){
			$this->error('Číselná řada musí být vyplněna!');
			return;
		};
		$this->registry->getObject('db')->initQuery('agendatype');
		$this->registry->getObject('db')->setFilter('NoSeries',$NoSeries);
		$this->registry->getObject('db')->setCondition("NoSeries <> $TypeID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->error("Kód číselné řady $NoSeries již existuje!");
			return;
		}

		// Update
		$changes = array();
		$changes['Name'] = $Name;
		$changes['NoSeries'] = $NoSeries;
		$condition = "TypeID = '$TypeID'";
		$this->registry->getObject('db')->updateRecords('agendatype',$changes, $condition);
		$this->listAgendaType();
	}

	/**
	 * Výmaz typu agendy
	 * @return void
	 */
	private function deleteAgendaType( $TypeID )
	{
		if ($this->isAgendaTypeUsed( $TypeID ))
		{
			$NoSeries = $this->getNoSeries($TypeID);
			$this->error("Číselná řada $NoSeries se používá, nelze ji odstranit.");
			return;
		};
		
		$condition = "TypeID = ".$TypeID;
		$this->registry->getObject('db')->deleteRecords( 'agendatype', $condition, 1); 
		$this->listAgendaType();

	}

    /**
     * Test, zda byl typ již použitý - podle TypeID
     * @param int $TypeID - Maska číselné řady agendy
     * @return boolean 
     */
    function isAgendaTypeUsed( $TypeID ) 
    {
        $this->registry->getObject('db')->initQuery('agenda');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
        if($this->registry->getObject('db')->isEmpty())
            return false;
        return true;
    }

    /**
     * Vrací kód číselné řady
     * @param int $TypeID
     * @return string $NoSeries
     */
    function getNoSeries( $TypeID ) 
    {
        $this->registry->getObject('db')->initQuery('agendatype');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
        if($this->registry->getObject('db')->findFirst()){
			$agendatype = $this->registry->getObject('db')->getResult();				
			return $agendatype['NoSeries'];
		};
        return '';
    }

	/**
     * Vrací záznam typu číselné řady
     * @param int $TypeID
     * @return array $agendatype table
	 */
	function getAgendatype( $TypeID ) 
    {
        $agendatype = null;
		$this->registry->getObject('db')->initQuery('agendatype');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
        if($this->registry->getObject('db')->findFirst()){
			$agendatype = $this->registry->getObject('db')->getResult();		
			
			$this->registry->getObject('db')->initQuery('agenda');
			$this->registry->getObject('db')->setFilter('NoSeries',$agendatype['NoSeries']);
			$this->registry->getObject('db')->setOrderBy('DocumentNo');
			if($this->registry->getObject('db')->findLast()){
				$agendatype2 = $this->registry->getObject('db')->getResult();				
				$agendatype['LastNo'] = $agendatype2['DocumentNo'];
			}else{
				$agendatype['LastNo'] = $agendatype['NoSeries'];
			};					
			// Update
			$changes = array();
			$changes['LastNo'] = $agendatype['LastNo'];
			$condition = "TypeID = '$TypeID'";
			$this->registry->getObject('db')->updateRecords('agendatype',$changes, $condition);
		};
        return $agendatype;
    }

	/**
	 * Vrací první volné číslo, nebo založí nové
	 * @param int $TypeID
	 * @return string $DocumentNo	 * 
	 */
	function getNextDocumentNo( $TypeID , $update = true)
	{
		$agendatype = $this->getAgendatype( $TypeID );
		$noSeries = $agendatype['NoSeries'];
		$DocumentNo = '';

		$this->registry->getObject('db')->initQuery('agenda');
        $this->registry->getObject('db')->setFilter('NoSeries',$noSeries);
        $this->registry->getObject('db')->setCondition("IFNULL(Description,'') = ''");
        $this->registry->getObject('db')->setOrderBy('DocumentNo');

        if($this->registry->getObject('db')->findFirst()){
			$agenda = $this->registry->getObject('db')->getResult();				
			$DocumentNo = $agenda['DocumentNo'];
		}else{
			$DocumentNo = $agendatype['LastNo'];
			++$DocumentNo;

			// Insert New to Agenda
			$data = array();
			$data['TypeID'] = $TypeID;
			$data['DocumentNo'] = $DocumentNo;
			$data['NoSeries'] = $noSeries;
			$data['CreateDate'] = date("Y-m-d H:i:s");
			$this->registry->getObject('db')->insertRecords('agenda',$data);			

			// Update
			if ($update) {
				$changes = array();
				$changes['LastNo'] = $DocumentNo;
				$condition = "TypeID = '$TypeID'";
				$this->registry->getObject('db')->updateRecords('agendatype', $changes, $condition);
			}
		};
		return $DocumentNo;
	}

	/**
	 * Odstranení odkazu agendy na dokument (číslo jednací)
	 * @param string $ID = ID agendy
	 */
	private function unlinkAgenda($ID)
	{
		$this->registry->getObject('db')->initQuery('agenda');
		$this->registry->getObject('db')->setFilter('ID',$ID);
		if ($this->registry->getObject('db')->findFirst())
		{
			$agenda = $this->registry->getObject('db')->getResult();
			$TypeID = $agenda['TypeID'];
			$changes =  array();
			$changes['Description'] = '';
			$changes['EntryID'] = '';
			$condition = "ID = '$ID'";
			$this->registry->getObject('db')->updateRecords('agenda',$changes, $condition);
			$this->listAgenda($TypeID);
		}else{
			$this->pageNotFound();
		}
	}

    /**
     * Webová služba 
	 * @param string $ID = ID položky Agendy
     * @return string = Návratová hodnota
	 *                  => OK    = zápis proběhl korektně
	 *                  => Error = zápis do logu skončil chybou
     */
	private function wsUnlinkAgenda( $ID )
	{
		global $deb;
		require_once( FRAMEWORK_PATH . 'models/agenda/model.php');
		$this->model = new Agenda( $this->registry, $ID );

		if( $this->model->isValid() )
		{
			$agenda = $this->model->getData();
	
			$changes =  array();
			$changes['Description'] = '';
			$changes['EntryID'] = '';
			$condition = "ID = '$ID'";
			$this->registry->getObject('db')->updateRecords('agenda',$changes, $condition);			
			return 'OK';
		}
		return 'Error';
	}	
}