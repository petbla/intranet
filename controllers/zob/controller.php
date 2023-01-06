<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.11.2022
 */
class Zobcontroller{
	
	private $registry;
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
					case 'manage':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->manage($action);
						break;
					case 'meetingattachment':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;						
						$this->meetingattachment($action);
						break;
					default:
						$this->pageNotFound();
						break;
				}
			}
		}
	}

    /**
     * Sestavení stránky pro TISK
     * @return void
     */
	private function print( $content = '', $template = 'print-body.tpl.php' )
	{
		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);
		$this->registry->getObject('template')->getPage()->addTag('content',$content);

		// Build page
		$this->registry->getObject('template')->buildFromTemplates('print-header.tpl.php', $template , 'print-footer.tpl.php');
	}
	
    /**
     * Sestavení stránky
     * @return void
     */
	private function build( $template = 'page.tpl.php' )
	{
		// Category Menu
		$this->createCategoryMenu();

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

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
		$this->error("Pokus o zobrazení neznámé agendy");
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
		$this->errorMessage = $message;
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
				$typeID = 'meeting/list/';
				$MeetingTypeID = isset( $urlBits[3]) ? $urlBits[3] : (isset($_POST['MeetingTypeID']) ? $_POST['MeetingTypeID'] : '');
				$typeID .= $MeetingTypeID;
				break;			
			case 'meetingline':
				$typeID = 'meeting/list/';
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
					$rec['idCat'] = 'meeting/list/'.$mt['MeetingTypeID'];
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
				$this->errorMessage = "Volební období $PeriodName již bylo použito, nelze jej odstranit!";
				$this->listElectionperiod();
				return;	
			}
			$condition = "ElectionPeriodID = ".$ElectionPeriodID;
			$this->registry->getObject('db')->deleteRecords( 'electionperiod', $condition, 1); 
			$this->listElectionperiod();
			return;
		}

		if ($PeriodName == ''){
			$this->errorMessage = 'Název musí být vyplněn!';
			$this->listElectionperiod();
			return;
		};		
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setFilter('PeriodName',$PeriodName);
		if ($ElectionPeriodID > 0)
			$this->registry->getObject('db')->setCondition("ElectionPeriodID <> $ElectionPeriodID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->errorMessage = "Volební období $PeriodName již existuje!";
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
				$this->errorMessage = "Typ jednání $MeetingName pro volební období již bylo použito, nelze jej odstranit!";
				$this->listElectionperiod( $ElectionPeriodID );
				return;	
			}

			$condition = "MeetingTypeID = $MeetingTypeID";
			$this->registry->getObject('db')->deleteRecords( 'meetingtype', $condition, 1); 
			$this->listElectionPeriod( $ElectionPeriodID );
			return;
		}

		if ($MeetingName == ''){
			$this->errorMessage = 'Název musí být vyplněn!';
			$this->listElectionperiod( $ElectionPeriodID );
			return;
		};		
		if ($Members == 0){
			$this->errorMessage = "Zadejte počet členů.";
			$this->listElectionperiod( $ElectionPeriodID );
			return;	
		};

		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
		$this->registry->getObject('db')->setFilter('MeetingName',$MeetingName);
		if ($MeetingTypeID > 0)
			$this->registry->getObject('db')->setCondition("MeetingTypeID <> $MeetingTypeID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->errorMessage = "Typ jednání $MeetingName pro volební období již existuje!";
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
			$this->errorMessage = 'Není vyplněno ID jednání';
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
			$this->errorMessage = 'Není vyplněno ID jednání';
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
				$this->errorMessage = "Člen $MemberID již byl použit, nelze jej odstranit!";
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
			$this->errorMessage = 'Jméno musí být vyplněno.';
			$this->listElectionperiod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}

		$contact = $this->getContactByName($ContactName);
		if(!$contact){
			$this->errorMessage = "Jméno $ContactName nenalezeno v kontaktech.";
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
			$this->errorMessage = "Člen jednání $ContactName pro volební období již existuje!";
			$this->listElectionPeriod( $ElectionPeriodID, $MeetingTypeID );
			return;
		}

		if ($action == 'add'){
			$countMember = $this->countRec('member', "MeetingTypeID = $MeetingTypeID");
			if ($countMember >= $meetingtype['Members']){
				$this->errorMessage = "Překročen maximální počet členů";
				$this->listElectionPeriod( $ElectionPeriodID, $MeetingTypeID );
				return;
			}
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

		switch ($action) {
			case 'modify':
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : '';
				$meeting = $this->getMeeting($MeetingID);
				if($meeting)
					$MeetingTypeID = $meeting['MeetingTypeID'];
				$this->modifyMeeting($MeetingID);
				break;
			case 'delete':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				$meeting = $this->getMeeting($MeetingID);
				if($meeting)
					$MeetingTypeID = $meeting['MeetingTypeID'];
				$this->deleteMeeting($MeetingID);
				break;
			case 'add':
				$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : 0;
				$this->addMeeting($MeetingTypeID);
				break;
			case 'list':
				$MeetingTypeID = isset($urlBits[3]) ? $urlBits[3] : '';
				break;
		}		
		$this->listMeeting( $MeetingTypeID );
	}	

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingattachment( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     
		$MeetingID = 0;
		$MeetingLineID = 0;
		$AttachmentID = 0;

		switch ($action) {
			case 'assign':
				$AttachmentID = isset($urlBits['3']) ? $urlBits['3'] : 0;
				$MeetingLineID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$meetingline = $this->getMeetingline($MeetingLineID);
				if($meetingline){
					$MeetingID = $meetingline['MeetingID'];
				}else{
					$meetingattachment = $this->getMeetingattachment($AttachmentID);
					if($meetingattachment)
					$meetingline = $this->getMeetingline($meetingattachment['MeetingLineID']);
						$MeetingID = $meetingline['MeetingID'];
				}

				$this->assignMeetingattachment( $AttachmentID, $MeetingLineID );
				$MeetingLineID = 0;
				break;
		}
		if(!$MeetingID)
			$this->pageNotFound();
		$this->listMeetingLine( $MeetingID,$MeetingLineID );
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
				}
				$MeetingLineID = 0;
				break;
			case 'movedown':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				$MeetingLineID = isset($urlBits[4]) ? $urlBits[4] : null;
				if($MeetingLineID){
					$this->moveMeetingline($MeetingLineID, 1 );
				}
				$MeetingLineID = 0;
				break;
			case 'delete':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				$MeetingLineID = isset($urlBits[4]) ? $urlBits[4] : null;
				$this->deleteMeetingline($MeetingLineID);
				$MeetingLineID = 0;
				break;
			case 'modify':
				$MeetingLineID = isset($_POST["MeetingLineID"]) ? $_POST["MeetingLineID"] : $MeetingLineID;
				$this->modifyMeetingline($MeetingLineID);
				$meetingline = $this->getMeetingline($MeetingLineID);
				if($meetingline){
					$MeetingID = $meetingline['MeetingID'];
				}
				break;
			case 'add':
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : $MeetingID;
				$this->addMeetingline($MeetingID);
				break;
			case 'list':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
				if($MeetingID)
					$this->listMeetingLine( $MeetingID );
				break;
			default:
				$this->pageNotFound();
				return;
		}		
		$this->listMeetingLine( $MeetingID , $MeetingLineID );
	}

	/**
	 * Správa dat a modulu
	 * @return void
	 */
	private function manage( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
			case 'importMeeting':
				$this->importMeeting();
				return;
			case 'deleteAllMeeting':
				$this->deleteAllMeeting();
				break;
		}
		$this->listElectionPeriod();
	}

	private function addMeeting($MeetingTypeID){
		$EntryNo = $this->geNextMeetingEntryNo( $MeetingTypeID );				
		$meetingtype = $this->getMeetingtype($MeetingTypeID);
	
		// Data z formuláře
		$AtDate = isset($_POST['AtDate']) ? $_POST['AtDate'] : null;
		$AtDate = $AtDate != '' ? $AtDate : null;
		$AtTime = isset($_POST['AtTime']) ? $_POST['AtTime'] : null;
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
		$Close =  0;

		$isTemplate = $this->isElectionperiodTemplate( $meetingtype['ElectionPeriodID'] );
		
		$data = array();
		
		// Pole editace šablony
		$data['MeetingTypeID'] = $MeetingTypeID;
		$data['EntryNo'] = $EntryNo;
		$data['MeetingPlace'] = $MeetingPlace;
		$data['RecorderBy'] = $RecorderBy;
		$data['AtTime'] = $AtTime;

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

		// Check Template
		if ($isTemplate){
			if ($data['EntryNo'] <> 1){
				$this->errorMessage = "Šablona jednání může mít jen jeden vzorový zápis";
				return false;					
			}
		}

		// Kontrola, zda jsou všechny zápisy uzavřeny
		$this->registry->getObject('db')->initQuery('meeting');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
		$this->registry->getObject('db')->setFilter('Close',0);
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->errorMessage = 'Před založení nového jednání musí být všechny předchozí uzavřeny.';
			return false;					
		}
		
		// Reset pole Actual na všech záznamech
		$changes = array();
		$changes['Actual'] = 0;
		$condition = "MeetingTypeID = $MeetingTypeID";
		$this->registry->getObject('db')->updateRecords('meeting',$changes,$condition);

		$data['Actual'] = 1;
		$this->registry->getObject('db')->insertRecords('meeting',$data);
		return true;
	}	

	private function addMeetingline($MeetingID)
	{
		IF(!$MeetingID)
			return false;
		$meeting = $this->getMeeting($MeetingID);
		if($meeting){
			$meetingtype = $this->getMeetingtype($meeting['MeetingTypeID']);
		}

		// Vložení bodů ze šablony
		if (isset($_POST['submitTemplate'])){
			
			$meetingTemplate = $this->readMeetingLinesFromTemplate( $meetingtype['MeetingName']);
			if($meetingTemplate == null){
				$this->errorMessage = 'Šablona pro jednání '.$meetingtype['MeetingName'].'nebyla nalezena.';
				return false;
			}
			foreach($meetingTemplate as $data){
				$data['MeetingLineID'] = null;
				$data['MeetingID'] = $MeetingID;
				$this->registry->getObject('db')->insertRecords('meetingline',$data);
			}
			return true;
		}

		// Data z FORMuláře
		$data = array();
		$data['LineType'] = isset($_POST['LineType']) ? $_POST['LineType'] : '';
		$data['PresenterID'] = isset($_POST['PresenterID']) ? $this->registry->getObject('db')->sanitizeData($_POST['PresenterID']) : '';
		$data['Title'] = isset($_POST['Title']) ? $this->registry->getObject('db')->sanitizeData($_POST['Title']) : '';
		$data['Content'] = isset($_POST['Content']) ? $this->registry->getObject('db')->sanitizeData($_POST['Content']) : '';
		$data['Discussion'] = isset($_POST['Discussion']) ? $this->registry->getObject('db')->sanitizeData($_POST['Discussion']) : '';
		$data['Vote'] = isset($_POST['Vote']) ? $_POST['Vote'] : 0;
		$data['VoteFor'] = isset($_POST['VoteFor']) ? (int) $_POST['VoteFor'] : 0;
		$data['VoteAgainst'] = isset($_POST['VoteAgainst']) ? (int) $_POST['VoteAgainst'] : 0;
		$data['VoteDelayed'] = isset($_POST['VoteDelayed']) ? (int) $_POST['VoteDelayed'] : 0;

		// Kontroly
		if($meeting['Close'] == 1){
			$this->errorMessage = 'Nelze měnit zápis uzavřeného jednání.';
			return false;
		}

		if($data['Vote'] == 1){
			if(($data['VoteFor'] + $data['VoteAgainst'] + $data['VoteDelayed']) <> $meetingtype['Members']){
				$this->errorMessage = 'Součet hlasování neodpovídá počtu členů ' . $meetingtype['Members'];
				return false;
			}
		}

		if($data['Title'] == ''){
			$this->errorMessage = 'Text bodu jednání musí být vyplněn.';
			return false;
		}

		$data['MeetingID'] = $MeetingID;
		if($data['LineType'] == 'Podbod'){
			$data['LineNo'] = $this->getLastMeetinglineLineNo( $MeetingID );
		}else
			$data['LineNo'] = $this->getNextMeetinglineLineNo( $MeetingID );
		$this->registry->getObject('db')->insertRecords('meetingline',$data);

		return true;
	}

	private function deleteMeeting($MeetingID){
		$meeting = $this->getMeeting($MeetingID);

		if ($meeting['Close'] == 1){
			$this->errorMessage = "Nelze odstranit uzavřené jednání.";
			return false;
		}			

		if ($this->isMeetingUsed($MeetingID)){
			$this->errorMessage = "Jednání ".
				$this->getMeetingNo($meeting['MeetingID']).
				" již bylo použito, nelze jej odstranit!";
			return false;
		}
		
		$condition = "MeetingID = $MeetingID";
		$this->registry->getObject('db')->deleteRecords( 'meeting', $condition, 1); 
		return true;
	}	

	private function deleteAllMeeting(){
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setCondition("PeriodName like '<%>'");
		if ($this->registry->getObject('db')->findFirst()){
			$electionperiod = $this->registry->getObject('db')->getResult();
			$ElectionPeriodID = $electionperiod['ElectionPeriodID'];
		}
		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setCondition("ElectionPeriodID <> $ElectionPeriodID");
		if ($this->registry->getObject('db')->findSet()){
			$meetingtypes = $this->registry->getObject('db')->getResult();	
			foreach ($meetingtypes as $meetingtype){
				$MeetingTypeID = $meetingtype['MeetingTypeID'];
				$this->registry->getObject('db')->initQuery('meeting');
				$this->registry->getObject('db')->setCondition("MeetingTypeID = $MeetingTypeID");
				if ($this->registry->getObject('db')->findSet()){
					$meetings = $this->registry->getObject('db')->getResult();
					foreach ($meetings as $meeting){
						$MeetingID = $meeting['MeetingID'];
						$this->registry->getObject('db')->initQuery('meetingline');
						$this->registry->getObject('db')->setCondition("MeetingID = $MeetingID");
						if ($this->registry->getObject('db')->findSet()){
							$meetinglines = $this->registry->getObject('db')->getResult();
							foreach ($meetinglines as $meetingline){
								$MeetingLineID = $meetingline['MeetingLineID'];
								$condition = "MeetingLineID = $MeetingLineID";
								$this->registry->getObject('db')->deleteRecords('meetingattachment',$condition);
							}
						}								
						$condition = "MeetingID = $MeetingID";
						$this->registry->getObject('db')->deleteRecords('meetingline',$condition);
					}
				}
				$condition = "MeetingTypeID = $MeetingTypeID";
				$this->registry->getObject('db')->deleteRecords('meeting',$condition);
			}
		}
	}

	private function deleteMeetingline($MeetingLineID)
	{
		if((int) $MeetingLineID == 0)
			return false;
		$meetingline = $this->getMeetingline($MeetingLineID);
		$meeting = $this->getMeeting($meetingline['MeetingID']);

		// Kontroly
		if($meeting['Close'] == 1){
			$this->errorMessage = 'Nelze měnit zápis uzavřeného jednání.';
			return false;
		}

		if($meetingline['LineType'] == 'Bod'){
			if($this->hasChildMeetingline($MeetingLineID)){
				$this->errorMessage = 'Nejprve je třeba smazat všechny podbody.';
				return false;	
			}
		}

		if($this->isMeetingLineUsed( $MeetingLineID )){
			$this->errorMessage = 'Bod programu již obsahuje přílohy, nelze jej odstranit.';
			return false;
		}else{
			$condition = "MeetingLineID = $MeetingLineID";
			$this->registry->getObject('db')->deleteRecords( 'meetingline', $condition, 1); 					
			$this->reorderMeetingLines($meeting['MeetingID']);
		}
		return true;
	}
	
	private function reorderMeetingLines($MeetingID){
		// Přečíslování bodů
		$meetinglines = $this->readMeetingLines($MeetingID);
		if($meetinglines){
			$LineNo = 0; 
			foreach ($meetinglines as $meetingline){								
				if($meetingline['LineType'] != 'Podbod')
					$LineNo += 1;				
				$change['LineNo'] = $LineNo;
				$condition = "MeetingLineID = ".$meetingline['MeetingLineID'];
				$this->registry->getObject('db')->updateRecords('meetingline',$change,$condition);
			}
		}
	}

	private function modifyMeeting($MeetingID){

		$meeting = $this->getMeeting($MeetingID);	
		if($meeting == null)
			return false;
		$EntryNo = $meeting['EntryNo'];
		
		$MeetingTypeID = $meeting['MeetingTypeID'];
		$meetingtype = $this->getMeetingtype($MeetingTypeID);
		
		// Data z formuláře
		$AtDate = isset($_POST['AtDate']) ? $_POST['AtDate'] : $meeting['AtDate'];
		$AtDate = $AtDate != '' ? $AtDate : null;
		$AtTime = isset($_POST['AtTime']) ? $_POST['AtTime'] : $meeting['AtTime'];
		if($AtDate)
			$Year = $this->registry->getObject('core')->formatDate($AtDate,'Y');
		else
			$Year = 0;
		$MeetingPlace = isset($_POST['MeetingPlace']) ? $_POST['MeetingPlace'] : $meeting['MeetingPlace'];
		$PostedUpDate = isset($_POST['PostedUpDate']) ? $_POST['PostedUpDate'] : $meeting['PostedUpDate'];
		$PostedUpDate = $PostedUpDate != '' ? $PostedUpDate : null;
		$PostedDownDate = isset($_POST['PostedDownDate']) ? $_POST['PostedDownDate'] : $meeting['PostedDownDate'];
		$PostedDownDate = $PostedDownDate != '' ? $PostedDownDate : null;
		$State = isset($_POST['State']) ? $_POST['State'] : $meeting['State'];
		$RecorderAtDate = isset($_POST['RecorderAtDate']) ? $_POST['RecorderAtDate'] : $meeting['RecorderAtDate'];
		$RecorderAtDate = $RecorderAtDate != '' ? $RecorderAtDate : null;
		$RecorderBy = isset($_POST['RecorderBy']) ? $_POST['RecorderBy'] : $meeting['RecorderBy'];
		$VerifierBy1 = isset($_POST['VerifierBy1']) ? $_POST['VerifierBy1'] : $meeting['VerifierBy1'];
		$VerifierBy2 = isset($_POST['VerifierBy2']) ? $_POST['VerifierBy2'] : $meeting['VerifierBy2'];
		$Close = isset($_POST['Close']) ? $_POST['Close'] : $meeting['Close'];

		// Test uzavření jednání
		if ($Close == 1){
			if ($AtDate ==null){
				$this->errorMessage = "Nelze uzavřít jednání pokud není vyplnměn termín jednání.";
				return false;
			};
			if ($MeetingPlace == ''){
				$this->errorMessage = "Nelze uzavřít jednání pokud není vyplnměno místo jednání.";
				return false;
			};
			if ($RecorderAtDate == null){
				$this->errorMessage = "Nelze uzavřít jednání pokud není vyplnměn datum zápisu.";
				return false;
			};
			if ($RecorderBy == ''){
				$this->errorMessage = "Nelze uzavřít jednání pokud není vyplnměn zapisovatel.";
				return false;
			};

			//TODO-Kontrola zadaných bodů z jednání
			if (true == true){
				$this->errorMessage = "Nelze uzavřít jednání pokud nejsou vyplněny body jednání.";
				return false;
			};
		}
		$isTemplate = $this->isElectionperiodTemplate( $meetingtype['ElectionPeriodID'] );

		// Inicializace zánamu pro modifikaci
		$data = array();
		
		// Pole editace šablony
		$data['MeetingTypeID'] = $MeetingTypeID;
		$data['EntryNo'] = $EntryNo;
		$data['MeetingPlace'] = $MeetingPlace;
		$data['RecorderBy'] = $RecorderBy;
		$data['AtTime'] = $AtTime;

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

		// Uložení záznamu
		$condition = "MeetingID = $MeetingID";
		$this->registry->getObject('db')->updateRecords('meeting',$data,$condition);
		return true;
	}	

	private function modifyMeetingline($MeetingLineID)
	{	
		IF(!$MeetingLineID)
			return false;
		
		$meetingline = $this->getMeetingline($MeetingLineID);
		$MeetingID = $meetingline['MeetingID'];
		$meeting = $this->getMeeting($MeetingID);
		if($meeting){
			$meetingtype = $this->getMeetingtype($meeting['MeetingTypeID']);
			if($meetingtype)
				$electionperiod = $this->getElectionperiod($meetingtype['ElectionPeriodID']);
		}


		// Data z FORMuláře
		$post = $_POST;
		$data = array();
		$data['LineType'] = isset($_POST['LineType']) ? $_POST['LineType'] : $meetingline['LineType'];
		$data['Title'] = isset($_POST['Title']) ? $this->registry->getObject('db')->sanitizeData($_POST['Title']) : $meetingline['Title'];
		$data['Content'] = isset($_POST['Content']) ? $this->registry->getObject('db')->sanitizeData($_POST['Content']) : $meetingline['Content'];
		$data['Discussion'] = isset($_POST['Discussion']) ? $this->registry->getObject('db')->sanitizeData($_POST['Discussion']) : $meetingline['Discussion'];
		$data['Vote'] = isset($_POST['Vote']) ? $_POST['Vote'] : $meetingline['Vote'];
		$data['VoteFor'] = isset($_POST['VoteFor']) ? (int) $_POST['VoteFor'] : $meetingline['VoteFor'];
		$data['VoteAgainst'] = isset($_POST['VoteAgainst']) ? (int) $_POST['VoteAgainst'] : $meetingline['VoteAgainst'];
		$data['VoteDelayed'] = isset($_POST['VoteDelayed']) ? (int) $_POST['VoteDelayed'] : $meetingline['VoteDelayed'];
		$Presenter = isset($_POST['Presenter']) ? $_POST['Presenter'] : '';

		// Kontroly
		if($Presenter == '')
			$data['PresenterID'] = '00000000-0000-0000-0000-000000000000';
		else{
			$contact = $this->getContactByName($Presenter);
			if($contact){
				$data['PresenterID'] = $contact['ID'];
			}else{
				$this->errorMessage = "Předkladatel $Presenter nebyl nalezen v kontaktech. Zadejte jej ručně a pak akci opakujte.";
				return false;		
			}
		}

		if($meeting['Close'] == 1){
			$this->errorMessage = 'Nelze měnit zápis uzavřeného jednání.';
			return false;	
		}

		if($data['Vote'] == 1){
			if(($data['VoteFor'] + $data['VoteAgainst'] + $data['VoteDelayed']) <> $meetingtype['Members']){
				$this->errorMessage = 'Součet hlasování neodpovídá počtu členů ' . $meetingtype['Members'];
				return false;	
			}
		}

		if($data['Title'] == ''){
			$this->errorMessage = 'Text bodu jednání musí být vyplněn.';
			return false;	
		}

		$condition = "MeetingLineID = $MeetingLineID";
		$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);

		return true;
	}

	private function assignMeetingattachment( $AttachmentID, $MeetingLineID ){
		if(!$AttachmentID)
			return false;
			
		$change = array();
		$change['MeetingLineID'] = $MeetingLineID;
		$condition = "AttachmentID = $AttachmentID";
		$this->registry->getObject('db')->updateRecords('meetingattachment',$change,$condition);
		return true;
	}

	public function moveMeetingline( $MeetingLineID, $step ){
		$fromMeetingline = $this->getMeetingline( $MeetingLineID );
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
		$data['LineNo'] = -1;
		$condition = "MeetingID = ".$fromMeetingline['MeetingID']." AND LineNo = $fromLine";
		$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
		
		$data['LineNo'] = $fromLine;
		$condition = "MeetingID = ".$toMeetingline['MeetingID']." AND LineNo = $toLine";
		$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);

		$data['LineNo'] = $toLine;
		$condition = "MeetingID = ".$toMeetingline['MeetingID']." AND LineNo = -1";
		$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
		return;
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
								
								// Překlad typu člena
								$member['MemberTypeCSY'] = $member['MemberType'];
								$idx = $member['MemberTypeCSY'];
								if ($idx)
									$member['MemberTypeCSY'] = $caption[$idx];
								
								
								$member['MemberTypeCSY'.$mt['MeetingTypeID']] = $member['MemberTypeCSY'];
								$member['ContactName'.$mt['MeetingTypeID']] = $member['ContactName'];
								
								$result[] = $member;
							}						
							$cache = $this->registry->getObject('db')->cacheData( $result );						
							$this->registry->getObject('template')->getPage()->addTag( 'memberList'.$mt['MeetingTypeID'], array( 'DATA', $cache ) );		
						}else{
							$this->registry->getObject('template')->getPage()->addTag( 'ContactName'.$mt['MeetingTypeID'], '' );				
							$this->registry->getObject('template')->getPage()->addTag( 'MemberTypeCSY'.$mt['MeetingTypeID'], '' );				
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
	private function listMeeting( $MeetingTypeID )
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
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', $MeetingTypeID );						
		$this->registry->getObject('template')->addTemplateBit('editdMeetingCard', 'zob-meeting-edit.tpl.php');

		$this->build('zob-meeting-list.tpl.php');
	}

	 /**
     * Zobrazení seznamu bodů jednání
     * @return void
     */
	private function listMeetingLine( $MeetingID , $activeMeetingLineID = 0 )
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
				$meetingline['dmsClassName'] = 'meetingline';
				$contact = $this->getContactByID($meetingline['PresenterID']);
				if($contact)
					$meetingline['Presenter'] = $contact['FullName'];
				else
					$meetingline['Presenter'] = '';
				$meetingline['Attachments'] = $this->countRec('meetingattachment','MeetingLineID = '.$meetingline['MeetingLineID']);
				$meetingline['bold'] = $meetingline['LineType'] == 'Podbod' ? '' : 'bold';


				$meetinglines[] = $meetingline;
			}
			$cache = $this->registry->getObject('db')->cacheData( $meetinglines );
			$this->registry->getObject('template')->getPage()->addTag( 'meetinglineList', array( 'DATA', $cache ) );
			$this->registry->getObject('template')->getPage()->addTag( 'isEmpty', 0 );				

			foreach($meetinglines as $meetingline){
				$sql = "SELECT * FROM ".$this->prefDb."meetingattachment WHERE MeetingID = $MeetingID AND MeetingLineID = ".$meetingline['MeetingLineID'];
				$cache = $this->registry->getObject('db')->cacheQuery( $sql );			
				$this->registry->getObject('template')->getPage()->addTag( 'meetingattachmentList'.$meetingline['MeetingLineID'], array( 'SQL', $cache ) );
			}
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'LineType', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'LineNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Title', '== Uložit první záznam, nebo Vložit ze šablony ==' );				
			$this->registry->getObject('template')->getPage()->addTag( 'isEmpty', 1 );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingLineID', 0 );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingLineID', 0 );				
			$this->registry->getObject('template')->getPage()->addTag( 'Attachments', '' );				
			//$this->registry->getObject('template')->getPage()->addTag( 'Description', '' );				
		}

		// Přílkohy jednání (bez přiřazení k řádku)
		$sql = "SELECT * FROM ".$this->prefDb."meetingattachment WHERE MeetingID = $MeetingID AND MeetingLineID = 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );			
		if(!$this->registry->getObject('db')->isEmpty( $cache )){
			$meetingattachments = array();
			while( $meetingattachment = $this->registry->getObject('db')->resultsFromCache( $cache ) )
			{
				# Code...
				
				$meetingattachments[] = $meetingattachment;
			}
			$cache = $this->registry->getObject('db')->cacheData( $meetingattachments );
			$this->registry->getObject('template')->getPage()->addTag( 'meetingattachmentListNo0', array( 'DATA', $cache ) );
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'Description', '' );				
		}

		// Hlavička
		$this->registry->getObject('template')->getPage()->addTag( 'Year', $Year );						
		$this->registry->getObject('template')->getPage()->addTag( 'EntryNo', $EntryNo );						
		$this->registry->getObject('template')->getPage()->addTag( 'Header', $○r );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingID', $MeetingID );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', $MeetingTypeID );						
		$this->registry->getObject('template')->getPage()->addTag( 'activeMeetingLineID', $activeMeetingLineID );				
		$this->registry->getObject('template')->addTemplateBit('editdMeetingLine', 'zob-meetingline-edit.tpl.php');

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

		// meetingattachment
		$this->registry->getObject('db')->initQuery('meetingattachment');
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
		// meetingattachment
		$this->registry->getObject('db')->initQuery('meetingattachment');
		$this->registry->getObject('db')->setFilter('MeetingLineID',$MeetingLineID);
		if (!$this->registry->getObject('db')->isEmpty())
			return true;

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

	public function getActualMeeting ( $MeetingName )
	{
		$meeting = null;
		$meetingtype = null;
		$electionperiod = $this->getActualElectionperiod();
		if($electionperiod){
			$this->registry->getObject('db')->initQuery('meetingtype');
			$this->registry->getObject('db')->setfilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);
			$this->registry->getObject('db')->setfilter('MeetingName',$MeetingName);
			if ($this->registry->getObject('db')->findFirst())
				$meetingtype = $this->registry->getObject('db')->getResult();
		}
		if($meetingtype){
			$this->registry->getObject('db')->initQuery('meeting');
			$this->registry->getObject('db')->setfilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
			$this->registry->getObject('db')->setfilter('Actual',1);
			if ($this->registry->getObject('db')->findFirst()){
				$meeting = $this->registry->getObject('db')->getResult();
			}else{
				$_POST['MeetingTypeID'] = $meetingtype['MeetingTypeID'];
				$this->meeting('add');
				$this->registry->getObject('db')->initQuery('meeting');
				$this->registry->getObject('db')->setfilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
				$this->registry->getObject('db')->setfilter('Actual',1);
				if ($this->registry->getObject('db')->findFirst()){
					$meeting = $this->registry->getObject('db')->getResult();
				}
			}
		}
		return $meeting;
	}

	public function getMeetingTemplate ( $MeetingName )
	{
		$meeting = null;
		$this->registry->getObject('db')->initQuery('electionperiod');
		$this->registry->getObject('db')->setCondition("PeriodName like '<%>'");
		if (!$this->registry->getObject('db')->findFirst())
			return null;	
		$electionperiod = $this->registry->getObject('db')->getResult();

		$this->registry->getObject('db')->initQuery('meetingtype');
		$this->registry->getObject('db')->setFilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);
		$this->registry->getObject('db')->setFilter('MeetingName',$MeetingName);
		if (!$this->registry->getObject('db')->findFirst())
			return null;	
		$meetingtype = $this->registry->getObject('db')->getResult();			

		$this->registry->getObject('db')->initQuery('meeting');
		$this->registry->getObject('db')->setFilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
		if ($this->registry->getObject('db')->findFirst())
			$meeting = $this->registry->getObject('db')->getResult();			
		return $meeting;
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

	public function getMeetingline ( $MeetingLineID )
	{
		$meetingline = null;
		$this->registry->getObject('db')->initQuery('meetingline');
		$this->registry->getObject('db')->setFilter('MeetingLineID',$MeetingLineID);
		if ($this->registry->getObject('db')->findFirst())
			$meetingline = $this->registry->getObject('db')->getResult();			
		return $meetingline;
	}

	public function getMeetingattachment ( $AttachmentID )
	{
		$meetingattachment = null;
		$this->registry->getObject('db')->initQuery('meetingattachment');
		$this->registry->getObject('db')->setFilter('AttachmentID',$AttachmentID);
		if ($this->registry->getObject('db')->findFirst())
			$meetingattachment = $this->registry->getObject('db')->getResult();			
		return $meetingattachment;
	}

	public function getMeetinglineByLineNo ( $MeetingID, $LineNo )
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

	public function getNextMeetinglineLineNo( $MeetingID )
	{
		$sql = "SELECT max(LineNo) as LineNo FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $result['LineNo'] + 1;				
	}

	public function getLastMeetinglineLineNo( $MeetingID )
	{
		$sql = "SELECT max(LineNo) as LineNo FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $result['LineNo'] ;				
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

	private function hasChildMeetingline($MeetingLineID){
		$meetingline = $this->getMeetingline($MeetingLineID);
		$MeetingID = $meetingline['MeetingID'];
		$LineNo = $meetingline['LineNo'];
		$sql = "SELECT Count(*) as pocet FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID AND MeetingLineID <> $MeetingLineID and LineNo = $LineNo";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return ($result['pocet'] > 0);		
	}

	private function importMeeting(){
		$content = "";
		$filename = "files/ImportMeeting.csv";

		if(!file_exists($filename)){
			$this->errorMessage = "Soubor $filename nebyl nalezen.";
			$this->print();
			return;
		}
		
		$electionperiod = $this->getActualElectionperiod();
		if(!$electionperiod){
			$this->errorMessage = "Není nastaveno výchozí volební období..";
			$this->print();
			return;
		}
		$ElectionPeriodID = $electionperiod['ElectionPeriodID'];
		$verifier = 0;
		$MeetingID = 0;
		$MeetingLineID = 0;

		// Read file
		$file =  fopen( $filename, 'r' );
		while(!feof($file)) {
		
			$line = fgets($file);
			if($line){
				$field = explode(';',$line);
				$type = count($field)>1 ? $field[0] : '';
				switch ($type) {
					case 'J':
						# Hlavička jednání
						$meeting = null;
						$meetingline = null;
						$MeetingName = '';
						$verifier = 0;
						switch ($field[1]){
							case 'Z':
								$MeetingName = 'Zastupitelstvo';
								break;
							case 'R':
								$MeetingName = 'Rada';
								break;
							case 'S':
								$MeetingName = 'Stavební komise';
								break;
							default:
								$this->errorMessage = "Neočekávaný typ jednání '".$field[1]."'";
								$this->print();
								return;
						}
						$EntryNo = $field[2];
						$Year = date('Y', strtotime($field[4]));

						$meeting = array();
						$meeting['EntryNo'] = $EntryNo;
						$meeting['Present'] = (int) $field[3];
						$meeting['AtDate'] = $this->text2Date($field[4]);
						$meeting['Year'] = $Year;
						$meeting['AtTime'] = date('H:i', strtotime($field[5]));
						$meeting['PostedUpDate'] = $this->text2Date($field[6]);
						$meeting['PostedDownDate'] = $this->text2Date($field[7]);

						$this->registry->getObject('db')->initQuery('meetingtype');
						$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
						$this->registry->getObject('db')->setFilter('MeetingName',$MeetingName);
						if(!$this->registry->getObject('db')->findFirst()){
							$this->errorMessage = "Typ jednání $MeetingName pro volební období ".$electionperiod['PeriodName']." není definováno.";
							$this->print();
							return;
						}
						$meetingtype = $this->registry->getObject('db')->getResult();
						$meetingTemplate = $this->getMeetingTemplate($MeetingName);
						if($meetingTemplate){
							$meeting['MeetingPlace'] = $meetingTemplate['MeetingPlace'];
						}
						$MeetingTypeID = $meetingtype['MeetingTypeID'];
						$meeting['MeetingTypeID'] = $MeetingTypeID;
						$meeting['Close'] = 1;

						$this->registry->getObject('db')->initQuery('meeting');
						$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
						$this->registry->getObject('db')->setFilter('EntryNo',$EntryNo);
						if($this->registry->getObject('db')->findFirst()){
							$this->errorMessage = "Jednání typu $MeetingName číslo $EntryNo/$Year již existuje.";
							$this->print();
							return;					
						}
						$this->registry->getObject('db')->insertRecords('meeting',$meeting);
						$this->registry->getObject('db')->initQuery('meeting');
						$this->registry->getObject('db')->setFilter('MeetingTypeID',$MeetingTypeID);
						$this->registry->getObject('db')->setFilter('EntryNo',$EntryNo);
						$this->registry->getObject('db')->findFirst();
						$meeting = $this->registry->getObject('db')->getResult();
						$MeetingID = $meeting['MeetingID'];

						$line = $MeetingID;

						break;
					case 'V':
						$verifier += 1;
						$data = null;
						switch ($verifier){
							case 1:	
								$data['VerifierBy1'] = $field[1];
							case 2:	
								$data['VerifierBy2'] = $field[1];
						}
						if($data){
							$condition = "MeetingID = $MeetingID";
							$this->registry->getObject('db')->updateRecords('meeting',$data,$condition);
						}
						break;
					case 'D':
						$data = null;
						$data['RecorderAtDate'] = $this->text2Date($field[1]);
						if($data){
							$condition = "MeetingID = $MeetingID";
							$this->registry->getObject('db')->updateRecords('meeting',$data,$condition);
						}
						break;
					case 'Z':
						$data = null;
						$data['RecorderBy'] = trim($field[1]);
						if($data){
							$condition = "MeetingID = $MeetingID";
							$this->registry->getObject('db')->updateRecords('meeting',$data,$condition);
						}
						break;
					case 'B':
						$data = null;
						$data['MeetingID'] = $MeetingID;
						$data['LineNo'] = (int) $field[1];
						$data['LineType'] = 'Bod';
						$data['Title'] = $this->registry->getObject('db')->sanitizeData(trim($field[2]));
						$this->registry->getObject('db')->insertRecords('meetingline',$data);
						break;
					case 'T':
						$data = null;
						$data['MeetingID'] = $MeetingID;
						$data['LineNo'] = (int) $field[1];
						$data['LineType'] = 'Doplňující bod';
						$data['Title'] = $this->registry->getObject('db')->sanitizeData(trim($field[2]));
						$this->registry->getObject('db')->insertRecords('meetingline',$data);
						break;
					case 'P':
						preg_match("/(\d+)(\D+)/", $field[1], $arr);
						$LineNo = (int) $arr[1];
						$data = null;
						$data['MeetingID'] = $MeetingID;
						$data['LineNo'] = $LineNo;
						$data['LineType'] = 'Podbod';
						$data['Title'] = $this->registry->getObject('db')->sanitizeData($arr[2].") ".trim($field[2]));
						$this->registry->getObject('db')->insertRecords('meetingline',$data);
						break;
					case 'O':
						preg_match("/(\d+)(\D*)/", $field[1], $arr);
						$LineNo = (int) $arr[1];
						$par = $arr[2] != '' ? $arr[2].")" : null;

						$this->registry->getObject('db')->initQuery('meetingline');
						$this->registry->getObject('db')->setFilter('MeetingID',$MeetingID);
						$this->registry->getObject('db')->setFilter('LineNo',$LineNo);
						if($par)
							$this->registry->getObject('db')->setCondition("Title like '".$par."%'");
						$this->registry->getObject('db')->findFirst();
						$meetingline = $this->registry->getObject('db')->getResult();
						$MeetingLineID = $meetingline['MeetingLineID'];

						$data = null;
						$data['Content'] = $this->registry->getObject('db')->sanitizeData(trim($field[2]));
						$condition = "MeetingLineID = $MeetingLineID";
						$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
						break;
					case 'H':
						$line = preg_replace('/\s/',"",$field[1]);
						$arr = explode(':',$line);
						$data = null;		
						$data['Vote'] = 1;
						$data['VoteFor'] = (int) $arr[1];
						$data['VoteAgainst'] = (int) $arr[2];
						$data['VoteDelayed'] = (int) $arr[3];
						$condition = "MeetingLineID = $MeetingLineID";
						$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
						break;
					default:
						$meetingline = $this->getMeetingline($MeetingLineID);
						$data = null;
						$data['Content'] = $meetingline['Content']."\n".$this->registry->getObject('db')->sanitizeData($line);
						$condition = "MeetingLineID = $MeetingLineID";
						$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);
						break;
				}
			}
			$content .= $line."<br>";
			
		  }		
		fclose( $file );
	
		$this->print($content);
	}

	private function text2Date($text){
		$text = trim($text);
		$arr = explode('.',$text);
		if($arr[2] < 100)
			$arr[2] = $arr[2] + 2000;
		$text = $arr[0].".".$arr[1].".".$arr[2];
		$date = date('Y-m-d', strtotime($text));
		return $date;
	}
}