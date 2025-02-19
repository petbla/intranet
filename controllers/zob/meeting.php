<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    19.10.2024
 */
class Meetingcontroller
{
    private $registry;
    private $db;
    private $errorMessage;
    private $prefDb;

    /**
     * Summary of __construct
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        global $config;
        $this->registry = $registry;
        $this->db = $this->registry->getObject('db');
        $this->prefDb = $config['dbPrefix'];
    }
    /**
     * Summary of index
     * @param string $action
     * @return void
     */
    public function index(String $action): void
    {
 		// 0/1/2/3..
		// zob/meeting/<$action>/$MeetingTypeID/$MeetingID/<parametry>

		$urlBits = $this->registry->getURLBits();
		$zob = new Zobcontroller($this->registry, false);
		$post = $_POST;

		switch ($action) {
			case 'add':
				$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : 0;
				$this->addMeeting($MeetingTypeID);
				break;
			case 'delete':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				$this->deleteMeeting($MeetingID);
				break;
			case 'list':
				$MeetingTypeID = isset($urlBits[3]) ? $urlBits[3] : '';
				break;
        }
		$zob->errorMessage = $this->errorMessage;
		$zob->listMeeting($MeetingTypeID );
    }

        /**
	  * Add new Meeting
	  * @param int|array $param - MeetingTypeID or table meetingtype
	  * @return bool
	  */
	private function addMeeting(int|array $param): bool{
        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;

		$electionperiodinstance = new Electionperiodcontroller($this->registry);
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
		
		$EntryNo = $this->geNextMeetingEntryNo( $MeetingTypeID );				
		$meetingtype = $meetingtypeinstance->getMeetingtype($MeetingTypeID);
		$isTemplate = $electionperiodinstance->isElectionperiodTemplate( $meetingtype);

		// Data z formuláře
		$AtDate = isset($_POST['AtDate']) ? $_POST['AtDate'] : null;
		$AtDate = $AtDate != '' ? $AtDate : null;
		$Year = ($AtDate) ? $this->registry->getObject('core')->formatDate($AtDate,'Y') : 0;
		$PostedUpDate = isset($_POST['PostedUpDate']) ? $_POST['PostedUpDate'] : '';
		$PostedUpDate = $PostedUpDate != '' ? $PostedUpDate : null;
		$PostedDownDate = isset($_POST['PostedDownDate']) ? $_POST['PostedDownDate'] : '';
		$PostedDownDate = $PostedDownDate != '' ? $PostedDownDate : null;
		$Present = '';

		if(!$isTemplate){
			$meetingTemplate = $this->getMeetingTemplate($meetingtype['MeetingName']);
			$MeetingPlace = $meetingTemplate['MeetingPlace'];
			$RecorderBy = $meetingTemplate['RecorderBy'];
			$AtTime = $meetingTemplate['AtTime'];
			$Present = $meetingTemplate['Present'];
		}else{
			$MeetingPlace = '';
			$RecorderBy = '';
			$AtTime = '00:00';
		}
		$Close =  0;
	
		$data = array();
		
		// Pole editace šablony
		$data['MeetingID'] = 0;
		$data['MeetingTypeID'] = $MeetingTypeID;
		$data['ElectionPeriodID'] = $meetingtype['ElectionPeriodID'];
		$data['EntryNo'] = $EntryNo;
		$data['AtTime'] = $AtTime;
		$data['MeetingPlace'] = $MeetingPlace;
		$data['RecorderBy'] = $RecorderBy;
		$data['Present'] = $Present;
		$data['ParentID'] = '00000000-0000-0000-0000-000000000000';
		$data['ParentID'] = $this->getMeetingParentID($data);

		if (!$isTemplate){
			$data['AtDate'] = $AtDate;
			$data['Year'] = $Year;
			$data['PostedUpDate'] = $PostedUpDate;
			$data['PostedDownDate'] = $PostedDownDate;
			$data['Close'] = $Close;
		}else{
			// Check Template
			if ($data['EntryNo'] <> 1){
				$this->errorMessage = "Šablona jednání může mít jen jeden vzorový zápis";
				return false;					
			}
		}

		// Kontrola, zda jsou všechny zápisy uzavřeny
		$this->db->initQuery('meeting');
		$this->db->setFilter('MeetingTypeID',$MeetingTypeID);
		$this->db->setFilter('Close',0);
		if (!$this->db->isEmpty()){
			$this->errorMessage = 'Před založení nového jednání musí být všechny předchozí uzavřeny.';
			return false;					
		}
		
		// Reset pole Actual na všech záznamech
		$condition = "MeetingTypeID = $MeetingTypeID";
        $this->db->updateall('meeting', $condition, 'Actual', 0);

		$data['Actual'] = 1;
		$this->db->insertRecords('meeting',$data);
		return true;
	}	

    	/**
	 * Summary of deleteMeeting
	 * @param int|array $param - MeetingID or table meeting
	 * @return bool
	 */
	private function deleteMeeting(int|array $param): bool{
        $MeetingID = is_array($param) ? $param['MeetingID'] : $param;
		$meeting = $this->getMeeting($MeetingID);
		if (!$meeting)
			return false;

		if ($meeting['Close'] == 1){
			$this->errorMessage = "Nelze odstranit uzavřené jednání.";
			return false;
		}			

		if ($this->isMeetingUsed($MeetingID)){
			$this->errorMessage = "Jednání ".
				$this->getMeetingNo($meeting['MeetingID'])." již bylo použito, nelze jej odstranit!";
			return false;
		}
		$condition = "MeetingID = $MeetingID";
		$this->db->deleteRecords( 'meeting', $condition, 1); 
		return true;
	}
    /**
     * Summary of getMeeting
     * @param int|array $param - MeetingID or table with index 'MeetingID'
     * @return null|array
     */
    public function getMeeting ( int|array $param ): ?array
	{
        $MeetingID = is_array($param) ? $param['MeetingID'] : $param;
		$meeting = null;
		$this->db->initQuery('meeting');
		$this->db->setFilter('MeetingID',$MeetingID);
		if ($this->db->findFirst())
			$meeting = $this->db->getResult();			
		return $meeting;
	}
    /**
     * Summary of getMeetingByEntryNo
     * @param int|array $param - MeetingTypeID or table with index 'MeetingTypeID'
     * @param int $EntryNo
     * @return null|array
     */
	public function getMeetingByEntryNo ( int|array $param, int $EntryNo ): ?array
	{
        $meetingtypeinstance = new Meetingtypecontroller($this->registry);
        $meetingtype = $meetingtypeinstance->getMeetingType($param);
		$meeting = null;
		$this->db->initQuery('meeting');
		$this->db->setFilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
		$this->db->setFilter('EntryNo',$EntryNo);
		if ($this->db->findFirst())
			$meeting = $this->db->getResult();			
		return $meeting;
	}

    /**
     * Summary of geNextMeetingEntryNo
     * @param int|array $MeetingTypeID
     * @return int
     */
    public function geNextMeetingEntryNo( int|array $param ): int
	{
        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;
		$sql = "SELECT max(EntryNo) as EntryNo FROM ".$this->prefDb."meeting WHERE MeetingTypeID = $MeetingTypeID";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return $result['EntryNo'] + 1;				
	}
    /**
     * Summary of getMeetingParentID
     * @param int|null $param - MeetingID of table with index 'MeetingID'
     * @return int
     */
    public function getMeetingParentID(int|array $param): int
	{
        $meeting = $this->getMeeting($param);
		$ParerntEntryNo = $this->getMeetingParentEntryNo($meeting);
		$parententry = $this->getDmsentry($ParerntEntryNo);
		return $parententry['ID'];
	}
    /**
     * Summary of getMeetingParentEntryNo
     * @param int|null $param - MeetingID of table with index 'MeetingID'
     * @return int
     */
    public function getMeetingParentEntryNo(int|array $param): int
	{
		global $config;
		$electionperiodinstance = new Electionperiodcontroller($this->registry);
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
        $meeting = $this->getMeeting($param);
        if (!$meeting)
            return 0;

		// Return existing
		if($meeting['ParentID'] != '00000000-0000-0000-0000-000000000000'){
			$parententry = $this->getDmsentryByID($meeting['ParentID']);
			return $parententry['EntryNo'];
		};

		// Create FOLDER new
		$meetingtype = $meetingtypeinstance->getMeetingtype($meeting);
		$electionperiod = $electionperiodinstance->getElectionPeriod($meetingtype);
		$parentFolder = $config['zobroot']."/_".$meetingtype['MeetingName']."/";
		$parentFolder .= $electionperiod['PeriodName']."/".$meeting['EntryNo']."/Přílohy";
		$DmsParentEntryNo = $this->registry->getObject('file')->findItem( $parentFolder, true );
		if(($DmsParentEntryNo) && ($meeting['MeetingID'] > 0)){
			$parententry = $this->getDmsentry($DmsParentEntryNo);
			$change = array();
			$change['ParentID'] = $parententry['ID'];
			$condition = "MeetingID = ".$meeting['MeetingID'];
			$this->db->updateRecords('meeting',$change,$condition);
		}
		return $DmsParentEntryNo;
	}

    /**
     * Summary of getMeetingTemplate
     * @param string $MeetingName
     * @return null|array
     */
    public function getMeetingTemplate ( String $MeetingName ): ?array
	{
		$meeting = null;
		$this->db->initQuery('electionperiod');
		$this->db->setCondition("PeriodName like '<%>'");
		if (!$this->db->findFirst())
			return null;	
		$electionperiod = $this->db->getResult();

		$this->db->initQuery('meetingtype');
		$this->db->setFilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);
		$this->db->setFilter('MeetingName',$MeetingName);
		if (!$this->db->findFirst())
			return null;	
		$meetingtype = $this->db->getResult();			

		$this->db->initQuery('meeting');
		$this->db->setFilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
		if ($this->db->findFirst())
			$meeting = $this->db->getResult();			
		return $meeting;
	}

    /**
     * Summary of getDmsentryByID
     * Find dmsentry record by ID (= Guid)
     * @param string $ID - Guid of Dmsentry record
     * @return null|array
     */
    public function getDmsentryByID ( String $ID ): ?array
	{
		$dmsentry = null;
		$this->db->initQuery('dmsentry');
		$this->db->setFilter('ID',$ID);
		if ($this->db->findFirst())
			$dmsentry = $this->db->getResult();			
		return $dmsentry;		
	}

    /**
     * Summary of getDmsentryByID
     * Find dmsentry record by ID (= Guid)
     * @param int $EntryNo - primary key (integer) of Dmsentry record
     * @return null|array
     */
	public function getDmsentry ( $EntryNo )
	{
		$dmsentry = null;
		$this->db->initQuery('dmsentry');
		$this->db->setFilter('EntryNo',$EntryNo);
		if ($this->db->findFirst())
			$dmsentry = $this->db->getResult();			
		return $dmsentry;		
	}

    /**
     * Summary of getActualMeeting
     * @param string $MeetingName
     * @return null|array
     */
    public function getActualMeeting (string $MeetingName ): ?array
	{
		$electionperiodinstance = new Electionperiodcontroller($this->registry);

		$meeting = null;
		$meetingtype = null;
		$electionperiod = $electionperiodinstance->getActualElectionperiod();


		if($electionperiod){
			$this->db->initQuery('meetingtype');
			$this->db->setfilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);
			$this->db->setfilter('MeetingName',$MeetingName);
			if ($this->db->findFirst())
				$meetingtype = $this->db->getResult();
		}
		if($meetingtype){
			$this->db->initQuery('meeting');
			$this->db->setfilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
			$this->db->setfilter('Actual',1);
			if ($this->db->findFirst()){
				$meeting = $this->db->getResult();
			}else{
				$_POST['MeetingTypeID'] = $meetingtype['MeetingTypeID'];
				$this->index('add');
				$this->db->initQuery('meeting');
				$this->db->setfilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
				$this->db->setfilter('Actual',1);
				if ($this->db->findFirst()){
					$meeting = $this->db->getResult();
				}
			}
		}
		return $meeting;
	}

    /**
	 * Summary of isMeetingUsed
	 * @param int|array $param - MeetingID or table meeting
	 * @return bool
	 */
	public function isMeetingUsed( int|array $param): bool 
	{
        $MeetingID = is_array($param) ? $param['MeetingID'] : $param;
		$meeting = $this->getMeeting($MeetingID);
		if (!$meeting)
			return false;

		// meetingline
		$this->db->initQuery('meetingline');
		$this->db->setFilter('MeetingID',$MeetingID);
		if (!$this->db->isEmpty())
			return true;

		// meetingattachment
		$this->db->initQuery('meetingattachment');
		$this->db->setFilter('MeetingID',$MeetingID);
		if (!$this->db->isEmpty())
			return true;

		// meetinglinepage
		$this->db->initQuery('meetinglinepage');
		$this->db->setFilter('MeetingID',$MeetingID);
		if (!$this->db->isEmpty())
			return true;

		// meetinglinepageline
		$this->db->initQuery('meetinglinepageline');
		$this->db->setFilter('MeetingID',$MeetingID);
		if (!$this->db->isEmpty())
			return true;

		// meetinglinetask
		$this->db->initQuery('meetinglinetask');
		$this->db->setFilter('MeetingID',$MeetingID);
		if (!$this->db->isEmpty())
			return true;

		// inbox
		$this->db->initQuery('inbox');
		$this->db->setFilter('MeetingID',$MeetingID);
		if (!$this->db->isEmpty())
			return true;

		return false;
	}

//--------------------------------------------------------------------------------------------
// GET strings from meeting
//--------------------------------------------------------------------------------------------
    /**
     * Summary of getMeetingNo
     * @param int|array $param - MeetingID or tablemeeting or table with index 'MeetingID'
     * @param bool $withMeetingName
     * @return string - Return string "1/2024" or "Rada - 1/2024"  
     */
    public function getMeetingNo ( int|array $param , bool $withMeetingName = false): string
	{
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);

		$MeetingNo = '';
		$meeting = $this->getMeeting( $param );
		if($meeting){
			$MeetingNo = $meeting['EntryNo'].'/'.$meeting['Year'];
			if ($withMeetingName){
				$meetingtype = $meetingtypeinstance->getMeetingtype($meeting['MeetingTypeID']);
				$MeetingNo = $meetingtype['MeetingName'].' - '.$MeetingNo;
			}
		}
		return $MeetingNo;
	}
    /**
     * Summary of getMeetingExcused
     * @param int|array $param - MeetingID or table meeting
     * @return string
     */
    public function getMeetingExcused( int|array $param )
	{
		$meeting = $this->getMeeting($param);
		$excused = '';

		//TODO - Doplnit jména omluvených členů
		$excused = '';

		return $excused;
	}
    /**
     * Summary of getMeetingVerifierBy
     * @param int|array $param - MeetingID or table meeting
     * @return string
     */
	public function getMeetingVerifierBy( int|array $param ): string
	{
        $zob = new Zobcontroller($this->registry, false);
        $meeting = $this->getMeeting($param);
		$verifier = '';

        $verifier = $zob->getContactFullName($meeting['VerifierBy1']);
		$name2 = $zob->getContactFullName($meeting['VerifierBy2']);
		if($name2 != ''){			
			$verifier .= ($verifier == '') ? '' : ', ';
			$verifier .= $name2;
		}
		return $verifier;
	}

}