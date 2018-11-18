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
	
  //  příkaz SQL pro filtrované produkty
	private $filterSQL = '';
	
	/**
	 * Konstruktor řadiče – parametr directCall má hodnotu false, pokud konstruktor volá jiný řadič 
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();
      $this->filterDocuments( $urlBits );
      
      // ..index.php?page=document/view/10
			if( !isset( $urlBits[1] ) )
			{		
        // voláno pouze ..index.php?page=document  NEBO  ..index.php
				// NIC neprovede
				$this->DocumentNotFound();
			}
			else
			{
				switch( $urlBits[1] )
				{
					case 'view':
						$this->viewDocument();
						break;
					default:				
						$this->viewDocument();
						break;		
				}
			}
  		$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'Documents/search');
		}
	}
	
	/**
	 * Zobraz dokument
	 * @return void
	 */
	private function viewDocument()
	{
		global $caption;
		
		if( !isset( $urlBits[2] ) )
		{
			$this->DocumentNotFound();
		}

		require_once( FRAMEWORK_PATH . 'models/Document/model.php');
		$this->model = new Document( $this->registry, $urlBits[2] );
		if( $this->model->isValid() )
		{
			$DocumentData = $this->model->getData();
			
			$this->registry->getObject('template')->getPage()->addTag( 'name', $DocumentData['name'] );
			$this->registry->getObject('template')->getPage()->setTitle($caption['Document'] . ': ' . $DocumentData['name'] );
			
      $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'Document.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->DocumentNotFound();
		}
	}
	
	/**
	 * Zobraz stránku o neplatném produktu
	 * @return void
	 */
	private function DocumentNotFound()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-Document.tpl.php', 'footer.tpl.php');
	}

}
?>