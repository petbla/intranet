<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.2.2023
 */
class Zobexport {
	
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
			case 'zapis':
                $MeetingID = $urlBits[3];
                $meeting = $this->zob->getMeeting($MeetingID);
                if(!$meeting){
                    $this->errorMessage = 'ERROR: Nezadáno číslo jednání nebo jednání $MeetingID neexistuje.';
                    return;
                };
				$this->exportZapis($meeting);
                break;
		}
		exit;
	}

	private function exportZapis($meeting)
	{
		
        $filename = 'document.pdf';
        $reportTitle = '';
     
        $this->registry->getObject('pdf')->SetDocument('report',$filename, $reportTitle);

        $this->registry->getObject('pdf')->NewOrder();
  
        // Orders Lines
//        $this->registry->getObject('pdf')->ItemHeader();

//        $this->registry->getObject('pdf')->ItemLine($aLine);

        $this->registry->getObject('pdf')->Show();
  
		exit;

	}

}
