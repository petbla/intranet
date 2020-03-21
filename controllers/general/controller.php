<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Generalcontroller {

	private $registry;
	private $urlBits;
	
	public function __construct( Registry $registry, $directCall )
	{
		global $caption;

		$this->registry = $registry;
		$perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if( $directCall == true )
		{
      		$urlBits = $this->registry->getURLBits();     
			
			if($perSet == 0)
			{
				$this->registry->getObject('log')->addMessage($caption['msg_unauthorized'],'contact','');
				// Search BOX
				$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
				return;
			}

			if( isset( $urlBits[1] ) )
			{
				switch( $urlBits[1] )
				{				
					case 'searchContact':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchContacts($searchText);
							return;
						}
						break;
					case 'searchItem':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchDocuments($searchText);
							return;
						}
						break;
				}
			}
			$this->notFound();
		}
	}

    /**
     * Zobrazení chybové stránky, pokud dokument nebyl nalezem 
     * @return void
     */
	private function notFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámého obsahu",'dmsentry','');
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-page.tpl.php', 'footer.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'contact','');
		// Nastavení parametrů
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
	}

	/**
	 * Zobrazení požadovaného seznamu dokumentů, které současně 
	 * zajistí zobrazení stránkování s možností výběru stránek a listování
	 * @param String $sql = sestavený kompletní SQL dotaz
	 * @param String $pageLink
	 * @param Boolean $isHeader
	 * @param Boolean $isFooter 
	 * @param String $template
	 * @return void
	 */
	private function listContactResult( $sql, $pageLink , $isHeader, $isFooter, $template = 'list-contact.tpl.php')
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        
		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, '' );
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
				$this->registry->getObject('template')->getPage()->addTag( 'ContactList', array( 'SQL', $cache ) );
				$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
				$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', '' );

				$this->registry->getObject('template')->addTemplateBit('editcard', 'list-contact-editcard.tpl.php');
				$this->registry->getObject('template')->addTemplateBit('editIcon', 'list-contact-editicon.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag( 'dmsClassName', 'contact' );
				$this->registry->getObject('template')->getPage()->addTag( 'ID', 'newcontact' );
				$this->registry->getObject('template')->getPage()->addTag( 'Address', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'Note', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'ContactGroups', '' );
	
				$cache2 = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
				$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache2) );
					
				$this->registry->getObject('log')->addMessage("Zobrazení seznamu kontaktů",'Contact','');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
			}
			else
			{
				$this->registry->getObject('template')->addTemplateBit('editcard', 'list-contact-editcard.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag( 'dmsClassName', 'contact' );
				$this->registry->getObject('template')->getPage()->addTag( 'ID', 'newcontact' );
				$this->registry->getObject('template')->getPage()->addTag( 'Address', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'Note', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'ContactGroups', '' );
				
				$cache2 = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
				$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache2) );
				
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-contact-empty.tpl.php', 'footer.tpl.php');			
			}
			$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', '' );

			// Search BOX
			$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');

		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
    }	

	private function searchContacts( $searchText )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$searchText = htmlspecialchars($searchText);
		$searchText = str_replace('*','',$searchText);
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, c.Note, c.ContactGroups ".
				"FROM ".$pref."Contact c ".
				"WHERE Close = 0 ".
				"AND MATCH(FullName,Function,Company,Address,Note,Phone,Email,ContactGroups) AGAINST ('*".$searchText."*' IN BOOLEAN MODE) ".
				"ORDER BY FullName";
		$isHeader = true;
		$isFooter = true;
		$pageLink = '';

		$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', $searchText );
		// Logování
		$this->registry->getObject('log')->addMessage("Zobrazení vyhledaných kontaktů `$searchText`",'Contact','');
		// Zobrazení seznamu
		$this->listContactResult($sql, $pageLink, $isHeader, $isFooter );
	}	

    /**
     * Zobrazení seznamu vyhledaných položek dle hledaného řetězce
	 * @param String $searchText = maska hledaných položek
	 * @return void
     */
	private function searchDocuments( $searchText )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry,'' );
		$entry = $this->model->getData();
		$this->registry->setLevel(0);
		$this->registry->setEntryNo(0);
		$showFolder = false;
		$searchText = htmlspecialchars($searchText);
		$searchText = str_replace('*','',$searchText);
		$sql = "SELECT ID,Title,Name,Type,Url,Parent,ModifyDateTime,LOWER(FileExtension) as FileExtension ".
					",IF(Remind=0,'0','1') as Remind,IF(RemindClose=0,'0','1') as RemindClose,RemindFromDate,RemindLastDate".
					",Content,RemindResponsiblePerson,RemindUserID,RemindContactID,RemindState,Path ".	
					"FROM ".$pref."DmsEntry ".
					"WHERE Archived = 0 AND Type IN (20,25,30,35) ".
					//"AND MATCH(Title) AGAINST ('*".$searchText."*' IN BOOLEAN MODE) ".
					"AND Title like '%".$searchText."%' ".
					"AND PermissionSet <= $perSet ".
					"ORDER BY Remind DESC,Title ";
		$showBreads = false;
		$pageTitle = '<h3>'.$caption['Archive'].'</h3>';
		$template = 'list-entry-resultsearch.tpl.php';
		$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', $searchText );
		// Logování
		$this->registry->getObject('log')->addMessage('Zobrazení seznamu souborů a složek','DmsEntry','');
		// Zobrazení seznamu
		$this->registry->getObject('document')->listDocuments($entry,$showFolder,$sql,$showBreads,$pageTitle,$template);
	}	

}
?>