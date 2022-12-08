<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    04.07.2019
 */
class Agendacontroller{
	
	private $registry;
	private $model;
	private $perSet;
	private $prefDb;

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
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
				$this->pageNotFound();
			}
			else
			{
				$ID = '';
				switch ($urlBits[1]) {
					case 'list':
						$TypeID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->listAgenda($TypeID);
						break;
					case 'add':
						$TypeID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->addAgenda($TypeID);
						break;
					case 'modify':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						//TODO $this->modifyAgenda($ID);
						break;
					case 'unlink':
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->unlinkAgenda($ID);
						break;
					case 'type':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;
						switch ($action) {
							case 'list':
								$this->listAgendaType();
								break;
							case 'add':
								$this->addAgendaType();
								break;
							case 'modify':
								$TypeID = isset($_POST["TypeID"]) ? $_POST["TypeID"] : '';
								if($TypeID !== ''){
									$this->modifyAgendaType( $TypeID );
								}else{
									$this->pageNotFound();
								}
								break;
							case 'delete':
								$TypeID = isset($urlBits[3]) ? $urlBits[3] : '';
								if($TypeID !== ''){
									$this->deleteAgendaType( $TypeID );
								}else{
									$this->pageNotFound();
								}
								break;
							default:
								$this->pageNotFound();
								break;
						}		
						break;				
					case 'WS':
						// Je voláno jako XMLHttpRequest (function.js) a pouze loguje zobrazené položky
						switch ($urlBits[2]) {
							case 'xxx':
								$ID = isset($urlBits[3]) ? $urlBits[3] : '';
								//$result = $this->wsLogDocumentView($ID);
								//exit($result);		
								break;
						}
						break;
					default:
						$this->pageNotFound();
						break;
				}
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
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-agenda.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');
	}

	/**
     * Zobrazení chybové stránky, pokud agenda nebyla nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámé agendy",'agenda','');
		$this->build('invalid-contact.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'agenda','');
			
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
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$urlBits = $this->registry->getURLBits();
		$typeID = isset( $urlBits[2]) ? ($urlBits[2] != 'list' ? $urlBits[2] : 0) : 0;

        $sql = "SELECT TypeID as idCat, Name as titleCat,
					IF(TypeID = '$typeID','active','') as activeCat 
					FROM ".$pref."agendatype";
        
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        $cacheCategory = $this->registry->getObject('db')->cacheQuery( $sql );
        $this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'SQL', $cache ) );
    }

    /**
     * Zobrazení položek agendy
	 * @param String $TypeID = ID agendy
     * @return void
     */
	private function listAgenda( $TypeID )
	{
		global $caption, $deb;		
		$sql = "SELECT a.ID,a.TypeID,a.DocumentNo,a.Description,a.EntryID,a.CreateDate,a.ExecuteDate,e.Name ".
					 "FROM ".$this->prefDb."agenda as a ".
					 "LEFT JOIN ".$this->prefDb."dmsentry as e ON a.EntryID = e.ID ".
					 "WHERE a.TypeID = $TypeID ".
					 "ORDER BY a.DocumentNo DESC ";

		$this->registry->getObject('db')->initQuery('agendatype');
		$this->registry->getObject('db')->setFilter('TypeID',$TypeID);
		if ($this->registry->getObject('db')->findFirst())
		{
			$agendatype = $this->registry->getObject('db')->getResult();
		}else{
			$this->pageNotFound();
			return;
		}
	 
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if($perSet > 0)
		{
			// Zobrazení výsledku
			$templateList = 'agenda-list.tpl.php';
			$templateCard = 'agenda-edit.tpl.php';

			$cache = $this->listResult( $sql );
			if($this->registry->getObject('db')->isEmpty( $cache )){
				$this->registry->getObject('template')->getPage()->addTag( 'ID', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'DocumentNo', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'Description', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'CreateDate', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'ExecuteDate', '' );				
			}else{
				$this->registry->getObject('template')->getPage()->addTag( 'AgendaList', array( 'SQL', $cache ) );
			}
			$this->registry->getObject('template')->getPage()->addTag( 'TypeID', $TypeID );				
			$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', $agendatype['Name'] );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditDocumentNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditDescription', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditCreateDate', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditExecuteDate', '' );				

			$this->registry->getObject('template')->addTemplateBit('editcard', $templateCard);
			$this->build($templateList);
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
	}

	/**
	 * Odstranení odkazu agendy na dokument (číslo jednací)
	 * @param string $ID = ID agendy
	 */
	private function unlinkAgenda($ID)
	{
		$this->registry->getObject('db')->initQuery('agenda');
		$this->registry->getObject('db')->setFilter('ID',$ID);
		if ($this->registry->getObject('db')->findFirst())
		{
			$agenda = $this->registry->getObject('db')->getResult();
			$TypeID = $agenda['TypeID'];
			$changes =  array();
			$changes['Description'] = '';
			$changes['EntryID'] = '';
			$condition = "ID = '$ID'";
			$this->registry->getObject('db')->updateRecords('agenda',$changes, $condition);
			$this->listAgenda($TypeID);
		}else{
			$this->pageNotFound();
		}
	}

    /**
     * Zobrazení seznam typů agend
     * @return void
     */
	private function listAgendaType( )
	{
		global $caption,$deb;		
    	$sql = "SELECT * FROM ".$this->prefDb."agendatype ";

		// Zobrazení výsledku
		$templateList = 'agenda-type-list.tpl.php';
		$cache = $this->listResult( $sql );
		if($this->registry->getObject('db')->isEmpty( $cache )){
			$this->registry->getObject('template')->getPage()->addTag( 'TypeID', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'Name', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'NoSeries', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'LastNo', '' );				
			$this->registry->getObject('template')->getPage()->addTag( 'editcard', '' );				
		}else{
			$this->registry->getObject('template')->getPage()->addTag( 'AgendaTypeList', array( 'SQL', $cache ) );
		}
		$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', '' );				
		$this->build($templateList);
	}

	/**
	 * Založení nového typu dokumentu
	 * @return void
	 */
	private function addAgenda( $TypeID )
	{
		global $config, $caption;

		require_once( FRAMEWORK_PATH . 'models/agenda/model.php');
		$this->model = new Agenda( $this->registry, '' );
		$this->model->initNew( $TypeID );
		$this->listAgenda( $TypeID );
	}	

	/**
	 * Založení nového typu dokumentu
	 * @return void
	 */
	private function addAgendaType( )
	{
		global $config, $caption;

		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Název musí být vyplněn!');
			return;
		};
		$NoSeries = isset($_POST['NoSeries']) ? $_POST['NoSeries'] : '';
		if ($Name == ''){
			$this->error('Číselná řada musí být vyplněna!');
			return;
		};

		$this->registry->getObject('db')->initQuery('agendatype');
		$this->registry->getObject('db')->setFilter('NoSeries',$NoSeries);
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->error("Kód číselné řady $NoSeries již existuje!");
			return;
		}

		$data = array();
		$data['Name'] = $Name;
		$data['NoSeries'] = $NoSeries;
		$this->registry->getObject('db')->insertRecords('agendatype',$data);
		$this->listAgendaType();
	}	

	
	/**
	 * Editace typu agendy
	 * @return void
	 */
	private function modifyAgendaType( $TypeID )
	{
		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Název musí být vyplněn!');
			return;
		};
		$NoSeries = isset($_POST['NoSeries']) ? $_POST['NoSeries'] : '';
		if ($Name == ''){
			$this->error('Číselná řada musí být vyplněna!');
			return;
		};
		$this->registry->getObject('db')->initQuery('agendatype');
		$this->registry->getObject('db')->setFilter('NoSeries',$NoSeries);
		$this->registry->getObject('db')->setCondition("NoSeries <> $TypeID");
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->error("Kód číselné řady $NoSeries již existuje!");
			return;
		}

		// Update
		$changes = array();
		$changes['Name'] = $Name;
		$changes['NoSeries'] = $NoSeries;
		$condition = "TypeID = '$TypeID'";
		$this->registry->getObject('db')->updateRecords('agendatype',$changes, $condition);
		$this->listAgendaType();
	}

	/**
	 * Výmaz typu agendy
	 * @return void
	 */
	private function deleteAgendaType( $TypeID )
	{
		if ($this->isAgendaTypeUsed( $TypeID ))
		{
			$NoSeries = $this->getNoSeries($TypeID);
			$this->error("Číselná řada $NoSeries se používá, nelze ji odstranit.");
			return;
		};
		
		$condition = "TypeID = ".$TypeID;
		$this->registry->getObject('db')->deleteRecords( 'agendatype', $condition, 1); 
		$this->listAgendaType();

	}

    /**
     * Test, zda byl typ již použitý - podle TypeID
     * @param int $TypeID - Maska číselné řady agendy
     * @return boolean 
     */
    function isAgendaTypeUsed( $TypeID ) 
    {
        $this->registry->getObject('db')->initQuery('agenda');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
        if($this->registry->getObject('db')->isEmpty())
            return false;
        return true;
    }

    /**
     * Vrací kód číselné řady
     * @param int $TypeID
     * @return varchar(20) $NoSeries
     */
    function getNoSeries( $TypeID ) 
    {
        $this->registry->getObject('db')->initQuery('agendatype');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
        if($this->registry->getObject('db')->findFirst()){
			$agendatype = $this->registry->getObject('db')->getResult();				
			return $agendatype['NoSeries'];
		};
        return '';
    }

	/**
	 * SQL dotaz přeskládán do listu položek v $cache
	 * @param string $sql - SELECT dotaz
	 * @return int $cache - index výsledku dotazu
	 */
	private function listResult( $sql )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if($perSet > 0)
		{
			// Group records by Page
			$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			return ( $cache );
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
    }	

}