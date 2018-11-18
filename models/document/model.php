<?php
/**
 * Class Document
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 */

class Document{
	
	private $registry;
	private $name;
	private $categoryId;
	private $active;
	
	public function __construct( Registry $registry, $id )
	{
    
    $this->registry = $registry;
		if( $documentPath != '' )
		{
			$documentPath = $this->registry->getObject('db')->sanitizeData( $id );
		
			$sql = "SELECT id,name,close,categoryId
                FROM katalog
                WHERE  id=$id AND close=0";

      $this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				
				$this->activedocument = true;
			  $data = $this->registry->getObject('db')->getRows();
				
				$this->name = $data['name'];
				$this->categoryId = $data['categoryId']; 
				$this->active = 1;
				
		  }
		}
		else
		{
			// zde můžeme chtít provést něco jiného...
		}
	}

	public function isValid()
	{
		return $this->activedocument;
	}
	
	public function getData()
	{
		$data = array();
		foreach( $this as $field => $fdata )
		{
			if( !is_object( $fdata ) )
			{
				$data[ $field ] = $fdata;
			}
		}
		return $data;
	}
}
?>