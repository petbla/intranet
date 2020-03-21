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
						switch ($action) {
							case 'list':
								$this->listAgendaType();
								break;
							case 'add':
								$TypeID = isset($urlBits[3]) ? $urlBits[3] : '';
								$this->addAgendaType( $TypeID );
								break;
							case 'modify':
								$TypeID = isset($urlBits[3]) ? $urlBits[3] : '';
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
     * Zobrazení chybové stránky, pokud agenda nebyla nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámé agendy",'agenda','');
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page-notfound.tpl.php', 'footer.tpl.php');
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
		// Nastavení parametrů
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení stránky
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
	}

    /**
     * Zobrazení položek agendy
	 * @param String $TypeID = ID agendy
     * @return void
     */
	private function listAgenda( $TypeID )
	{
		global $caption;		
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
	 
		// Zobrazení výsledku
		$templateList = 'list-agenda.tpl.php';
		$templateCard = 'edit-agenda.tpl.php';
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
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $templateList, 'footer.tpl.php');			
		$this->registry->getObject('template')->addTemplateBit('editcard', $templateCard);
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
			return;
		}
	}

    /**
     * Zobrazení seznam typů agend
     * @return void
     */
	private function listAgendaType( )
	{
		global $caption;		
    	$sql = "SELECT * FROM ".$this->prefDb."agendatype ";
		
		// Zobrazení výsledku
		$templateList = 'list-agenda-type.tpl.php';
		$templateCard = 'edit-agenda-type.tpl.php';
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
		$this->registry->getObject('template')->getPage()->addTag( 'EditName', '' );
		$this->registry->getObject('template')->getPage()->addTag( 'EditNoSeries', '' );
		$this->registry->getObject('template')->getPage()->addTag( 'EditTypeID', '' );
		$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', '' );
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $templateList, 'footer.tpl.php');			
		$this->registry->getObject('template')->addTemplateBit('editcard', $templateCard);
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
	private function addAgendaType( $TypeID )
	{
		global $config, $caption;

		require_once( FRAMEWORK_PATH . 'models/agenda/model.php');
		$this->model = new Agenda( $this->registry, '' );

		$data = array();
		$data['Name'] = isset($_POST['Name']) ? $_POST['Name'] : '';
		$data['NoSeries'] = isset($_POST['NoSeries']) ? $_POST['NoSeries'] : '';
		$data['TypeID'] = isset($_POST['TypeID']) ? $_POST['TypeID'] : '';
		$this->newAgendaType( $data );
		$this->listAgendaType();
	}	

    /**
     * Globální funkce pro založení nového typu agendy
     * @param $data - pole nového záznamu
     * @return boolean $success - výsledek založení nového záznamu
     */
    function newAgendaType( $data )
    {
		global $config;
        $pref = $config['dbPrefix'];

        $Name = isset($data['Name']) ? $data['Name'] : '';
        if (($Name == '') && ($data['TypeID'] == ''))
            return false;   
        
        $this->registry->getObject('db')->initQuery('agendatype');
        $this->registry->getObject('db')->setFilter('TypeID',$data['TypeID']);
        if (($data['TypeID'] !== "") && ($this->registry->getObject('db')->findFirst())){
            $TypeID = $data['TypeID'];
            
            // Update
            $changes = array();
            $changes['Name'] = $data['Name'];
            $changes['NoSeries'] = $data['NoSeries'];
            $condition = "TypeID = '$TypeID'";
            $this->registry->getObject('db')->updateRecords('agendatype',$changes, $condition);
    
        }else{
            // Insert New
            unset($data['TypeID']);
            $this->registry->getObject('db')->initQuery('agendatype');
            $this->registry->getObject('db')->setFilter('NoSeries',$data['NoSeries']);
            if($this->registry->getObject('db')->isEmpty()){
                $this->registry->getObject('db')->insertRecords('agendatype',$data);
                return true;
            }
            return false;
        }
    }
	
	/**
	 * Editace typu agendy
	 * @return void
	 */
	private function modifyAgendaType( $TypeID )
	{
        $this->registry->getObject('db')->initQuery('agendatype');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
		if ($this->registry->getObject('db')->findFirst())
		{
			$agendaType = $this->registry->getObject('db')->getResult();
			
			$this->registry->getObject('template')->getPage()->addTag( 'EditName', $agendaType['Name'] );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditNoSeries', $agendaType['NoSeries'] );				
			$this->registry->getObject('template')->getPage()->addTag( 'EditTypeID', $agendaType['TypeID'] );				
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'edit-agenda-type.tpl.php', 'footer.tpl.php');			
		}else{
			$this->pageNotFound();
		}
		return;
	}

	/**
	 * Výmaz typu agendy
	 * @return void
	 */
	private function deleteAgendaType( $TypeID )
	{
		$condition = "TypeID = ".$TypeID;
		$this->registry->getObject('db')->deleteRecords( 'agendatype', $condition, 1); 
		$this->listAgendaType();
	}

    /**
     * Test, zda byla již číselná řada použita
     * @param int $NoSeries - Maska číselné řady agendy
     * @return boolean - info, zda byla číselná řada použita 
     */
    function isAgendaTypeUsed( $NoSeries ) 
    {
        $this->registry->getObject('db')->initQuery('agenda');
        $this->registry->getObject('db')->setFilter('NoSeries',$NoSeries);
        if($this->registry->getObject('db')->isEmpty())
            return false;
        return true;
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
			// Stránkování
			$cacheFull = $this->registry->getObject('db')->cacheQuery( $sql );
			$records = $this->registry->getObject('db')->numRowsFromCache( $cacheFull );
			$pageCount = (int) ($records / $config['maxVisibleItem']);
			$pageCount = ($records > $pageCount * $config['maxVisibleItem']) ? $pageCount + 1 : $pageCount;  
			$pageNo = ( isset($_GET['p'])) ? $_GET['p'] : 1;
			$pageNo = ($pageNo > $pageCount) ? $pageCount : $pageNo;
			$pageNo = ($pageNo < 1) ? 1 : $pageNo;
			$fromItem = (($pageNo - 1) * $config['maxVisibleItem']);    
			$navigate = $this->registry->getObject('template')->NavigateElement( $pageNo, $pageCount ); 
			$this->registry->getObject('template')->getPage()->addTag( 'navigate_menu', $navigate );
			$sql .= " LIMIT $fromItem," . $config['maxVisibleItem']; 
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );

			// Search BOX
			$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');

			return ( $cache );
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
    }	

}