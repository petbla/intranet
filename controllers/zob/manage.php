<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    6.1.2023
 */
class Zobmanage {
	
    private $registry;
    private $zob;
	private $message;
	private $errorMessage;
    
    /**
	 * @param Registry $registry 
	 */
	public function __construct( Registry $registry )
	{
        $this->registry = $registry;
        $this->zob = new Zobcontroller( $this->registry, false );					
    }

	/**
	 * Správa dat a modulu
	 * @return void
	 */
	public function manage( $action )
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
		$this->zob->listElectionPeriod();
	}

    /**
     * Sestavení stránky pro TISK
     * @return void
     */
	public function print( $content = '', $template = 'print-body.tpl.php' )
	{
		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);
		$this->registry->getObject('template')->getPage()->addTag('content',$content);

		// Build page
		$this->registry->getObject('template')->buildFromTemplates('print-header.tpl.php', $template , 'print-footer.tpl.php');
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

	private function importMeeting(){
		$content = "";
		$filename = "files/ImportMeeting.csv";

		if(!file_exists($filename)){
			$this->errorMessage = "Soubor $filename nebyl nalezen.";
			$this->print();
			return;
		}
		
		$electionperiod = $this->zob->getActualElectionperiod();
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
						$meeting['AtDate'] = $this->zob->text2Date($field[4]);
						$meeting['Year'] = $Year;
						$meeting['AtTime'] = date('H:i', strtotime($field[5]));
						$meeting['PostedUpDate'] = $this->zob->text2Date($field[6]);
						$meeting['PostedDownDate'] = $this->zob->text2Date($field[7]);

						$this->registry->getObject('db')->initQuery('meetingtype');
						$this->registry->getObject('db')->setFilter('ElectionPeriodID',$ElectionPeriodID);
						$this->registry->getObject('db')->setFilter('MeetingName',$MeetingName);
						if(!$this->registry->getObject('db')->findFirst()){
							$this->errorMessage = "Typ jednání $MeetingName pro volební období ".$electionperiod['PeriodName']." není definováno.";
							$this->print();
							return;
						}
						$meetingtype = $this->registry->getObject('db')->getResult();
						$meetingTemplate = $this->zob->getMeetingTemplate($MeetingName);
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
						$data['RecorderAtDate'] = $this->zob->text2Date($field[1]);
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
						$meetingline = $this->zob->getMeetingline($MeetingLineID);
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

}