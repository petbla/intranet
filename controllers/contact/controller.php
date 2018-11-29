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
		global $caption;
  			
    	$this->urlBits = $this->registry->getURLBits();

		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-contact.tpl.php', 'footer.tpl.php');
	}
}
?>