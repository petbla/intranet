<?php

class Contactcontroller {

	private $registry;
	private $urlBits;
	
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $directCall == true )
		{
      		$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
		        $this->listContacts();
			}
			else
			{
				if( !isset( $urlBits[2] ) )
				{		
					$ID = '';
				}
				else
				{
					$ID = $urlBits[2];
				}
					switch( $urlBits[1] )
				{				
					case 'list':
						$this->listContacts();
						break;
					case 'view':
						//TOTO: doplnit
						break;
					case 'edit':
						//TOTO: doplnit
						break;
					case 'search':
						//TOTO: doplnit 
						break;
					default:				
						$this->listContacts();
						break;		
				}
			}
			$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'Contact/search');
		}
	}

	
	private function listContacts()
	{
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, ".
						"(SELECT GROUP_CONCAT( cg.GroupCode SEPARATOR ',' ) FROM contactgroups cg ".
						" WHERE cg.ContactID = c.ID) AS Groups ".
					"FROM Contact c ".
					"WHERE  Close=0 ".
					"ORDER BY c.FullName ";
		$isHeader = true;
		$isFooter = true;
		$pageLink = '';
		$this->listResult($sql, $pageLink, $isHeader, $isFooter );
	}

	private function listResult( $sql, $pageLink , $isHeader, $isFooter, $template = 'list-contact.tpl.php')
	{
		global $config, $caption;
        
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
			}
			$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
		}
        if ($perSet > 0)
        {
        }
        else
        {
        }
    }	
}
?>