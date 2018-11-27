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
	private $Level;
	private $Parent;
	private $Type;
	private $LineNo;
	private $Title;
	private $Name;
	private $Path;
	private $FileExtension;
	private $CreateDate;
	private $Archived;
	private $NewEntry;
	private $activedocument;
		
	public function __construct( Registry $registry, $id )
	{
		$this->registry = $registry;
		$this->activedocument = false;
		if( $id != '' )
		{
			$id = $this->registry->getObject('db')->sanitizeData( $id );
			$sql = "SELECT *
                		FROM DmsEntry
                		WHERE  id='$id'";

      		$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->name = $data['Level'];
				$this->name = $data['Parent'];
				$this->name = $data['Type'];
				$this->name = $data['LineNo'];
				$this->name = $data['Title'];
				$this->name = $data['Name'];
				$this->name = $data['Path'];
				$this->name = $data['FileExtension'];
				$this->name = $data['CreateDate'];
				$this->name = $data['Archived'];
				$this->name = $data['NewEntry'];
				$this->activedocument = true;
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