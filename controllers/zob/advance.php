<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    6.1.2023
 */
class Zobadvance {
	
    private $registry;
    private $zob;
	private $message;
	private $errorMessage;
	private $MeetingID;
	private $anchor;
    
    /**
	 * @param Registry $registry 
	 */
	public function __construct( Registry $registry )
	{
        $this->registry = $registry;
        $this->zob = new Zobcontroller( $this->registry, false );					
    }

	/**
	 * Rozšířený modul
	 * @return void
	 */
	public function main( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
            case 'meetingline':
                $action = isset($urlBits[3]) ? $urlBits[3] : '';
                $action = isset($_POST["action"]) ? $_POST["action"] : $action;						
                $this->meetingline($action);
                break;
            case 'meetinglinecontent':
                $action = isset($urlBits[3]) ? $urlBits[3] : '';
                $action = isset($_POST["action"]) ? $_POST["action"] : $action;						
                $this->meetinglinecontent($action);
                break;
            case 'meetingattachment':
                $action = isset($urlBits[3]) ? $urlBits[3] : '';
                $action = isset($_POST["action"]) ? $_POST["action"] : $action;						
                $this->meetingattachment($action);
                break;
        default:
                $MeetingID = isset($urlBits[2]) ? (int) $urlBits[2] : null;
                if($MeetingID){
                    $this->MeetingID = $MeetingID;
                }
        }
        $this->build();            
	}

    /**
     * Sestavení stránky pro TISK
     * @return void
     */
	public function build( $template = 'zob-adv-meetingline-list.tpl.php' )
	{
        $this->zob->setDatasetMeetingLine($this->MeetingID);

		$this->registry->getObject('template')->addTemplateBit('editdMeetingLine', 'zob-adv-meetingline-edit.tpl.php');

        // Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);
		$this->registry->getObject('template')->getPage()->addTag('anchor',$this->anchor);

		// Build page
		$this->registry->getObject('template')->buildFromTemplates('print-header.tpl.php', $template , 'print-footer.tpl.php');
	}

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingline( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
			case 'moveup':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : null;
				$MeetingLineID = isset($urlBits[5]) ? $urlBits[5] : null;
				if($MeetingLineID){
					$this->zob->moveMeetingline($MeetingLineID, -1 );
				}
				$MeetingLineID = 0;
				break;
			case 'movedown':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : null;
				$MeetingLineID = isset($urlBits[5]) ? $urlBits[5] : null;
				if($MeetingLineID){
					$this->zob->moveMeetingline($MeetingLineID, 1 );
				}
				$MeetingLineID = 0;
				break;
			case 'delete':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : null;
				$MeetingLineID = isset($urlBits[5]) ? $urlBits[5] : null;
				$this->zob->deleteMeetingline($MeetingLineID);
				break;
			case 'add':
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : $MeetingID;
				$this->zob->addMeetingline($MeetingID);
				break;
			default:
				$this->zob->pageNotFound();
				return;
		}		
        $this->MeetingID = $MeetingID;        
	}

	/**
	 * Modifikace tabulky meetinglinecontent
	 * @return void
	 */
	private function meetinglinecontent( $action )
	{
		$urlBits = $this->registry->getURLBits();     
		$MeetingID = 0;
		$MeetingLineID = 0;
		switch ($action) {
			case 'add':
				$MeetingLineID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$meetingline = $this->zob->getMeetingline($MeetingLineID);
				$MeetingID = $meetingline['MeetingID'];
				$meeting = $this->zob->getMeeting($MeetingID);
				if ($meeting['Close'] == 1){
					$this->errorMessage = 'Nelze editovat uzavřený zápis.';
				}else{
					$data['MeetingLineID'] = $MeetingLineID;
					$data['MeetingID'] = $MeetingID;
					$data['MeetingTypeID'] = $meetingline['MeetingTypeID'];
					$data['LineNo'] = $this->zob->getNextMeetinglineContentLineNo($MeetingLineID);
					$this->registry->getObject('db')->insertRecords('meetinglinecontent',$data);
					$this->anchor = "anchor".$MeetingLineID;
				}
				break;
		}
		$this->MeetingID = $MeetingID;
	}

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingattachment( $action )
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();     
		$MeetingID = 0;
		$MeetingLineID = 0;
		$AttachmentID = 0;

		switch ($action) {
			case 'assign':
				$AttachmentID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$MeetingLineID = isset($urlBits['5']) ? $urlBits['5'] : 0;
				$meetingline = $this->zob->getMeetingline($MeetingLineID);
				if($meetingline){
					$MeetingID = $meetingline['MeetingID'];
				}else{
					$meetingattachment = $this->zob->getMeetingattachment($AttachmentID);
					if($meetingattachment)
					$meetingline = $this->zob->getMeetingline($meetingattachment['MeetingLineID']);
						$MeetingID = $meetingline['MeetingID'];
				}

				$this->zob->assignMeetingattachment( $AttachmentID, $MeetingLineID );
				$MeetingLineID = 0;
				break;
		}
        $this->MeetingID = $MeetingID;        
	}
   
}