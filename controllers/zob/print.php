<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.2.2023
 * 
 * Sestavy
 *  10000   - zápis z jednání
 *  10020   - pozvánka na jednání
 *  10030   - usnesení z jednání (všechny body)
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
			case '10030':
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
                    case '10030':
                        $this->report10030($meeting);
                        break;
                };
                break;
            case '20000':
                $this->report20000();
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
        $headerTitle['HeadMan'] = 'starosta';
        $headerTitle['SubHeadMan1'] = '';
        $headerTitle['SubHeadMan2'] = '';
        switch (mb_strtolower($meetingtype['MeetingName'])){
            case 'zastupitelstvo':
                $headerTitle['FromMeting'] = 'ze zasedání zastupitelstva obce '.$config['compCity'];
                $headerTitle['RecorderBy'] = 'zapsala: '.$meeting['RecorderBy'];
                break;
            case 'rada':
                $headerTitle['FromMeting'] = 'z jednání rady obce '.$config['compCity'];
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

        // Headmen
        $this->registry->getObject('pdf')->MeetingHeadMen($meeting, $headerTitle);

        // Verified By
        $this->registry->getObject('pdf')->MeetingVerifiedBy($meeting);


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
        $headerTitle = $this->zob->getMeetingHeader($meeting);
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

    private function report10030($meeting)
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
        $headerTitle['MeetingName'] = $meetingtype['MeetingName'];
        $headerTitle['HeadMan'] = 'starosta';
        $headerTitle['SubHeadMan1'] = '';
        $headerTitle['SubHeadMan2'] = '';
        switch (mb_strtolower($meetingtype['MeetingName'])){
            case 'zastupitelstvo':
                $headerTitle['FromMeting'] = 'zastupitelstva obce '.$config['compCity'];
                $headerTitle['RecorderBy'] = 'zapsala: '.$meeting['RecorderBy'];
                if ($config['SubHeadManTotal'] == 1){
                    $headerTitle['SubHeadMan1'] = 'místostarosta';
                }else{
                    $headerTitle['SubHeadMan1'] = '1. místostarosta';
                    $headerTitle['SubHeadMan2'] = '2. místostarosta'; 
                }
                break;
            case 'rada':
                $headerTitle['FromMeting'] = 'rady obce '.$config['compCity'];
                $headerTitle['RecorderBy'] = 'zapsal: '.$meeting['RecorderBy'];
                if ($config['SubHeadManTotal'] == 1){
                    $headerTitle['SubHeadMan1'] = 'místostarosta';
                }else{
                    $headerTitle['SubHeadMan1'] = '1. místostarosta';
                    $headerTitle['SubHeadMan2'] = '2. místostarosta'; 
                }
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
        $headerTitle['PresentMembers'] = '';
        $headerTitle['ExcusedMemberNames'] = '';
        $headerTitle['VerifiedMemberNames'] = '';
        
        $redundand[0] = $headerTitle['MeetingName'].' obce schvaluje';
        $redundand[1] = $headerTitle['MeetingName'].' schvaluje';

        $this->registry->getObject('pdf')->DocumentTitle('10030',$headerTitle);

        // Meeting Lines - Content
        $yy = $this->registry->getObject('pdf')->GetY();
        $this->registry->getObject('pdf')->SetY($yy);
        $line = 0;
        foreach($meetinglines as $meetingline){
            if ($meetingline['Vote']) {
                $line += 1;
                $lineno = $meeting['EntryNo'] . '/' . $line;
                $this->registry->getObject('pdf')->MeetingLineUsneseni($meetingline,$lineno,$redundand);
            }
           
            // Line Contents
            $meetinglinecontents = $this->zob->readMeetingLineContents ($meetingline['MeetingLineID']);
            if ($meetinglinecontents){
                foreach($meetinglinecontents as $meetinglinecontent){
                    $meetingline = array();
                    $meetingline['Title'] = '';
                    $meetingline['LineNo'] = null;
                    $meetingline['LineNo2'] = $meetinglinecontent['LineNo'];
                    $meetingline['Content'] = $meetinglinecontent['Content'];
                    $meetingline['Discussion'] = $meetinglinecontent['Discussion'];
                    $meetingline['DraftResolution'] = $meetinglinecontent['DraftResolution'];
                    $meetingline['Vote'] = $meetinglinecontent['Vote'];
                    $meetingline['VoteFor'] = $meetinglinecontent['VoteFor'];
                    $meetingline['VoteAgainst'] = $meetinglinecontent['VoteAgainst'];
                    $meetingline['VoteDelayed'] = $meetinglinecontent['VoteDelayed'];
                    if ($meetingline['Vote']) {
                        $line += 1;
                        $lineno = $meeting['EntryNo'] . '/' . $line;
                        $this->registry->getObject('pdf')->MeetingLineUsneseni($meetingline,$lineno,$redundand);
                    }
                }
            }
        }

        // Headmen
        $this->registry->getObject('pdf')->MeetingHeadMen($meeting, $headerTitle);
       

        // Show PDF document
        $this->registry->getObject('pdf')->Show();
  
		exit;
	}

	private function report20000()
	{
        global $config;

        $filename = 'document.pdf';
        $reportTitle = '';
        $headerTitle = array();

        // Create PDF
        $this->registry->getObject('pdf')->SetDocument('document',$filename, $reportTitle);
        $this->registry->getObject('pdf')->NewDocument();

        // Read Dat from From
        require_once( FRAMEWORK_PATH . 'controllers/contact/controller.php');
        $contact = new Contactcontroller( $this->registry , false);
        $doc = $contact->readFromData();

        // Záhlaví
        $headerTitle['ClientName'] = $doc['FullName'];
        $headerTitle['ClientAddress'] = $doc['Address'];
        $headerTitle['ClientPhone'] = $doc['Phone'];
        $headerTitle['ClientEmail'] = $doc['Email'];
        $headerTitle['DS'] = $doc['DataBox'];  
        
        $headerTitle['DocumentNo'] = $doc['DocumentNo'];
        $AtDate = $doc['AtDate'];
        $headerTitle['Name'] = $doc['PresenterName'];
        $headerTitle['Phone'] = $doc['PresenterPhone'];
        
        $headerTitle['City'] = $config['compCity'];
        $headerTitle['CompName'] = 'OBEC '.mb_strtoupper($config['compCity']);
        $headerTitle['CompAddress'] = $config['compAddress'] . ', PSČ ' . $config['compZip'] . ' ' . $config['compCity'];
        $headerTitle['AtDate'] = $this->registry->getObject('core')->formatDate($AtDate, 'd.m.Y');
        $this->registry->getObject('pdf')->DocumentTitle('20000',$headerTitle);

        // Lines - Program
        $subject = $doc['Subject'];
        
        //$line = explode(chr(13) . chr(10), $doc['Content']);
        $content = $doc['Content'];

        // Sign
        $sing = array();
        $sing[] = $doc['SignatureName'];
        $sing[] = $doc['SignatureFunction'];

        $this->registry->getObject('pdf')->DocumentLine('20000',$subject,$content,$sing);

        // Show PDF document
        $this->registry->getObject('pdf')->Show();
  
		exit;
	}
}
