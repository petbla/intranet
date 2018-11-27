<?php
/**
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 */
class Documentcontroller{
	
	private $registry;
	private $model;
	

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
        $this->listDocuments('');
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
						$this->listDocuments($ID);
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
						$this->listDocuments('');
						break;		
				}
			}
			$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'Document/search');
		}
	}
	
	/**
	 * @return void
	 */
	private function documentNotFound()
	{
		//TOTO: doplnit šablonu
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-document.tpl.php', 'footer.tpl.php');
	}
	
	private function listDocuments( $ID )
	{
		global $config, $caption;

		// Find level
		$level = 0;
		if ($ID != '')
		{
			$sql = "SELECT level FROM DmsEntry WHERE ID = '{$ID}'";
			$this->registry->getObject('db')->executeQuery( $sql );			
			if( $this->registry->getObject('db')->numRows() != 0 )
			{
				$dmsEntry = $this->registry->getObject('db')->getRows();
				$level = $dmsEntry['level'];
			}
		}

    $sql = "SELECT title, type
							FROM DmsEntry AS d
							WHERE d.Archived = 0 AND 
										d.level={$level}";
		if ($level == 0)
		{
			$sql .= " AND d.Type <> 20"; 	// Not Directory od root
		}
		$sql .= " ORDER BY Level,Parent,Type,LineNo";
		
		$this->registry->getObject('document')->listDocuments($sql,'');
	}	
}
?>