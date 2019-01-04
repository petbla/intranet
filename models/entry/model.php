<?php
/**
 * Class DMS Entry
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 * 
 * Type 10 - Header		položka      	   .... položka jako text v záhlaví (první část na stránce)
 * 		20 - Folder 	obal (10,30,35,40) .... fyzický (soubory) i virtuální obsah
 * 		25 - Block		obal (10,35,40)    .... virtuální obsah
 * 		30 - File		položka            .... fyzický soubor
 * 		35 - Note		položka            .... virtuální, jako odkaz, text, poznámka
 * 		40 - Footer     položka			   .... položka jako text v zápatí (poslední část na stránce)
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
	private $Url;
	private $ModifyDateTime;
	private $CreateDate;
	private $Archived;
	private $NewEntry;
	private $PermissionSet;
	private $activeEntry;
	private $linkToFile;
		
	public function __construct( Registry $registry, $id )
	{
		global $config;
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
				$this->Url = $data['Url'];
				$this->ModifyDateTime = $data['ModifyDateTime'];
				$this->CreateDate = $data['CreateDate'];
				$this->Archived = $data['Archived'];
				$this->NewEntry = $data['NewEntry'];
				$this->PermissionSet = $data['PermissionSet'];
				$this->activeEntry = true;
				$this->linkToFile = $data['Name'];  //iconv("windows-1250","utf-8",
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
	public function getlinkToFileToFile()
	{
		if ($this->isValid())
		{
			return $this->linkToFile;
		}
		return null;
	}
}
?>