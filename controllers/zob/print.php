<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.2.2023
 * 
 * Sestavy
 *  10000   - zápis
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
                $MeetingID = $urlBits[3];
                $meeting = $this->zob->getMeeting($MeetingID);
                if(!$meeting){
                    $this->errorMessage = 'ERROR: Nezadáno číslo jednání nebo jednání $MeetingID neexistuje.';
                    return;
                };
				$this->report10000($meeting);
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
        switch (strtolower($meetingtype['MeetingName'])){
            case 'zastupitelstvo':
                $headerTitle['FromMeting'] = 'ze zasedání zastupitelstva obce '.$config['compCity'];
                break;
            case 'rada':
                $headerTitle['FromMeting'] = 'z jednání rady obce '.$config['compCity'];
                break;
            case 'stavevni komise':
                $headerTitle['FromMeting'] = 'z jednání stavební komise obce '.$config['compCity'];
                break;
            default:
                $headerTitle['FromMeting'] = strtolower($meetingtype['MeetingName']).' obce '.$config['compCity'];
                break;
        }
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
        $this->registry->getObject('pdf')->DocumentTitle($headerTitle);

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
        }

//        $this->registry->getObject('pdf')->ItemLine($aLine);

        $this->registry->getObject('pdf')->Show();
  
		exit;

	}

}
