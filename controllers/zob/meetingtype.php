<?php
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    13.10.2024
 */
class Meetingtypecontroller 
{
	private $registry;
    private $db;
    private $errorMessage;

    private $MeetingName;
    private $Members;
    private $ElectionPeriodID = 0;

	public function __construct(Registry $registry)
	{		
        $this->registry = $registry;
        $this->db = $this->registry->getObject('db');
	}

    /**
     * Summary of index
     * @param mixed $action
     * @return void
     */
    public function index($action): void
    {
 		// 0/1/2/3..
		// zob/meetingtype/<$action>/$MeetingTypeID/<parametry>

        $zob = new Zobcontroller($this->registry, false);

        $urlBits = $this->registry->getURLBits();     
		$this->MeetingName = isset($_POST['MeetingName']) ? $_POST['MeetingName'] : '';
		$this->Members = isset($_POST['Members']) ? $_POST['Members'] : 0;

		$this->ElectionPeriodID = isset($_POST["ElectionPeriodID"]) ? $_POST["ElectionPeriodID"] : '';
		switch ($action) {
            case 'add':
                $this->addMeetingType();
				break;
			case 'modify':
				$MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : '';
                $this->modifyMeetingType($MeetingTypeID);
				break;
			case 'delete':
				$MeetingTypeID = isset($urlBits[3]) ? $urlBits[3] : '';
				$this->ElectionPeriodID = isset($urlBits[4]) ? $urlBits[4] : '';
                $this->deleteMeetingType($MeetingTypeID);
				break;
		}
        $zob->errorMessage = $this->errorMessage;
        $zob->listElectionperiod( $this->ElectionPeriodID );
	}	

    /**
     * Summary of addMeetingType
     * @return void
     */
    private function addMeetingType()
    {
        $zob = new Zobcontroller($this->registry, false);

        if ($this->MeetingName == ''){
			$this->errorMessage = 'Název musí být vyplněn!';
			return;
		};		
		if ($this->Members == 0){
			$this->errorMessage = "Zadejte počet členů.";
			return;	
		};

		$this->db->initQuery('meetingtype');
		$this->db->setFilter('ElectionPeriodID',$this->ElectionPeriodID);
		$this->db->setFilter('MeetingName',$this->MeetingName);
		if (!$this->db->isEmpty()){
			$this->errorMessage = "Typ jednání $this->MeetingName pro volební období již existuje!";
			return;
		}
		$data = array();
		$data['ElectionPeriodID'] = $this->ElectionPeriodID;
		$data['MeetingName'] = $this->db->sanitizeData($this->MeetingName);
		$data['Members'] = $this->Members;
		$this->db->insertRecords('meetingtype',$data);
    }
    /**
     * Summary of modifyMeetingType
     * @param int|array $param MeetingTypeID or table meetingtype
     * @return void
     */
    private function modifyMeetingType(int|array $param):void
    {
        $zob = new Zobcontroller($this->registry, false);

        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;
		if ($this->MeetingName == ''){
			$this->errorMessage = 'Název musí být vyplněn!';
			return;
		};		
		if ($this->Members == 0){
			$this->errorMessage = "Zadejte počet členů.";
			return;	
		};

		$this->db->initQuery('meetingtype');
		$this->db->setFilter('ElectionPeriodID',$this->ElectionPeriodID);
		$this->db->setFilter('MeetingName',$this->MeetingName);
		$this->db->setCondition("MeetingTypeID <> $MeetingTypeID");
		if (!$this->db->isEmpty()){
			$this->errorMessage = "Typ jednání $this->MeetingName pro volební období již existuje!";
			return;
		}
		$data = array();
		$data['MeetingName'] = $this->db->sanitizeData($this->MeetingName);
		$data['Members'] = $this->Members;
    	$condition = "MeetingTypeID = $MeetingTypeID";
		$this->db->updateRecords('meetingtype',$data,$condition);
    }
    /**
     * Summary of deleteMeetingType
     * @param int|array $param MeetingTypeID or table meetingtype
     * @return void
     */
    private function deleteMeetingType(int|array $param):void
    {
        $zob = new Zobcontroller($this->registry, false);
        $electionperiodinstance = new Electionperiodcontroller($this->registry);
        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;
        if ($this->isMeetingtypeUsed($MeetingTypeID)){
            $this->errorMessage = "Typ jednání $this->MeetingName pro volební období již bylo použito, nelze jej odstranit!";
            return;	
        }
        $condition = "MeetingTypeID = $MeetingTypeID";
        $this->db->deleteRecords( 'meetingtype', $condition, 1); 
        return;
    }
    /**
    * Summary of getMeetingtype
    * @param int|array $param MeetingTypeID or table meetingtype
    * @return null|array
    */
    public function getMeetingtype (int|array $param ):null|array
    {
        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;      
        $meetingtype = null;
        $this->db->initQuery('meetingtype');
        $this->db->setFilter('MeetingTypeID',$MeetingTypeID);
        if ($this->db->findFirst())
            $meetingtype = $this->db->getResult();			
        return $meetingtype;
    }
    /**
    * Summary of readMeetingtypesByElectionperiodID
    * @param int|array $param ElectionPeriodID or table with index 'ElectionPeriodID'
    * @return null|array
    */
    public function readMeetingtypesByElectionperiodID (int|array $param ):null|array
    {
        $electionperiodinstance = new Electionperiodcontroller($this->registry);

        $ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
        $electionPeriod = $electionperiodinstance->getElectionPeriod($ElectionPeriodID);
        $meetingtype = null;
        if ($electionPeriod){
           $this->db->initQuery('meetingtype');
           $this->db->setFilter('ElectionPeriodID',$ElectionPeriodID);
           if ($this->db->findSet())
               $meetingtype = $this->db->getResult();			
       }
       return $meetingtype;
    }

    /**
    * Summary of isMeetingtypeUsed
    * @param int|array $param - MeetingTypeID or table meetingtype
    * @return bool
    */
    public function isMeetingtypeUsed(int|array $param) 
    {
        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;   

       // members
       $this->db->initQuery('member');
       $this->db->setFilter('MeetingTypeID',$MeetingTypeID);
       if (!$this->db->isEmpty())
           return true;

       // meeting
       $this->db->initQuery('meeting');
       $this->db->setFilter('MeetingTypeID',$MeetingTypeID);
       if (!$this->db->isEmpty())
           return true;
       return false;
   }
}