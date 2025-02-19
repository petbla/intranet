<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    13.10.2024
 */
class Electionperiodcontroller
{
	private $registry;
	private $db;
	private $errorMessage;

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
		// zob/electionperiod/<$action>/$ElectionPeriodID/<parametry>

		$zob = new Zobcontroller($this->registry, false);
		$urlBits = $this->registry->getURLBits();

		switch ($action) {
			case 'add':
				$ElectionPeriodID = 0;
				$this->addElectionPeriod();
				break;
			case 'modify':
				$ElectionPeriodID = isset($_POST["ElectionPeriodID"]) ? $_POST["ElectionPeriodID"] : '';
				$this->modifyElectionPeriod($ElectionPeriodID);
				break;
			case 'delete':
				$ElectionPeriodID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$this->deleteElectionPeriod($ElectionPeriodID);
				break;
			case 'active':
				$ElectionPeriodID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$this->setElectionperiodActive( $ElectionPeriodID );
				break;
		}
		$zob->errorMessage = $this->errorMessage;
		$zob->listElectionperiod();
	}	

    /**
	 * Summary of addElectionPeriod
	 * @return void
	 */
	private function addElectionPeriod(): void{
		$PeriodName = isset($_POST['PeriodName']) ? $_POST['PeriodName'] : '';
		$Actual = isset($_POST['Actual']) ? $_POST['Actual'] : '';
		$Actual = $Actual != '' ? $Actual : 0;
		if ($PeriodName == ''){
			$this->errorMessage = 'Název musí být vyplněn!';
			return ;
		};		
		$this->db->initQuery('electionperiod');
		$this->db->setFilter('PeriodName',$PeriodName);
		if (!$this->db->isEmpty()){
			$this->errorMessage = "Volební období $PeriodName již existuje!";
			return;
		}
		$data = array();
		$data['PeriodName'] = $this->db->sanitizeData($PeriodName);
		$data['Actual'] = $Actual;
		$this->db->insertRecords('electionperiod',$data);
		return;
	}
	/**
	 * Summary of modifyElectionPeriod
	 * @param int|array $param - $ElectionPeriodID or table with index 'ElectionPeriodID'
	 * @return void
	 */
	private function modifyElectionPeriod(int|array $param): void{
		$ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
		$PeriodName = isset($_POST['PeriodName']) ? $_POST['PeriodName'] : '';
		$Actual = isset($_POST['Actual']) ? $_POST['Actual'] : '';
		$Actual = $Actual != '' ? $Actual : 0;

		if ($PeriodName == ''){
			$this->errorMessage = 'Název musí být vyplněn!';
			return;
		};		
		$this->db->initQuery('electionperiod');
		$this->db->setFilter('PeriodName',$PeriodName);
		if ($ElectionPeriodID > 0)
			$this->db->setCondition("ElectionPeriodID <> $ElectionPeriodID");
		if (!$this->db->isEmpty()){
			$this->errorMessage = "Volební období $PeriodName již existuje!";
			return;
		}
		// Reset pole Actual na všech záznamech
		if ($Actual == 1){
			$changes = array();
			$changes['Actual'] = 0;
			$this->db->updateRecords('electionperiod',$changes, '');
		}
		$data = array();
		$data['PeriodName'] = $this->db->sanitizeData($PeriodName);
		$data['Actual'] = $Actual;
		$condition = "ElectionPeriodID = $ElectionPeriodID";
		$this->db->updateRecords('electionperiod',$data,$condition);
	}
	/**
	 * Summary of deleteElectionPeriod
	 * @param int|array $param - $ElectionPeriodID or table with index 'ElectionPeriodID'
	 * @return void
	 */
	private function deleteElectionPeriod(int|array $param ): void{
		$ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
		if ($this->isElectionperiodUsed($ElectionPeriodID)){
			$electionpoeriod = $this->getElectionperiod($ElectionPeriodID);
			$PeriodName = $electionpoeriod['PeriodName'];
			$this->errorMessage = "Volební období $PeriodName již bylo použito, nelze jej odstranit!";
		}else{
			$condition = "ElectionPeriodID = $ElectionPeriodID";
			$this->db->deleteRecords('electionperiod',$condition);
		}
		return;	
	}
	/**
	 * Summary of getElectionperiod
	 * @param int|array $param - $ElectionPeriodID or table with index 'ElectionPeriodID'
	 * @return null|array
	 */
	public function getElectionperiod ($param ): null|array
	{
		$ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
		$electionperiod = null;
		$this->db->initQuery('electionperiod');
		$this->db->setFilter('ElectionPeriodID',$ElectionPeriodID);
		if ($this->db->findFirst())
			$electionperiod = $this->db->getResult();
		return $electionperiod;
	}
	/**
	 * Summary of getActualElectionperiod
	 * @return null|array
	 */
	public function getActualElectionperiod ( ): null|array
	{
		$electionperiod = null;
		$this->db->initQuery('electionperiod');
		$this->db->setFilter('Actual',1);
		if ($this->db->findFirst()) {
			$electionperiod = $this->db->getResult();
		}
		return $electionperiod;
	}
	/**
	 * Summary of readElectionperiods
	 * @return null|array
	 */
	public function readElectionperiods ( ): null|array
	{
		$electionperiod = null;
		$this->db->initQuery('electionperiod');
		if ($this->db->findSet())
			$electionperiod = $this->db->getResult();			
		return $electionperiod;
	}
	/**
	 * Summary of isElectionperiodTemplate
	 * @param int|array $param - $ElectionPeriodID or table with index 'ElectionPeriodID'
	 * @return bool
	 */
	public function isElectionperiodTemplate (int|array $param ): bool
	{
		$ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
		// meetingtype
		$this->db->initQuery('electionperiod');
		$this->db->setFilter('ElectionPeriodID',$ElectionPeriodID);
		$this->db->setCondition("PeriodName like '<%>'");
		return (!$this->db->isEmpty());
	}
	/**
	 * Summary of isElectionperiodUsed
	 * Search in table meetingtype
	 * @param int|array $param - $ElectionPeriodID or table with index 'ElectionPeriodID'
	 * @return bool
	 */
	public function isElectionperiodUsed (int|array $param ): bool
	{
		$ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
		// meetingtype
		$this->db->initQuery('meetingtype');
		$this->db->setFilter('ElectionPeriodID',$ElectionPeriodID);
		if (!$this->db->isEmpty())
			return true;
		return false;
	}

	public function setElectionperiodActive (int|array $param ): void
	{
		$ElectionPeriodID = is_array($param) ? $param['ElectionPeriodID'] : $param;
		// Reset pole Actual na všech záznamech
		$changes = array();
		$changes['Actual'] = 0;
		$this->db->updateRecords('electionperiod',$changes, '');

		$data = array();
		$data['Actual'] = 1;
		$condition = "ElectionPeriodID = $ElectionPeriodID";
		$this->db->updateRecords('electionperiod',$data,$condition);
	}

}
