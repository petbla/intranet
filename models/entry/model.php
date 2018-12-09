<?php
/**
 * Class DMS Entry
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 */

class Entry{
	private $registry;
	private $EntryNo;
	private $ID;
	private $Level;
	private $Parent;
	private $Type;
	private $LineNo;
	private $Title;
	private $Name;
	private $FileExtension;
	private $ModifyDateTime;
	private $CreateDate;
	private $Archived;
	private $NewEntry;
	private $activeEntry;
		
	public function __construct( Registry $registry, $id )
	{
		$this->registry = $registry;
		$this->activeEntry = false;
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
				$this->EntryNo = $data['EntryNo'];
				$this->ID = $data['ID'];
				$this->Level = $data['Level'];
				$this->Parent = $data['Parent'];
				$this->Type = $data['Type'];
				$this->LineNo = $data['LineNo'];
				$this->Title = $data['Title'];
				$this->Name = $data['Name'];
				$this->FileExtension = $data['FileExtension'];
				$this->ModifyDateTime = $data['ModifyDateTime'];
				$this->CreateDate = $data['CreateDate'];
				$this->Archived = $data['Archived'];
				$this->NewEntry = $data['NewEntry'];
				$this->activeEntry = true;
			}
		}
		else
		{
			// zde můžeme chtít provést něco jiného...
		}
	}

	public function isValid()
	{
		return $this->activeEntry;
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