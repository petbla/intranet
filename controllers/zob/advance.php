<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    6.1.2023
 */
class Zobadvance
{

	private $registry;
	private $zob;
	private $message;
	private $errorMessage;
	public $MeetingID;
	private $anchor;

	/**
	 * @param Registry $registry 
	 */
	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
		$this->zob = new Zobcontroller($this->registry, false);
	}

	/**
	 * Rozšířený modul
	 * @return void
	 */
	public function main($action)
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();
		$MeetingLineID = 0;
		$MeetingID = 0;
		$template = 'zob-adv-meetingline-list.tpl.php';

		switch ($action) {
			case 'presentation':
				$action = isset($urlBits[3]) ? $urlBits[3] : '';
				$action = isset($_POST["action"]) ? $_POST["action"] : $action;
				$this->presentation($action);
				return;
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
			case 'print':
				$action = isset($urlBits[3]) ? $urlBits[3] : '';
				$template = $this->print($action);
				break;
			default:
				$MeetingID = isset($urlBits[2]) ? (int) $urlBits[2] : null;
				if ($MeetingID) {
					$this->MeetingID = $MeetingID;
				}
		}
		$this->build($template);
	}

	/**
	 * Sestavení stránky pro TISK
	 * @return void
	 */
	public function buildpresentation($template = 'presentation-edit.tpl.php')
	{
		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message', $this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage', $this->errorMessage);
		$this->registry->getObject('template')->getPage()->addTag('anchor', $this->anchor);

		// Build page
		$this->registry->getObject('template')->buildFromTemplates('presentation-header.tpl.php', $template, 'presentation-footer.tpl.php');
	}

	/**
	 * Sestavení stránky pro TISK
	 * @return void
	 */
	public function build($template = 'zob-adv-meetingline-list.tpl.php')
	{
		$this->zob->setDatasetMeetingLine($this->MeetingID);

		$this->registry->getObject('template')->addTemplateBit('editdMeetingLine', 'zob-adv-meetingline-edit.tpl.php');

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message', $this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage', $this->errorMessage);
		$this->registry->getObject('template')->getPage()->addTag('anchor', $this->anchor);

		// Build page
		$this->registry->getObject('template')->buildFromTemplates('print-header.tpl.php', $template, 'print-footer.tpl.php');
	}

	/**
	 * Tisk dokumentů jednání
	 * URL: zob/adv/print/invitation/<MeetingID>
	 * @param string $action
	 * @return mixed|string
	 */
	private function print($action)
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();
		$template = '';
		$MeetingID = $urlBits[4];
		$meeting = $this->zob->getMeeting($MeetingID);
		if (!$meeting) {
			$this->errorMessage = 'ERROR: Nezadáno číslo jednání nebo jednání $MeetingID neexistuje.';
			return '';
		}

		switch ($action) {
			case 'invitation':
				# code
				$template = 'zob-print-invitation.tpl.php';
				break;
		}
		$this->MeetingID = $MeetingID;
		return $template;
	}

	public function presentationcontent( $MeetingID)
	{
		$pages = $this->zob->synchroMeetinglinepage($MeetingID);
		$meeting = $this->zob->getMeeting($MeetingID);
		$MeetingTypeID = $meeting['MeetingTypeID'];
		$meetingtype = $this->zob->getMeetingtype($MeetingTypeID);
		$meetinglinepage = $this->zob->readMeetinglinepages($MeetingID);		
		
		$Year = $meeting['Year'];
		$EntryNo = $meeting['EntryNo'];
		$○r = $meetingtype['MeetingName']." - <b>$EntryNo/$Year</b>, datum jednání: ";
		$○r .= $this->registry->getObject('core')->formatDate($meeting['AtDate'],'d.m.Y');

		$this->registry->getObject('template')->getPage()->addTag('Header', $○r);
		$this->registry->getObject('template')->getPage()->addTag('MeetingID', $MeetingID);

		$cache = $this->registry->getObject('db')->cacheData( $meetinglinepage );
		$this->registry->getObject('template')->getPage()->addTag( 'meetinglinepageList', array( 'DATA', $cache ) );	

		$this->MeetingID = $MeetingID;
	}

	/**
	 * @return void
	 */
	private function addfrontpage($MeetingID)
	{
		// Kontrola existence frontPage a vložení nové (úvodní strana)
		$this->zob->addMeetinglinepageFrontPage($MeetingID);

		// Přerovnání stránek
		$this->zob->synchroMeetinglinepage($MeetingID);
	}

	/**
	 * @return void
	 */
	private function addwarppage($MeetingID,$PageID)
	{
		// Kontrola existence frontPage a vložení nové (úvodní strana)
		$this->zob->addMeetinglinepageWarpPage($MeetingID,$PageID);

		// Přerovnání stránek
		$this->zob->synchroMeetinglinepage($MeetingID);
	}

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function setDatasetPresentation($MeetingID, $PageNo)
	{
		$pages = $this->zob->synchroMeetinglinepage($MeetingID);

		$prevPageNo = $PageNo > 1 ? $PageNo - 1 : $PageNo;
		$nextPageNo = $PageNo >= $pages ? $pages : $PageNo + 1;

		$meetinglinepage = $this->zob->getMeetinglinepageByPageNo($MeetingID, $PageNo);
		$meetinglinepageline = $this->zob->readMeetinglinepagelines($meetinglinepage);
		$meetingline = $this->zob->getMeetingline($meetinglinepage['MeetingLineID']);
		if(!$meetingline){
			$meetingline = array();
			$meetingline['MeetingLineID'] = 0;
			$meetingline['MeetingID'] = $MeetingID;
			$meetingline['LineNo'] = 0;
			$meetingline['LineNo2'] = 0;
			$meetingline['LineType'] = '';
			$meetingline['Title'] = '';
			$meetingline['Content'] = '';
			$meetingattachment = null;
			$meetingLinepageattachment = null;
		}else{
			$meetingline['LineNo'] .= $meetingline['LineNo2'] > 0 ? '.' . $meetingline['LineNo2'] . '.' : '.';
			$meetingattachment = $this->zob->readMeetingAttachments($meetingline);
			$meetingLinepageattachment = $this->zob->readMeetingLinePageAttachments($meetinglinepage);
		}

		$this->registry->getObject('template')->dataToTags($meetinglinepage, 'page_');		
		$this->registry->getObject('template')->dataToTags($meetingline, 'line_');

		if($meetinglinepageline){
			$cache = $this->registry->getObject('db')->cacheData( $meetinglinepageline );
			$this->registry->getObject('template')->getPage()->addTag( 'meetinglinepagelines', array( 'DATA', $cache ) );	
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'meetinglinepagelines', '' );
		};

		if($meetingLinepageattachment){
			$cache = $this->registry->getObject('db')->cacheData( $meetingLinepageattachment );
			$this->registry->getObject('template')->getPage()->addTag( 'pageattachments', array( 'DATA', $cache ) );	
			$this->registry->getObject('template')->getPage()->addTag( 'visibleattachments', 'yes' );	
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'pageattachments', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'visibleattachments', 'no' );	
		};

		$this->registry->getObject('template')->getPage()->addTag('MeetingID', $MeetingID);
		$this->registry->getObject('template')->getPage()->addTag('prevPageNo', $prevPageNo);
		$this->registry->getObject('template')->getPage()->addTag('nextPageNo', $nextPageNo);
		$this->registry->getObject('template')->getPage()->addTag('PageNo', $PageNo);
		if(is_array($meetingattachment)){
			$arrCount = count($meetingattachment);
			$this->registry->getObject('template')->getPage()->addTag('AttachmentCount', $arrCount);
		}else{
			$this->registry->getObject('template')->getPage()->addTag('AttachmentCount', '');
		}
		

		$this->MeetingID = $MeetingID;
	}

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function presentation($action)
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
			case 'content':
				$template = 'presentation-content.tpl.php';
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				$this->presentationcontent($MeetingID);
				$this->buildpresentation($template);
				break;
			case 'edit':
				$template = 'presentation-edit.tpl.php';
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				$PageNo = isset($urlBits[5]) ? (int) $urlBits[5] : 1;
				$this->setDatasetPresentation($MeetingID, $PageNo);
				$this->buildpresentation($template);
				break;
			case 'addfrontpage':
				$template = 'presentation-edit.tpl.php';
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				$this->addfrontpage($MeetingID);
				$this->setDatasetPresentation($MeetingID, 1);
				$this->buildpresentation($template);
				break;
			case 'addwarppage':
				$template = 'presentation-edit.tpl.php';
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : 0;
				$PageID = isset($urlBits[4]) ? $urlBits[5] : 0;
				$this->addwarppage($MeetingID,$PageID);
				$this->setDatasetPresentation($MeetingID, 2);
				$this->buildpresentation($template);
				break;
			case 'show':
				$template = 'presentation-slideshow.tpl.php';
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : '';
				$PageNo = isset($urlBits[5]) ? (int) $urlBits[5] : 1;
				$this->setDatasetPresentation($MeetingID, $PageNo);
				$this->buildpresentation($template);
				break;
			default:
				$this->zob->pageNotFound();
				break;
		};
		$this->MeetingID = $MeetingID;
	}

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingline($action)
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();
		$MeetingLineID = 0;
		$MeetingID = 0;

		switch ($action) {
			case 'moveup':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : null;
				$MeetingLineID = isset($urlBits[5]) ? $urlBits[5] : null;
				if ($MeetingLineID) {
					$this->zob->moveMeetingline($MeetingLineID, -1);
				}
				$MeetingLineID = 0;
				break;
			case 'movedown':
				$MeetingID = isset($urlBits[4]) ? $urlBits[4] : null;
				$MeetingLineID = isset($urlBits[5]) ? $urlBits[5] : null;
				if ($MeetingLineID) {
					$this->zob->moveMeetingline($MeetingLineID, 1);
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
	private function meetinglinecontent($action)
	{
		$urlBits = $this->registry->getURLBits();
		$MeetingID = 0;
		$MeetingLineID = 0;
		switch ($action) {
			case 'delete':
				$ContentID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$meetinglinecontent = $this->zob->getMeetinglinecontent($ContentID);
				$MeetingLineID = $meetinglinecontent['MeetingLineID'];
				$MeetingID = $meetinglinecontent['MeetingID'];
				$condition = "ContentID = $ContentID";
				$this->registry->getObject('db')->deleteRecords('meetinglinecontent', $condition, 1);
				$meetinglinecontents = $this->zob->readMeetingLineContents($MeetingLineID);
				if ($meetinglinecontents) {
					$i = 0;
					foreach ($meetinglinecontents as $meetinglinecontent) {
						$i++;
						$ContentID = $meetinglinecontent['ContentID'];
						$changes = array();
						$changes['LineNo'] = $i;
						$condition = "ContentID = $ContentID";
						$this->registry->getObject('db')->updateRecords('meetinglinecontent', $changes, $condition);
					}
				}
				break;
			case 'add':
				$MeetingLineID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$meetingline = $this->zob->getMeetingline($MeetingLineID);
				$MeetingID = $meetingline['MeetingID'];
				$meeting = $this->zob->getMeeting($MeetingID);
				if ($meeting['Close'] == 1) {
					$this->errorMessage = 'Nelze editovat uzavřený zápis.';
				} else {
					$data['MeetingLineID'] = $MeetingLineID;
					$data['MeetingID'] = $MeetingID;
					$data['MeetingTypeID'] = $meetingline['MeetingTypeID'];
					$data['LineNo'] = $this->zob->getNextMeetinglineContentLineNo($MeetingLineID);
					$this->registry->getObject('db')->insertRecords('meetinglinecontent', $data);
					$this->anchor = "anchor" . $MeetingLineID;
				}
				break;
		}
		$this->MeetingID = $MeetingID;
	}

	/**
	 * Modifikace tabulky bodů jednání
	 * @return void
	 */
	private function meetingattachment($action)
	{
		global $config, $caption;
		$urlBits = $this->registry->getURLBits();
		$MeetingID = 0;
		$MeetingLineID = 0;
		$AttachmentID = 0;

		switch ($action) {
			case 'delete':
				$AttachmentID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$meetingattachment = $this->zob->getMeetingattachment($AttachmentID);
				$MeetingID = $meetingattachment['MeetingID'];
				$MeetingLineID = $meetingattachment['MeetingLineID'];
				$condition = 'AttachmentID = ' . $AttachmentID;
				$this->registry->getObject('db')->deleteRecords('meetingattachment', $condition, 1);
				break;
			case 'assign':
				$AttachmentID = isset($urlBits['4']) ? $urlBits['4'] : 0;
				$MeetingLineID = isset($urlBits['5']) ? $urlBits['5'] : 0;
				$meetingline = $this->zob->getMeetingline($MeetingLineID);
				if ($meetingline) {
					$MeetingID = $meetingline['MeetingID'];
				} else {
					$meetingattachment = $this->zob->getMeetingattachment($AttachmentID);
					if ($meetingattachment)
						$meetingline = $this->zob->getMeetingline($meetingattachment['MeetingLineID']);
					$MeetingID = $meetingline['MeetingID'];
				}

				$this->zob->assignMeetingattachment($AttachmentID, $MeetingLineID);
				$MeetingLineID = 0;
				break;
		}
		$this->MeetingID = $MeetingID;
	}
}