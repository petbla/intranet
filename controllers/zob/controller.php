<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.11.2022
 */
class Zobcontroller{
	
	private $registry;
	private $message = '';

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
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
				$this->pageNotFound();
			}
			else
			{
				$action = isset($urlBits[2]) ? $urlBits[2] : 'list';
				$ID = '';
				switch ($urlBits[1]) {
					case 'electionperiod':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->electionperiod($action);
						break;
					case 'meetingtype':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->meetingtype($action);
						break;
					case 'member':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->member($action);
						break;
					case 'contact':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->contact($action);
						break;
					case 'meeting':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->meeting($action);
						break;
					case 'meetingline':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->meetingline($action);
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
		$this->createCategoryMenu();

		// Build page
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-zob.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template , 'footer.tpl.php');
	}
	
    /**
     * Zobrazení chybové stránky, pokud agenda nebyla nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámé agendy",'agenda','');
		$this->build('page-notfound.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'agenda','');
		
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		$this->build();
	}

    /**
	 * Generování menu
	 * @return void
	 */
	public function createCategoryMenu()
    {
		global $caption;
		$urlBits = $this->registry->getURLBits();

		$post = $_POST;
		switch ($urlBits[1]) {
			case 'meeting':
				$typeID = 'meetingtype/list/';
				$MeetingTypeID = isset( $urlBits[3]) ? $urlBits[3] : (isset($_POST['MeetingTypeID']) ? $_POST['MeetingTypeID'] : '');
				$typeID .= $MeetingTypeID;
				break;			
			case 'meetingline':
				$typeID = 'meetingtype/list/';
				$MeetingID = isset( $urlBits[3]) ? $urlBits[3] : (isset($_POST['MeetingID']) ? $_POST['MeetingID'] : '');
				$meeting = $this->getMeeting($MeetingID);
				$typeID .= $meeting['MeetingTypeID'];
				break;			
			default:
				$typeID = isset( $urlBits[1]) ? $urlBits[1] : '';
				$typeID .= isset( $urlBits[2]) ? '/'.$urlBits[2] : '';
				$typeID .= isset( $urlBits[3]) ? '/'.$urlBits[3] : '';
		}

		$electionperiod = $this->getActualElectionperiod();

		$rec['idCat'] = 'electionperiod';
		$rec['titleCat'] = $caption['electionperiod'].' - '.$electionperiod['PeriodName'];
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;
	
		// Výběr typů jednání pro aktuální volební období
		if ($electionperiod){
			$this->registry->getObject('db')->initQuery('meetingtype');
			$this->registry->getObject('db')->setFilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);	
			if ($this->registry->getObject('db')->findSet()){
				$result = $this->registry->getObject('db')->getResult();
				foreach ($result as $mt) {
					$rec['idCat'] = 'meetingtype/list/'.$mt['MeetingTypeID'];
					$rec['titleCat'] = $mt['MeetingName'] ;
					$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
					$table[] = $rec;
				} 
			} 
		}
		$cache = $this->registry->getObject('db')->cacheData( $table );
		$this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'DATA', $cache ) );
    }
	

	/**
	 * Modifikace tabulky volebního období
	 * @return void
	 */
	private function electionperiod( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     

		switch ($action) {
			case 'modify':
				$ElectionPeriodID = isset($_POST["ElectionPeriodID"]) ? $_POST["ElectionPeriodID"] : '';
				break;
			case 'delete':
				$ElectionPeriodID = isset($urlBits[3]) ? $urlBits[3] : '';
				break;
			case 'add':
				$ElectionPeriodID = 0;
				break;
			case 'active':
				$ElectionPeriodID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$this->setElectionperiodActive( $ElectionPeriodID );
				$this->listElectionPeriod();
				return;
				break;
			default:
				$this->listElectionPeriod();
				return;
		}		

		$PeriodName = isset($_POST['PeriodName']) ? $_POST['PeriodName'] : '';
		$Actual = isset($_POST['Actual']) ? $_POST['Actual'] : '';
		$Actual = $Actual != '' ? $Actual : 0;

		if ($action == 'delete'){
			if ($this->isElectionperiodUsed($ElectionPeriodID)){
				$this->message = "Volební období $PeriodName již bylo použito, nelze jej odstranit!";
				$this->listElectionperiod();
				return;	
			}
			$condition = "ElectionPeriodID = ".$ElectionPeriodID;
			$this->registry->getObject('db')->deleteRecords( 'electionperiod', $condition, 1); 
			$this->listElectionperiod();
			return;
		}

		if ($PeriodName == ''){
			$this->message = 'Název musí být vyplněn!';
			$this->listElectionperiod();
			return;
		};		
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setFilter('PeriodName',$PeriodName);
		if ($ElectionPeriodID > 0)
			$this->registry->getObject('db')->setCondition("ElectionPeriodID <> $ElectionPeriodID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->message = "Volební období $PeriodName již existuje!";
			$this->listElectionPeriod( );
			return;
		}

		// Reset pole Actual na všech záznamech
		if ($Actual == 1){
			$changes = array();
			$changes['Actual'] = 0;
			$this->registry->getObject('db')->updateRecords('electionperiod',$changes, '');
		}

		$data = array();
		$data['PeriodName'] = $this->registry->getObject('db')->sanitizeData($PeriodName);
		$data['Actual'] = $Actual;
		if ($action == 'add'){
			$this->registry->getObject('db')->insertRecords('electionperiod',$data);
		}else{
			$condition = "ElectionPeriodID = $ElectionPeriodID";
			$this->registry->getObject('db')->updateRecords('electionperiod',$data,$condition);
		}
		$this->listElectionperiod();
	}	

	/**
	 * Modifikace tabulky typu jednání
	 * @return void
	 */
	private function meetingtype( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     

		$post = $_POST;
		$ElectionPeriodID = isset($_POST["ElectionPeriodID"]) ? $_POST["ElectionPeriodID"] : '';
		switch ($action) {
			case 'modify':
				$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : '';
				break;
			case 'list':
				$MeetingTypeID = isset($urlBits[3]) ? $urlBits[3] : '';
				$this->listMeetingType( $MeetingTypeID );
				return;
			case 'delete':
				$MeetingTypeID = isset($urlBits[3]) ? $urlBits[3] : '';
				$ElectionPeriodID = isset($urlBits[4]) ? $urlBits[4] : '';
				break;
			case 'add':
				$MeetingTypeID = 0;
				break;
			default:
				$this->listElectionperiod( $ElectionPeriodID );
				return;
		}		

		$MeetingName = isset($_POST['MeetingName']) ? $_POST['MeetingName'] : '';
		$Members = isset($_POST['Members']) ? $_POST['Members'] : 0;
		if ($action == 'delete'){
			if ($this->isMeetingtypeUsed($MeetingTypeID)){
				$this->message = "Typ jednání $MeetingName pro volební období již bylo použito, nelze jej odstranit!";
				$this->listElectionperiod( $ElectionPeriodID );
				return;	
			}

			$condition = "MeetingTypeID = $MeetingTypeID";
			$this->registry->getObject('db')->deleteRecords( 'meetingtype', $condition, 1); 
			$this->listElectionPeriod( $ElectionPeriodID );
			return;
		}

		if ($MeetingName == ''){
			$this->message = 'Název musí být vyplněn!';
			$this->listElectionperiod( $ElectionPeriodID );
			return;
		};		
		if ($Members == 0){
			$this->message = "Zadejte počet členů.";
			$this->listElectionperiod( $ElectionPeriodID );
			return;	
		};

		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
		$this->registry->getObject('db')->setFilter('MeetingName',$MeetingName);
		if ($MeetingTypeID > 0)
			$this->registry->getObject('db')->setCondition("MeetingTypeID <> $MeetingTypeID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->message = "Typ jednání $MeetingName pro volební období již existuje!";
			$this->listElectionperiod( $ElectionPeriodID );
			return;
		}

		$data = array();
		$data['ElectionPeriodID'] = $ElectionPeriodID;
		$data['MeetingName'] = $this->registry->getObject('db')->sanitizeData($MeetingName);
		$data['Members'] = $Members;
		if ($action == 'add'){
			$this->registry->getObject('db')->insertRecords('meetingtype',$data);
		}else{
			$condition = "MeetingTypeID = $MeetingTypeID";
			$this->registry->getObject('db')->updateRecords('meetingtype',$data,$condition);
		}
		$this->listElectionperiod( $ElectionPeriodID );
	}	

	/**
	 * Založení kontaktu
	 * @return void
	 */
	private function contact( $action )
	{
		$post = $_POST;
		$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : '';

		if ($MeetingTypeID == ''){
			$this->message = 'Není vyplněno ID jednání';
			$this->listElectionperiod( );
			return;
		}
		$meetingtype = $this->getMeetingtype($MeetingTypeID);
		$ElectionPeriodID = $meetingtype['ElectionPeriodID'];

		switch ($action) {
			case 'add':
				$ContactID = $this->registry->getObject('fce')->GUID();
				break;
			default:
				$this->listElectionperiod( $ElectionPeriodID, $MeetingTypeID );
				return;
		}
		
		// Založení kontaktu		
		$contact = array();
		$contact['ID'] = $ContactID;
		$contact['Title'] = isset($_POST['newContactTitle']) ? $_POST['newContactTitle'] : ''; 
		$contact['FirstName'] = isset($_POST['newContactFirstName']) ? $_POST['newContactFirstName'] : ''; 
		$contact['LastName'] = isset($_POST['newContactLastName']) ? $_POST['newContactLastName'] : ''; 
		$contact['Email'] = isset($_POST['newContactEmail']) ? $_POST['newContactEmail'] : ''; 
		$contact['Phone'] = isset($_POST['newContactPhone']) ? $_POST['newContactPhone'] : ''; 
		$FullName = $contact['LastName'];
		if($contact['FirstName'] !== "")
		{
			$sp = ($FullName !== "") ? " " : "";
			$FullName = $FullName . $sp . $contact['FirstName'];
		}
		if($contact['Title'] !== "")
		{
			$sp = ($FullName !== "" ) ? " " : "";
			$FullName = $FullName . $sp . $contact['Title'];
		}
		$contact['FullName'] = $this->registry->getObject('db')->sanitizeData($FullName);
		$this->registry->getObject('db')->insertRecords('contact',$contact);

		// Založení člena jednání
		$member = array();
		$member['MeetingTypeID'] = $MeetingTypeID;
		$member['ContactID'] = $ContactID;
		$this->registry->getObject('db')->insertRecords('member',$member);

		$this->listElectionperiod( $ElectionPeriodID , $MeetingTypeID );
	}
	
	/**
	 * Modifikace tabulky členů jednání
	 * @return void
	 */
	private function member( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     

		$post = $_POST;
		$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : '';
		$MeetingTypeID = isset($urlBits[4]) ? $urlBits[4] : $MeetingTypeID;

		if ($MeetingTypeID == ''){
			$this->message = 'Není vyplněno ID jednání';
			$this->listElectionperiod( );
			return;
		}
		$meetingtype = $this->getMeetingtype($MeetingTypeID);
		$ElectionPeriodID = $meetingtype['ElectionPeriodID'];

		switch ($action) {
			case 'modify':
				$MemberID = isset($_POST["MemberID"]) ? $_POST["MemberID"] : '';
				break;
			case 'delete':
				$MemberID = isset($urlBits[3]) ? $urlBits[3] : '';
				break;
			case 'add':
				$MemberID = 0;
				break;
			default:
				$this->listElectionperiod( $ElectionPeriodID, $MeetingTypeID );
				return;
		}		

		if ($action == 'delete'){
			if ($this->isMemberUsed($MemberID)){
				$this->message = "Člen $MemberID již byl použit, nelze jej odstranit!";
				$this->listElectionPeriod( $ElectionPeriodID, $MeetingTypeID );
				return;	
			}
			$condition = "MemberID = $MemberID";
			$this->registry->getObject('db')->deleteRecords( 'member', $condition, 1); 
			$this->listElectionPeriod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}

		$MemberType = isset($_POST['MemberType']) ? $_POST['MemberType'] : '';
		$ContactName = isset($_POST['ContactName']) ? $_POST['ContactName'] : 0;
		if ($ContactName == ''){
			$this->message = 'Jméno musí být vyplněno.';
			$this->listElectionperiod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}

		$contact = $this->getContactByName($ContactName);
		if(!$contact){
			$this->message = "Jméno $ContactName nenalezeno v kontaktech.";
			$this->listElectionperiod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}
		$ContactID = $contact['ID'];

		$this->registry->getObject('db')->initQuery('member');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
		$this->registry->getObject('db')->setFilter('ContactID',$ContactID);
		if ($MemberID > 0)
			$this->registry->getObject('db')->setCondition("$MemberID <> $MemberID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->message = "Člen jednání $ContactName pro volební období již existuje!";
			$this->listElectionPeriod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}

		$countMember = $this->countRec('member', "MeetingTypeID = $MeetingTypeID");
		if ($countMember >= $meetingtype['Members']){
			$this->message = "Překročen maximální počet členů";
			$this->listElectionPeriod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}

		$data = array();
		$data['MemberID'] = $MemberID;
		$data['MeetingTypeID'] = $MeetingTypeID;
		$data['MemberType'] = $MemberType;
		$data['ContactID'] = $ContactID;
		if ($action == 'add'){
			$this->registry->getObject('db')->insertRecords('member',$data);
		}else{
			$condition = "MemberID = $MemberID";
			$this->registry->getObject('db')->updateRecords('member',$data,$condition);
		}
		$this->listElectionperiod( $ElectionPeriodID , $MeetingTypeID );
	}	
	
	/**
	 * Modifikace tabulky jednání
	 * @return void
	 */
	private function meeting( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     

		$post = $_POST;
		$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : 0;

		switch ($action) {
			case 'modify':
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : '';
				break;
			case 'delete':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				break;
			case 'add':
				$MeetingID = 0;
				$EntryNo = $this->geNextMeetingEntryNo( $MeetingTypeID );				
				break;
			default:
				$this->listMeetingType( $MeetingTypeID );
				return;
		}		
		$meeting = $this->getMeeting($MeetingID);
		if($meeting){
			$MeetingTypeID = $meeting['MeetingTypeID'];
			$EntryNo = $meeting['EntryNo'];
		}
		$meetingtype = $this->getMeetingtype($MeetingTypeID);
		
		if ($action == 'delete'){
			if ($meeting['Close'] == 1){
				$this->message = "Nelze odstranit uzavřené jednání.";
				$this->listMeetingType( $MeetingTypeID );
				return;
			}			

			if ($this->isMeetingUsed($MeetingID)){
				$this->message = "Jednání ".
					$this->getMeetingNo($meeting['MeetingID']).
					" již bylo použito, nelze jej odstranit!";
				$this->listMeetingType( $MeetingTypeID );
				return;	
			}
			
			$condition = "MeetingID = $MeetingID";
			$this->registry->getObject('db')->deleteRecords( 'meeting', $condition, 1); 
			$this->listMeetingType( $MeetingTypeID );
			return;
		}

		$AtDate = isset($_POST['AtDate']) ? $_POST['AtDate'] : null;
		$AtDate = $AtDate != '' ? $AtDate : null;
	
		if($AtDate)
			$Year = $this->registry->getObject('core')->formatDate($AtDate,'Y');
		else
			$Year = 0;
		$MeetingPlace = isset($_POST['MeetingPlace']) ? $_POST['MeetingPlace'] : '';
		$PostedUpDate = isset($_POST['PostedUpDate']) ? $_POST['PostedUpDate'] : '';
		$PostedUpDate = $PostedUpDate != '' ? $PostedUpDate : null;
		$PostedDownDate = isset($_POST['PostedDownDate']) ? $_POST['PostedDownDate'] : '';
		$PostedDownDate = $PostedDownDate != '' ? $PostedDownDate : null;
		$State = isset($_POST['State']) ? $_POST['State'] : '';
		$RecorderAtDate = isset($_POST['RecorderAtDate']) ? $_POST['RecorderAtDate'] : '';
		$RecorderAtDate = $RecorderAtDate != '' ? $RecorderAtDate : null;
		$RecorderBy = isset($_POST['RecorderBy']) ? $_POST['RecorderBy'] : '';
		$VerifierBy1 = isset($_POST['VerifierBy1']) ? $_POST['VerifierBy1'] : '';
		$VerifierBy2 = isset($_POST['VerifierBy2']) ? $_POST['VerifierBy2'] : '';
		$Close = isset($_POST['Close']) ? $_POST['Close'] : 0;

		// Test uzavření jednání
		if ($Close == 1){
			if ($meeting['AtDate'] ==null){
				$this->message = "Nelze uzavřít jednání pokud není vyplnměn termín jednání.";
				$this->listMeetingType( $MeetingTypeID );
				return;
			};
			if ($meeting['MeetingPlace'] == ''){
				$this->message = "Nelze uzavřít jednání pokud není vyplnměno místo jednání.";
				$this->listMeetingType( $MeetingTypeID );
				return;
			};
			if ($meeting['RecorderAtDate'] == null){
				$this->message = "Nelze uzavřít jednání pokud není vyplnměn datum zápisu.";
				$this->listMeetingType( $MeetingTypeID );
				return;
			};
			if ($meeting['RecorderBy'] == ''){
				$this->message = "Nelze uzavřít jednání pokud není vyplnměn zapisovatel.";
				$this->listMeetingType( $MeetingTypeID );
				return;
			};

			//TODO-Kontrola zadaných bodů z jednání
			if (true == true){
				$this->message = "Nelze uzavřít jednání pokud nejsou vyplněny body jednání.";
				$this->listMeetingType( $MeetingTypeID );
				return;
			};

		}

		$isTemplate = $this->isElectionperiodTemplate( $meetingtype['ElectionPeriodID'] );

		
		$data = array();
		
		// Pole šablony
		$data['MeetingTypeID'] = $MeetingTypeID;
		$data['EntryNo'] = $EntryNo;
		$data['MeetingPlace'] = $MeetingPlace;
		$data['RecorderBy'] = $RecorderBy;

		if (!$isTemplate){
			$data['Year'] = $Year;
			$data['AtDate'] = $AtDate;
			$data['PostedUpDate'] = $PostedUpDate;
			$data['PostedDownDate'] = $PostedDownDate;
			$data['State'] = $State;
			$data['RecorderAtDate'] = $RecorderAtDate;
			$data['VerifierBy1'] = $VerifierBy1;
			$data['VerifierBy2'] = $VerifierBy2;
			$data['Close'] = $Close;
		}

		if ($action == 'add'){
			// Check Template
			if ($isTemplate){
				if ($data['EntryNo'] <> 1){
					$this->message = "Šablona jednání může mít jen jeden vzorový zápis";
					$this->listMeetingType( $MeetingTypeID );
					return;					
				}
			}

			// Kontrola, zda jsou všechny zápisy uzavřeny
			$this->registry->getObject('db')->initQuery('meeting');
			$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
			$this->registry->getObject('db')->setFilter('Close',0);
			if (!$this->registry->getObject('db')->isEmpty()){
				$this->message = 'Před založení nového jednání musí být všechny předchozí uzavřeny.';
				$this->listMeetingType( $MeetingTypeID );
				return;	
			}
			
			// Reset pole Actual na všech záznamech
			$changes = array();
			$changes['Actual'] = 0;
			$condition = "MeetingTypeID = $MeetingTypeID";
			$this->registry->getObject('db')->updateRecords('meeting',$changes,$condition);

			$data['Actual'] = 1;
			$this->registry->getObject('db')->insertRecords('meeting',$data);
		}else{
			$condition = "MeetingID = $MeetingID";
			$this->registry->getObject('db')->updateRecords('meeting',$data,$condition);
		}
		$this->listMeetingType( $MeetingTypeID );
	}	

		/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingline( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
			case 'moveup':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				$MeetingLineID = isset($urlBits[4]) ? $urlBits[4] : null;
				if($MeetingLineID){
					$this->moveMeetingline($MeetingLineID, -1 );
					$this->listMeeting( $MeetingID );
					return;
				}
				break;
			case 'movedown':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				$MeetingLineID = isset($urlBits[4]) ? $urlBits[4] : null;
				if($MeetingLineID){
					$this->moveMeetingline($MeetingLineID, 1 );
					$this->listMeeting( $MeetingID );
					return;
				}
				break;
			case 'delete':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				$MeetingLineID = isset($urlBits[4]) ? $urlBits[4] : null;
				break;
			case 'list':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				if($MeetingID){
					$this->listMeeting( $MeetingID );
				}else
					$this->pageNotFound();
				return;
				break;			
		}		

		$post = $_POST;
		$MeetingLineID = isset($_POST["MeetingLineID"]) ? $_POST["MeetingLineID"] : $MeetingLineID;
		$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : $MeetingID;
		$meeting = $this->getMeeting($MeetingID);
		if($meeting){
			$meetingtype = $this->getMeetingtype($meeting['MeetingTypeID']);
			if($meetingtype)
				$electionperiod = $this->getElectionperiod($meetingtype['ElectionPeriodID']);
		}

		// Vložení bodů ze šablony
		if (isset($_POST['submitTemplate'])){
			
			$meetingTemplate = $this->readMeetingLinesFromTemplate( $meetingtype['MeetingName']);
			if($meetingTemplate == null){
				$this->message = 'Šablona pro jednání '.$meetingtype['MeetingName'].'nebyla nalezena.';
				$this->listMeeting( $MeetingID );
				return;		
			}
			foreach($meetingTemplate as $data){
				$data['MeetingLineID'] = null;
				$data['MeetingID'] = $MeetingID;
				$this->registry->getObject('db')->insertRecords('meetingline',$data);
			}
			
			$this->listMeeting( $MeetingID );
			return;
		}

		// Data z FORMuláře
		$data = array();
		$data['MeetingID'] = $MeetingID;
		$data['LineNo'] = isset($_POST['LineNo']) ? $_POST['LineNo'] : '';
		$data['LineType'] = isset($_POST['LineType']) ? $_POST['LineType'] : '';
		$data['PresenterID'] = isset($_POST['PresenterID']) ? $this->registry->getObject('db')->sanitizeData($_POST['PresenterID']) : '';
		$data['Title'] = isset($_POST['Title']) ? $this->registry->getObject('db')->sanitizeData($_POST['Title']) : '';
		$data['Content'] = isset($_POST['Content']) ? $this->registry->getObject('db')->sanitizeData($_POST['Content']) : '';
		$data['Discussion'] = isset($_POST['Discussion']) ? $this->registry->getObject('db')->sanitizeData($_POST['Discussion']) : '';
		$data['Vote'] = isset($_POST['Vote']) ? $_POST['Vote'] : 0;
		$data['VoteFor'] = isset($_POST['VoteFor']) ? $_POST['VoteFor'] : 0;
		$data['VoteAgainst'] = isset($_POST['VoteAgainst']) ? $_POST['VoteAgainst'] : 0;
		$data['VoteDelayed'] = isset($_POST['VoteDelayed']) ? $_POST['VoteDelayed'] : 0;


		// Kontroly
		if($meeting['Close'] == 1){
			$this->message = 'Nelze měnit zápis uzavřeného jednání.';
			$this->listMeeting( $MeetingID );
			return;				
		}

		if (($action == 'add') || ($action == 'modify'))
			if($data['Title'] == ''){
				$this->message = 'Text bodu jednání musí být vyplněn.';
				$this->listMeeting( $MeetingID );
				return;				
			}

		switch ($action) {
			case 'add':
				$data['LineNo'] = $this->geNextMeetinglineLineNo( $MeetingID );
				$this->registry->getObject('db')->insertRecords('meetingline',$data);
				break;
			case 'modify':
				$condition = "MeetingLineID = $MeetingLineID";
				$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
				break;
			case 'delete':
				if($this->isMeetingLineUsed( $MeetingLineID )){
					$this->message = 'Bod programu již obsahuje přílohy, nelze jej odstranit.';
				}else{
					$condition = "MeetingLineID = $MeetingLineID";
					$this->registry->getObject('db')->deleteRecords( 'meetingline', $condition, 1); 					

					// Přečíslování bodů
					$meetinglines = $this->readMeetingLines($MeetingID);
					if($meetinglines){
						$LineNo = 0; 
						foreach ($meetinglines as $meetingline){
							$LineNo += 1;
							$change['LineNo'] = $LineNo;
							$condition = "MeetingLineID = ".$meetingline['MeetingLineID'];
							$this->registry->getObject('db')->updateRecords('meetingline',$change,$condition);
						}
					}
				}
				break;
			default:
				$this->pageNotFound();
				return;	
			}
		$this->listMeeting( $MeetingID );
	}

	 /**
     * Zobrazení seznam volebních období
     * @return void
     */
	private function listElectionperiod( $activeElectionPeriodID = 0, $activeMemberTypeID = 0 )
	{
		global $caption,$deb;		

		// Řádky 'electionperiod'
		$sql = "SELECT * FROM ".$this->prefDb."electionperiod ORDER BY PeriodName";
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		if($this->registry->getObject('db')->isEmpty( $cache )){
			$this->registry->getObject('template')->getPage()->addTag( 'ElectionPeriodID', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Name', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Actual', '' );				
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'electionPeriodList', array( 'SQL', $cache ) );
		}
		$this->registry->getObject('template')->getPage()->addTag( 'activeElectionPeriodID', $activeElectionPeriodID );				
		$this->registry->getObject('template')->getPage()->addTag( 'activeMemberTypeID', $activeMemberTypeID );				

		// Podřádky 'meetingtype' pro každý záznam 'electionperiod'
		// a 'member' pro  každý záznam 'meetingtype'
		$electionperiod = $this->readElectionperiods();		
		if ($electionperiod){
			foreach ($electionperiod as $rec) {				
				$meetingtype = $this->readMeetingtypesByElectionperiodID($rec['ElectionPeriodID']);
				if ($meetingtype){
					$cache = $this->registry->getObject('db')->cacheData( $meetingtype );
					$this->registry->getObject('template')->getPage()->addTag( 'meetingTypeList'.$rec['ElectionPeriodID'], array( 'DATA', $cache ) );
					foreach($meetingtype as $mt){
						$members = $this->readMembers($mt['MeetingTypeID']);
						$result = array();
						if($members){
							foreach($members as $member){
								$contact = $this->getContactByID($member['ContactID']);
								if($contact)
									$member['ContactName'] = $contact['FullName'];
								else
									$member['ContactName'] = $member['MemberID'];
								$idx = $member['MemberType'];
								if ($idx)
									$member['MemberType'] = $caption[$idx];
								
								$member['MemberType'.$mt['MeetingTypeID']] = $member['MemberType'];
								$member['ContactName'.$mt['MeetingTypeID']] = $member['ContactName'];
								
								$result[] = $member;
							}						
							$cache = $this->registry->getObject('db')->cacheData( $result );						
							$this->registry->getObject('template')->getPage()->addTag( 'memberList'.$mt['MeetingTypeID'], array( 'DATA', $cache ) );		
						}else{
							$this->registry->getObject('template')->getPage()->addTag( 'ContactName'.$mt['MeetingTypeID'], '' );				
							$this->registry->getObject('template')->getPage()->addTag( 'MemberType'.$mt['MeetingTypeID'], '' );				
						}
					}
				}else{
					$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', '' );				
					$this->registry->getObject('template')->getPage()->addTag( 'ElectionPeriodID', '' );				
					$this->registry->getObject('template')->getPage()->addTag( 'MeetingName', '' );				
					$this->registry->getObject('template')->getPage()->addTag( 'Members', '' );					
				}
			} 
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'PeriodName','' );
		}

		$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', $this->message );						
		$this->registry->getObject('template')->addTemplateBit('meetingtypeCard', 'zob-meetingtype-list.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('memberCard', 'zob-member-list.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('memberTypeSelect', 'zob-member-type.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('newContactCard', 'zob-contact-new.tpl.php');

		$this->build('zob-electionperiod-list.tpl.php');
	}

	 /**
     * Zobrazení seznam zápisů jednání
     * @return void
     */
	private function listMeetingType( $MeetingTypeID )
	{

		// Zápis z jednání
		$sql = "SELECT * FROM ".$this->prefDb."meeting WHERE MeetingTypeID = $MeetingTypeID";
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		if(!$this->registry->getObject('db')->isEmpty( $cache )){
			$meetings = array();
			while( $meeting = $this->registry->getObject('db')->resultsFromCache( $cache ) )
			{
				$meetingtype = $this->getMeetingtype($MeetingTypeID);
				$electionperiod = $this->getElectionperiod($meetingtype['ElectionPeriodID']);

				$meeting['PeriodName'] = $electionperiod['PeriodName'];
				$meeting['MeetingName'] = $meetingtype['MeetingName'];

				$meeting['lineclass'] = $meeting['Close'] == 1 ? 'blue' : '';
				$meeting['disabled'] = $meeting['Close'] == 1 ? 'disabled' : '';

				if($meeting['AtDate'] != null){
					$meeting['AtDate_view'] = $this->registry->getObject('core')->formatDate($meeting['AtDate']);
				}else{
					if($this->isElectionperiodTemplate($meetingtype['ElectionPeriodID']))
						$meeting['AtDate_view'] = 'šablona';
					else
						$meeting['AtDate_view'] = 'aktuální';
				}
				$meeting['PostedUpDate_view'] = $this->registry->getObject('core')->formatDate($meeting['PostedUpDate']);
				$meeting['PostedDownDate_view'] = $this->registry->getObject('core')->formatDate($meeting['PostedDownDate']);
				$meeting['RecorderAtDate_view'] = $this->registry->getObject('core')->formatDate($meeting['RecorderAtDate']);
				$meeting['dmsClassName'] = 'meeting';
				
				
				$meetings[] = $meeting;
			}
			$cache = $this->registry->getObject('db')->cacheData( $meetings );
			$this->registry->getObject('template')->getPage()->addTag( 'meetingList', array( 'DATA', $cache ) );
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'Actual', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EntryNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Year', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'AtDate_view', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'PostedUpDate_view', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'PostedDownDate_view', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingID', 0 );				
		}
		$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', $this->message );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', $MeetingTypeID );						
		$this->registry->getObject('template')->addTemplateBit('editdMeetingCard', 'zob-meeting-edit.tpl.php');

		$this->build('zob-meeting-list.tpl.php');
	}

	 /**
     * Zobrazení seznamu bodů jednání
     * @return void
     */
	private function listMeeting( $MeetingID )
	{
		$meeting = $this->getMeeting($MeetingID);
		$MeetingTypeID = $meeting['MeetingTypeID'];
		$meetingtype = $this->getMeetingtype($MeetingTypeID);
		$electionperiod = $this->getElectionperiod($meetingtype['ElectionPeriodID']);
		$Year = $meeting['Year'];
		$EntryNo = $meeting['EntryNo'];
		$○r = $meetingtype['MeetingName']." - <b>$EntryNo/$Year</b>, datum jednání: ";
		$○r .= $this->registry->getObject('core')->formatDate($meeting['AtDate'],'d.m.Y');
				
		if($this->isElectionperiodTemplate($electionperiod['ElectionPeriodID']))
			$○r .= ' (šablona)';

		// Body zápisu z jednání
		$sql = "SELECT * FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID ORDER BY LineNo";
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );			
		if(!$this->registry->getObject('db')->isEmpty( $cache )){
			$meetinglines = array();
			while( $meetingline = $this->registry->getObject('db')->resultsFromCache( $cache ) )
			{
				# Code...
				
				$meetinglines[] = $meetingline;
			}
			$cache = $this->registry->getObject('db')->cacheData( $meetinglines );
			$this->registry->getObject('template')->getPage()->addTag( 'meetinglineList', array( 'DATA', $cache ) );
			$this->registry->getObject('template')->getPage()->addTag( 'isEmpty', 0 );				
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'LineType', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'LineNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Title', '== Uložit první záznam, nebo Vložit ze šablony ==' );				
			$this->registry->getObject('template')->getPage()->addTag( 'isEmpty', 1 );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingLineID', 0 );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingLineID', 0 );				
		}
		$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', $this->message );						
		$this->registry->getObject('template')->getPage()->addTag( 'Year', $Year );						
		$this->registry->getObject('template')->getPage()->addTag( 'EntryNo', $EntryNo );						
		$this->registry->getObject('template')->getPage()->addTag( 'Header', $○r );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingID', $MeetingID );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', $MeetingTypeID );						
		//$this->registry->getObject('template')->addTemplateBit('editdMeetingCard', 'zob-meetingline-edit.tpl.php');

		$this->build('zob-meetingline-list.tpl.php');
	}

	public function isElectionperiodTemplate ( $ElectionPeriodID )
	{
		// meetingtype
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
		$this->registry->getObject('db')->setCondition("PeriodName like '<%>'");
		return (!$this->registry->getObject('db')->isEmpty());
	}

	public function isElectionperiodUsed ( $ElectionPeriodID )
	{
		// meetingtype
		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
		if (!$this->registry->getObject('db')->isEmpty())
			return true;

		return false;
	}

	public function isMeetingtypeUsed($MeetingTypeID) 
	{
		// members
		$this->registry->getObject('db')->initQuery('member');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
		if (!$this->registry->getObject('db')->isEmpty())
			return true;

		// meeting
		$this->registry->getObject('db')->initQuery('meeting');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
		if (!$this->registry->getObject('db')->isEmpty())
			return true;

		return false;
	}

	public function isMemberUsed($MemberID) 
	{
		// Check if used somewhere

		return false;
	}

	public function isMeetingUsed($MeetingID) 
	{
		// meetingline
		$this->registry->getObject('db')->initQuery('meetingline');
		$this->registry->getObject('db')->setFilter('MeetingID',$MeetingID);
		if (!$this->registry->getObject('db')->isEmpty())
			return true;

		// inbox
		$this->registry->getObject('db')->initQuery('inbox');
		$this->registry->getObject('db')->setFilter('MeetingID',$MeetingID);
		if (!$this->registry->getObject('db')->isEmpty())
			return true;

		return false;
	}

	public function isMeetinglineUsed ( $MeetingLineID )
	{
		//TODO-kontrola příloh k meetingline


		return false;
	}

	public function readMeetingtypesByElectionperiodID ( $ElectionPeriodID  )
	{
		$meetingtype = null;
		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
		if ($this->registry->getObject('db')->findSet())
			$meetingtype = $this->registry->getObject('db')->getResult();			
		return $meetingtype;
	}

	public function readMeetingLinesFromTemplate( $MeetingName )
	{
		$electionperiod = null;
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setCondition("PeriodName like '<%>'");
		if (!$this->registry->getObject('db')->findFirst())
			return null;	
		$electionperiod = $this->registry->getObject('db')->getResult();

		$meetingtype = null;
		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);
		$this->registry->getObject('db')->setFilter('MeetingName',$MeetingName);
		if (!$this->registry->getObject('db')->findFirst())
			return null;	
		$meetingtype = $this->registry->getObject('db')->getResult();

		$meeting = null;
		$this->registry->getObject('db')->initQuery('meeting');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
		$this->registry->getObject('db')->setFilter('EntryNo',1);
		if (!$this->registry->getObject('db')->findFirst())
			return null;	
		$meeting = $this->registry->getObject('db')->getResult();

		$meetingline = null;
		$this->registry->getObject('db')->initQuery('meetingline');
		$this->registry->getObject('db')->setFilter('MeetingID',$meeting['MeetingID']);
		if ($this->registry->getObject('db')->findSet())
			$meetingline = $this->registry->getObject('db')->getResult();			
		return $meetingline;
	}

	public function readElectionperiods ( )
	{
		$electionperiod = null;
		$this->registry->getObject('db')->initQuery('electionperiod');
		if ($this->registry->getObject('db')->findSet())
			$electionperiod = $this->registry->getObject('db')->getResult();			
		return $electionperiod;
	}

	public function readMembers ( $MeetingTypeID  )
	{
		$member = null;
		$this->registry->getObject('db')->initQuery('member');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
		if ($this->registry->getObject('db')->findSet())
			$member = $this->registry->getObject('db')->getResult();			
		return $member;
	}

	public function readMeetingLines ( $MeetingID  )
	{
		$meetingline = null;
		$this->registry->getObject('db')->initQuery('meetingline');
		$this->registry->getObject('db')->setFilter('MeetingID',$MeetingID);
		$this->registry->getObject('db')->setOrderBy('LineNo');
		if ($this->registry->getObject('db')->findSet())
			$meetingline = $this->registry->getObject('db')->getResult();			
		return $meetingline;
	}


	public function getElectionperiod ($ElectionPeriodID )
	{
		$electionperiod = null;
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
		if ($this->registry->getObject('db')->findFirst())
			$electionperiod = $this->registry->getObject('db')->getResult();
		return $electionperiod;
	}

	public function getActualElectionperiod ( )
	{
		$electionperiod = null;
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setFilter('Actual',1);
		if ($this->registry->getObject('db')->findFirst())
			$electionperiod = $this->registry->getObject('db')->getResult();
		return $electionperiod;
	}

	public function getMeetingtype ( $MeetingTypeID )
	{
		$meetingtype = null;
		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
		if ($this->registry->getObject('db')->findFirst())
			$meetingtype = $this->registry->getObject('db')->getResult();			
		return $meetingtype;
	}

	public function getMeeting ( $MeetingID )
	{
		$meeting = null;
		$this->registry->getObject('db')->initQuery('meeting');
		$this->registry->getObject('db')->setFilter('MeetingID',$MeetingID);
		if ($this->registry->getObject('db')->findFirst())
			$meeting = $this->registry->getObject('db')->getResult();			
		return $meeting;
	}

	public function getMeetingNo ( $MeetingID , $withMeetingName = false)
	{
		$MeetingNo = '';
		$meeting = $this->getMeeting( $MeetingID );
		if($meeting){
			$MeetingNo = $meeting['EntryNo'].'/'.$meeting['Year'];
			if ($withMeetingName){
				$meetingtype = $this->getMeetingtype($meeting['MeetingTypeID']);
				$MeetingNo = $meetingtype['MeetingName'].' - '.$MeetingNo;
			}
		}
		return $MeetingNo;
	}

	public function getMeetingLine ( $MeetingLineID )
	{
		$meetingline = null;
		$this->registry->getObject('db')->initQuery('meetingline');
		$this->registry->getObject('db')->setFilter('MeetingLineID',$MeetingLineID);
		if ($this->registry->getObject('db')->findFirst())
			$meetingline = $this->registry->getObject('db')->getResult();			
		return $meetingline;
	}

	public function getMeetingLineByLineNo ( $MeetingID, $LineNo )
	{
		$meetingline = null;
		$this->registry->getObject('db')->initQuery('meetingline');
		$this->registry->getObject('db')->setFilter('MeetingID',$MeetingID);
		$this->registry->getObject('db')->setFilter('LineNo',$LineNo);
		if ($this->registry->getObject('db')->findFirst())
			$meetingline = $this->registry->getObject('db')->getResult();			
		return $meetingline;
	}

	public function getContactByName ( $FullName )
	{
		$contact = null;
		$this->registry->getObject('db')->initQuery('contact');
		$this->registry->getObject('db')->setFilter('FullName',$FullName);
		if ($this->registry->getObject('db')->findFirst())
			$contact = $this->registry->getObject('db')->getResult();			
		return $contact;
	}

	public function getContactByID ( $ContactID )
	{
		$contact = null;
		$this->registry->getObject('db')->initQuery('contact');
		$this->registry->getObject('db')->setFilter('ID',$ContactID);
		if ($this->registry->getObject('db')->findFirst())
			$contact = $this->registry->getObject('db')->getResult();			
		return $contact;
	}

	public function geNextMeetingEntryNo( $MeetingTypeID )
	{
		$sql = "SELECT max(EntryNo) as EntryNo FROM ".$this->prefDb."meeting WHERE MeetingTypeID = $MeetingTypeID";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $result['EntryNo'] + 1;				
	}

	public function geNextMeetinglineLineNo( $MeetingID )
	{
		$sql = "SELECT max(LineNo) as LineNo FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $result['LineNo'] + 1;				
	}

	public function setElectionperiodActive ( $ElectionPeriodID )
	{
		// Reset pole Actual na všech záznamech
		$changes = array();
		$changes['Actual'] = 0;
		$this->registry->getObject('db')->updateRecords('electionperiod',$changes, '');

		$data = array();
		$data['Actual'] = 1;
		$condition = "ElectionPeriodID = $ElectionPeriodID";
		$this->registry->getObject('db')->updateRecords('electionperiod',$data,$condition);
	}

	public function countRec($table, $filter = ''){
		$sql = "SELECT count(*) as pocet FROM ".$this->prefDb.$table;
		if ($filter != '')
			$sql .= " WHERE $filter";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $result['pocet'];				
	}

	public function moveMeetingline( $MeetingLineID, $step ){
		$fromMeetingline = $this->getMeetingLine( $MeetingLineID );
		$fromLine = $fromMeetingline['LineNo'];
		$toLine = $fromMeetingline['LineNo'] + $step;

		$meeting = $this->getMeeting($fromMeetingline['MeetingID']);
		if($meeting['Close'] == 1)
			return;				

		// Kontrola z prvního řádku výš (což nelze)
		if($toLine == 0)
			return;

		// Kontrola posledního řádku - $toLine neexistuje
		$toMeetingline = $this->getMeetinglineByLineNo( $fromMeetingline['MeetingID'] , $toLine );
		if($toMeetingline == null)
			return

		// Přesun
		$data = array();
		$data['LineNo'] = $toLine;
		$condition = "MeetingLineID = ".$fromMeetingline['MeetingLineID'];
		$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
		$data['LineNo'] = $fromLine;
		$condition = "MeetingLineID = ".$toMeetingline['MeetingLineID'];
		$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
		return;
	}


}