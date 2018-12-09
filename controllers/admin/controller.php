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

		// TEST
//		$this->registry->getObject('db')->initQuery('DmsEntry');
//		$this->registry->getObject('db')->setFilter('LineNo',100);
//		$this->registry->getObject('db')->setRange('Level',1,2);
//		$this->registry->getObject('db')->setOrderBy('EntryNo');
//		if ($this->registry->getObject('db')->findFirst())
//		$ss = $this->registry->getObject('db')->isEmpty();
//		if ($this->registry->getObject('db')->findSet())
//		{
//			$result = $this->registry->getObject('db')->getResult();
//		}
		
		$file = '_K vyřízení';
		$file = iconv("utf-8","windows-1250",$file);
		$handle = opendir($config['fileserver'].$file);


		$files = $this->registry->getObject('file')->getFiles($config['fileserver']);
		$idx = 10;
		
//		$this->registry->getObject('file')->addPath('Projekty\\Dětská hřiště RZM\\Foto\\Náves\\');

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