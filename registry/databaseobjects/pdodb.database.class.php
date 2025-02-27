<?php

/**
 * PDO Management
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    21.2.2025
 */
 class pdodbdatabase
{
    private $registry;
    private PDO $pdo;

    
    public function __construct($registry)
    {   
        global $config;
        $this->registry = $registry;

		require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
		require_once( FRAMEWORK_PATH . 'controllers/zob/electionperiod.php');
		require_once( FRAMEWORK_PATH . 'controllers/agenda/controller.php');
		require_once( FRAMEWORK_PATH . 'controllers/contact/controller.php');
    }

    public function main()
    {
        $urlBits = $this->registry->getURLBits();
        $table = isset($urlBits[2]) ? $urlBits[2] : '';
        $action = isset($urlBits[3]) ? $urlBits[3] : "";     

        switch ($table) {
            // urlBits = 2
            case 'dmsentry':
                switch ($action) {
                    // urlBits = 3
                    case 'lookup':
                        $query = isset($urlBits[4]) ? $urlBits[4] : "";
                        $result = $this->searchDmsentry($query);
                        break;                   
                }
                break;
            case 'inbox':
                switch ($action) {
                    // urlBits = 3
                    case 'modify':
                        $result = $this->modifyInbox();
                        break;
                }
                break;
            case 'contact':
                switch ($action) {
                    // urlBits = 3
                    case 'lookup':
                        $query = isset($urlBits[4]) ? $urlBits[4] : "";
                        $result = $this->searchContact($query);
                        break;
                    case 'new':
                        $result = $this->newContact();
                        break;
                    case 'modify':
                        $result = $this->modifyContact();
                        break;
                }
                break;
            case 'meeting':
                switch ($action) {
                    // urlBits = 3
                    case 'modify':
                        $result = $this->updateMeeting();
                        break;
                }
                break;
            case 'meetingline':
                switch ($action) {
                    // urlBits = 3
                    case 'modify':
                        $result = $this->updateMeetingline();
                        break;
                }
                break;
            default:
                break;
        }
        exit($result);
    }

    private function connect()
    {   
        global $config;
        $dsn = "mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'] . ";charset=utf8";
        $username = $config['db_user'];
        $password = $config['db_pass'];

        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Chyba databáze: " . $e->getMessage()]);
        }
    }

    public function modify()
    {
        global $config;
        $this->connect();
        $pref = $config['dbPrefix'];
        $core = $this->registry->getObject('core');

        $data = json_decode(file_get_contents("php://input"), true);

        try{
            return json_encode([
                "status" => "OK",
                "message" => "Záznam byl úspěšně modifikován."
            ]);        
        } catch (PDOException $e) {
            return json_encode(["status" => "error", "message" => "Chyba vložení do databáze: " . $e->getMessage()]);
        }
    }

    //*******************************************************************************************************************/
    // DmsEntry
    //*******************************************************************************************************************/
    public function searchDmsentry(string $query)
    {
        global $config;
        $this->connect();
        $pref = $config['dbPrefix'];

        try{
            $sql = "SELECT ID,`Name` as FullName FROM $pref"."dmsentry WHERE (Type = 20) and (Archived = 0) and Name LIKE ? ORDER BY Name LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(["%" . $query . "%"]);
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode(["status" => "error", "message" => "Chyba dotazu do databáze: " . $e->getMessage()]);
        }
    }

    //*******************************************************************************************************************/
    // Inbox
    //*******************************************************************************************************************/
	private function modifyInbox()
    {
		$zob = new Zobcontroller( $this->registry, false );					       

        $data = json_decode(file_get_contents("php://input"), true);
		$table = $data['table'];
		$field = $data['field'];
        $value = $data['newvalue'];
		$ID = $data['pkID'];
        $item = $data['item'];
        $data = null;

		$inbox = $zob->getInbox($ID);
		$data = null;

		/*
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
        */
        return null;
    }
    

    //*******************************************************************************************************************/
    // Meeting
    //*******************************************************************************************************************/
	private function updateMeeting()
	{
		$zob = new Zobcontroller( $this->registry, false );					       
		$contact = new Contactcontroller( $this->registry, false );
		$electionperiodinstance = new Electionperiodcontroller($this->registry);					
		$meetinginstance = new Meetingcontroller($this->registry);

        $data = json_decode(file_get_contents("php://input"), true);
		$table = $data['table'];
		$field = $data['field'];
        $value = $data['newvalue'];
		$ID = $data['pkID'];
        $data = null;

		$meeting = $meetinginstance->getMeeting($ID);
		$isTemplate = $electionperiodinstance->isElectionperiodTemplate( $meeting['ElectionPeriodID'] );
		if(($meeting['Close'] == 1) && ($field != 'Close')){
			return json_encode(["status" => "error", "message" => "Nelze editovat uzavřený zápis jednání."]);
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
						return json_encode(["status" => "error", "message" => "Nelze uzavřít jednání pokud není vyplnměn termín jednání."]);
					};
					if ($meeting['MeetingPlace'] == ''){
						return json_encode(["status" => "error", "message" => "Nelze uzavřít jednání pokud není vyplnměno místo jednání."]);
					};
					if ($meeting['RecorderAtDate'] == null){
						return json_encode(["status" => "error", "message" => "Nelze uzavřít jednání pokud není datum zápisu."]);
					};
					if ($meeting['RecorderBy'] == ''){
						return json_encode(["status" => "error", "message" => "Nelze uzavřít jednání pokud není vyplnměn zapisovatel."]);
					};
					if ($this->countRec('meetingline','MeetingID = '.$meeting['MeetingID']) == 0){
						return json_encode(["status" => "error", "message" => "Nelze uzavřít zápis jednání bez obsahu."]);
					}
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
					$data[$field] = $contact->getContactID($value);
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
		if($data)
            try{
                $this->registry->getObject('db')->updateRecords($table,$data,$condition);
                return json_encode([
                    "status" => "OK",
                    "message" => "Záznam byl úspěšně modifikován."
                ]);        
            } catch (PDOException $e) {
                return json_encode(["status" => "error", "message" => "Chyba modifikace do databáze: " . $e->getMessage()]);
            }
	}

    //*******************************************************************************************************************/
    // Meetingline
    //*******************************************************************************************************************/
	private function updateMeetingline()
	{
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
		$meetinginstance = new Meetingcontroller($this->registry);		
		$meetinglineinstance = new Meetinglinecontroller($this->registry);
		$zob = new Zobcontroller( $this->registry, false );					       
		$contact = new Contactcontroller( $this->registry, false );

        $data = json_decode(file_get_contents("php://input"), true);
		$table = $data['table'];
		$field = $data['field'];
        $value = $data['newvalue'];
		$ID = $data['pkID'];
        $data = null;

		$meetingline = $meetinglineinstance->getMeetingline($ID);
		$meeting = $meetinginstance->getMeeting($meetingline['MeetingID']);
		$meetingtype = $meetingtypeinstance->getMeetingtype($meeting['MeetingTypeID']);

		if($meeting['Close'] == 1){
			return json_encode(["status" => "error", "message" => "Nelze editovat uzavřený zápis jednání."]);
		}

		switch ($field) {
			case 'Title':
				if($value == ''){
					return json_encode(["status" => "error", "message" => "Text bodu jednání musí být vyplněn."]);
				}
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
				    return json_encode(["status" => "error", "message" => "Počet hlasujících nesouhlasí, maximální počet přítomných členů je ".$meeting['Present']]);	
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
					$contact = $zob->getContactByName($value);
                    if (!$contact) {
                        $contact = $zob->getContactByID($value);
                    };
                    if($contact){
						$data['PresenterID'] = $contact['ID'];
					}else{
                        $data['PresenterID'] = '00000000-0000-0000-0000-000000000000';
                        //return json_encode(["status" => "error", "message" => "Předkladatel $value nebyl nalezen v kontaktech."]);
					}
				}
				break;
            default:
                $data[$field] = $value;
                break;
		}
        					
		$condition = "MeetingLineID = $ID";
		if($data)
            try{
                $this->registry->getObject('db')->updateRecords($table,$data,$condition);
                return json_encode([
                    "status" => "OK",
                    "message" => "Záznam byl úspěšně modifikován."
                ]);        
            } catch (PDOException $e) {
                return json_encode(["status" => "error", "message" => "Chyba modifikace do databáze: " . $e->getMessage()]);
            }
	}

    //*******************************************************************************************************************/
    // Contact
    //*******************************************************************************************************************/
    
    private function modifyContact()
	{
		$zob = new Zobcontroller( $this->registry, false );					       
		$contactClass = new Contactcontroller( $this->registry, false );

        $data = json_decode(file_get_contents("php://input"), true);

		$table = $data['table'];
		$field = $data['field'];
        $value = $data['newvalue'];
		$ID = $data['pkID'];

		$contact = $zob->getContactByID($ID);
		$data = null;

		switch ($field) {
			case 'LastName':
			case 'FirstName':
			case 'Title':
			case 'Company':
				$data['LastName'] = $contact['LastName'];
				$data['FirstName'] = $contact['FirstName'];
				$data['Title'] = $contact['Title'];
				$data['Company'] = $contact['Company'];
				$data[$field] = $value;
				$data['FullName'] = $contactClass->makeFullName($data);
				break;
			default:
				$data[$field] = $value;
		}
		$condition = "ID = '$ID'";
		if($data)
            try{
                $this->registry->getObject('db')->updateRecords($table,$data,$condition);
                return json_encode([
                    "status" => "OK",
                    "message" => "Záznam byl úspěšně modifikován."
                ]);        
            } catch (PDOException $e) {
                return json_encode(["status" => "error", "message" => "Chyba modifikace do databáze: " . $e->getMessage()]);
            }
	}

    public function searchContact(string $query)
    {
        global $config;
        $this->connect();
        $pref = $config['dbPrefix'];

        try{
            $sql = "SELECT FullName, ID, Address, Phone, Email, Company FROM $pref"."contact WHERE FullName LIKE ? LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(["%" . $query . "%"]);
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode(["status" => "error", "message" => "Chyba dotazu do databáze: " . $e->getMessage()]);
        }
    }

    public function newContact()
    {
        global $config;
        $this->connect();
        $pref = $config['dbPrefix'];
        $core = $this->registry->getObject('core');
        try{
            $UUID = $core->createGUID();
            $stmt = $this->pdo->prepare("INSERT INTO $pref"."contact (ID) VALUES (?)");
            $stmt->execute([$UUID]);
            return json_encode([
                "status" => "OK",
                "message" => "Záznam byl úspěšně přidán",
                "id" => $UUID
            ]);        
        } catch (PDOException $e) {
            return json_encode(["status" => "error", "message" => "Chyba vložení do databáze: " . $e->getMessage()]);
        }
    }

    //*******************************************************************************************************************/
    // Local functions
    //*******************************************************************************************************************/

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

