<?php

class Admincontroller {

	private $registry;
	private $urlBits;
	
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $directCall == true )
		{
      		$urlBits = $this->registry->getURLBits();     
			if( isset( $urlBits[1] ) )
			{		
				switch( $urlBits[1] )
				{				
					case 'update':
						$this->updateDmsStore();
						break;
				}
			}
		}
	}

	
	private function updateDmsStore()
	{
		global $caption, $config;
	    $this->urlBits = $this->registry->getURLBits();
		$files = $this->registry->getObject('file')->updateFiles($config['fileserver']);
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin.tpl.php', 'footer.tpl.php');
	}
}
?>