<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.2.2023
 * 
 * Sestavy
 *  10000   - zápis z jednání
 *  10020   - pozvánka na jednání
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
        $headerTitle['RecorderAt'] = $config['compCity'].', '.date('d.m.Y',strtotime($meeting['RecorderAtDate']));
        $headerTitle['City'] = 'OBEC '.mb_strtoupper($config['compCity']);
        $headerTitle['AtDate'] = 'dne '.date('d.m.Y',strtotime($meeting['AtDate']));
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
        $headerTitle['AtDate'] = 'Na den '.date('d.m.Y',strtotime($meeting['AtDate']));
        $headerTitle['AtTime'] = 'ZAČÁTEK: '.date('H:i',strtotime($meeting['AtTime'])).' HODIN';
        $headerTitle['MeetingPlace'] = 'MÍSTO KONÁNÍ: '.$meeting['MeetingPlace'];
        $atdate = $meeting['PostedUpDate'] != null ? date('d.m.Y',strtotime($meeting['PostedUpDate'])) : '........................';
        $headerTitle['PostedUp'] = 'Vyvěšeno: '.$atdate;
        $atdate = $meeting['PostedDownDate'] != null ? date('d.m.Y',strtotime($meeting['PostedDownDate'])) : '........................';
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

}
