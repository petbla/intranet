<?php
/**
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    26.11.2018
 */
class Newscontroller{
	
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
					default:				
						$this->listDocuments('');
						break;		
				}
			}
		}
	}
		
	private function listDocuments( $ID )
	{
		global $config, $caption;

    $sql = "SELECT title,type
							FROM DmsEntry AS d
							WHERE NewEntry = 1 
							ORDER BY Level,Parent,Type,LineNo";

		$this->registry->getObject('document')->listDocuments($sql,'Nově přidané dokumenty');
	}	
}
?>