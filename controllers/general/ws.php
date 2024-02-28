<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    18.1.2023
 */
class Generalws {
	
    private $registry;
    private $zob;
    private $agenda;
    private $contact;
	private $table;
	private $ID;
	private $field;
	private $fieldlist;
	private $result;
    
    /**
	 * @param Registry $registry 
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;

		require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
		$this->zob = new Zobcontroller( $this->registry, false );					
        
		require_once( FRAMEWORK_PATH . 'controllers/agenda/controller.php');
		$this->agenda = new Agendacontroller( $this->registry, false );					
        
		require_once( FRAMEWORK_PATH . 'controllers/contact/controller.php');
		$this->contact = new Contactcontroller( $this->registry, false );					
        
    }

	/**
	 * Rozšířený modul
	 * @return void
	 */
	public function main( $action )
	{
		$this->result = 'OK';
		switch ($action) {
			case 'addMeetinglineattachment':
				$this->result = $this->addMeetinglineattachment();
				break;
			case 'deleteMeetinglineattachment':
				$this->result = $this->deleteMeetinglineattachment();
				break;
			default:
				if ($this->setParam()){
					// Action for element with <name==field>, <table==table name> , <kdID==primary Key>
					switch ($action) {
						case 'upd':
							$this->update($action);
							break;
						case 'copyFrom':
							$this->copyFrom($action);
							break;
						case 'getValue':
							$this->result = $this->getValue($this->field);
							break;
						case 'getJson':
							$this->result = $this->getJson();
							break;
						case 'log':
								$this->log();
								break;
						default:
							$this->result = "ERROR: Action '$action' is not specified. ";	
					}
				}else{
					if($this->result == 'OK'){
						switch ($action) {
							case 'log':
								$this->log();
								break;
							default:
								$message = isset($_POST['message']) ? $_POST['message'] : '';
								$this->result = "ERROR: Action '$message' of modify database field is not specified. ";	
						}
					}
				}
		}
		exit($this->result);
    }

	private function setParam()
	{
		$urlBits = $this->registry->getURLBits();

		# URL: general/ws/<action>/<table>/<ID>/<FieldName>
		$this->table = strtolower(isset($urlBits[3]) ? $urlBits[3] : ''); 
		$this->ID = isset($urlBits[4]) ? $urlBits[4] : 0; 
		$this->field = isset($urlBits[5]) ? $urlBits[5] : '';
		$this->fieldlist = isset($urlBits[6]) ? $urlBits[6] : '';
		if($this->table == ''){
			$this->result = 'ERROR: Table not specific.';
			return false;
		}
		if($this->ID == ''){
			$this->result = 'ERROR: ID not specific.';
			return false;
		}
		if($this->field == ''){
			$this->result = 'ERROR: Field not specific.';
			return false;
		}
		return true;
	}

	/**
	 * URL: general/ws/upd/<table>/<ID>/<FieldName>/<FieldValue>
	 */
	private function update($action)
	{		
		$value = isset($_POST['value']) ? $_POST['value'] : '';
		if($value == 'undefined')
			return;
		switch ($this->table) {
			case 'dmsentry':
				$this->updateDmsentry($value);
				break;
			case 'inbox':
				$this->updateInbox($value);
				break;
			case 'meeting':
				$this->updateMeeting($value);
				break;
			case 'meetingline':
				$this->updateMeetingline($value);
				break;
			case 'meetinglinecontent':
				$this->updateMeetinglinecontent($value);
				break;
			case 'meetinglinepage':
				$this->updateMeetinglinepage($value);
				break;
			case 'contact':
				$this->updateContact($value);
				break;
			default:
				$data = null;
				$data[$this->field] = $value;
				$pk = $this->getFieldPK($this->table);
				$condition = "`$pk` = '".$this->ID."'";
				if($this->result == 'OK') 
					$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);	
				break;
		}
	}

	private function updateMeeting($value)
	{
		$ID = $this->ID;
		$field = $this->field;
		$data = null;

		$meeting = $this->zob->getMeeting($ID);
		$isTemplate = $this->zob->isElectionperiodTemplate( $meeting['ElectionPeriodID'] );
		if(($meeting['Close'] == 1) && ($field != 'Close')){
			$this->result = 'Nelze editovat uzavřený zápis jednání;';
			return;
		}

		switch ($field) {
			case 'AtDate':
				if(!$isTemplate){
					$data[$field] = $value;
					if($value)
						$data['Year'] = $this->registry->getObject('core')->formatDate($value,'Y');
				}
				break;
			case 'Close':
				if((!$isTemplate) && ($meeting['Close'] == 0)){
					if ($meeting['AtDate'] ==null){
						$this->result = "Nelze uzavřít jednání pokud není vyplnměn termín jednání.";
					};
					if ($meeting['MeetingPlace'] == ''){
						$this->result = "Nelze uzavřít jednání pokud není vyplnměno místo jednání.";
					};
					if ($meeting['RecorderAtDate'] == null){
						$this->result = "Nelze uzavřít jednání pokud není vyplnměn datum zápisu.";
					};
					if ($meeting['RecorderBy'] == ''){
						$this->result = "Nelze uzavřít jednání pokud není vyplnměn zapisovatel.";
					};
					if ($this->countRec('meetingline','MeetingID = '.$meeting['MeetingID']) == 0){
						$this->result = "Nelze uzavřít zápis jednání bez obsahu.";
					}
					if($this->result == 'OK')
						$data[$field] = $value;
				}else{
					$data[$field] = $value;
				}
				break;
			case 'PostedUpDate':
			case 'PostedDownDate':
			case 'State':
			case 'RecorderAtDate':
				if(!$isTemplate)
					$data[$field] = $value;
				break;
			case 'VerifierBy1':
			case 'VerifierBy2':
				if(!$isTemplate)
					$data[$field] = $this->contact->getContactID($value);
				break;
			case 'Actual':
				// Reset pole Actual na všech záznamech
				$changes = array();
				$changes['Actual'] = 0;
				$condition = "MeetingTypeID = ".$meeting['MeetingTypeID'];
				$this->registry->getObject('db')->updateRecords('meeting',$changes,$condition);
				$data['Actual'] = 1;
				break;
			default:
				$data[$field] = $value;
		}
		$condition = "MeetingID = $ID";
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);
	}


	private function updateMeetingline($value)
	{
		$ID = $this->ID;
		$field = $this->field;
		$data = null;
		
		$meetingline = $this->zob->getMeetingline($ID);
		$meeting = $this->zob->getMeeting($meetingline['MeetingID']);
		$meetingtype = $this->zob->getMeetingtype($meeting['MeetingTypeID']);

		if($meeting['Close'] == 1){
			$this->result = 'Nelze editovat uzavřený zápis jednání;';
			return;
		}

		switch ($field) {
			case 'Title':
				if($value == ''){
					$this->result = 'Text bodu jednání musí být vyplněn.';
				}
				if($this->result == 'OK')
					$data[$field] = $value;
				break;
			case 'VoteFor':
			case 'VoteAgainst':
			case 'VoteDelayed':
				$value = (int) $value;									
				$total = $meetingline['VoteFor'] + $meetingline['VoteAgainst'] + $meetingline['VoteDelayed'];
				if($value > 0)
					switch ($field) {
						case 'VoteFor':
							$total = $value + $meetingline['VoteAgainst'] + $meetingline['VoteDelayed'];
							break;
						case 'VoteAgainst':
							$total = $meetingline['VoteFor'] + $value + $meetingline['VoteDelayed'];
							break;
						case 'VoteDelayed':
							$total = $meetingline['VoteFor'] + $meetingline['VoteAgainst'] + $value;
							break;
					}
				if($total > $meeting['Present'])
					$this->result = "Počet hlasujících nesouhlasí, maximální počet přítomných členů je ".$meeting['Present'];
				if($this->result == 'OK')
					$data[$field] = $value;
				break;
			case 'Vote':
				if($value == 1){
					$data['VoteFor'] = $meeting['Present'];
					$data['VoteAgainst'] = "0";
					$data['VoteDelayed'] = "0";										
				}else{
					$data['VoteFor'] = '0';
					$data['VoteAgainst'] = "0";
					$data['VoteDelayed'] = "0";										
				}
				$data[$field] = $value;
				break;
			case 'Presenter':
				if($value == '')
					$data['PresenterID'] = '00000000-0000-0000-0000-000000000000';
				else{
					$contact = $this->zob->getContactByName($value);
					if($contact){
						$data['PresenterID'] = $contact['ID'];
					}else{
						$this->result = "Předkladatel $value nebyl nalezen v kontaktech.";
						return $this->result;
					}
				}
				break;
			default:
				$data[$field] = $value;
		}
		$condition = "MeetingLineID = $ID";
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);	
	}

	private function updateMeetinglinecontent($value)
	{
		$ID = $this->ID;
		$field = $this->field;
		$data = null;

		$meetinglinecontent = $this->zob->getMeetinglinecontent($ID);
		$meeting = $this->zob->getMeeting($meetinglinecontent['MeetingID']);
		if($meeting['Close'] == 1){
			$this->result = 'Nelze editovat uzavřený zápis jednání;';
			return;
		}

		switch ($field) {
			case 'VoteFor':
			case 'VoteAgainst':
			case 'VoteDelayed':
				$value = (int) $value;									
				$total = $meetinglinecontent['VoteFor'] + $meetinglinecontent['VoteAgainst'] + $meetinglinecontent['VoteDelayed'];
				if($value > 0)
					switch ($field) {
						case 'VoteFor':
							$total = $value + $meetinglinecontent['VoteAgainst'] + $meetinglinecontent['VoteDelayed'];
							break;
						case 'VoteAgainst':
							$total = $meetinglinecontent['VoteFor'] + $value + $meetinglinecontent['VoteDelayed'];
							break;
						case 'VoteDelayed':
							$total = $meetinglinecontent['VoteFor'] + $meetinglinecontent['VoteAgainst'] + $value;
							break;
					}
				if($total > $meeting['Present'])
					$this->result = "Počet hlasujících nesouhlasí, maximální počet přítomných členů je ".$meeting['Present'];
				if($this->result == 'OK')
					$data[$field] = $value;
				break;
			case 'Vote':
				if($value == 1){
					$data['VoteFor'] = $meeting['Present'];
					$data['VoteAgainst'] = "0";
					$data['VoteDelayed'] = "0";										
				}else{
					$data['VoteFor'] = '0';
					$data['VoteAgainst'] = "0";
					$data['VoteDelayed'] = "0";										
				}
				$data[$field] = $value;
				break;
			default:
				$data[$field] = $value;
		}
		$condition = "ContentID = $ID";								
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);	
	}

	private function updateMeetinglinepage($value)
	{
		$ID = $this->ID;
		$field = $this->field;
		$data = null;

		$meetinglinepage = $this->zob->getMeetinglinepage($ID);
		$meeting = $this->zob->getMeeting($meetinglinepage['MeetingID']);
		if($meeting['Close'] == 1){
			$this->result = 'Nelze editovat uzavřený zápis jednání;';
			return;
		}

		if ($field == 'Changed'){
			$data['Changed'] = 0;
			if ($meetinglinepage['Lin_Changed'] == 1){
				$condition = "MeetingLineID = " . $meetinglinepage['MeetingLineID'];								
				$this->registry->getObject('db')->updateRecords('meetingline',$data,$condition);	
			}
			if ($meetinglinepage['Con_Changed'] == 1){
				$condition = "ContentID = " . $meetinglinepage['ContentID'];								
				$this->registry->getObject('db')->updateRecords('meetinglinecontent',$data,$condition);	
			}
			return;
		}

		$data[$field] = $value;
		
		$condition = "PageID = $ID";								
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);	
	}

	private function updateDmsentry($value)
	{
		$dmsentry = $this->zob->getDmsentryByID($this->ID);
		$field = $this->field;
		$data = null;
		$ID = $this->ID;

		switch ($this->field) {
			case 'Title':
				$data[$this->field] = $value;

				if($value != $dmsentry['Title']){
					$condition = "DmsEntryID = '$ID'";
					//  Do tabulky příloh jednání
					$meetingattachment = array();
					$meetingattachment['Description'] = $value;
					$this->registry->getObject('db')->updateRecords('meetingattachment',$meetingattachment,$condition);
		
					// Do tabulky Inboxu
					$inbox = array();
					$inbox['Title'] = $value;
					$this->registry->getObject('db')->updateRecords('inbox',$inbox,$condition);
				}
				break;
			case 'NewDocumentNo':
				// $value == agendatype.TypeID

				$DocumentNo = $this->agenda->getNextDocumentNo($value);
				$changes = array();
				$changes['EntryID']	= $ID;
				$changes['Description'] = $dmsentry['Title'];				
				$condition = "DocumentNo = '$DocumentNo'";
				$this->registry->getObject('db')->updateRecords('agenda',$changes, $condition);

				break;
			default:
				$data[$field] = $value;
		}
		$data['ModifyDateTime'] = date("Y-m-d H:i:s");
		$condition = "ID = '$ID'";
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);
	}

	private function updateInbox($value)
	{
		$inbox = $this->zob->getInbox($this->ID);
		$field = $this->field;
		$data = null;
		$ID = $this->ID;

		switch ($this->field) {
			case 'Title':
				$data[$this->field] = $value;
				$data['Modified'] = 1;

				if($value != $inbox['Title']){
					$condition = "InboxID = $ID";
					//  Do tabulky příloh jednání
					if ($inbox['MeetingID'] > 0){
						$meetingattachment = array();
						$meetingattachment['Description'] = $value;
						$this->registry->getObject('db')->updateRecords('meetingattachment',$meetingattachment,$condition);
					}
		
					// Do tabulky dokumentů
					if($inbox['DmsEntryID'] != '00000000-0000-0000-0000-000000000000'){
						$entry = array();
						$entry['Title'] = $value;
						$condition = "ID = '".$inbox['DmsEntryID']."'";
						$this->registry->getObject('db')->updateRecords('dmsentry',$entry,$condition);
					}
				}
				break;
			default:
				$data[$field] = $value;
		}
		$condition = "InboxID = $ID";
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);
	}

	private function updateContact($value)
	{
		$contact = $this->zob->getContactByID($this->ID);
		$field = $this->field;
		$data = null;
		$ID = $this->ID;

		switch ($this->field) {
			case 'LastName':
			case 'FirstName':
			case 'Title':
			case 'Company':
				$data['LastName'] = $contact['LastName'];
				$data['FirstName'] = $contact['FirstName'];
				$data['Title'] = $contact['Title'];
				$data['Company'] = $contact['Company'];
				$data[$field] = $value;
				$data['FullName'] = $this->contact->makeFullName($data);
				break;
			default:
				$data[$field] = $value;
		}
		$condition = "ID = '$ID'";
		if(($this->result == 'OK') && $data)
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);
	}

	/**
	 * general/ws/copyFrom/<table>/<ID>/<FieldName>/<fromFieldName>
	 */
	private function copyFrom($action)
	{
		$urlBits = $this->registry->getURLBits();

		$fieldFrom = isset($urlBits[6]) ? $urlBits[6] : '';
		if($fieldFrom == '')
			$this->result = 'Error: Field from not specified';
		if($this->table == 'meetingline'){
			$meetingline = $this->zob->getMeetingline($this->ID);
			$meeting = $this->zob->getMeeting($meetingline['MeetingID']);
			if($meeting['Close'] == 1)
				$this->result = 'Nelze editovat uzavřený zápis jednání;';
		}
		if($this->result == 'OK'){
			$data = array();
			$data[$this->field] = $this->getValue( $fieldFrom );  
			$pk = $this->getFieldPK($this->table);
			$condition = "`$pk` = ".$this->ID;
			$this->registry->getObject('db')->updateRecords($this->table,$data,$condition);
		}
	}

	private function getValue($field)
	{
		$table = $this->table;
		$ID = $this->ID;
		$value = '';
		if($value == ''){
			$pkField = $this->getFieldPK($table);
			if($pkField){
				$this->registry->getObject('db')->initQuery($table);
				$this->registry->getObject('db')->setFilter($pkField,$ID);
				if ($this->registry->getObject('db')->findFirst()){
					$rec = $this->registry->getObject('db')->getResult();
					$value =  isset($rec[$field]) ? $rec[$field] : '<NULL>';
				}else
					$value = '<NULL>';
			}else
				$value = '<NULL>';
		}
		return $value;
	}

	private function getJson()
	{
		$table = $this->table;
		$ID = $this->ID;
		$field = $this->field;
		$fieldlist = $this->fieldlist;
		$valueArray = '';
		if($valueArray == ''){
			$pkField = $this->getFieldPK($table);
			if($pkField){
				$this->registry->getObject('db')->initQuery($table,$fieldlist);
				$this->registry->getObject('db')->setFilter($field,$ID);
				if ($this->registry->getObject('db')->findSet()){
					$valueArray = $this->registry->getObject('db')->getResult();
				}else
					$valueArray = '<NULL>';
				$valueJson = json_encode($valueArray);
			}else
				$valueJson = '<NULL>';
		}
		return $valueJson;
	}
	private function addMeetinglineattachment()
	{
		$urlBits = $this->registry->getURLBits();

		$AttachmentID = isset($urlBits[3]) ? $urlBits[3] : 0; 
		$PageID = isset($urlBits[4]) ? $urlBits[4] : 0;

		if (($AttachmentID <> 0) && ($PageID <> 0)){
			$this->registry->getObject('db')->initQuery('meetinglinepageattachment');
			$this->registry->getObject('db')->setFilter('PageID',$PageID);
			$this->registry->getObject('db')->setFilter('AttachmentID',$AttachmentID);
			if ($this->registry->getObject('db')->isEmpty()){
				$data = array();
				$data['PageID'] = $PageID;
				$data['AttachmentID'] = $AttachmentID;
				$this->registry->getObject('db')->insertRecords('meetinglinepageattachment',$data);

				$this->registry->getObject('db')->initQuery('meetinglinepageattachment');
				$this->registry->getObject('db')->setFilter('PageID',$PageID);
				$this->registry->getObject('db')->setFilter('AttachmentID',$AttachmentID);
				if ($this->registry->getObject('db')->findFirst()) {
					$meetinglinepageattachment = $this->registry->getObject('db')->getResult();
					return $meetinglinepageattachment['EntryNo'];
				}
			}			
		}			
		return 0;
	}

	private function deleteMeetinglineattachment()
	{
		$urlBits = $this->registry->getURLBits();

		$EntryNo = isset($urlBits[3]) ? $urlBits[3] : 0; 

		if ($EntryNo <> 0){
			$condition = "EntryNo = $EntryNo";
			$this->registry->getObject('db')->deleteRecords( 'meetinglinepageattachment', $condition, 1); 
		}
		return true;
	}

	private function log()
	{
		$ID = $this->ID;
		$table = $this->table;
		$message = null;
		switch ($table) {	
			case 'contact':
				$contact = $this->contact->getContact($ID);
				if($contact)
					$message = "Zobrazení kontaktu. ".$contact['FullName'];
				break;
			default:
				$message = isset($_POST['message']) ? $_POST['message'] : '';
				$message = $this->registry->getObject('db')->sanitizeData($message);
		}
		if($message)
			$this->registry->getObject('log')->addMessage($message,$table,$ID);
	}

	private function getFieldPK($table){
		switch (strtolower($table)) {	
			case 'agenda':
				return 'ID';
			case 'contact':
				return 'ID';
			case 'user':
				return 'ID';
			case 'dmsentry':
				return 'ID';
			case 'inbox':
				return 'InboxID';
			case 'meeting':
				return 'MeetingID';
			case 'meetingline':
				return 'MeetingLineID';
			case 'meetinglinecontent':
				return 'ContentID';
			case 'meetingattachment':
				return 'AttachmentID';
			case 'meetinglinepage':
				return 'PageID';
			default:
				$this->result = "ERROR: Unknown table name '$table'.";
		}
		return null;
	}

	private function countRec($table, $filter = ''){
		global $config;
		$prefDb = $config['dbPrefix'];

		$sql = "SELECT count(*) as pocet FROM ".$prefDb.$table;
		if ($filter != '')
			$sql .= " WHERE $filter";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$rec = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $rec['pocet'];				
	}

}