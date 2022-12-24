<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    11.12.2022
 */
class Todocontroller{
	
	private $registry;
	private $perSet;
	private $prefDb;

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		global $config, $caption, $deb;
		$this->registry = $registry;
		$this->perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        $this->prefDb = $config['dbPrefix'];
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     
			
			if($this->perSet == 10)
			{
				$this->error($caption['msg_unauthorized']);
				return;
			}

			if( isset( $urlBits[1] ) )
			{		
				switch ($urlBits[1]) {
					case 'listTodo':
						$this->listTodo();
						break;
					case 'listMyTodo':
						$this->listMyTodo();
						break;
					case 'listTodoClose':
						$this->listTodoClose();
						break;
					case 'listMyTodoClose':
						$this->listMyTodoClose();
						break;
					case 'listNote':
						$this->listNote();
						break;
					case 'listNew':
						$this->listNewDocuments();
						break;
					case 'listArchive':
						$this->listArchiveDocuments();
						break;
					case 'inbox':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						switch ($action) {
							case 'refresh':
								$this->refreshInbox();
								break;
							case 'modify':
								$this->modifyInbox();
								break;
							default:
								$this->listInbox();
						}
						break;
					case 'WS':
						// Je voláno jako XMLHttpRequest (function.js) a pouze loguje zobrazené položky
						switch ($urlBits[2]) {
							case 'setRemind':
								$ID = isset($urlBits[3]) ? $urlBits[3] : '';
								if($this->perSet > 0)
									$result = $this->wsSetRemindEntry($ID);
								else
									$result = 'Error';
								exit($result);
								break;
						}
						break;
					default:
						$this->pageNotFound();
						break;
					}
			}else{
        		$this->listMyTodo('');
			}
		}
	}
	
    /**
     * Sestavení stránky
     * @return void
     */
	private function build( $template = 'page.tpl.php' )
	{
		// Category Menu
		$this->createCategoryMenu();

		// Build page
        $BaseUrl = $this->registry->getURLPath();
        $this->registry->getObject('template')->getPage()->addTag( 'BaseUrl', $BaseUrl );

        $this->registry->getObject('template')->getPage()->addTag( 'controller', 'todo' );
		$this->registry->getObject('template')->addTemplateBit('editcard', 'document-entry-editcard.tpl.php');

		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-todo.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template , 'footer.tpl.php');	
	}
    
	/**
     * Zobrazení chybové stránky, pokud dokument nebyl nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámého obsahu.",'dmsentry','');
		$this->build('invalid-document.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'dmsentry','');

		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		$this->build();
	}

    /**
	 * Generování menu
	 * @return void
	 */
	public function createCategoryMenu()
    {
		global $config;
		$urlBits = $this->registry->getURLBits();
		$action = isset( $urlBits[1]) ? $urlBits[1] : '';

		$countInboxNew = $this->countInboxNew();

		$rec['idCat'] = 'inbox';
		if($countInboxNew)
			$rec['titleCat'] = "<b>Doručená pošta ($countInboxNew)</b>";
		else
			$rec['titleCat'] = "Doručená pošta ($countInboxNew)";
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listTodo';
		$rec['titleCat'] = 'Úkoly';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listMyTodo';
		$rec['titleCat'] = 'Úkoly (vlastní)';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listTodoClose';
		$rec['titleCat'] = 'Vyřízené úkoly';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listMyTodoClose';
		$rec['titleCat'] = 'Vyřízené úkoly (vlastní)';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listNote';
		$rec['titleCat'] = 'Poznámky';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listNew';
		$rec['titleCat'] = 'Novinky - (poslední změny)';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'listArchive';
		$rec['titleCat'] = 'Archivní položky (uzavřené)';
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$cache = $this->registry->getObject('db')->cacheData( $table );
		$this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'DATA', $cache ) );
    }


    /**
     * Zobrazení doručené pošty
     * @return void
     */
	public function listInbox($activeInboxID = 0)
	{
		require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
		$zob = new Zobcontroller( $this->registry, true );
		
		$sql = "SELECT * FROM ".$this->prefDb."inbox ".
				  	"WHERE Close = 0 ".
				  	"ORDER BY CreateDate DESC ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		while( $inbox = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{				
			
			$inbox['ismodify'] = $inbox['Modified'] == 0 ? 'no' : '';
			$inbox['isfolder'] = $inbox['DmsEntryID'] == '00000000-0000-0000-0000-000000000000' ? 'no' : '';			
			$MeetingID = $inbox['MeetingID'] == 0 ? 'no' : '';
			$inbox['ismeeting'] = $MeetingID == 0 ? 'no' : '';
			$inbox['MeetingNo'] = $zob->getMeetingNo($MeetingID,true);
			$inbox['CreateDate'] = $this->registry->getObject('core')->formatDate($inbox['CreateDate'],'d.m.Y H:i');
			$inbox['SelectMeetingTypeID'] = '';
			$inbox['MeetingNo'] = '';
			if($inbox['MeetingID']){
				$meeting = $zob->getMeeting($inbox['MeetingID']);
				if($meeting){
					$meetingtype = $zob->getMeetingtype($meeting['MeetingTypeID']);
					$inbox['MeetingNo'] = $zob->getMeetingNo($inbox['MeetingID']);
					$inbox['SelectMeetingTypeID'] = $meetingtype['MeetingTypeID'];
				}
			}
			$result[] = $inbox;
		};
		if($result){
			$cache = $this->registry->getObject('db')->cacheData( $result );
			$this->registry->getObject('template')->getPage()->addTag( 'listInbox', array( 'DATA', $cache ) );
			
			$electionperiod = $zob->getActualElectionperiod();
			$meetingtype = $zob->readMeetingtypesByElectionperiodID($electionperiod['ElectionPeriodID']);
			$meetingtype[] = array('MeetingName' => 'Úkol');
			$meetingtype[] = array('MeetingName' => 'Storno');
			$meetingtype[] = array('MeetingName' => '');
				
			foreach ($result as $inbox) {
				if($meetingtype){
					$cache = $this->registry->getObject('db')->cacheData( $meetingtype );
					$this->registry->getObject('template')->getPage()->addTag( 'listType'.$inbox['InboxID'], array( 'DATA', $cache ) );	
				}
			}
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'Title', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'CreateDate', '' );
		}
		$this->registry->getObject('template')->getPage()->addTag( 'activeInboxID', $activeInboxID );					
		$this->registry->getObject('template')->addTemplateBit('inboxCard', 'todo-inbox-edit.tpl.php');

		$this->build('todo-inbox-list.tpl.php');
	}	
	
    /**
     * Zobrazení seznamu všech úkolů
     * @return void
     */
	public function listTodo()
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
					",(DATE_FORMAT(RemindLastDate,'%Y-%m-%d') < CURDATE()) as term ".
					",(RemindLastDate - CURDATE()) as termDays ".
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 AND Remind = 1 ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"ORDER BY RemindLastDate,Title ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		
		while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{			
			$this->model = new Entry( $this->registry, $rec['ID'] );
			$entry = $this->model->getData();
		
			$rec['dmsClassName'] = 'item';                
			$rec['DocumentType'] = $entry['DocumentType'];
			$rec['RemindState'] = $entry['RemindStateText'];
			$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
			$result[] = $rec;
		};

		if ($result == null){			
			$this->model = new Entry($this->registry,'');
			$rec = $this->model->getEmpty();
			$rec['termDays'] = '';
			$result[] = $rec;
		}

		$cache = $this->registry->getObject('db')->cacheData( $result );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'DATA', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech aktivních úkolů.','DmsEntry','');
		// Zobrazení výsledku 
		$this->build('todo-list.tpl.php');
	}	
	
    /**
     * Zobrazení seznamu všech úkolů
     * @return void
     */
	public function listMyTodo()
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		
		$userID = $this->registry->getObject('authenticate')->getUserID();
		
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
					",(DATE_FORMAT(RemindLastDate,'%Y-%m-%d') < CURDATE()) as term ".
					",(RemindLastDate - CURDATE()) as termDays ".
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 AND Remind = 1 ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"AND RemindUserID = '$userID' ".
				  	"ORDER BY RemindLastDate,Title ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		
		while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{			
			$this->model = new Entry( $this->registry, $rec['ID'] );
			$entry = $this->model->getData();
		
			$rec['dmsClassName'] = 'item';                
			$rec['DocumentType'] = $entry['DocumentType'];
			$rec['RemindState'] = $entry['RemindStateText'];
			$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
			$result[] = $rec;
		};

		if ($result == null){			
			$this->model = new Entry($this->registry,'');
			$rec = $this->model->getEmpty();
			$rec['termDays'] = '';
			$result[] = $rec;
		}

		$cache = $this->registry->getObject('db')->cacheData( $result );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'DATA', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech aktivních úkolů.','DmsEntry','');
		// Zobrazení výsledku 
		$this->build('todo-list.tpl.php');
	}	
	
    /**
     * Zobrazení seznamu všech úkolů
     * @return void
     */
	public function listNote()
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		
		$userID = $this->registry->getObject('authenticate')->getUserID();
		
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
					",(DATE_FORMAT(RemindLastDate,'%Y-%m-%d') < CURDATE()) as term ".
					",(RemindLastDate - CURDATE()) as termDays ".
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"AND Type = 35 ".
				  	"ORDER BY RemindLastDate,Title ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		
		while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{			
			$this->model = new Entry( $this->registry, $rec['ID'] );
			$entry = $this->model->getData();
		
			$rec['dmsClassName'] = 'item';                
			$rec['DocumentType'] = $entry['DocumentType'];
			$rec['RemindState'] = $entry['RemindStateText'];
			$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
			$result[] = $rec;
		};

		if ($result == null){			
			$this->model = new Entry($this->registry,'');
			$rec = $this->model->getEmpty();
			$rec['termDays'] = '';
			$result[] = $rec;
		}

		$cache = $this->registry->getObject('db')->cacheData( $result );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'DATA', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech aktivních úkolů.','DmsEntry','');
		// Zobrazení výsledku 
		$this->build('todo-list.tpl.php');
	}	
	
    /**
     * Zobrazení seznamu VŠECH vyřízených úkolů, tj. položky s Připomenutím označené jako vyřízeno
     * @return void
     */
	private function listTodoClose()
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');

		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
					",(DATE_FORMAT(RemindLastDate,'%Y-%m-%d') < CURDATE()) as term ".
					",(RemindLastDate - CURDATE()) as termDays ".
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 AND RemindClose = 1 ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"ORDER BY RemindLastDate,Title ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		
		while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{			
			$this->model = new Entry( $this->registry, $rec['ID'] );
			$entry = $this->model->getData();
		
			$rec['dmsClassName'] = 'item';                
			$rec['DocumentType'] = $entry['DocumentType'];
			$rec['RemindState'] = $entry['RemindStateText'];
			$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
			$result[] = $rec;
		};

		if ($result == null){			
			$this->model = new Entry($this->registry,'');
			$rec = $this->model->getEmpty();
			$rec['termDays'] = '';
			$result[] = $rec;
		}

		$cache = $this->registry->getObject('db')->cacheData( $result );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'DATA', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech aktivních úkolů.','DmsEntry','');
		// Zobrazení výsledku 
		$this->build('todo-list.tpl.php');
		//$this->build('todo-list-close.tpl.php');
	}	
	
    /**
     * Zobrazení seznamu VŠECH vyřízených úkolů, tj. položky s Připomenutím označené jako vyřízeno
     * @return void
     */
	private function listMyTodoClose()
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$userID = $this->registry->getObject('authenticate')->getUserID();

		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
					",(DATE_FORMAT(RemindLastDate,'%Y-%m-%d') < CURDATE()) as term ".
					",(RemindLastDate - CURDATE()) as termDays ".
				  	"FROM ".$this->prefDb."DmsEntry ".
				  	"WHERE Archived = 0 AND RemindClose = 1 ".
				  	"AND PermissionSet <= $this->perSet ".
				  	"AND RemindUserID = '$userID' ".
				  	"ORDER BY RemindLastDate,Title ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		
		while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{			
			$this->model = new Entry( $this->registry, $rec['ID'] );
			$entry = $this->model->getData();
		
			$rec['dmsClassName'] = 'item';                
			$rec['DocumentType'] = $entry['DocumentType'];
			$rec['RemindState'] = $entry['RemindStateText'];

	
			$rec['editFileCardID'] = 'editFileCard'.$rec['ID'];					
		
			$result[] = $rec;
		};
		if ($result == null){			
			$this->model = new Entry($this->registry,'');
			$rec = $this->model->getEmpty();
			$rec['termDays'] = '';
			$result[] = $rec;
		}

		$cache = $this->registry->getObject('db')->cacheData( $result );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'DATA', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech aktivních úkolů.','DmsEntry','');
		// Zobrazení výsledku 
		$this->build('todo-list.tpl.php');
		//$this->build('todo-list-close.tpl.php');
	}	
	
    /**
     * Zobrazení seznamu VŠECH NOVÝCH položek
     * @return void
     */
	private function listNewDocuments( )
	{
		global $caption;		
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry,'' );
		$entry = $this->model->getData();
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$showFolder = false;
    	$sql = "SELECT EntryNo, ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
					",(RemindLastDate - CURDATE()) as termDays ".
				"FROM ".$this->prefDb."DmsEntry ".
			   	"WHERE Archived = 0  ".
					"AND PermissionSet <= $this->perSet ".
				"ORDER BY EntryNo DESC ";
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			
		$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', '' );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'SQL', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech aktivních úkolů.','DmsEntry','');
		// Zobrazení výsledku 
		$this->build('todo-list.tpl.php');
	}	

    /**
     * Zobrazení seznamu VŠECH ARCHIVNÍCH položek
     * @return void
     */
	private function listArchiveDocuments()
	{
		global $caption;		
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry,'' );
		$entry = $this->model->getData();
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$showFolder = false;
    	$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
				",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
				",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState ".	
				",(RemindLastDate - CURDATE()) as termDays ".
				"FROM ".$this->prefDb."DmsEntry ".
			   	"WHERE Archived = 1 AND Type = 30 ".
			   	"AND PermissionSet <= $this->perSet ".
			   	"ORDER BY Level,Parent,Type,LineNo" ;
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		   
		$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', '' );
		$this->registry->getObject('template')->getPage()->addTag( 'listTodo', array( 'SQL', $cache ) );
        $this->registry->getObject('template')->getPage()->addTag( 'controllerAction', 'listTodo' );

		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu všech archivních souborů a složek','DmsEntry','');
		// Zobrazení výsledku
		$this->build('todo-list.tpl.php');
	}	

	private function modifyInbox(){
		$InboxID = isset($_POST['InboxID']) ? $_POST['InboxID'] : 0;
		$Title = isset($_POST['Title']) ? $_POST['Title'] : 0;
		if ($InboxID){
			$data = array();
			$data['Title'] = $this->registry->getObject('db')->sanitizeData($Title);
			$data['Modified'] = 1;
			$condition = "InboxID = $InboxID";
			$this->registry->getObject('db')->updateRecords('inbox',$data,$condition);
		}
		$this->listInbox($InboxID);
	}

	public function countInboxNew(){
		$table = 'inbox';
		$sql = "SELECT count(*) as pocet FROM ".$this->prefDb.$table;
		$sql .= " WHERE Close = 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );	
		$this->registry->getObject('db')->findFirst( $cache );
		$result = $this->registry->getObject('db')->resultsFromCache( $cache );
		return $result['pocet'];				
	}

	public function refreshInbox()	
	{
		// DefaultAppPool => IIS AppPool\DefaultAppPool

		$inboxUrl = 'http://petbla:91/';
		$inboxRoot = 'c:/Temp/Sken/';

		$this->registry->getObject('log')->addMessage("Přihlášený uživatel: ".get_current_user(),'inbox','');

		if ($handle = opendir($inboxRoot)) { 
			while (false !== ($fileName = readdir($handle))) 
			{ 
				if ($fileName == '.' |0| $fileName == '..' | $fileName == 'web.config') 
			  	{ 
					continue; 
			  	};
			  	$fullItemPath = $inboxRoot.$fileName;
			  	$url = $inboxUrl.$fileName;
				
				// Windows Name
				$SourcePath  = $this->registry->getObject('fce')->ConvertToDirectorySeparator( $fullItemPath,false );    // formát Xxxxx\Ddddd\Aaaaa
				
				$this->registry->getObject('db')->initQuery('inbox');
				$SourcePath = $this->registry->getObject('db')->sanitizeData($SourcePath);
				$url = $this->registry->getObject('db')->sanitizeData($url);
				$this->registry->getObject('db')->setFilter('SourcePath',$SourcePath);
				if ($this->registry->getObject('db')->isEmpty()){
					$data = array();
					$data['SourcePath'] = $SourcePath;
					$data['SourceUrl'] = $url;
					$data['Title'] = $this->registry->getObject('db')->sanitizeData($fileName);
					$data['CreateDate'] = date('Y-m-d H:i');
					$this->registry->getObject('db')->insertRecords('inbox',$data);
				}		  			  	
			} 
			closedir($handle); 
		  } 
		$this->listInbox();
	}

    /**
     * Webová služba 
	 *  - mění stav položky typu "Připomenutí = ANO" na vyřešeno
	 * @param String $ID = ID položky DMSEntry
     * @return String = Návratová hodnota
	 *                  => OK    = zápis proběhl korektně
	 *                  => Error = zápis do logu skončil chybou
     */
	private function wsSetRemindEntry( $ID )
	{
		global $deb;
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );

		if( $this->model->isValid() )
		{
			$entry = $this->model->getData();
	
			if ($entry['Remind'] == '1')
			{
				$changes['Remind'] = '0';
				$changes['RemindClose'] = '1';
				$this->registry->getObject('log')->addMessage("Úkol vyřízen",'dmsentry',$ID);
				// Update
				$condition = "ID = '$ID'";
				$this->registry->getObject('db')->updateRecords('dmsentry',$changes, $condition);
			}
			return 'OK';
		}
		return 'Error';
	}	
	
}
?>