<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    19.10.2024
 */
class Zobcontroller{
	
	protected $registry;
	private $document;
	public $message;
	public $errorMessage;
	private $perSet;
	private $prefDb;
	private $db;
	
	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		global $config, $caption;
		$this->registry = $registry;
		$this->perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        $this->prefDb = $config['dbPrefix'];
		$this->db = $this->registry->getObject('db');

		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
				$this->pageNotFound();
			}
			else
			{
				$this->index($urlBits[1]);				 		
			}
		}
	}
	
	public function index($part)
	{
		// 0/1/2/3..
		// zob/<$part>/<$action>/<parametry>

		$urlBits = $this->registry->getURLBits();     
		$action = isset($urlBits[2]) ? $urlBits[2] : (isset($_POST["action"]) ? $_POST["action"] : 'list');

		switch ($part) {
			case 'electionperiod':
				$electionperiodinstance = new Electionperiodcontroller( $this->registry );					
				$electionperiodinstance->index($action);
				break;
			case 'member':
				$memberinstance = new Membercontroller( $this->registry );					
				$memberinstance->index($action);
				break;
			case 'meetingtype':
				$meetingtypeinstance = new Meetingtypecontroller( $this->registry );					
				$meetingtypeinstance->index($action);
				break;
			case 'meeting':
				$meetinginstance = new Meetingcontroller( $this->registry );
				$meetinginstance->index($action);
				break;
			case 'meetingline':
				$meetinglineinstance = new Meetinglinecontroller( $this->registry );
				$meetinglineinstance->index($action);
				break;
			case 'meetingattachment':
				$this->meetingattachment($action);
				break;
			case 'manage':
				require_once( FRAMEWORK_PATH . 'controllers/zob/manage.php');
				$manage = new Zobmanage( $this->registry );					
				$manage->manage($action);
				break;
			case 'addFiles':						
				require_once( FRAMEWORK_PATH . 'controllers/document/controller.php');
				$this->document = new Documentcontroller( $this->registry , false);					

				$uploadDocument = $this->document->addFiles( false );
				$MeetingID = isset($_POST["MeetingID"]) ? $_POST["MeetingID"] : 0;
				$this->addFiles($uploadDocument, $MeetingID);
				break;
			case 'adv':
				require_once( FRAMEWORK_PATH . 'controllers/zob/advance.php');
				$adv = new Zobadvance( $this->registry );					
				$adv->	main($action);
				break;
			case 'print':
				require_once( FRAMEWORK_PATH . 'controllers/zob/print.php');
				$print = new Zobprint( $this->registry );					
				$print->main($action);
				break;
			default:
				$this->pageNotFound();
				break;
		}
	}

    /**
	 * -------------------------------------------------------------------------------------------------------------
	 * Sestavení stránky
	 * -------------------------------------------------------------------------------------------------------------
	 */
    /**
     * Sestavení stránky
     * @return void
     */
	public function build( $template = 'page.tpl.php' )
	{
		// Category Menu
		$this->createCategoryMenu();

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

		// Build page
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-zob.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template , 'footer.tpl.php');
	}
    /**
     * Zobrazení chybové stránky, pokud agenda nebyla nalezem 
     * @return void
     */
	public function pageNotFound()
	{
		// Logování
		$this->error("Pokus o zobrazení neznámé stránky");
	}
    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param string $message = text zobrazen jako chyba
     * @return void
     */
	public function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'agenda','');		
		$this->errorMessage = $message;
		$this->build();
	}
	public function main($action)
	{
		require_once( FRAMEWORK_PATH . 'controllers/zob/print.php');
		$print = new Zobprint( $this->registry );					
		$print->main($action);
	}
    /**
	 * Generování menu
	 * @return void
	 */
	private function createCategoryMenu(): void
    {
		global $caption;
		$urlBits = $this->registry->getURLBits();
		$electionperiodinstance = new Electionperiodcontroller($this->registry);
		$meetinginstance = new Meetingcontroller($this->registry);

		$post = $_POST;
		switch ($urlBits[1]) {
			case 'meeting':
				$typeID = 'meeting/list/';
				$MeetingTypeID = isset( $urlBits[3]) ? $urlBits[3] : (isset($_POST['MeetingTypeID']) ? $_POST['MeetingTypeID'] : '');
				$typeID .= $MeetingTypeID;
				break;			
			case 'meetingline':
				$typeID = 'meeting/list/';
				$MeetingID = isset( $urlBits[3]) ? $urlBits[3] : (isset($_POST['MeetingID']) ? $_POST['MeetingID'] : '');
				$meeting = $meetinginstance->getMeeting($MeetingID);
				$typeID .= $meeting['MeetingTypeID'];
				break;			
			default:
				$typeID = isset( $urlBits[1]) ? $urlBits[1] : '';
				$typeID .= isset( $urlBits[2]) ? '/'.$urlBits[2] : '';
				$typeID .= isset( $urlBits[3]) ? '/'.$urlBits[3] : '';
		}

		$electionperiod = $electionperiodinstance->getActualElectionperiod();
		$PeriodName = is_array($electionperiod) ? $electionperiod['PeriodName'] : "";
		
		$rec['idCat'] = 'electionperiod';
		$rec['titleCat'] = $caption['electionperiod'].' - '.$PeriodName;
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;
	
		// Výběr typů jednání pro aktuální volební období
		if ($electionperiod){
			$this->db->initQuery('meetingtype');
			$this->db->setFilter('ElectionPeriodID',$electionperiod['ElectionPeriodID']);	
			if ($this->db->findSet()){
				$result = $this->db->getResult();
				foreach ($result as $mt) {
					$rec['idCat'] = 'meeting/list/'.$mt['MeetingTypeID'];
					$rec['titleCat'] = $mt['MeetingName'] ;
					$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
					$table[] = $rec;
				} 
			} 
		}
		$cache = $this->db->cacheData( $table );
		$this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'DATA', $cache ) );
    }
	
    /**
	 * -------------------------------------------------------------------------------------------------------------
	 *   Podmenu 
	 * -------------------------------------------------------------------------------------------------------------
	 */
	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingattachment( $action )
	{
		$meetinglineinstance = new Meetinglinecontroller($this->registry);
		$urlBits = $this->registry->getURLBits();     
		$MeetingID = 0;
		$MeetingLineID = 0;
		$AttachmentID = 0;

		switch ($action) {
			case 'delete':
				$AttachmentID = isset($urlBits['3']) ? $urlBits['3'] : 0;
				$meetingattachment = $this->getMeetingattachment($AttachmentID);
				$MeetingID = $meetingattachment['MeetingID'];
				$MeetingLineID = $meetingattachment['MeetingLineID'];
				$condition = 'AttachmentID = '.$AttachmentID;
				$this->db->deleteRecords('meetingattachment', $condition, 1);
				break;
			case 'assign':
				$AttachmentID = isset($urlBits['3']) ? $urlBits['3'] : 0;
				$MeetingLineID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$meetingline = $meetinglineinstance->getMeetingline($MeetingLineID);
				if($meetingline){
					$MeetingID = $meetingline['MeetingID'];
				}else{
					$meetingattachment = $this->getMeetingattachment($AttachmentID);
					if($meetingattachment)
					$meetingline = $meetinglineinstance->getMeetingline($meetingattachment['MeetingLineID']);
						$MeetingID = $meetingline['MeetingID'];
				}

				$this->assignMeetingattachment( $AttachmentID, $MeetingLineID );
				$MeetingLineID = 0;
				break;
		}
		if(!$MeetingID)
			$this->pageNotFound();
		$this->listMeetingLine( $MeetingID,$MeetingLineID );
	}
	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_ep - Table electionperiod actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	/**
	 * Summary of listElectionperiod
	 * Shoe page Eletion Period witt mark active Period
	 * @param mixed $activeElectionPeriodID
	 * @param mixed $activeMemberTypeID
	 * @return void
	 */
	public function listElectionperiod( $activeElectionPeriodID = 0, $activeMemberTypeID = 0 )
	{
		global $caption,$deb;
		$memberinstance = new Membercontroller($this->registry);
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
		$electionperiodinstance = new Electionperiodcontroller($this->registry);

		// Řádky 'electionperiod'
		$sql = "SELECT * FROM ".$this->prefDb."electionperiod ORDER BY PeriodName";
		$sql = $this->db->getSqlByPage( $sql );
		$cache = $this->db->cacheQuery( $sql );	
		if($this->db->isEmpty( $cache )){
			$this->registry->getObject('template')->getPage()->addTag( 'ElectionPeriodID', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Name', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Actual', '' );				
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'electionPeriodList', array( 'SQL', $cache ) );
		}
		$this->registry->getObject('template')->getPage()->addTag( 'activeElectionPeriodID', $activeElectionPeriodID );				
		$this->registry->getObject('template')->getPage()->addTag( 'activeMemberTypeID', $activeMemberTypeID );						

		// Podřádky 'meetingtype' pro každý záznam 'electionperiod'
		// a 'member' pro  každý záznam 'meetingtype'
		$electionperiod = $electionperiodinstance->readElectionperiods();		
		if ($electionperiod){
			foreach ($electionperiod as $rec) {				
				$meetingtype = $meetingtypeinstance->readMeetingtypesByElectionperiodID($rec['ElectionPeriodID']);
				if ($meetingtype){
					$cache = $this->db->cacheData( $meetingtype );
					$this->registry->getObject('template')->getPage()->addTag( 'meetingTypeList'.$rec['ElectionPeriodID'], array( 'DATA', $cache ) );
					foreach($meetingtype as $mt){
						$members = $memberinstance->readMembers($mt['MeetingTypeID']);
						$result = array();
						if($members){
							foreach($members as $member){
								$contact = $this->getContactByID($member['ContactID']);
								if($contact)
									$member['ContactName'] = $contact['FullName'];
								else
									$member['ContactName'] = $member['MemberID'];
								
								// Překlad typu člena
								$member['MemberTypeCSY'] = $member['MemberType'];
								$idx = $member['MemberTypeCSY'];
								if ($idx)
									$member['MemberTypeCSY'] = $caption[$idx];
								
								
								$member['MemberTypeCSY'.$mt['MeetingTypeID']] = $member['MemberTypeCSY'];
								$member['ContactName'.$mt['MeetingTypeID']] = $member['ContactName'];
								
								$result[] = $member;
							}						
							$cache = $this->db->cacheData( $result );						
							$this->registry->getObject('template')->getPage()->addTag( 'memberList'.$mt['MeetingTypeID'], array( 'DATA', $cache ) );		
						}else{
							$this->registry->getObject('template')->getPage()->addTag( 'ContactName'.$mt['MeetingTypeID'], '' );				
							$this->registry->getObject('template')->getPage()->addTag( 'MemberTypeCSY'.$mt['MeetingTypeID'], '' );				
						}
					}
				}else{
					$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', '' );				
					$this->registry->getObject('template')->getPage()->addTag( 'ElectionPeriodID', '' );				
					$this->registry->getObject('template')->getPage()->addTag( 'MeetingName', '' );				
					$this->registry->getObject('template')->getPage()->addTag( 'Members', '' );					
				}
			} 
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'PeriodName','' );
		}

		$this->registry->getObject('template')->addTemplateBit('meetingtypeCard', 'zob-meetingtype-list.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('memberCard', 'zob-member-list.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('memberTypeSelect', 'zob-member-type.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('newContactCard', 'zob-contact-new.tpl.php');

		$this->build('zob-electionperiod-list.tpl.php');
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_m - Table meeting actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// get
	// read
	
	
	public function getMeetingHeader($meeting)
	{
		global $config;
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);

		$meetingtype = $meetingtypeinstance->getMeetingtype($meeting);
		$headerTitle = array();
        $headerTitle['City'] = 'OBEC '.mb_strtoupper($config['compCity']);
        $met = mb_strtolower($meetingtype['MeetingName']);
        switch ($met){
            case 'zastupitelstvo':
                $headerTitle['FromMeting'] = 'STAROSTA OBCE '.mb_strtoupper($config['compCity']);
                $headerTitle['FromMeting2'] = 'SVOLÁVÁ';
                $headerTitle['MetingTitle'] = 'Veřejné zasedání';
                $headerTitle['MetingTitle2'] = 'zastupitelstva obce';
                $headerTitle['HeadMan'] = 'starosta obce';
                break;
            case 'rada':
                $headerTitle['FromMeting'] = 'STAROSTA OBCE '.mb_strtoupper($config['compCity']);
                $headerTitle['FromMeting2'] = 'SVOLÁVÁ';
                $headerTitle['MetingTitle'] = 'Jednání rady';
                $headerTitle['MetingTitle2'] = '';
                $headerTitle['HeadMan'] = 'starosta obce';
                break;
            case 'stavební komise':
                $headerTitle['FromMeting'] = 'předseda stavební komise obce '.$config['compCity'];
                $headerTitle['FromMeting2'] = 'SVOLÁVÁ';
                $headerTitle['MetingTitle'] = 'Jednání stavební komise';
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
		
		return $headerTitle;
	}
	public function readMeetingByElectionperiodID ( $ElectionPeriodID  )
	{
		$electionperiodinstance = new Electionperiodcontroller($this->registry);

		$meetings = null;
		$electionPeriod = $electionperiodinstance->getElectionPeriod($ElectionPeriodID);
		if ($electionPeriod) {
			$this->db->initQuery('meeting');
			$this->db->setFilter('ElectionPeriodID', $ElectionPeriodID);
			if ($this->db->findSet())
				$meetings = $this->db->getResult();
		}
		return $meetings;		
	}

	 /**
     * Zobrazení seznam zápisů jednání
     * @return void
     */
	public function listMeeting( $MeetingTypeID )
	{
		$electionperiodinstance = new Electionperiodcontroller($this->registry);
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);

		// Zápis z jednání
		$sql = "SELECT * FROM ".$this->prefDb."meeting WHERE MeetingTypeID = $MeetingTypeID ORDER BY EntryNo DESC";
		$sql = $this->db->getSqlByPage( $sql );
		$cache = $this->db->cacheQuery( $sql );	
		if(!$this->db->isEmpty( $cache )){
			$meetings = array();
			while( $meeting = $this->db->resultsFromCache( $cache ) )
			{
				$meetingtype = $meetingtypeinstance->getMeetingtype($MeetingTypeID);
				$electionperiod = $electionperiodinstance->getElectionPeriod($meetingtype['ElectionPeriodID']);

				$meeting['PeriodName'] = $electionperiod['PeriodName'];
				$meeting['MeetingName'] = $meetingtype['MeetingName'];

				$meeting['lineclass'] = $meeting['Close'] == 1 ? 'blue' : '';
				$meeting['disabled'] = $meeting['Close'] == 1 ? 'disabled' : '';

				if($meeting['AtDate'] != null){
					$meeting['AtDate_view'] = $this->registry->getObject('core')->formatDate($meeting['AtDate']);
				}else{
					if($electionperiodinstance->isElectionperiodTemplate($meetingtype['ElectionPeriodID']))
						$meeting['AtDate_view'] = 'šablona';
					else
						$meeting['AtDate_view'] = 'aktuální';
				}
				$meeting['PostedUpDate_view'] = $this->registry->getObject('core')->formatDate($meeting['PostedUpDate']);
				$meeting['PostedDownDate_view'] = $this->registry->getObject('core')->formatDate($meeting['PostedDownDate']);
				$meeting['RecorderAtDate_view'] = $this->registry->getObject('core')->formatDate($meeting['RecorderAtDate']);
				$meeting['dmsClassName'] = 'meeting';
				
				$contact = $this->getContactByID($meeting['VerifierBy1']);
				$meeting['VerifierBy1Name'] = $contact == null ? '' : $contact['FullName'];
				$contact = $this->getContactByID($meeting['VerifierBy2']);
				$meeting['VerifierBy2Name'] = $contact == null ? '' : $contact['FullName'];

				$meetings[] = $meeting;
			}
			$cache = $this->db->cacheData( $meetings );
			$this->registry->getObject('template')->getPage()->addTag( 'meetingList', array( 'DATA', $cache ) );
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'Actual', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EntryNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Year', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'AtDate_view', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'PostedUpDate_view', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'PostedDownDate_view', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingID', 0 );				
		}
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', $MeetingTypeID );						
		$this->registry->getObject('template')->addTemplateBit('editdMeetingCard', 'zob-meeting-edit.tpl.php');
		
		$this->build('zob-meeting-list.tpl.php');
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_ml - Table meetingline actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// get
	
	 /**
     * Zobrazení seznamu bodů jednání
     * @return void
     */
	public function listMeetingLine( $MeetingID , $activeMeetingLineID = 0 )
	{
		$this->setDatasetMeetingLine($MeetingID, $activeMeetingLineID);
		
		$this->registry->getObject('template')->addTemplateBit('editdMeetingLine', 'zob-meetingline-edit.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('newDocument', 'document-edit.tpl.php');

		$this->build('zob-meetingline-list.tpl.php');
	}
	 /**
     * Zobrazení seznamu bodů jednání
     * @return void
     */
	public function setDatasetMeetingLine( $MeetingID , $activeMeetingLineID = 0 )
	{
		$electionperiodinstance = new Electionperiodcontroller($this->registry);
		$meetingtypeinstance = new Meetingtypecontroller($this->registry);
		$meetinginstance = new Meetingcontroller($this->registry);
		$meetinglineinstance = new Meetinglinecontroller($this->registry);

		$meeting = $meetinginstance->getMeeting($MeetingID);
		$MeetingTypeID = $meeting['MeetingTypeID'];
		$meetingtype = $meetingtypeinstance->getMeetingtype($MeetingTypeID);
		$electionperiod = $electionperiodinstance->getElectionPeriod($meetingtype['ElectionPeriodID']);
		$Year = $meeting['Year'];
		$EntryNo = $meeting['EntryNo'];
		$○r = $meetingtype['MeetingName']." - <b>$EntryNo/$Year</b>, datum jednání: ";
		$○r .= $this->registry->getObject('core')->formatDate($meeting['AtDate'],'d.m.Y');
				
		if($electionperiodinstance->isElectionperiodTemplate($electionperiod['ElectionPeriodID']))
			$○r .= ' (šablona)';

		// Body zápisu z jednání
		$meetinglines = $meetinglineinstance->readMeetingLines($MeetingID);
		if($meetinglines){
			$meetinglines2 = array();
			$printLineNo = 0;
			foreach($meetinglines as $meetingline)
			{
				if($meetingline['LineType'] == 'Bod'){
					$printLineNo += 1;
					$meetingline['PrintLineNo'] = $printLineNo; 
				}else{
					$meetingline['PrintLineNo'] = $printLineNo;
				}
				
				$meetingline['dmsClassName'] = 'meetingline';
				$contact = $this->getContactByID($meetingline['PresenterID']);
				if($contact)
					$meetingline['Presenter'] = $contact['FullName'];
				else
					$meetingline['Presenter'] = '';
				$meetingline['Attachments'] = $this->countRec('meetingattachment','MeetingLineID = '.$meetingline['MeetingLineID']);
				$meetingline['bold'] = $meetingline['LineType'] == 'Podbod' ? '' : 'bold';
				if($meetingline['LineNo2'] <> '0')
					$meetingline['LineNo2'] = ".".$meetingline['LineNo2'];
				else
					$meetingline['LineNo2'] = '';
				
				$meetingline['isContent'] = $meetingline['Content'] == '' ? '0' : '1';
				$meetingline['isDiscussion'] = $meetingline['Discussion'] == '' ? '0' : '1';
				$meetingline['isDraftResolution'] = $meetingline['DraftResolution'] == '' ? '0' : '1';

				// Content of meetingline
				$meetinglinecontents = $this->readMeetingLineContents($meetingline['MeetingLineID']);
				if($meetinglinecontents){
					$meetingline['isNextContent'] = '1';
				}else{
					$meetingline['isNextContent'] = '0';
				}									
				$meetingline['attachments'] = $this->countRec('meetingattachment',"MeetingLineID = ".$meetingline['MeetingLineID']);

				$meetinglines2[] = $meetingline;
			}
			$cache = $this->db->cacheData( $meetinglines2 );	
			$this->registry->getObject('template')->getPage()->addTag( 'meetinglineList', array( 'DATA', $cache ) );
			$this->registry->getObject('template')->getPage()->addTag( 'isEmpty', 0 );				

			foreach($meetinglines as $meetingline){
				$meetinglinecontents = $this->readMeetingLineContents($meetingline['MeetingLineID']);
				if($meetinglinecontents){
					$meetinglinecontents2 = array();
					foreach($meetinglinecontents as $meetinglinecontent){
						foreach($meetinglinecontent as $key => $value){
							$rec['con_'.$key] = $value;
			
						}
						$rec['con_isContent'] = $rec['con_Content'] == '' ? '0' : '1';
						$rec['con_isDiscussion'] = $rec['con_Discussion'] == '' ? '0' : '1';
						$rec['con_isDraftResolution'] = $rec['con_DraftResolution'] == '' ? '0' : '1';
						$meetinglinecontents2[] = $rec;
					}
					$cache = $this->db->cacheData( $meetinglinecontents2 );
					$this->registry->getObject('template')->getPage()->addTag( 'meetinglinecontent'.$meetingline['MeetingLineID'], array( 'DATA', $cache ) );
					$meetingline['isNextContent'] = '1';
				}else{
					$meetingline['isNextContent'] = '0';
				}	
			}


			foreach($meetinglines as $meetingline){
				$sql = "SELECT * FROM ".$this->prefDb."meetingattachment WHERE MeetingID = $MeetingID AND MeetingLineID = ".$meetingline['MeetingLineID'];
				$cache = $this->db->cacheQuery( $sql );	
				if(!$this->db->isEmpty( $cache )){
					$meetingattachments = array();
					while( $meetingattachment = $this->db->resultsFromCache( $cache ) )
					{
						$dmsentry = $meetinginstance->getDmsentryByID($meetingattachment['DmsEntryID']);
						if($dmsentry){
							$meetingattachment['ID'] = $dmsentry['ID'];
							$meetingattachment['Name'] = $dmsentry['Name'];
							$meetingattachment['Type'] = $dmsentry['Type'];
						}else{
							$meetingattachment['ID'] = '';
							$meetingattachment['Name'] = '';
							$meetingattachment['Type'] = '';
						}				
						$meetingattachments[] = $meetingattachment;
					}
					$cache = $this->db->cacheData( $meetingattachments );
					$this->registry->getObject('template')->getPage()->addTag( 'meetingattachmentList'.$meetingline['MeetingLineID'], array( 'DATA', $cache ) );
				}
						
				
			}
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'LineType', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'LineNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'LineNo2', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Presenter', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Title', '== Uložit první záznam, nebo Vložit ze šablony ==' );				
			$this->registry->getObject('template')->getPage()->addTag( 'isEmpty', 1 );				
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingLineID', 0 );				
			$this->registry->getObject('template')->getPage()->addTag( 'VoteFor', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'VoteAgainst', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'VoteDelayed', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Content', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Description', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Attachments', '' );				
		}

		// Přílkohy jednání (bez přiřazení k řádku)
		$sql = "SELECT * FROM ".$this->prefDb."meetingattachment WHERE MeetingID = $MeetingID AND MeetingLineID = 0";
		$cache = $this->db->cacheQuery( $sql );			
		if(!$this->db->isEmpty( $cache )){
			$meetingattachments = array();
			while( $meetingattachment = $this->db->resultsFromCache( $cache ) )
			{
				$dmsentry = $meetinginstance->getDmsentryByID($meetingattachment['DmsEntryID']);
				if($dmsentry){
					$meetingattachment['ID'] = $dmsentry['ID'];
					$meetingattachment['Name'] = $dmsentry['Name'];
					$meetingattachment['Type'] = $dmsentry['Type'];
				}else{
					$meetingattachment['ID'] = '';
					$meetingattachment['Name'] = '';
					$meetingattachment['Type'] = '';
				}				
				$meetingattachments[] = $meetingattachment;
			}
			$cache = $this->db->cacheData( $meetingattachments );
			$this->registry->getObject('template')->getPage()->addTag( 'meetingattachmentListNo0', array( 'DATA', $cache ) );
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'Description', '' );				
		}

		// Hlavička
		$this->registry->getObject('template')->getPage()->addTag( 'Year', $Year );						
		$this->registry->getObject('template')->getPage()->addTag( 'EntryNo', $EntryNo );						
		$this->registry->getObject('template')->getPage()->addTag( 'Header', $○r );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingID', $MeetingID );						
		$this->registry->getObject('template')->getPage()->addTag( 'ParentID', $meeting['ParentID'] );						
		$this->registry->getObject('template')->getPage()->addTag( 'MeetingTypeID', $MeetingTypeID );						
		$this->registry->getObject('template')->getPage()->addTag( 'activeMeetingLineID', $activeMeetingLineID );				
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_mlc - Table meetinglinecontent actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// add
	// delete
	// get
	// read

	public function getMeetinglinecontent ( $ContentID )
	{
		$meetinglinecontent = null;
		$this->db->initQuery('meetinglinecontent');
		$this->db->setFilter('ContentID',$ContentID);
		if ($this->db->findFirst())
			$meetinglinecontent = $this->db->getResult();			
		return $meetinglinecontent;
	}
	public function readMeetingLineContents ( $param  ): ?array
	{
		if(is_array($param)){
			$MeetingLineID = $param['MeetingLineID'];
		}else{
			$MeetingLineID = $param;
		}

		$meetinglinecontent = null;
		$this->db->initQuery('meetinglinecontent');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		$this->db->setOrderBy('LineNo');
		if ($this->db->findSet())
			$meetinglinecontent = $this->db->getResult();			
		return $meetinglinecontent;
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_mla - Table meetinglinecontent actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// delete
	// get
	// read
	/**
	 * Summary of addMeetingLineAttachment
	 * @param mixed $param - MeetingLineID or table meetingline
	 * @param mixed $DmsEntryID
	 * @return void
	 */
	function addMeetingLineAttachment($param, $DmsEntryID)
	{
		$meetinginstance = new Meetingcontroller($this->registry);
		$meetinglineinstance = new Meetinglinecontroller($this->registry);

		if(is_array($param)){
			$MeetingLineID = $param['MeetingLineID'];
		}else{
			$MeetingLineID = $param;
		}
		$meetingline = $meetinglineinstance->getMeetingline($MeetingLineID);
		if (!$meetingline)
			return;
		$dmsentry = $meetinginstance->getDmsentry($DmsEntryID);
		if (!$dmsentry)
			return;
		$data = array();
		$data['MeetinglineID'] = $MeetingLineID;
		$data['MeetingID'] = $meetingline['MeetingID'];
		$data['Description'] = $dmsentry['Title'];
		$data['DmsEntryID'] = $dmsentry['ID'];
		$this->db->insertRecords('meetingattachment',$data);
	}
	public function getMeetingattachment ( $AttachmentID )
	{
		$meetingattachment = null;
		$this->db->initQuery('meetingattachment');
		$this->db->setFilter('AttachmentID',$AttachmentID);
		if ($this->db->findFirst())
			$meetingattachment = $this->db->getResult();			
		return $meetingattachment;
	}
	public function getMeetingattachmentByDmsEntryID ( $MeetingID, $DmsEntryID )
	{
		$meetingattachment = null;
		$this->db->initQuery('meetingattachment');
		$this->db->setFilter('MeetingID',$MeetingID);
		$this->db->setFilter('DmsEntryID',$DmsEntryID);
		if ($this->db->findFirst())
			$meetingattachment = $this->db->getResult();			
		return $meetingattachment;
	}
	public function readMeetingAttachments ( $param  )
	{
		if(is_array($param)){
			$MeetingLineID = $param['MeetingLineID'];
		}else{
			$MeetingLineID = $param;
		}
		$meetingattachment = null;
		$this->db->initQuery('meetingattachment');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if ($this->db->findSet())
			$meetingattachment = $this->db->getResult();			
		return $meetingattachment;
	}

	/**
	 * Summary of assignMeetingattachment
	 * @param mixed $AttachmentID
	 * @param mixed $param - MeetingLineID or table meetingline
	 * @return bool
	 */
	public function assignMeetingattachment( $AttachmentID, $param ){
		$meetinglineinstance = new Meetinglinecontroller($this->registry);

		if(!$AttachmentID)
			return false;
		if(is_array($param)){
			$MeetingLineID = $param['MeetingLineID'];
		}else{
			$MeetingLineID = $param;
		}
		$meetingline = $meetinglineinstance->getMeetingline($MeetingLineID);
		if (!$meetingline)
			return false;
				
		$change = array();
		$change['MeetingLineID'] = $MeetingLineID;
		$condition = "AttachmentID = $AttachmentID";
		$this->db->updateRecords('meetingattachment',$change,$condition);
		return true;
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_mlt - Table meetinglinetask actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// add
	// delete
	// get
	// read

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_mlp - Table meetinglinepage actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// add
	// delete
	// get
	// read

	
	public function getMeetinglinepage ( $PageID )
	{
		$meetinglinepage = $this->readMeetinglinepages('', 'PageID='.$PageID);
		return $meetinglinepage[0];
	}
	public function getMeetinglinepageByMeeting ( $MeetingLineID, $ContentID )
	{
		$meetinglinepage = null;
		$this->db->initQuery('meetinglinepage');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		$this->db->setFilter('ContentID',$ContentID);
		$this->db->setFilter('System',1);
		if ($this->db->findFirst())
			$meetinglinepage = $this->db->getResult();			
		return $meetinglinepage;
	}
	public function getMeetinglinepageByPageNo ( $MeetingID, $PageNo )
	{
		$meetinglinepage = $this->readMeetinglinepages($MeetingID, 'PageNo='.$PageNo);
		return $meetinglinepage[0];
	}
	public function readMeetinglinepage ( $MeetingLineID , $condition='' )
	{
		$meetinglinepages = null;
		$this->db->initQuery('meetinglinepage');
		$this->db->setFilter('MeetingLineID',$MeetingLineID);
		if ($condition <> ''){
			$this->db->setCondition($condition);
		};
		if ($this->db->findSet())
			$meetinglinepages = $this->db->getResult();			
		return $meetinglinepages;
	}
	public function readMeetinglinepages( $param , $condition='')
	{
		if(is_array($param)){
			$MeetingID = $param['MeetingID'];
		}else{
			$MeetingID = $param;
		}
		$where = '';	
		if ($MeetingID != ''){
			$where = "WHERE mp.MeetingID = $MeetingID";
		};
		if ($condition != ''){
			if ($where != '') {
				$where .= ' AND ' . $condition;
			}else{
				$where = 'WHERE ' . $condition;
			}
		};

		$meetinglinepage = null;
		$sql = "SELECT mp.PageID,mp.MeetingTypeID,mp.MeetingID,mp.MeetingLineID,mp.ContentID,".
				"mp.Order,mp.PageNo,mp.Content,mp.ImageURL,mp.ImageWidth,mp.ImageHeight,mp.System,mp.PageType," .
				"ml.Title as Lin_Title, ml.LineType as Lin_LineType, ml.LineNo as Lin_LineNo, ml.LineNo2 as Lin_LineNo2, ml.Content as Lin_Content,ml.Changed as Lin_Changed,".
				"mlc.LineNo as Con_LineNo, mlc.Content as Con_Content,mlc.Changed as Con_Changed ".
			"FROM " . $this->prefDb . "meetinglinepage as mp " .
			"LEFT JOIN " . $this->prefDb . "meetingline as ml ON ml.MeetingLineID = mp.MeetingLineID ".
			"LEFT JOIN " . $this->prefDb . "meetinglinecontent as mlc ON mlc.ContentID = mp.ContentID ".
			"$where ".
			"ORDER BY PageNo" ;
		
		$cache = $this->db->cacheQuery( $sql );
		if (!$this->db->isEmpty( $cache ))
		{
			while( $rec = $this->db->resultsFromCache( $cache ) )
			{
				$Point = $rec['Lin_LineNo'] . '.';
				if ($rec['Lin_LineNo2'] <> 0){
					$Point .= $rec['Lin_LineNo2'].'.';
				};
				if ($rec['Con_LineNo'] <> 0) {
					$Point .= $rec['Con_LineNo'].')';
				};
				$rec['Point'] = $Point;
				$rec['MeetingContent'] = $rec['ContentID'] > 0 ? $rec['Con_Content'] : $rec['Lin_Content'];
				$rec['Changed'] = (($rec['Lin_Changed'] == 1) || ($rec['Con_Changed'] == 1)) ? 1 : 0;

				if ($rec['MeetingLineID'] == 0){
					$rec['Lin_LineNo'] = 0;
					$rec['Lin_LineNo2'] = 0;
					$rec['Lin_Title'] = '';
					switch ($rec['PageType']) {
						case 'front':
							$rec['Lin_LineType'] = 'Úvod';
							break;
						case 'warp':
							$rec['Lin_LineType'] = 'Obsah';
							break;
						default:
							$rec['Lin_LineType'] = '';
							break;
					}
					$rec['Point'] = '';
					$rec['MeetingContent'] = '';
					$rec['Changed'] = 0;
				}
				$meetinglinepage[] = $rec;
			}
		}
		return $meetinglinepage;
	}

	public function readMeetinglinepageByMeetingID ( $MeetingID , $condition='' ): ?array
	{
		$meetinglinepages = null;
		$this->db->initQuery('meetinglinepage');
		$this->db->setFilter('MeetingID',$MeetingID);
		if ($condition <> ''){
			$this->db->setCondition($condition);
		};
		if ($this->db->findSet())
			$meetinglinepages = $this->db->getResult();			
		return $meetinglinepages;
	}

	public function addMeetinglinepageFromMeetingline($meetingline, $pageNo){
		$MeetingLineID = $meetingline['MeetingLineID'];
		$meetinglinepage = $this->getMeetinglinepageByMeeting($MeetingLineID, 0);

		if ($meetinglinepage == null){
			$data['MeetingLineID'] = $MeetingLineID;
			$data['MeetingID'] = $meetingline['MeetingID'];
			$data['MeetingTypeID'] = $meetingline['MeetingTypeID']; 
			$data['PageNo'] = $pageNo;
			$data['ContentID'] = 0; 
			$data['System'] = 1;
			$data['PageType'] = 'page';
			$this->db->InsertRecords('meetinglinepage',$data);	
			// Add Page Line			
			$this->db->initQuery('meetinglinepage');
			$this->db->setOrderBy('PageID');
			if ($this->db->findLast()){
				$meetinglinepage = $this->db->getResult();			
				$this->addMeetinglinepageline($meetinglinepage, $meetingline['Content']);
			}
		}else{
			$change['PageNo'] = $pageNo;
			$condition = "MeetinglineID = $MeetingLineID";
			$this->db->updateRecords('meetinglinepage',$change,$condition);
		}
	} 

	public function addMeetinglinepageFromMeetinglinecontent($meetinglinecontent, $pageNo){
		$MeetingLineID = $meetinglinecontent['MeetingLineID'];
		$ContentID = $meetinglinecontent['ContentID'];
		$meetinglinepage = $this->getMeetinglinepageByMeeting($MeetingLineID, $ContentID);

		if ($meetinglinepage == null){
			$data['MeetingLineID'] = $MeetingLineID;
			$data['MeetingID'] = $meetinglinecontent['MeetingID'];
			$data['MeetingTypeID'] = $meetinglinecontent['MeetingTypeID']; 
			$data['PageNo'] = $pageNo;
			$data['ContentID'] = $ContentID; 
			$data['System'] = 1;
			$data['PageType'] = 'page';
			$this->db->InsertRecords('meetinglinepage',$data);	
			// Add Page Line			
			$this->db->initQuery('meetinglinepage');
			$this->db->setOrderBy('PageID');
			if ($this->db->findLast()){
				$meetinglinepage = $this->db->getResult();			
				$this->addMeetinglinepageline($meetinglinepage, $meetinglinecontent['Content']);
			}
		}else{
			$change['PageNo'] = $pageNo;
			$condition = "MeetinglineID = $MeetingLineID AND ContentID = $ContentID";
			$this->db->updateRecords('meetinglinepage',$change,$condition);
		}
	} 

	public function addMeetinglinepageFrontPage($MeetingID){
		global $config;
		$meetinginstance = new Meetingcontroller($this->registry);
		
		$meeting = $meetinginstance->getMeeting($MeetingID);
		$headerTitle = $this->getMeetingHeader($meeting);
		$meetinglinepage = $this->readMeetinglinepageByMeetingID($MeetingID, "`PageType` like 'front'");
		if ($meetinglinepage){
			$condition = '`MeetingID` = ' . $meetinglinepage[0]['MeetingID'] . " AND `PageType` = 'front'";
			$this->db->deleteRecords('meetinglinepage',$condition);	
		};

		$data['MeetingID'] = $MeetingID;
		$data['MeetingLineID'] = 0;
		$data['PageNo'] = 1;
		$data['System'] = 1;
		$data['PageType'] = 'front';
		$this->db->InsertRecords('meetinglinepage',$data);	

		// Add Page Line			
		$this->db->initQuery('meetinglinepage');
		$this->db->setOrderBy('PageID');
		if ($this->db->findLast()){
			$meetinglinepage = $this->db->getResult();			
			$this->addMeetinglinepageline($meetinglinepage, $headerTitle['MetingTitle'], 'H1');
			$this->addMeetinglinepageline($meetinglinepage, $headerTitle['MetingTitle2'], 'H1');
			$content = $this->registry->getObject('core')->formatDate($meeting['AtDate']) . ', ';
			$content .= $this->registry->getObject('core')->formatDate($meeting['AtTime'],'H:i').' hodin';
			$this->addMeetinglinepageline($meetinglinepage, $content, 'H3');
			$content = $config['compCity'] .', ' . $meeting['MeetingPlace'];
			$this->addMeetinglinepageline($meetinglinepage, $content, 'H3');
		}
	} 
	/**
	 * 
	 * Struktura
	 *   meeting['MeetingID'] ........................meetinglinepage['MeetingID']
	 *     meetingline['LineNo'] .....................meetinglinepage['MeetingLineID']
	 *       meetingline['LineNo2'] ..................meetinglinepage['MeetingLineID']
	 *         meetinglinecontent['LineNo'] ..........meetinglinepage['ContentID']
	 * 
	 * @param mixed $param
	 * @return int
	 */
	public function synchroMeetinglinepage ( $param  )
	{
		$meetinglineinstance = new Meetinglinecontroller($this->registry);

		if(is_array($param)){
			$MeetingID = $param['MeetingID'];
		}else{
			$MeetingID = $param;
		}
		$pageNo = 0;

		$meetinglinepage = $this->readMeetinglinepageByMeetingID($MeetingID, "`PageType` like 'front'");
		if($meetinglinepage)
			$pageNo = count($meetinglinepage);

		$meetinglinepage = $this->readMeetinglinepageByMeetingID($MeetingID, "`PageType` like 'warp'");
		if($meetinglinepage){
			foreach($meetinglinepage as $rec){
				$pageNo++;
				$update = array();
				$update['PageNo'] = $pageNo;
				$condition = 'PageID = ' . $rec['PageID'];
				$this->db->updateRecords('meetinglinepage',$update,$condition);
			}
		};
		
		$meetinglines = $meetinglineinstance->readMeetingLines($MeetingID);
		if ($meetinglines){
			foreach ($meetinglines as $meetingline){
				
				// Create meetinglinepage from meetingline
				$pageNo++;
				$this->addMeetinglinepageFromMeetingline($meetingline, $pageNo);

				$meetinglinecontents = $this->readMeetingLineContents($meetingline);
				if ($meetinglinecontents){
					foreach ($meetinglinecontents as $meetinglinecontent){	

						// Create meetinglinepage from meetinglinecontent
						$pageNo++;
						$this->addMeetinglinepageFromMeetinglinecontent($meetinglinecontent,$pageNo);				

					}
				}
			}
		}

		return ($pageNo);
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_mlpl - Table meetinglinepageline actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// delete
	// get
	// read
	/**
	 * Summary of addMeetinglinepageline
	 * @param mixed $param - PageID or table meetinglinepage
	 * @param mixed $content
	 * @param mixed $FontStyle
	 * @return void
	 */
	public function addMeetinglinepageline($param, $content, $FontStyle = 'T1')
	{
		if(is_array($param)){
			$PageID = $param['PageID'];
		}else{
			$PageID = $param;
		}
		$meetinglinepage = $this->getMeetinglinepage($PageID);
		if (!$meetinglinepage)
			return;

		$data['PageID'] = $meetinglinepage['PageID'];
		$data['MeetingLineID'] = $meetinglinepage['MeetingLineID'];
		$data['MeetingID'] = $meetinglinepage['MeetingID'];
		$data['MeetingTypeID'] = $meetinglinepage['MeetingTypeID'];
		$data['Content'] = $content;
		$data['FontStyle'] = $FontStyle;
		$this->db->InsertRecords('meetinglinepageline', $data);
	}

	public function addMeetinglinepageWarpPage($MeetingID,$PageID){
		global $config;
		$meetinginstance = new Meetingcontroller($this->registry);
		$meetinglineinstance = new Meetinglinecontroller($this->registry);

		$meeting = $meetinginstance->getMeeting($MeetingID);
		$meetinglinepage = $this->getMeetinglinepage($PageID);

		$condition = "PageID = ".$PageID;
		$this->db->deleteRecords( 'meetinglinepageline', $condition); 

		// Načtu počet stran
		$maxLine = 8;
		$meetingline = $meetinglineinstance->readMeetingLines($meeting);
		if (!$meetingline)
			return;
		if(!$meetinglinepage)
			return;
		$suppPoit = 0;

		foreach($meetingline as $rec){
			$content = "";
			if ($rec['LineType'] == 'Doplňující bod') {
				$suppPoit++;
				if ($suppPoit == 1){
					$content = "\n\nDoplňující body\n";
					$this->addMeetinglinepageline($meetinglinepage, $content, 'T2');		
				}
				$content = $rec['LineNo'] . '. (' . $rec['LineType']. ')  ' .$rec['Title'];
			}else{
				if ($suppPoit > 0) {
					$suppPoit = 0;
					$this->addMeetinglinepageline($meetinglinepage, "", 'T1');		
				}
				$content = $rec['LineNo'] . '. ' . ' ' .$rec['Title'];
			}	
			$this->addMeetinglinepageline($meetinglinepage, $content, 'T1');
		}
	} 
	public function readMeetinglinepagelines ( $param  )
	{
		if(is_array($param)){
			$PageID = $param['PageID'];
		}else{
			$PageID = $param;
		}

		$meetinglinepagelines = null;
		$this->db->initQuery('meetinglinepageline');
		$this->db->setFilter('PageID',$PageID);
		$this->db->setOrderBy('`Order`');
		if ($this->db->findSet()){
			$meetinglinepagelines = $this->db->getResult();			
		}
		return $meetinglinepagelines;
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   tab_mlpa - Table meetinglinepageattachment actions
	 * -------------------------------------------------------------------------------------------------------------
	 */
	// delete
	// get
	// read
	public function readMeetingLinePageAttachments ( $param  )
	{
		if(is_array($param)){
			$PageID = $param['PageID'];
		}else{
			$PageID = $param;
		}

		$meetinglinepageattachment = null;

		$sql = "SELECT pa.EntryNo,pa.PageID,pa.AttachmentID,a.DmsEntryID,a.Description " .
			"FROM " . $this->prefDb . "meetinglinepageattachment as pa " .
			"LEFT JOIN " . $this->prefDb . "meetingattachment as a ON a.AttachmentID = pa.AttachmentID ".
			"WHERE pa.PageID = $PageID" ;
		$cache = $this->db->cacheQuery( $sql );
		if (!$this->db->isEmpty( $cache ))
		{
			while( $rec = $this->db->resultsFromCache( $cache ) )
			{
				$meetinglinepageattachment[] = $rec;
			}
		}
		return $meetinglinepageattachment;
	}

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 *   Common function
	 * -------------------------------------------------------------------------------------------------------------
	 */

	/**
	 * Summary of addFiles
	 * @param array $uploadDocument
	 * @param int $MeetingID
	 * @return void
	 */
	function addFiles($uploadDocument, $MeetingID)
	{
		$meetinginstance = new Meetingcontroller($this->registry);
		if ($uploadDocument){
			foreach ($uploadDocument as $entryNo) {
				$dmsentry = $meetinginstance->getDmsentry($entryNo);
				$data = array();
				$data['MeetinglineID'] = 0;
				$data['MeetingID'] = $MeetingID;
				$data['Description'] = $dmsentry['Title'];
				$data['DmsEntryID'] = $dmsentry['ID'];
				$this->db->insertRecords('meetingattachment',$data);
			}
		}
		$this->listMeetingLine( $MeetingID , 0 );
	}

	public function getInbox ( $InboxID )
	{
		$inbox = null;
		$this->db->initQuery('inbox');
		$this->db->setFilter('InboxID',$InboxID);
		if ($this->db->findFirst())
			$inbox = $this->db->getResult();			
		return $inbox;
	}
	public function getContactByName ( $FullName )
	{
		$contact = null;
		$this->db->initQuery('contact');
		$this->db->setFilter('FullName',$FullName);
		if ($this->db->findFirst())
			$contact = $this->db->getResult();			
		return $contact;
	}

	public function getContactByID ( $ContactID )
	{
		$contact = null;
		$this->db->initQuery('contact');
		$this->db->setFilter('ID',$ContactID);
		if ($this->db->findFirst())
			$contact = $this->db->getResult();			
		return $contact;
	}

	public function getContactFullName ( $ContactID )
	{
		$fullName = '';
		$contact = $this->getContactByID($ContactID);
		if ($contact)
			$fullName = $contact['FullName'];
		else{
			if($ContactID != '00000000-0000-0000-0000-000000000000'){			
				$fullName = $ContactID;
			}
		}
		return $fullName;
	}

	public function getDmsentryByInboxID ( $InboxID )
	{
		$inbox = null;
		$dmsentry = null;
		$this->db->initQuery('inbox');
		$this->db->setFilter('InboxID',$InboxID);
		if ($this->db->findFirst())
			$inbox = $this->db->getResult();			
		if($inbox){
			$this->db->initQuery('dmsentry');
			$this->db->setFilter('ID',$inbox['DmsEntryID']);
			if ($this->db->findFirst())
				$dmsentry = $this->db->getResult();			
		}
		return $dmsentry;		
	}



	public function getNextMeetinglineContentLineNo( $MeetingLineID )
	{
		$sql = "SELECT max(LineNo) as LineNo FROM ".$this->prefDb."meetinglinecontent WHERE MeetingLineID = $MeetingLineID ";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return $result['LineNo'] + 1;				
	}


	
	public function countRec($table, $filter = ''){
		$sql = "SELECT count(*) as pocet FROM ".$this->prefDb.$table;
		if ($filter != '')
			$sql .= " WHERE $filter";
		$cache = $this->db->cacheQuery( $sql );	
		$this->db->findFirst( $cache );
		$result = $this->db->resultsFromCache( $cache );
		return $result['pocet'];				
	}


	public function text2Date($text){
		if($text == "")
			return null;
		$text = trim($text);
		$arr = explode('.',$text);
		if(!isset($arr[1]))
			return null;
		if(!isset($arr[2]))
			return null;
		if($arr[2] < 100)
			$arr[2] = $arr[2] + 2000;
		$text = $arr[0].".".$arr[1].".".$arr[2];
		$date = $this->registry->getObject('core')->formatDate($text,'Y-m-d');
		return $date;
	}
}


