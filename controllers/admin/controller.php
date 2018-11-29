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

		//$files = $this->registry->getObject('file')->getFiles($config['fileserver']);
		//$idx = 100;
		$this->registry->getObject('file')->addPath('Stavby\\Garáž-hasiči\\Hasičská garáž-individuální dotace ZK\\');

		$this->registry->getObject('template')->getPage()->addTag( 'root', $config['fileserver']);
		$this->registry->getObject('template')->getPage()->addTag( 'name', ($files[$idx]['name']));
		$this->registry->getObject('template')->getPage()->addTag( 'type', ($files[$idx]['type']));
		$this->registry->getObject('template')->getPage()->addTag( 'path', ($files[$idx]['path']));
		$this->registry->getObject('template')->getPage()->addTag( 'fileExtension', ($files[$idx]['fileExtension']));
		$this->registry->getObject('template')->getPage()->addTag( 'level', ($files[$idx]['level']));
		$this->registry->getObject('template')->getPage()->addTag( 'title', ($files[$idx]['title']));

		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'admin.tpl.php', 'footer.tpl.php');

	}
}
?>