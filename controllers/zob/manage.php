<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    6.1.2023
 */
class Zobmanage {
	
    private $registry;
    private $db;
	private $message;
	private $errorMessage;
    
    /**
	 * @param Registry $registry 
	 */
	public function __construct( Registry $registry )
	{
        $this->registry = $registry;
		$this->db = $this->registry->getObject('db');
	}

	/**
	 * Správa dat a modulu
	 * @return void
	 */
	public function manage( $action )
	{
		global $config, $caption;
        $zob = new Zobcontroller( $this->registry, false );					

		$urlBits = $this->registry->getURLBits();     
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
			case 'importMeeting':
				$this->importMeeting();
				$zob->errorMessage = $this->errorMessage;
				return;
			case 'deleteAllMeeting':
				$this->deleteAllMeeting();
				$zob->errorMessage = $this->errorMessage;
				break;
			case 'deleteMeeting':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$this->deleteMeeting($MeetingID);
				$zob->errorMessage = $this->errorMessage;
				break;
			case 'backupElectionPeriod':
				$ElectionPeriodID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$this->backupElectionPeriod($ElectionPeriodID);
				break;
			case 'backupMeeting':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$this->backupMeeting($MeetingID);
				break;
			case 'backupContact':
				$this->backupContact();
				break;
			case 'scanAllMeeting':
				$this->scanAllMeeting();
				$zob->errorMessage = $this->errorMessage;
				break;
			case 'scanMeeting':
				$MeetingID = isset($urlBits[3]) ? $urlBits[3] : 0;
				$MeetingID = $this->scanMeeting($MeetingID);
				$zob->errorMessage = $this->errorMessage;
				if($MeetingID != null){
					$zob->listMeetingLine($MeetingID);
					return;
				}
				break;
		}
		$zob->listElectionPeriod();
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

	private function backupElectionPeriod($ElectionPeriodID = 0){
		global $config;
        $zob = new Zobcontroller( $this->registry, false );					

		$pref = $config['dbPrefix'];
		$electionperiodinstance = new Electionperiodcontroller( $this->registry );
		$meetingtypeinstance = new Meetingtypecontroller( $this->registry );

		if($ElectionPeriodID == 0)
			$electionperiod = $electionperiodinstance->getActualElectionperiod();
		else
			$electionperiod = $electionperiodinstance->getElectionperiod($ElectionPeriodID);
		if(!$electionperiod){
			$this->errorMessage = "Není nastaveno výchozí volební období..";
			$this->print();
			return; 
		}
		$ElectionPeriodID = $electionperiod['ElectionPeriodID'];
		$condition = "ElectionPeriodID = $ElectionPeriodID";
		$filename = $electionperiod['PeriodName'];
		header('Content-Type: text/csv; charset=utf-8');
		header("Content-Disposition: attachment; filename=$filename.csv");
		
		// Create stream
		$output = fopen("php://output","w");		
		// Export tables
		$this->exportTable($output, $pref.'electionperiod',$condition);
		$this->exportTable($output, $pref.'meetingtype',$condition);
		$meetingtypes = $meetingtypeinstance->readMeetingtypesByElectionperiodID($ElectionPeriodID);
		foreach($meetingtypes as $meetingtype){
			$MeetingTypeID = $meetingtype['MeetingTypeID'];
			$condition = "MeetingTypeID = $MeetingTypeID";
			$this->exportTable($output, $pref.'member',$condition);
		}
		$meetings = $zob->readMeetingByElectionperiodID($ElectionPeriodID);
		foreach($meetings as $meeting){
			$this->exportTableMeeting ($output, $meeting['MeetingID'] );
		}
		// Close strem and download
		fclose($output);
		exit;
		// Backup - EXPORT dat
		/**
		 * Export record of DMS
		 *  - agenda
		 *  - agendatype
		 *  - contact
		 *  - contactgroup
		 *  - dmsentry
		 *  - inbox
		 *  - user
		 *  - permissionset
		 *  - setup
		 *  - source
		 */

	}

	private function backupContact(){
		global $config;
		$pref = $config['dbPrefix'];

		$filename = 'contacts';
		$condition = '';

		header('Content-Type: text/csv; charset=utf-8');
		header("Content-Disposition: attachment; filename=$filename.csv");
		// Create stream
		$output = fopen("php://output","w");		
		// Export tables
		$this->exportTable($output, $pref.'contact',$condition);
		// Close and download stream
		fclose($output);
		exit;
	}

	private function backupMeeting($MeetingID = 0){
        $zob = new Zobcontroller( $this->registry, false );					
		$meetingtypeinstance = new Meetingtypecontroller( $this->registry );
		$meetinginstance = new Meetingcontroller($this->registry);

		if ($MeetingID == 0){
			$filename = 'meetings';
		}else{
			$meeting = $meetinginstance->getMeeting($MeetingID);
			$meetingtype = $meetingtypeinstance->getMeetingtype($meeting['MeetingTypeID']);
			$filename = $meetingtype['MeetingName'].'-'.$meeting['EntryNo'].'-'.$meeting['Year'];
		};
		header('Content-Type: text/csv; charset=utf-8');
		header("Content-Disposition: attachment; filename=$filename.csv");
		
		// Create stream
		$output = fopen("php://output","w");		
		// Export tables
		$this->exportTableMeeting ($output, $MeetingID );
		// Close and download stream
		fclose($output);
		exit;
	}
	
	private function exportTableMeeting ($output, $MeetingID = 0 ){
		global $config;
		$pref = $config['dbPrefix'];
		$condition = $MeetingID != 0 ? "MeetingID = $MeetingID" : '';

		$this->exportTable($output, $pref.'meeting',$condition);
		$this->exportTable($output, $pref.'meetingline',$condition);
		$this->exportTable($output, $pref.'meetinglinecontent',$condition);
		$this->exportTable($output, $pref.'meetingattachment',$condition);
		$this->exportTable($output, $pref.'meetinglinepage',$condition);
		$this->exportTable($output, $pref.'meetinglinetask',$condition);
	}

	private function exportTable ($output, $table, $condition = ''){
		if($table == '')
			return;

		fputcsv($output,["$table"],';');

		// Headers
		$sql = "SELECT * FROM information_schema.COLUMNS where TABLE_NAME = '$table' order by ORDINAL_POSITION";
		$this->db->executeQuery( $sql );
		while( $row = $this->db->getRows() )
		{
			$header[] = $row['COLUMN_NAME'];
		}
		fputcsv($output,$header,';');

		// Data
		$sql = "SELECT * FROM $table ";
		if($condition != '')
			$sql .= " WHERE $condition";
		$this->db->executeQuery( $sql );
		while( $row = $this->db->getRows() )
		{
			fputcsv($output,$row,';');
		}
	}
		
	private function deleteMeeting($MeetingID){
		$zob = new Zobcontroller( $this->registry, false );					
		$meetinginstance = new Meetingcontroller($this->registry);

		$meeting = $meetinginstance->getMeeting($MeetingID);
		if ($meeting == null)
			exit;
		if($meeting['Close'] == 1)
			exit;
		$condition = "MeetingID = $MeetingID";
		$this->db->deleteRecords('meetingattachment',$condition);
		$this->db->deleteRecords('meetinglinecontent',$condition);
		$this->db->deleteRecords('meetinglinepage',$condition);
		$this->db->deleteRecords('meetinglinetask',$condition);
		$this->db->deleteRecords('meetingline',$condition);
		$this->db->deleteRecords('meeting',$condition);
		return;
	}

	private function deleteAllMeeting(){
		$electionperiodinstance = new Electionperiodcontroller( $this->registry );

		$electionperiod = $electionperiodinstance->getActualElectionperiod();
		if(!$electionperiod){
			$this->errorMessage = "Není nastaveno výchozí volební období..";
			$this->print();
			return;
		}
		$ElectionPeriodID = $electionperiod['ElectionPeriodID'];

		$this->db->initQuery('meetingtype');
		$this->db->setCondition("ElectionPeriodID = $ElectionPeriodID");
		if ($this->db->findSet()){
			$meetingtypes = $this->db->getResult();	
			foreach ($meetingtypes as $meetingtype){
				$MeetingTypeID = $meetingtype['MeetingTypeID'];
				$this->db->initQuery('meeting');
				$this->db->setCondition("MeetingTypeID = $MeetingTypeID");
				if ($this->db->findSet()){
					$meetings = $this->db->getResult();
					foreach ($meetings as $meeting){
						$MeetingID = $meeting['MeetingID'];
						$this->db->initQuery('meetingline');
						$this->db->setCondition("MeetingID = $MeetingID");
						if ($this->db->findSet()){
							$meetinglines = $this->db->getResult();
							foreach ($meetinglines as $meetingline){
								$MeetingLineID = $meetingline['MeetingLineID'];
								$condition = "MeetingLineID = $MeetingLineID";
								$this->db->deleteRecords('meetinglinecontent',$condition);
								$this->db->deleteRecords('meetinglinepage',$condition);
								$this->db->deleteRecords('meetinglinetask',$condition);
							}
						}								
						$condition = "MeetingID = $MeetingID";
						$this->db->deleteRecords('meetingattachment',$condition);
						$this->db->deleteRecords('meetingline',$condition);
					}
				}
				$condition = "MeetingTypeID = $MeetingTypeID";
				$this->db->deleteRecords('meeting',$condition);
			}
		}
	}

	private function scanMeeting($param){
		$electionperiodinstance = new Electionperiodcontroller( $this->registry );
		$meetingtypeinstance = new Meetingtypecontroller( $this->registry );
        $zob = new Zobcontroller( $this->registry, false );					
		$meetinginstance = new Meetingcontroller($this->registry);

		if(is_array($param))
			$meeting = $param;
		else
			$meeting = $meetinginstance->getMeeting($param);
		$MeetingID = $meeting['MeetingID'];
		$meetingtype = $meetingtypeinstance->getMeetingtype($meeting['MeetingTypeID']);
		$electionperiod = $electionperiodinstance->getElectionperiod($meeting['ElectionPeriodID']);

		global $config;
		$fileroot = $config['fileroot'].$config['zobroot'];
		$EntryNo = $meeting['EntryNo'];

		$parentPath = $fileroot."/_".$meetingtype['MeetingName']."/".$electionperiod['PeriodName']."/";
		$this->scanDirPath($meetingtype,$EntryNo,$parentPath.$EntryNo.'/',false, '');
		$meeting = $meetinginstance->getMeetingByEntryNo($meetingtype, $EntryNo);
		return $meeting['MeetingID'];
	}

	private function scanAllMeeting(){
		global $config;
		$electionperiodinstance = new Electionperiodcontroller( $this->registry );
		$meetingtypeinstance = new Meetingtypecontroller( $this->registry );

		$fileroot = $config['fileroot'].$config['zobroot'];
		$content = '';

		$electionperiod = $electionperiodinstance->getActualElectionperiod();
		if(!$electionperiod){
			$this->errorMessage = "Není nastaveno výchozí volební období..";
			return;
		}
		$meetingtypes = $meetingtypeinstance->readMeetingtypesByElectionperiodID($electionperiod['ElectionPeriodID']);
		foreach($meetingtypes as $meetingtype)
		{
			$parentPath = $fileroot."/_".$meetingtype['MeetingName']."/".$electionperiod['PeriodName']."/";

			if(is_dir($parentPath)){
							
				$content .= $meetingtype['MeetingName']."/".$electionperiod['PeriodName']."<br>";
				$content .= $this->scanDirPath($meetingtype,0,$parentPath,true,'');

			}					
		};

		
		$this->print($content);
		return;
	}

	private function scanDirPath($meetingtype,$EntryNo,$dirPath,$topLevel, $dirName = ''){
		$zob = new Zobcontroller( $this->registry, false );					
		$meetinginstance = new Meetingcontroller($this->registry);

		$content = '';
		if ($handle = opendir($dirPath)) { 
			while (false !== ($fileName = readdir($handle))) 
			{ 
				if ($fileName == '.' |0| $fileName == '..') { 
					continue; 
				};			
			
				$fullFileName = $dirPath.$fileName;		// celá cesta k souboru nebo složce včetně rootu

				// Nyjvyšší úroveň jsou složky zápisů, co není číslo nás nezajímá
				if($topLevel){					
					$EntryNo = (int) $fileName;
					if($EntryNo == 0)
						return $content;
					if (!is_dir($fullFileName))
						continue;
				}

				// Název souboru s relativní cestou
				$Name = ($topLevel) ? "" : ($dirName == "" ? "" : $dirName."/").$fileName;
				$HtmlName = ($topLevel) ? "" : ($dirName == "" ? "" : $dirName."/")."<b>".$fileName."</b>";

				if(is_dir($fullFileName)){
					// Další složka => další analýza
					$content .= $this->scanDirPath($meetingtype,$EntryNo,$fullFileName.'/',false,$Name);
				}else{
					// Soubor
					$meeting = $meetinginstance->getMeetingByEntryNo($meetingtype, $EntryNo);

					// Pokud zápis ještě nebyl načten, pak se proveden impoert a zápisu do tabulek ZOB
					if($fileName == ".meeting"){
						// Tento nebude součástí příloh

						if($meeting != null){
							if($meeting['Close'] == 1){
								continue;
							}
							$this->deleteMeeting($meeting['MeetingID']);
						}
						$this->importMeeting($fullFileName);
					}else{
						if($meeting == null)
							continue;
							
						// Toto je příloha k založení 
						$content .= $EntryNo." ===> ".$HtmlName."<br>";

						// Najít/vytvořit EntryNo
						$dmsEntryNo = $this->registry->getObject('file')->findItem($fullFileName);
						$dmsentry = $meetinginstance->getDmsentry($dmsEntryNo);
						$DmsEntryID = $dmsentry['ID'];
						if($dmsentry){
							$meetingattachment = $zob->getMeetingattachmentByDmsEntryID ( $meeting['MeetingID'], $DmsEntryID );
							if(!$meetingattachment){
								$data = array();
								$data['MeetinglineID'] = 0;
								$data['MeetingID'] = $meeting['MeetingID'];
								$data['Description'] = $Name;
								$data['DmsEntryID'] = $DmsEntryID;	
								$this->db->insertRecords('meetingattachment',$data);
							}
						}
					}
				}
				

			//TODO
			/*			
			$fullItemPath = $directoryNamePath.$fileName;
			$winFullItemPath = $this->Convert2SystemCodePage($fullItemPath);
			$entryNo = $this->findItem($winFullItemPath);
			if(is_dir($fullItemPath))
			{ 
				$directory_path = $fullItemPath.DIRECTORY_SEPARATOR; 
				array_push($directories, $directory_path); 
			} 
			*/
			} 
			closedir($handle); 
		} 
		return $content;
	}

	private function importMeeting( $filename = '' ){
		$electionperiodinstance = new Electionperiodcontroller( $this->registry );
        $zob = new Zobcontroller( $this->registry, false );					
		$meetinginstance = new Meetingcontroller($this->registry);
		$meetinglineinstance = new Meetinglinecontroller($this->registry);

		$electionperiod = $electionperiodinstance->getActualElectionperiod();
		if(!$electionperiod){
			$this->errorMessage = "Není nastaveno výchozí volební období..";
			$this->print();
			return;
		}
		$ElectionPeriodID = $electionperiod['ElectionPeriodID'];
		$verifier = 0;
		$MeetingTypeID = 0;
		$MeetingID = 0;
		$MeetingLineID = 0;
		$ContentID = 0;
		$lastType = '';
		$lastOC = '';

		$content = "";
		if($filename == "")
			$filename = 'files/ImportMeeting'.$electionperiod['PeriodName'].'.csv';

		if(!file_exists($filename)){
			$this->errorMessage = "Soubor $filename nebyl nalezen.";
			$this->print();
			return;
		}
		

		// Read file
		$file =  fopen( $filename, 'r' );
		while(!feof($file)) {
		
			$line = fgets($file);
			$line = trim($line);
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
						$Year = $this->registry->getObject('core')->formatDate($field[4],'Y');

						$meeting = array();
						$meeting['EntryNo'] = $EntryNo;
						$meeting['Present'] = (int) $field[3];
						$meeting['AtDate'] = $zob->text2Date($field[4]);
						$meeting['Year'] = $Year;
						$meeting['AtTime'] = $this->registry->getObject('core')->formatDate($field[5],'H:i');
						$meeting['PostedUpDate'] = $zob->text2Date($field[6]);
						$meeting['PostedDownDate'] = $zob->text2Date($field[7]);

						$this->db->initQuery('meetingtype');
						$this->db->setFilter('ElectionPeriodID',$ElectionPeriodID);
						$this->db->setFilter('MeetingName',$MeetingName);
						if(!$this->db->findFirst()){
							$this->errorMessage = "Typ jednání $MeetingName pro volební období ".$electionperiod['PeriodName']." není definováno.";
							$this->print();
							return;
						}
						$meetingtype = $this->db->getResult();
						$meetingTemplate = $meetinginstance->getMeetingTemplate($MeetingName);
						if($meetingTemplate){
							$meeting['MeetingPlace'] = $meetingTemplate['MeetingPlace'];
						}
						$MeetingTypeID = $meetingtype['MeetingTypeID'];
						$meeting['MeetingID'] = 0;
						$meeting['MeetingTypeID'] = $MeetingTypeID;
						$meeting['ElectionPeriodID'] = $ElectionPeriodID;
						$meeting['Close'] = 0;
						$meeting['ParentID'] = '00000000-0000-0000-0000-000000000000';
						$meeting['ParentID'] = $meetinginstance->getMeetingParentID($meeting);
				
						$this->db->initQuery('meeting');
						$this->db->setFilter('MeetingTypeID',$MeetingTypeID);
						$this->db->setFilter('EntryNo',$EntryNo);
						if($this->db->findFirst()){
							$this->errorMessage = "Jednání typu $MeetingName číslo $EntryNo/$Year již existuje.<BR>";
							//$this->print();
							return;					
						}
						$this->db->insertRecords('meeting',$meeting);
						$this->db->initQuery('meeting');
						$this->db->setFilter('MeetingTypeID',$MeetingTypeID);
						$this->db->setFilter('EntryNo',$EntryNo);
						$this->db->findFirst();
						$meeting = $this->db->getResult();
						$MeetingID = $meeting['MeetingID'];

						$line = $MeetingID;

						break;
					case 'V':
						$verifier += 1;
						$data = null;
						switch ($verifier){
							case 1:	
								$data['VerifierBy1'] = trim($field[1]);
							case 2:	
								$data['VerifierBy2'] = trim($field[1]);
						}
						if($data){
							$condition = "MeetingID = $MeetingID";
							$this->db->updateRecords('meeting',$data,$condition);
						}
						break;
					case 'D':
						$data = null;
						$data['RecorderAtDate'] = $zob->text2Date($field[1]);
						if($data){
							$condition = "MeetingID = $MeetingID";
							$this->db->updateRecords('meeting',$data,$condition);
						}
						break;
					case 'Z':
						$data = null;
						$data['RecorderBy'] = trim($field[1]);
						if($data){
							$condition = "MeetingID = $MeetingID";
							$this->db->updateRecords('meeting',$data,$condition);
						}
						break;
					case 'B':
						# B;8;Text
						# B;9-1;Text
						$data = null;
						$data['MeetingID'] = $MeetingID;
						$data['MeetingTypeID'] = $MeetingTypeID;
						$data['ElectionPeriodID'] = $ElectionPeriodID;
						$arr = explode('-',trim($field[1]));
						$data['LineNo'] = (int) $arr[0];
						$data['LineNo2'] = isset($arr[1]) ? (int) $arr[1] : 0;
						$data['LineType'] = isset($arr[1]) ? 'Podbod' : 'Bod';
						$text = trim($field[2]);
						$text = str_replace("–","-",$text);
						if($text <> ''){
							if($text[0] == '-')
								$text = substr($text,1);
						};
						if (strlen($text) > 250){
							$data['Title'] = $this->db->sanitizeData(trim(substr($text,0,249)));
							$data['Title2'] = $this->db->sanitizeData(trim(substr($text,250,249)));
						}else
							$data['Title'] = $this->db->sanitizeData(trim($text));
						
						$this->db->insertRecords('meetingline',$data);
						break;
					case 'T':
						$data = null;
						$data['MeetingID'] = $MeetingID;
						$data['MeetingTypeID'] = $MeetingTypeID;
						$data['ElectionPeriodID'] = $ElectionPeriodID;
						$data['LineNo'] = (int) trim($field[1]);
						$data['LineNo2'] = 0;
						$data['LineType'] = 'Doplňující bod';
						$text = trim($field[2]);
						$text = str_replace("–","-",$text);
						if($text <> ''){
							if($text[0] == '-')
								$text = substr($text,1);
						};
						$data['Title'] = $this->db->sanitizeData($text);
						$this->db->insertRecords('meetingline',$data);
						break;
					case 'P':
						# P;9-1;Text
						$data = null;
						$data['MeetingID'] = $MeetingID;
						$data['MeetingTypeID'] = $MeetingTypeID;
						$data['ElectionPeriodID'] = $ElectionPeriodID;
						$arr = explode('-',trim($field[1]));
						$data['LineNo'] = (int) $arr[0];
						$data['LineNo2'] = isset($arr[1]) ? (int) $arr[1] : 0;
						$data['LineType'] = isset($arr[1]) ? 'Podbod' : 'Bod';
						$text = trim($field[2]);
						$text = str_replace("–","-",$text);
						if($text <> ''){
							if($text[0] == '-')
								$text = substr($text,1);
						};
						$data['Title'] = $this->db->sanitizeData($text);
						$this->db->insertRecords('meetingline',$data);
						break;
					case 'O':
						$arr = explode('-',trim($field[1]));
						$LineNo = (int) $arr[0];
						$LineNo2 = isset($arr[1]) ? (int) $arr[1] : null;
						$this->db->initQuery('meetingline');
						$this->db->setFilter('MeetingID',$MeetingID);
						$this->db->setFilter('LineNo',$LineNo);
						if($LineNo2)
							$this->db->setFilter('LineNo2',$LineNo2);
						if(!$this->db->findFirst()){
							$this->errorMessage = "Bod programu $LineNo.$LineNo2 nenalezen.";
							$this->print();
							return;
						}
						$meetingline = $this->db->getResult();
						$MeetingLineID = $meetingline['MeetingLineID'];
						
						$text = isset($field[2]) ? trim($field[2]) : '';
						$text = str_replace("–","-",$text);
						if($text == ""){
							$this->errorMessage = "Obsah bodu $LineNo.$LineNo2 není vyplněn, nebo struktura dat obsahuje chybu.";
							$this->print();
							return;
						};
						
						if($text[0] == '-')
							$text = substr($text,1);
						$data = null;
						$data['Content'] = $this->db->sanitizeData($text);
						$condition = "MeetingLineID = $MeetingLineID";
						$this->db->updateRecords('meetingline',$data,$condition);
						$lastOC = $type;
						break;
					case 'C':
						$text = isset($field[1]) ? trim($field[1]) : '';
						$text = str_replace("–","-",$text);
						if($text <> ''){
							if($text[0] == '-')
								$text = substr($text,1);
						};
						$data = null;
						$data['MeetingLineID'] = $MeetingLineID;
						$data['MeetingID'] = $MeetingID;
						$data['MeetingTypeID'] = $MeetingTypeID;
						$ContentLineNo = $zob->getNextMeetinglineContentLineNo( $MeetingLineID );
						$data['LineNo'] = $ContentLineNo;
						$data['Content'] = $this->db->sanitizeData($text);
						$this->db->insertRecords('meetinglinecontent',$data);

						$this->db->initQuery('meetinglinecontent');
						$this->db->setFilter('MeetingLineID',$MeetingLineID);
						$this->db->setFilter('LineNo',$ContentLineNo);
						$this->db->findFirst();
						$meetinglinecontent = $this->db->getResult();
						$ContentID = $meetinglinecontent['ContentID'];					
						
						$lastOC = $type;
						break;						
					case 'U':
						$text = trim($field[1]);
						$text = str_replace("–","-",$text);
						if($text[0] == '-')
							$text = substr($text,1);
						$data = null;
						$data['DraftResolution'] = $this->db->sanitizeData($text);
						if($lastOC == 'O'){
							$condition = "MeetingLineID = $MeetingLineID";
							$table = 'meetingline';
						}else{
							$condition = "ContentID = $ContentID";
							$table = 'meetinglinecontent';
						}
						$this->db->updateRecords($table,$data,$condition);
						break;
					case 'I':
						$text = trim($field[1]);
						$text = str_replace("–","-",$text);
						if($text[0] == '-')
							$text = substr($text,1);
						$data = null;
						$data['Discussion'] = $this->db->sanitizeData($text);
						if($lastOC == 'O'){
							$condition = "MeetingLineID = $MeetingLineID";
							$table = 'meetingline';
						}else{
							$condition = "ContentID = $ContentID";
							$table = 'meetinglinecontent';
						}
						$this->db->updateRecords($table,$data,$condition);
						break;
					case 'H':
						$line = preg_replace('/\s/',"",$field[1]);
						$arr = explode(':',$line);
						$data = null;		
						$data['Vote'] = 1;
						$data['VoteFor'] = (int) $arr[1];
						$data['VoteAgainst'] = (int) $arr[2];
						$data['VoteDelayed'] = (int) $arr[3];
						if($lastOC == 'O'){
							$condition = "MeetingLineID = $MeetingLineID";
							$table = 'meetingline';
						}else{
							$condition = "ContentID = $ContentID";
							$table = 'meetinglinecontent';
						}
						$this->db->updateRecords($table,$data,$condition);
						break;
					default:
						$data = null;
						switch ($lastType) {
							case 'O':
							case 'C':
								$field = 'Content';
								break;
							case 'I':
								$field = 'Discussion';
								break;
							case 'U':
								$field = 'DraftResolution';
								break;
							default:
								$this->errorMessage = "Navazující text '$line' nelze přiřadit předchozímu řádku typu '$lastType'.";
								$this->print();
								return;
						}
						$line = trim($line);
						$line = str_replace("–","-",$line);
						if($line != ""){
							if($lastOC == 'O'){
								$meetingline = $meetinglineinstance->getMeetingline($MeetingLineID);
								$data[$field] = $meetingline[$field]."\n".$this->db->sanitizeData($line);
								$condition = "MeetingLineID = $MeetingLineID";
								$table = 'meetingline';
							}else{
								$meetinglinecontent = $zob->getMeetinglinecontent($ContentID);
								$data[$field] = $meetinglinecontent[$field]."\n".$this->db->sanitizeData($line);
								$condition = "ContentID = $ContentID";
								$table = 'meetinglinecontent';
							}
							$this->db->updateRecords($table,$data,$condition);
						};
						$type = $lastType;
						break;
				}
				$lastType = $type;
			}
			$content .= $line."<br>";
			
		  }		
		fclose( $file );
		return $MeetingID;
	}

}