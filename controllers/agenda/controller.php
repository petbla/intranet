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
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->listAgenda($ID);
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
								$this->modifyAgendaType( $TypeID );
								break;
							case 'delete':
								$TypeID = isset($urlBits[3]) ? $urlBits[3] : '';
								$this->deleteAgendaType( $TypeID );
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
		// Sestavení stránky
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
	}

    /**
     * Zobrazení položek agendy
	 * @param String $ID = ID agendy
     * @return void
     */
	private function listAgenda( $ID )
	{
		$this->error('TODO: listAgenda');
    }

    /**
     * Zobrazení seznam typů agend
     * @return void
     */
	private function listAgendaType( )
	{
		global $caption;		
    	$sql = "SELECT * FROM ".$this->prefDb."agendatype ";
		$pageTitle = '<h3>'.$caption['Agenda'].'</h3>';
		$template = '';
		// Zobrazení výsledku
		$this->listResult( $sql, '', 'list-agenda-type.tpl.php' );	
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
		$this->model->newAgendaType( $data );
		$this->listAgendaType();
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
		$this->error('TODO: deleteAgendaType');
	}

	private function listResult( $sql, $pageLink, $template)
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
			if (!$this->registry->getObject('db')->isEmpty( $cache )){
				$this->registry->getObject('template')->getPage()->addTag( 'AgendaTypeList', array( 'SQL', $cache ) );
				$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
			}else{
				$this->registry->getObject('template')->getPage()->addTag( 'Name', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'NoSeries', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'LastNo', '' );				
				$this->registry->getObject('template')->getPage()->addTag( 'editcard', '' );				
			}
			$this->registry->getObject('template')->getPage()->addTag( 'EditName', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'EditNoSeries', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'EditTypeID', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', '' );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
			$this->registry->getObject('template')->addTemplateBit('editcard', 'edit-agenda-type.tpl.php');

			// Search BOX
			$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
    }	

}