<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.2.2023
 * 
 * Sestavy
 *  10000   - zápis z jednání
 *  10020   - pozvánka na jednání
 * 
 *  20000   - Vyjádření k projektové dokumentaci
 */
class Zobprint {
	
    private $registry;
    private $zob;
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
	public function main( $action )
	{
		$urlBits = $this->registry->getURLBits();     

		switch ($action) {
			case '10000':
			case '10020':
                $MeetingID = $urlBits[3];
                $meeting = $this->zob->getMeeting($MeetingID);
                if(!$meeting){
                    $this->errorMessage = 'ERROR: Nezadáno číslo jednání nebo jednání $MeetingID neexistuje.';
                    return;
                };
                switch ($action) {
                    case '10000':
                        $this->report10000($meeting);
                        break;
                    case '10020':
                        $this->report10020($meeting);
                        break;
                };
                break;
            case '20000':
                $MeetingID = $urlBits[3];
                $this->report20000($MeetingID);
                break;
        }
		exit;
	}

	private function report10000($meeting)
	{
        global $config;

        $meetingtype = $this->zob->getMeetingtype($meeting);        
        $meetinglines = $this->zob->readMeetingLines($meeting);
        $filename = 'document.pdf';
        $reportTitle = '';

        // Create PDF
        $this->registry->getObject('pdf')->SetDocument('document',$filename, $reportTitle);
        $this->registry->getObject('pdf')->NewDocument();
        
        // Záhlaví
        switch (mb_strtolower($meetingtype['MeetingName'])){
            case 'zastupitelstvo':
                $headerTitle['FromMeting'] = 'ze zasedání zastupitelstva obce '.$config['compCity'];
                $headerTitle['HeadMan'] = 'starosta';
                $headerTitle['RecorderBy'] = 'zapsala: '.$meeting['RecorderBy'];
                break;
            case 'rada':
                $headerTitle['FromMeting'] = 'z jednání rady obce '.$config['compCity'];
                $headerTitle['HeadMan'] = 'starosta';
                $headerTitle['RecorderBy'] = 'zapsal: '.$meeting['RecorderBy'];
                break;
            case 'stavevni komise':
                $headerTitle['FromMeting'] = 'z jednání stavební komise obce '.$config['compCity'];
                $headerTitle['HeadMan'] = 'předseda komise';
                $headerTitle['RecorderBy'] = 'zapsal: '.$meeting['RecorderBy'];
                break;
            default:
                $headerTitle['FromMeting'] = mb_strtolower($meetingtype['MeetingName']).' obce '.$config['compCity'];
                $headerTitle['HeadMan'] = 'předseda';
                $headerTitle['RecorderBy'] = 'zapsal(a): '.$meeting['RecorderBy'];
                break;
        }
        $headerTitle['RecorderAt'] = $config['compCity'].', '.$this->registry->getObject('core')->formatDate($meeting['RecorderAtDate']);
        $headerTitle['City'] = 'OBEC '.mb_strtoupper($config['compCity']);
        $headerTitle['AtDate'] = 'dne '.$this->registry->getObject('core')->formatDate($meeting['AtDate']);
        $headerTitle['MeetingNo'] = 'číslo '. $meeting['EntryNo'];
        $headerTitle['PresentMembers'] = 'Přítomno: '.$meeting['Present'];
        if($this->zob->getMeetingExcused($meeting) == '')
            $headerTitle['ExcusedMemberNames'] = '';
        else 
            $headerTitle['ExcusedMemberNames'] = 'Omluveni: '.$this->zob->getMeetingExcused($meeting);
        if($this->zob->getMeetingVerifierBy($meeting) == '')
            $headerTitle['VerifiedMemberNames'] = '';
        else
            $headerTitle['VerifiedMemberNames'] = 'Ověrovatelé zápisu: '.$this->zob->getMeetingVerifierBy($meeting);
        $this->registry->getObject('pdf')->DocumentTitle('10000',$headerTitle);

        // Meeting Lines - Program
        foreach($meetinglines as $meetingline){
            if($meetingline['LineType'] != 'Doplňující bod'){
                $lineno = $meetingline['LineNo'].'.';
                if ($meetingline['LineNo2'] > 0)
                    $lineno .= $meetingline['LineNo2'].'.';
                $text = $meetingline['Title'];
                $this->registry->getObject('pdf')->LineProgramPoint($lineno,$text);    
            }
        }

        // Meeting Lines - Content
        $yy = $this->registry->getObject('pdf')->GetY();
        $this->registry->getObject('pdf')->SetY($yy + 10);

        foreach($meetinglines as $meetingline){
            $this->registry->getObject('pdf')->MeetingLineZapis($meetingline);
            
            // Line Contents
            $meetinglinecontents = $this->zob->readMeetingLineContents ($meetingline['MeetingLineID']);
            if ($meetinglinecontents){
                foreach($meetinglinecontents as $meetinglinecontent){
                    $meetingline = array();
                    $meetingline['LineNo'] = null;
                    $meetingline['LineNo2'] = $meetinglinecontent['LineNo'];
                    $meetingline['Content'] = $meetinglinecontent['Content'];
                    $meetingline['Discussion'] = $meetinglinecontent['Discussion'];
                    $meetingline['DraftResolution'] = $meetinglinecontent['DraftResolution'];
                    $meetingline['Vote'] = $meetinglinecontent['Vote'];
                    $meetingline['VoteFor'] = $meetinglinecontent['VoteFor'];
                    $meetingline['VoteAgainst'] = $meetinglinecontent['VoteAgainst'];
                    $meetingline['VoteDelayed'] = $meetinglinecontent['VoteDelayed'];
                    $this->registry->getObject('pdf')->MeetingLineZapis($meetingline);                
                }
            }
        }

        // Verified By
        $this->registry->getObject('pdf')->MeetingVerifiedBy($meeting, $headerTitle['HeadMan']);


        // Recorder By and At day
        $this->registry->getObject('pdf')->MeetingRecorederBy($headerTitle['RecorderBy'], $headerTitle['RecorderAt']);

        // Show PDF document
        $this->registry->getObject('pdf')->Show();
  
		exit;
	}

	private function report10020($meeting)
	{
        global $config;

        $meetingtype = $this->zob->getMeetingtype($meeting);        
        $meetinglines = $this->zob->readMeetingLines($meeting);
        $filename = 'document.pdf';
        $reportTitle = '';

        // Create PDF
        $this->registry->getObject('pdf')->SetDocument('document',$filename, $reportTitle);
        $this->registry->getObject('pdf')->NewDocument();
        
        // Záhlaví
        $headerTitle['City'] = 'OBEC '.mb_strtoupper($config['compCity']);
        $met = mb_strtolower($meetingtype['MeetingName']);
        switch ($met){
            case 'zastupitelstvo':
                $headerTitle['FromMeting'] = 'STAROSTA OBCE '.mb_strtoupper($config['compCity']);
                $headerTitle['FromMeting2'] = 'SVOLÁVÁ';
                $headerTitle['MetingTitle'] = 'VEŘEJNÉ ZASEDÁNÍ';
                $headerTitle['MetingTitle2'] = 'ZASTUPITELSTVA OBCE';
                $headerTitle['HeadMan'] = 'starosta obce';
                break;
            case 'rada':
                $headerTitle['FromMeting'] = 'STAROSTA OBCE '.mb_strtoupper($config['compCity']);
                $headerTitle['FromMeting2'] = 'SVOLÁVÁ';
                $headerTitle['MetingTitle'] = 'JEDNÁNÍ RADY';
                $headerTitle['MetingTitle2'] = '';
                $headerTitle['HeadMan'] = 'starosta obce';
                break;
            case 'stavební komise':
                $headerTitle['FromMeting'] = 'předseda stavební komise obce '.$config['compCity'];
                $headerTitle['FromMeting2'] = 'SVOLÁVÁ';
                $headerTitle['MetingTitle'] = 'JEDNÁNÍ STAVEBNÍ KOMISE';
                $headerTitle['MetingTitle2'] = '';
                $headerTitle['HeadMan'] = 'předseda komise';
                break;
            default:
                $headerTitle['FromMeting'] = 'pozvánka na jednání:';
                $headerTitle['FromMeting2'] = mb_strtolower($meetingtype['MeetingName']).' obce '.$config['compCity'];
                $headerTitle['FromMeting2'] = '';
                $headerTitle['MetingTitle'] = '';
                $headerTitle['HeadMan'] = 'předseda';
                break;
        }
        $headerTitle['AtDate'] = 'Na den '.$this->registry->getObject('core')->formatDate($meeting['AtDate']);
        $headerTitle['AtTime'] = 'ZAČÁTEK: '.$this->registry->getObject('core')->formatDate($meeting['AtTime'],'H:i').' HODIN';
        $headerTitle['MeetingPlace'] = 'MÍSTO KONÁNÍ: '.$meeting['MeetingPlace'];
        $atdate = $meeting['PostedUpDate'] != null ? $this->registry->getObject('core')->formatDate($meeting['PostedUpDate']) : '........................';
        $headerTitle['PostedUp'] = 'Vyvěšeno: '.$atdate;
        $atdate = $meeting['PostedDownDate'] != null ? $this->registry->getObject('core')->formatDate($meeting['PostedDownDate']) : '........................';
        $headerTitle['PostedDown'] = 'Sňato: '.$atdate;

        $this->registry->getObject('pdf')->DocumentTitle('10020',$headerTitle);

        // Meeting Lines - Program
        $i = 0;
        $lastlineno = 0;
        if ($meetinglines != null){
            foreach($meetinglines as $meetingline){
                if($meetingline['LineType'] != 'Doplňující bod'){
                    if ($lastlineno <> $meetingline['LineNo'])
                        $i++;
                    $lineno = $i.'.';
                    if ($meetingline['LineNo2'] > 0)
                        $lineno .= $meetingline['LineNo2'].'.';
                    $text = $meetingline['Title'];
                    $this->registry->getObject('pdf')->LineProgramPoint($lineno,$text);    
                    $lastlineno = $meetingline['LineNo'];
                }
            }
        }

        // Posted Date
        $this->registry->getObject('pdf')->MeetingPostedDate($headerTitle['PostedUp'], $headerTitle['PostedDown'], $headerTitle['HeadMan']);

        // Show PDF document
        $this->registry->getObject('pdf')->Show();
  
		exit;
	}

	private function report20000($meeting)
	{
        global $config;

        $filename = 'document.pdf';
        $reportTitle = '';
        $headerTitle = array();

        // Create PDF
        $this->registry->getObject('pdf')->SetDocument('document',$filename, $reportTitle);
        $this->registry->getObject('pdf')->NewDocument();
        
        // Záhlaví
        $headerTitle['ClientName'] = 'Tomáš Chrástek';
        $headerTitle['ClientAddress1'] = "Javorovec 4";
        $headerTitle['ClientAddress2'] = "68712 Mistřice";
        $headerTitle['ClientAddress3'] = "";
        $headerTitle['ClientPhone'] = '723114521';
        $headerTitle['ClientEmail'] = 'chastek.tom@centrum.cz';
        $headerTitle['DS'] = 'zft8s4';  // datová schránka
        
        $headerTitle['DocumentNo'] = 'VYPD-2023-014';
        $AtDate = '18.5.2023';
        $headerTitle['Name'] = 'Petr Blažek';
        $headerTitle['Phone'] = '603772658';
        
        $headerTitle['City'] = $config['compCity'];
        $headerTitle['CompName'] = 'OBEC '.mb_strtoupper($config['compCity']);
        $headerTitle['CompAddress'] = $config['compAddress'] . ', PSČ ' . $config['compZip'] . ' ' . $config['compCity'];
        $headerTitle['AtDate'] = $this->registry->getObject('core')->formatDate($AtDate);
        $this->registry->getObject('pdf')->DocumentTitle('20000',$headerTitle);

        // Lines - Program
        $subject = 'Vyjádření k PD...';
        $line = array();
        $line[] = ['','Obci Mistřice byla předložena žádost o vyjádření k PD „Rodinný dům Chrástkovi“, p.č. 49, 24/1, 24/2 v k.ú. Javorovec, Investorem stavby je Tomáš Chrástek, Javorovec 334, 68712 Mistřice. Projektovou dokumentaci pro stavbu zpracoval projektant Ing. Pavel Gál, Trávník 2088, Staré Město'];
        $line[] = ['',''];
        $line[] = ['','Obec Mistřice ,,SOUHLASÍ“ se stavbou RD na pozemku p.č. 49, 24/1, 24/2 v k.ú. Javorovec dle PD.'];
        $line[] = ['',''];
        $line[] = ['','Podmínkou souhlasného stanoviska stavby RD je:'];
        $line[] = ['-','doložení zajištěných dvou parkovacích míst pro osobní automobily do 3,5t na pozemku p.č. 49, 24/1, 24/2 v k.ú. Javorovec'];
        $line[] = ['-','po vybudování kanalizace v dané lokalitě, bude povinnost stavebníka do jednoho roku provést napojení splaškových vod do této kanalizace'];
        $line[] = ['-',''];
        $line[] = ['','Projektová dokumentace novostavby RD investora Tomáše Chrástka není v rozporu s Územním plánem obce Mistřice'];
        $line[] = ['',''];
        $line[] = ['','Na základě provedeného posouzení a splnění výše uvedených podmínek, obec Mistřice'];
        $line[] = ['CB',' „SOUHLASÍ“'];
        $line[] = ['','s vydáním Územního a Stavebního povolení.'];
        $line[] = ['',''];


        // Sign
        $sing = array();
        $sing[] = 'Petr Blažek';
        $sing[] = 'místostarosta';

        $this->registry->getObject('pdf')->DocumentLine('20000',$subject,$line,$sing);

        // Show PDF document
        $this->registry->getObject('pdf')->Show();
  
		exit;
	}
}
