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