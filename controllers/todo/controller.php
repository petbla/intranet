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
	private $message;
	private $errorMessage;

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
							case 'folder':
								$action = isset($urlBits[3]) ? $urlBits[3] : '';
								$action = isset($_POST['storno']) ? 'storno' : $action;
								$action = isset($_POST['add']) ? 'add' : $action;
								$InboxID = isset($urlBits[4]) ? $urlBits[4] : '';
								$InboxID = isset($_POST['InboxID']) ? $_POST['InboxID'] : $InboxID;
								$ParentID = isset($urlBits[5]) ? $urlBits[5] : '';
								$ParentID = isset($_POST['ID']) ? $_POST['ID'] : $ParentID;		
								$this->inboxMoveToFolder($action, $InboxID, $ParentID);
								break;
							case 'refresh':
								$this->inboxRefresh();
								break;
							case 'modify':
								$this->inboxModify();
								break;
							case 'close':
								$this->inboxList(0,true);
								break;
							default:
								$this->inboxList();
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

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

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
		$this->error("Pokus o zobrazení neznámého obsahu.");
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
		$this->errorMessage = $message;
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
		$action .= isset( $urlBits[2]) ? '/'.$urlBits[2] : '';

		$countInboxNew = $this->countInboxNew();

		$rec['idCat'] = 'inbox';
		if($countInboxNew)
			$rec['titleCat'] = "<b>Doručená pošta ($countInboxNew)</b>";
		else
			$rec['titleCat'] = "Doručená pošta ($countInboxNew)";
		$rec['activeCat'] = $rec['idCat'] == $action ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'inbox/close';
		$rec['titleCat'] = "Vyřízená pošta";
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

	private function getDmsentryByID($ID)
	{
		$entry = null;
        if($ID != ''){
			$this->registry->getObject('db')->initQuery('dmsentry');
			$this->registry->getObject('db')->setFilter('ID',$ID);
			if ($this->registry->getObject('db')->findFirst())
				$entry = $this->registry->getObject('db')->getResult();			
		}
		return $entry;
	}

	private function getDmsentry($EntryNo)
	{
		$entry = null;
        if($EntryNo != 0){
			$this->registry->getObject('db')->initQuery('dmsentry');
			$this->registry->getObject('db')->setFilter('EntryNo',$EntryNo);
			if ($this->registry->getObject('db')->findFirst())
				$entry = $this->registry->getObject('db')->getResult();			
		}
		return $entry;
	}

	private function getInbox($InboxID)
	{
		$inbox = null;
		$this->registry->getObject('db')->initQuery('inbox');
		$this->registry->getObject('db')->setFilter('InboxID',$InboxID);
		if ($this->registry->getObject('db')->findFirst())
			$inbox = $this->registry->getObject('db')->getResult();			
		return $inbox;
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

	/**
	 * Editace z FORMuláře
	 * 
	 * @param $_POST['InboxID'] - id položky dokumentu v inboxu - povinné pole
	 * @param $_POST['Title'] - název položky
	 * @param $_POST['SettlementType'] - způsob vyřízení, určuje, kam se má přesunout dokument
	 *        - Rada, Zastupitelstvo, .... (typy jednání v ZOB)
	 *        - Úkol ..... zařazení do úkolů, podmínkou je předunutý dokument (vyplněno pole $DmsEntryID)
	 *        - Storno ... dokument bude odstraněn (pokud nebyl již přesunut do jiné složky) a označen jako vyřízený
	 */
	private function inboxModify(){
		global $config;
		
		// Načtení položky inboxu
		$InboxID = isset($_POST['InboxID']) ? (int) $_POST['InboxID'] : 0;
		if($InboxID == 0){
			$this->errorMessage = 'Nebylo nalezeno ID inboxu, položku doručené pošty nelze identifikovat.';
			$this->build();
			return;
		}
		$inbox = $this->getInbox($InboxID);

		// Data z formuláře
		$post = $_POST;
		$Title = isset($_POST['Title']) ? $_POST['Title'] : $inbox['Title'];
		$SettlementType = isset($_POST['SettlementType']) ? $_POST['SettlementType'] : '';
		
		// pole změn položky $inbox 
		$change = array();

		// Provedení akce k vyřízení dokumentu
		if($SettlementType != ''){
			// Zařazení dokumentu do příloh jednání
			if($inbox['MeetingID'] == 0){
				require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
				$zob = new Zobcontroller( $this->registry, true );
			
				$meeting = $zob->getActualMeeting($SettlementType);					
				if($meeting){
					// Pokud dokument nebyl ještě zařazen do složky, tak se nyní přesune do 
					// výchozí složky podkladů jednání
					// <rootZOB>/_<MeetingName>/<PeriodName>/<EntryNo>/Přílohy
					// Příklad: Obecní úřad/_Rada/2018-2022/10/Přílohy					 
					if($inbox['DmsEntryID'] == '00000000-0000-0000-0000-000000000000'){
						
						$meetingtype = $zob->getMeetingtype($meeting['MeetingTypeID']);
						$electionperiod = $zob->getElectionperiod($meetingtype['ElectionPeriodID']);
						$parentFolder = $config['rootZOB']."/_".$meetingtype['MeetingName']."/";
						$parentFolder .= $electionperiod['PeriodName']."/".$meeting['EntryNo']."/Přílohy";
						$DmsParentEntryNo = $this->registry->getObject('file')->findItem( $parentFolder, true );
						if($DmsParentEntryNo == 0 ){
							$this->errorMessage = "Složka $parentFolder nebyla vytvořena, přesun dokumentu nelze dokončit.";
							$this->build();
							return;
						}

						//Přesun souboru do složky
						$this->inboxMoveToFolder('set', $InboxID , $DmsParentEntryNo );
						$inbox = $this->getInbox($InboxID);
						if($inbox['DmsEntryID'] == '00000000-0000-0000-0000-000000000000'){
							$this->errorMessage = "Dokument se nepodařilo přesunout.";
							$this->build();
							return;
						}
					}

					// Zápis odkazu na jednání do položky inboxu
					$change['MeetingID'] = $meeting['MeetingID'];
					$inbox['MeetingID'] = $meeting['MeetingID'];    // pro test, zde je položka kompletně vyřízena
					
					// Vytvoření položky přílohy jednání
					$meetingattachment = array();
					$meetingattachment['MeetingID'] = $meeting['MeetingID'];
					$meetingattachment['Description'] = $Title;
					$meetingattachment['MeetingLineID'] = 0;
					$meetingattachment['InboxID'] = $InboxID;
					$this->registry->getObject('db')->insertRecords('meetingattachment',$meetingattachment);

				}else{
					$this->errorMessage = 'Nebyl nalezen aktivní zápis jednání $SettlementType. Vytořte zápis ručně a akci opakujte.';
					$this->build();
					return;
				}
			}else{
				// Byl vybrán způsob vyřízení, který není typu ZOB jednání 		
				switch ($SettlementType) {
					case 'Úkol':
						$this->message = "Přesun do úkolů není aktivní.";
						$this->inboxList($InboxID);
						return;	
					case 'Storno':
						$this->message = "STORNO - označní dokladů jako neprojednávaného není aktivní.";
						$this->inboxList($InboxID);
						return;	
				}
			}
		}

		// Editace názvu
		$change['Title'] = $this->registry->getObject('db')->sanitizeData($Title);
		$change['Modified'] = 1;

		// Test, zda bylo vyplněn způsob vyřízení a dokument byl přesunut
		if(($inbox['DmsEntryID'] != '00000000-0000-0000-0000-000000000000') && ($inbox['MeetingID'] > 0)){
			$change['Close'] = 1;
			$this->message = "Dokument byl přesunut do vyřízených..";
		}

		// Uložení změn
		$condition = "InboxID = $InboxID";
		$this->registry->getObject('db')->updateRecords('inbox',$change,$condition);

		// Synchronizaci názvu z inboxu 
		if($change['Title'] != $inbox['Title']){
			
			//  Do tabulky příloh jednání
			if ($inbox['MeetingID'] > 0){
				$meetingattachment = array();
				$meetingattachment['Description'] = $change['Title'];
				$this->registry->getObject('db')->updateRecords('meetingattachment',$meetingattachment,$condition);
			}

			// Do tabulky dokumentů
			if($inbox['DmsEntryID'] != '00000000-0000-0000-0000-000000000000'){
				$entry = array();
				$entry['Title'] = $change['Title'];
				$condition = "ID = '".$inbox['DmsEntryID']."'";
				$this->registry->getObject('db')->updateRecords('dmsentry',$entry,$condition);
			}
		}
		$this->inboxList($InboxID);
	}

    /**
     * Zobrazení doručené pošty
     * @return void
     */
	public function inboxList($activeInboxID = 0, $close = 0)
	{
		global $config;

		require_once( FRAMEWORK_PATH . 'controllers/zob/controller.php');
		$zob = new Zobcontroller( $this->registry, true );
		
		$sql = "SELECT * FROM ".$this->prefDb."inbox ".
				  	"WHERE Close = $close ".
				  	"ORDER BY CreateDate DESC ";

		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$result = null;
		while( $inbox = $this->registry->getObject('db')->resultsFromCache( $cache ) )
		{				
			
			$inbox['isfolder'] = 'no';	
			$inbox['DmsentryName'] = '';
			$inbox['ParentID'] = '';
			$inbox['ismodify'] = $inbox['Modified'] == 0 ? 'no' : '';
			if($inbox['DmsEntryID'] != '00000000-0000-0000-0000-000000000000'){
				$inbox['isfolder'] = '';
				$inbox['SourceUrl'] = $inbox['DestinationPath'];	
				$entry = $this->getDmsentryByID($inbox['DmsEntryID']);
				if($entry){
					$entry = $this->getDmsentryByID($inbox['DmsEntryID']);
					$inbox['DmsentryName'] = $entry['Name'];
					$parententry = $this->getDmsentry($entry['Parent']);					
					if($parententry)
						$inbox['ParentID'] = $parententry['ID'];
				}
			}
			
			$MeetingID = $inbox['MeetingID'];
			$inbox['ismeeting'] = $MeetingID == 0 ? 'no' : '';
			$inbox['MeetingNo'] = $zob->getMeetingNo($MeetingID,true);
			$inbox['CreateDate'] = $this->registry->getObject('core')->formatDate($inbox['CreateDate'],'d.m.Y H:i');
			$inbox['SelectMeetingTypeID'] = '';
			if($inbox['MeetingID']){
				$meeting = $zob->getMeeting($inbox['MeetingID']);
				if($meeting){
					$meetingtype = $zob->getMeetingtype($meeting['MeetingTypeID']);
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
			$this->registry->getObject('template')->getPage()->addTag( 'MeetingNo', '' );
		}
		$this->registry->getObject('template')->getPage()->addTag( 'activeInboxID', $activeInboxID );					
		$this->registry->getObject('template')->addTemplateBit('inboxCard', 'todo-inbox-edit.tpl.php');

		$this->build('todo-inbox-list.tpl.php');
	}	

	public function inboxMoveToFolder($action, $InboxID , $DmsParentEntryNo )	
	{
		// Najít ParentFolder - pokud byl zadán - a určit level složky pro výběr
		$parentPath = '';
		if ($DmsParentEntryNo != ''){
			$parententry = $this->getDmsentry($DmsParentEntryNo );
			if( $parententry ){
				$parentPath .= $parententry['Name'];
			}				
		}
		$breads = str_replace("\\"," => ",$parentPath);

		// Seznam složek pro výběr
		$folders = array();
		$dmsentry = $this->registry->getObject('document')->readFolders($DmsParentEntryNo);
		if($dmsentry){
			foreach ($dmsentry as $entry){
				$rec['ID'] = $entry['ID'];
				$rec['Name'] = $entry['Name'];
				$rec['Title'] = $entry['Title'];
				$folders[] = $rec;
			}
		}else{
			$rec['ID'] = '';
			$rec['Name'] = '<NONE>';
			$rec['Title'] = '<NONE>';
			$folders[] = $rec;
		}

		// Vložení do Page
		$cache =  $this->registry->getObject('db')->cacheData($folders);
		$this->registry->getObject('template')->getPage()->addTag( 'listFolder', array( 'DATA', $cache ) );

		$post = $_POST;
		switch ($action) {
			case 'storno':
				# Bez zápisu
				break;
			case 'select':
				# code...
				$this->inboxMoveToFolder('list',$InboxID, $DmsParentEntryNo );
				return;
			case 'add':
				$ParentID = isset($_POST['ParentID']) ? $_POST['ParentID'] : 0;
				$name = isset($_POST['newFolder']) ? $_POST['newFolder'] : '';
				
				if ($name != ""){
					//TODO: založení nové složky
					
					// Virtuální položka "dmsentry"
					//$fullname = "$parentPath\\$name"; 
					//$entry = $this->registry->getObject('file')->getItemFromName( $fullname , $isDir = false);
					//TODO: Zápis $entry do tabulky dmsentry
				}
				break;			
			case 'set':
				$inbox = $this->getInbox($InboxID);
				if($inbox){
					$filename = $inbox['Title'];
					$title = $inbox['Title'];
					$entry = $this->registry->getObject('file')->addFile($inbox['SourcePath'], $DmsParentEntryNo, $filename, $title);
					if($entry == null){
						$this->errorMessage = $this->registry->getObject('file')->getLastError();
					}else{
						$change = array();
						$change['DmsEntryID'] = $entry['ID'];
						$change['DestinationPath'] = $entry['DestinationPath'];
						$condition = "InboxID = $InboxID";
						$this->registry->getObject('db')->updateRecords('inbox',$change,$condition);
					}
				}
				break;			
			default:
				# 'list'
				# Zobrazení stránky pro výběr složky
				$this->registry->getObject('template')->getPage()->addTag( 'InboxID', $InboxID );							
				$this->registry->getObject('template')->getPage()->addTag( 'ParentID', $DmsParentEntryNo );							
				$this->registry->getObject('template')->getPage()->addTag( 'breads', $breads );							
				$this->build('todo-inbox-choicefolder.tpl.php');
				return;
		}
		$this->inboxList($InboxID);		
	}
	
	public function inboxRefresh()	
	{
		// DefaultAppPool => IIS AppPool\DefaultAppPool

		//TODO: změnit nastavení
		$inboxUrl = 'http://petbla:91/';
		$inboxRoot = 'c:/Temp/Sken/';

		$this->registry->getObject('log')->addMessage("Přihlášený uživatel: ".get_current_user(),'inbox','');

		$this->registry->getObject('db')->initQuery('inbox');
		$this->registry->getObject('db')->setfilter('Close',0);
		$this->registry->getObject('db')->setCondition("DestinationPath = ''");
		if($this->registry->getObject('db')->findSet()){
			$inboxes = $this->registry->getObject('db')->getResult();
			foreach($inboxes as $inbox){
				if(!file_exists($inbox['SourcePath'])){
					$change = array();
					$change['Close'] = 1;
					$condition = "InboxID = ".$inbox['InboxID'];
					$this->registry->getObject('db')->updateRecords('inbox',$change,$condition);
				}
			}
		}

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
		$this->inboxList();
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