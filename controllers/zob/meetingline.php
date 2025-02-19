<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    20.10.2024
 */
class Meetinglinecontroller
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
    public function index(string $action): void
    {
		// 0/1/2/3..
		// zob/meetingline/<$action>/$MeetingID/$MeetingLineID/<parametry>

        $zob = new Zobcontroller($this->registry, false);
        $urlBits = $this->registry->getURLBits();
        $MeetingID = isset($urlBits[3]) ? $urlBits[3] : null;
        $MeetingLineID = isset($urlBits[4]) ? $urlBits[4] : null;

        switch ($action) {
			case 'add':
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : $MeetingID;
                $MeetingLineID = 0;
                $this->addMeetingline($MeetingID);
				break;
			case 'delete':
				$this->deleteMeetingline($MeetingLineID);
				$MeetingLineID = 0;
				break;
			case 'list':
				break;
			case 'moveup':
				if($MeetingLineID){
					$this->moveMeetingline($MeetingLineID, -1 );
				}
				$MeetingLineID = 0;
				break;
			case 'movedown':
				if($MeetingLineID){
					$this->moveMeetingline($MeetingLineID, 1 );
				}
				$MeetingLineID = 0;
				break;
		}
        $zob->errorMessage = $this->errorMessage;	
		$zob->listMeetingLine( $MeetingID , $MeetingLineID );
    }

	/**
	 * Summary of addMeetingline
	 * @param int|array $param - MeetingID or table meeting
	 * @return bool
	 */
	public function addMeetingline( int|array $param): bool
	{
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
		$meetinginstance = new Meetingcontroller($this->registry);

		$MeetingID = is_array($param) ? $param['MeetingID'] : $param;       
		$meeting = $meetinginstance->getMeeting($MeetingID);
		if (!$meeting)
			return false;
		$meetingtype = $meetingtypeinstance->getMeetingtype($meeting['MeetingTypeID']);

		// Vložení bodů ze šablony
		if (isset($_POST['submitTemplate'])){
			
			$meetinglineTemplate = $this->readMeetingLinesFromTemplate( $meetingtype['MeetingName']);
			if($meetinglineTemplate == null){
				$this->errorMessage = 'Šablona pro jednání '.$meetingtype['MeetingName'].' nebyla nalezena.';
				return false;
			}
			foreach($meetinglineTemplate as $meetingline){
				$MeetingLineIDTemplate = $meetingline['MeetingLineID'];
				$meetingline['MeetingLineID'] = null;
				$meetingline['MeetingID'] = $MeetingID;
				$meetingline['MeetingTypeID'] = $meeting['MeetingTypeID'];
				$meetingline['ElectionPeriodID'] = $meeting['ElectionPeriodID'];
				$this->db->insertRecords('meetingline',$meetingline);
				$MeetingLineID = $this->getLastMeetinglineID();

				// meetinglinepage
				$meetinglinepages = $zob->readMeetinglinepage( $MeetingLineIDTemplate );
				if($meetinglinepages){
					foreach($meetinglinepages as $meetinglinepage){
						$meetinglinepage['PageID'] = null;
						$meetinglinepage['MeetingLineID'] = $MeetingLineID;
						$meetinglinepage['MeetingID'] = $MeetingID;
						$meetinglinepage['MeetingTypeID'] = $meeting['MeetingTypeID'];
						$meetinglinepage['System'] = 1;
						$this->db->insertRecords('meetinglinepage',$meetinglinepage);
					}
				}
			}
			return true;
		}

		// Data z FORMuláře
		$data = array();
		$data['LineType'] = isset($_POST['LineType']) ? $_POST['LineType'] : '';
		$data['Title'] = isset($_POST['Title']) ? $this->db->sanitizeData($_POST['Title']) : '';

		// Kontroly
		if($meeting['Close'] == 1){
			$this->errorMessage = 'Nelze měnit zápis uzavřeného jednání.';
			return false;
		}
		if($data['Title'] == ''){
			$this->errorMessage = 'Text bodu jednání musí být vyplněn.';
			return false;
		}

		$data['MeetingID'] = $MeetingID;
		$data['MeetingTypeID'] = $meeting['MeetingTypeID'];
		$data['ElectionPeriodID'] = $meeting['ElectionPeriodID'];
		if($data['LineType'] == 'Podbod'){
			$data['LineNo'] = $this->getLastMeetinglineLineNo( $MeetingID );
			$data['LineNo2'] = $this->getNextMeetinglineLineNo2( $MeetingID, $data['LineNo'] );
		}else
			$data['LineNo'] = $this->getNextMeetinglineLineNo( $MeetingID );
			$data['LineNo2'] = 0;
		$this->db->insertRecords('meetingline',$data);
		return true;
	}

    	/**
	 * Summary of deleteMeetingline
	 * @param int|array $param - MeetingLineID or table meetingline
	 * @return bool
	 */
	public function deleteMeetingline( int|array $param)
	{
		$meetinginstance = new Meetingcontroller($this->registry);
		$MeetingLineID = is_array($param) ? $param['MeetingLineID']: $param;       
		$meetingline = $this->getMeetingline($MeetingLineID);
		if(!$meetingline)
			return false;
		$meeting = $meetinginstance->getMeeting($meetingline['MeetingID']);

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
			$this->db->deleteRecords( 'meetingline', $condition, 1); 					
			$this->reorderMeetingLines($meeting['MeetingID']);
		}
		return true;
	}

    /**
     * Summary of getMeetingline
     * @param int|array $param - MeetingLineID or table with index 'MeetingLineID'
     * @return null|array
     */
    public function getMeetingline ( int|array $param ): ?array
	{
		$MeetingLineID = is_array($param) ? $param['MeetingLineID']: $param;       
		$meetingline = null;
		$this->db->initQuery('meetingline');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if ($this->db->findFirst())
			$meetingline = $this->db->getResult();			
		return $meetingline;
	}
    /**
     * Summary of getLastMeetinglineID
     * @return int
     */
    public function getLastMeetinglineID(  ): int
	{
		$MeetingLineID = 0;
		$this->db->initQuery('meetingline');
		$this->db->setOrderBy('MeetingLineID');
		if ($this->db->findLast()){
			$meetingline = $this->db->getResult();
			$MeetingLineID = $meetingline['MeetingLineID'];			
		}
		return $MeetingLineID;		
	}

    /**
     * Summary of getMeetinglineByLineNo
     * @param int|array $param - MeetingID or table meeting
     * @param int $LineNo
     * @return null|array
     */
	public function getMeetinglineByLineNo ( int|array $param, int $LineNo ): null|array
	{
        $MeetingID = is_array($param) ? $param['MeetingID']: $param;       
        $meetingline = null;
		$this->db->initQuery('meetingline');
		$this->db->setFilter('MeetingID',$MeetingID);
		$this->db->setFilter('LineNo',$LineNo);
		if ($this->db->findFirst())
			$meetingline = $this->db->getResult();			
		return $meetingline;
	}
    /**
     * Summary of getNextMeetinglineLineNo
     * @param mixed $param - MeetingID or table meeting
     * @return int
     */
    public function getNextMeetinglineLineNo( $param ): int
	{
		$MeetingID = is_array($param) ? $param['MeetingID']: $param;     
		$sql = "SELECT max(LineNo) as LineNo FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return $result['LineNo'] + 1;				
	}

    /**
     * Summary of readMeetingLines
     * @param int|array $param - MeetingID or table meeting
     * @return null|array - Return array of meetinglines[]
     */
    public function readMeetingLines ( int|array $param  ): null|array
	{
		$MeetingID = is_array($param) ? $param['MeetingID']: $param;     
		$meetinglines = null;
		$this->db->initQuery('meetingline');
		$this->db->setFilter('MeetingID',$MeetingID);
		$this->db->setOrderBy('LineNo');
		if ($this->db->findSet())
			$meetinglines = $this->db->getResult();			
		return $meetinglines;
	}
    /**
     * Summary of readMeetingLinesFromTemplate
     * @param string $MeetingName
     * @return null|array - Return template array of meetinglines[] OR null if not exists
     * Template has name '<....>'
     */
	public function readMeetingLinesFromTemplate( string $MeetingName ): null|array
	{
		$electionperiod = null;
		$this->db->initQuery('electionperiod');
		$this->db->setCondition("PeriodName like '<%>'");
		if (!$this->db->findFirst())
			return null;	
		$electionperiod = $this->db->getResult();

		$meetingtype = null;
		$this->db->initQuery('meetingtype');
		$this->db->setFilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);
		$this->db->setFilter('MeetingName',$MeetingName);
		if (!$this->db->findFirst())
			return null;	
		$meetingtype = $this->db->getResult();

		$meeting = null;
		$this->db->initQuery('meeting');
		$this->db->setFilter('MeetingTypeID',$meetingtype['MeetingTypeID']);
		$this->db->setFilter('EntryNo',1);
		if (!$this->db->findFirst())
			return null;	
		$meeting = $this->db->getResult();

		$meetingline = null;
		$this->db->initQuery('meetingline');
		$this->db->setFilter('MeetingID',$meeting['MeetingID']);
		if ($this->db->findSet())
			$meetingline = $this->db->getResult();			
		return $meetingline;
	}

    /**
     * Summary of isMeetinglineUsed
     * @param int|array $param - MeetingLineID or table meetingline
     * @return bool
     */
    public function isMeetinglineUsed ( int|array $param ): bool
	{
		$MeetingLineID = is_array($param) ? $param['MeetingLineID']: $param;       
		$meetingline = $this->getMeetingline($MeetingLineID);
		if (!$meetingline)
			return false;

    	// meetingattachment
		$this->db->initQuery('meetingattachment');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if (!$this->db->isEmpty())
			return true;

		// meetinglinecontent
		$this->db->initQuery('meetinglinecontent');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if (!$this->db->isEmpty())
			return true;

		// meetinglinepage
		$this->db->initQuery('meetinglinepage');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if (!$this->db->isEmpty())
			return true;

		// meetinglinetask
		$this->db->initQuery('meetinglinetask');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if (!$this->db->isEmpty())
			return true;

		return false;
	}

    /**
     * Summary of hasChildMeetingline
     * @param int|array $param - MeetingLineID or table meetingline
     * @return bool
     */
    private function hasChildMeetingline( int|array $param): bool{
		$MeetingLineID = is_array($param) ? $param['MeetingLineID']: $param;       
		$meetingline = $this->getMeetingline($MeetingLineID);
		$MeetingID = $meetingline['MeetingID'];
		$LineNo = $meetingline['LineNo'];
		$sql = "SELECT Count(*) as pocet FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID AND MeetingLineID <> $MeetingLineID and LineNo = $LineNo";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return ($result['pocet'] > 0);		
	}

    /**
     * Summary of getLastMeetinglineLineNo
     * @param int|array $param - MeetingID or table meeting
     * @return int
     */
    public function getLastMeetinglineLineNo( int|array $param ): int
	{
		$MeetingID = is_array($param) ? $param['MeetingID']: $param;     
        $sql = "SELECT max(LineNo) as LineNo FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return $result['LineNo'] ;				
	}
    /**
     * Summary of getNextMeetinglineLineNo2
     * @param int|array $param - MeetingID or table meeting
     * @param int $LineNo
     * @return int
     */
    public function getNextMeetinglineLineNo2( int|array $param , int $LineNo): int
	{
		$MeetingID = is_array($param) ? $param['MeetingID']: $param;     
		$sql = "SELECT max(LineNo) as LineNo2 FROM ".$this->prefDb."meetingline WHERE MeetingID = $MeetingID AND LineNo = $LineNo";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return $result['LineNo2'] + 1;				
	}

	/**
	 * Summary of reorderMeetingLines
	 * Přečíslování bodů
	 * @param int|array $param - MeetingID or table meeting
	 * @return void
	 */
	private function reorderMeetingLines( int|array $param): void
    {	
        $MeetingID = is_array($param) ? $param['MeetingID']: $param;     
        $meetinglines = $this->readMeetingLines($MeetingID);
		if($meetinglines){
			$LineNo = 0; 
			foreach ($meetinglines as $meetingline){								
				if($meetingline['LineType'] != 'Podbod')
					$LineNo += 1;
                $this->db->update('meetingline', $meetingline['MeetingLineID'], 'LineNo', $LineNo);
			}
		}
	}
    /**
     * Summary of moveMeetingline
     * @param int|array $param - MeetingLineID or table meetingline
     * @param int $step
     * @return bool
     */
    public function moveMeetingline( int|array $param, int $step ): bool{
		$meetinginstance = new Meetingcontroller($this->registry);
		$MeetingLineID = is_array($param) ? $param['MeetingLineID'] :$MeetingLineID = $param;
		$fromMeetingline = $this->getMeetingline($MeetingLineID);
		if(!$fromMeetingline)
			return false;
		
		$fromLine = $fromMeetingline['LineNo'];
		$toLine = $fromLine + $step;

		$meeting = $meetinginstance->getMeeting($fromMeetingline['MeetingID']);
		if($meeting['Close'] == 1){
			$this->errorMessage = 'Nelze měnit pořadí uzavřeného zápisu jednání.';
			return false;				
		}

		// Kontrola z prvního řádku výš (což nelze)
		if($toLine == 0)
			return false;

		// Kontrola posledního řádku - $toLine neexistuje
		$toMeetingline = $this->getMeetinglineByLineNo( $fromMeetingline['MeetingID'] , $toLine );
		if ($toMeetingline == null)
			return false;

		// Přesun
		$data = array();
		$data['LineNo'] = -1;
		$condition = "MeetingID = ".$fromMeetingline['MeetingID']." AND LineNo = $fromLine";
		$this->db->updateRecords('meetingline',$data,$condition);
		
		$data['LineNo'] = $fromLine;
		$condition = "MeetingID = ".$toMeetingline['MeetingID']." AND LineNo = $toLine";
		$this->db->updateRecords('meetingline',$data,$condition);

		$data['LineNo'] = $toLine;
		$condition = "MeetingID = ".$toMeetingline['MeetingID']." AND LineNo = -1";
		$this->db->updateRecords('meetingline',$data,$condition);
		return true;
	}

}