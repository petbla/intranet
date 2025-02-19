<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    13.10.2024
 */
class Membercontroller
{
	private $registry;
	private $db;
	private $errorMessage;
    private $MeetingTypeID;
    private $ElectionPeriodID;

    private array $meetingtype;

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
		// zob/member/<$action>/$MemberID/MeetingTypeID/<parametry>

		$urlBits = $this->registry->getURLBits();     
		$zob = new Zobcontroller($this->registry, false);
        $meetingtypeinstance = new Meetingtypecontroller($this->registry);

		$this->MeetingTypeID = isset($_POST["MeetingTypeID"]) ? $_POST["MeetingTypeID"] : '';
		$this->MeetingTypeID = isset($urlBits[4]) ? $urlBits[4] : $this->MeetingTypeID;

		if ($this->MeetingTypeID == ''){
			$zob->errorMessage = 'Není vyplněno ID jednání';
			$zob->listElectionperiod( );
			return;
		}
		$this->meetingtype = $meetingtypeinstance->getMeetingtype($this->MeetingTypeID);
		$this->ElectionPeriodID = $this->meetingtype['ElectionPeriodID'];

		switch ($action) {
			case 'add':
                $MemberID = isset($_POST["MemberID"]) ? $_POST["MemberID"] : 0;
				if ($MemberID)
					$this->modifyMember($MemberID);
				else
                	$this->addMember();
				break;
            case 'modify':
                $MemberID = isset($_POST["MemberID"]) ? $_POST["MemberID"] : 0;
                $this->modifyMember($MemberID);
                break;
            case 'delete':
				$MemberID = isset($urlBits[3]) ? $urlBits[3] : 0;
                $this->deleteMember($MemberID);
				break;
            case 'addcontact':
                $this->addcontact();
				break;
		}		
		$zob->errorMessage = $this->errorMessage;
        $zob->listElectionperiod( $this->ElectionPeriodID, $this->MeetingTypeID );
	}		

    /**
     * Summary of addMember
     * @return void
     */
    private function addMember(): void
    {
		$zob = new Zobcontroller($this->registry, false);

		$MemberType = isset($_POST['MemberType']) ? $_POST['MemberType'] : '';
		$ContactName = isset($_POST['ContactName']) ? $_POST['ContactName'] : 0;

        if ($ContactName == ''){
			$zob->errorMessage = 'Jméno musí být vyplněno.';
			return;
		}

		$contact = $zob->getContactByName($ContactName);
		if(!$contact){
			$this->errorMessage = "Jméno $ContactName nenalezeno v kontaktech.";
			return;
		}
		$ContactID = $contact['ID'];

		$this->db->initQuery('member');
		$this->db->setFilter('MeetingTypeID',$this->MeetingTypeID);
		$this->db->setFilter('ContactID',$ContactID);
		if (!$this->db->isEmpty()){
			$this->errorMessage = "Člen jednání $ContactName pro volební období již existuje!";
			return;
		}

        $countMember = $zob->countRec('member', "MeetingTypeID = $this->MeetingTypeID");
        if ($countMember >= $this->meetingtype['Members']){
            $this->errorMessage = "Překročen maximální počet členů";
            return;
        }

		$data = array();
		$data['MeetingTypeID'] = $this->MeetingTypeID;
		$data['MemberType'] = $MemberType;
		$data['ContactID'] = $ContactID;
		$this->db->insertRecords('member',$data);
    }
    /**
     * Summary of deleteMember
     * @param int|array $param - MemberID or table member
     * @return void
     */
    private function deleteMember(int|array $param):void
    {
		$zob = new Zobcontroller($this->registry, false);

		$MemberID = is_array($param) ? $param['MemberID'] : $param;
        $member = $this->getMember($MemberID);
        if (!$member)
            return;
        if ($this->isMemberUsed($MemberID)){
            $this->errorMessage = "Člen $MemberID již byl použit, nelze jej odstranit!";
            return;	
        }
        $condition = "MemberID = $MemberID";
        $this->db->deleteRecords( 'member', $condition, 1); 
    }
    /**
     * Summary of modifyMember
     * @param int|array $param - MemberID or table member
     * @return void
     */
    private function modifyMember(int|array $param): void
    {
		$zob = new Zobcontroller($this->registry, false);

		$MemberID = is_array($param) ? $param['MemberID'] : $param;
        $member = $this->getMember($MemberID);
        if(!$member){
			$this->errorMessage = 'Člen $MemberID neexistuje.';
			return;
        }
		$MemberType = isset($_POST['MemberType']) ? $_POST['MemberType'] : '';
		$ContactName = isset($_POST['ContactName']) ? $_POST['ContactName'] : 0;
		if ($ContactName == ''){
			$this->errorMessage = 'Jméno musí být vyplněno.';
			return;
		}
		$contact = $zob->getContactByName($ContactName);
		if(!$contact){
			$this->errorMessage = "Jméno $ContactName nenalezeno v kontaktech.";
			return;
		}
		$ContactID = $contact['ID'];

		$this->db->initQuery('member');
		$this->db->setFilter('MeetingTypeID',$this->MeetingTypeID);
		$this->db->setFilter('ContactID',$ContactID);
		$this->db->setCondition("MemberID <> $MemberID");
		if (!$this->db->isEmpty()){
			$this->errorMessage = "Člen jednání $ContactName pro volební období již existuje!";
			return;
		}

		$data = array();
		$data['MemberID'] = $MemberID;
		$data['MeetingTypeID'] = $this->MeetingTypeID;
		$data['MemberType'] = $MemberType;
		$data['ContactID'] = $ContactID;
	    $condition = "MemberID = $MemberID";
    	$this->db->updateRecords('member',$data,$condition);
    }
    /**
     * Summary of getMember
     * @param int $MemberID
     * @return null|array
     */
    public function getMember (int $MemberID ): null|array
	{
		$member = null;
		$this->db->initQuery('member');
		$this->db->setFilter('MemberID',$MemberID);
		if ($this->db->findFirst())
			$member = $this->db->getResult();
		return $member;
	}
    /**
     * Summary of readMembers
     * @param int|array $param - MeetingTypeID or table meetingtype or table with index 'MeetingTypeID'
     * @return null|array
     */
    public function readMembers ( int|array $param): null|array
	{
        $MeetingTypeID = is_array($param) ? $param['MeetingTypeID'] : $param;
        $members = null;
        if ($MeetingTypeID == 0)
            return null;
		$this->db->initQuery('member');
        $this->db->setFilter('MeetingTypeID',$MeetingTypeID);
		if ($this->db->findSet())
			$members = $this->db->getResult();			
		return $members;
	}
    /**
     * Summary of isMemberUsed
     * @param mixed $param $param - MemberID or table member
     * @return bool
     */
    public function isMemberUsed($param): bool 
	{
        $MemberID = is_array($param) ? $param['MemberID'] : $param;

        // Check if used somewhere
		return false;
	}
	/**
	 * Summary of addcontact
	 * @return void
	 */
	private function addcontact( ): void
	{
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
		$ContactID = $this->registry->getObject('fce')->GUID();

		if ($this->MeetingTypeID == ''){
			$this->errorMessage = 'Není vyplněno ID jednání';
			return;
		}
		$meetingtype = $meetingtypeinstance->getMeetingtype($this->MeetingTypeID);
		$ElectionPeriodID = $meetingtype['ElectionPeriodID'];
		
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
		$contact['FullName'] = $this->db->sanitizeData($FullName);
		$this->db->insertRecords('contact',$contact);

		// Založení člena jednání
		$member = array();
		$member['MeetingTypeID'] = $this->MeetingTypeID;
		$member['ContactID'] = $ContactID;
		$this->db->insertRecords('member',$member);
	}
}
    