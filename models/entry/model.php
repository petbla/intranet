<?php
/*
 * Class DMS Entry
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 * 
 * Type: 10 - Header		položka      	   .... položka jako text v záhlaví (první část na stránce)
 * 	   	 20 - Folder 	obal (10,30,35,40) .... fyzický (soubory) i virtuální obsah
 * 	 	 25 - Block		obal (10,35,40)    .... virtuální obsah
 * 		 30 - File		položka            .... fyzický soubor
 * 		 35 - Note		položka            .... virtuální, jako odkaz, text, poznámka
 * 		 40 - Footer     položka			   .... položka jako text v zápatí (poslední část na stránce)
 *
 * Multimedia: 	image
 * 				audio
 * 				video
 */

class Entry{
	private $registry;
	private $EntryNo = 0;
	private $ID;
	private $parentID = '';
	private $Level;
	private $Parent;
	private $Type;
	private $Multimedia;
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
	private $LastChange;

	private $activeEntry;
	private $linkToFile;
	private $breads;
	private $isHeader = false;
	private $isFooter = false;
	private $isFolder = false;
	private $isFile = false;
	private $isBlock = false;
	private $isNote = false;
	private $isAudio = false;
	private $isVideo = false;
	private $isImage = false;
		
	public function __construct( Registry $registry, $id )
	{
		global $config;
        $pref = $config['dbPrefix'];

		$this->registry = $registry;
		$this->activeEntry = false;
		if( $id != '' )
		{
			$id = $this->registry->getObject('db')->sanitizeData( $id );
			$sql = "SELECT *
                		FROM ".$pref."DmsEntry
                		WHERE  id='$id' AND Archived=0";

      		$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->EntryNo = $data['EntryNo'];
				$this->ID = $data['ID'];
				$this->Level = $data['Level'];
				$this->Parent = $data['Parent'];
				$this->Type = $data['Type'];
				$this->Multimedia = $data['Multimedia'];
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
				$this->LastChange = $data['LastChange'];
				$this->activeEntry = true;
				$this->linkToFile = str_replace(DIRECTORY_SEPARATOR,'/', $data['Name']);  //iconv("windows-1250","utf-8",
				$this->breads = $this->getBreads();

				if(($this->Type == 20) || ($this->Type == 25))
				{
					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',10);
					$this->isHeader = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',40);
					$this->isFooter = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','audio');
					$this->isAudio = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','video');
					$this->isVideo = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','image');
					$this->isImage = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',20);
					$this->isFolder = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','');
					$this->isFiles = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',25);
					$this->isBlock = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',35);
					$this->isNote = $this->registry->getObject('db')->findFirst();
				}

				$this->registry->getObject('db')->initQuery('dmsentry');
				$this->registry->getObject('db')->setFilter('EntryNo',$this->Parent );
				if ($this->registry->getObject('db')->findFirst())
				{
					$entryParent = $this->registry->getObject('db')->getResult();
					$this->parentID = $entryParent['ID'];
				}
			}
		}
		else
		{
			// Init empty
			$this->EntryNo = 0;
			$this->ID = '';
			$this->parentID = '';
			$this->Level = 0;
			$this->Parent = 0;
			$this->Type = 0;
			$this->Multimedia = '';
			$this->LineNo = 0;
			$this->Title = '';
			$this->Name = '';
			$this->FileExtension = '';
			$this->Url = '';
			$this->ModifyDateTime = null;
			$this->CreateDate = null;
			$this->Archived = false;
			$this->NewEntry = false;
			$this->PermissionSet = '';
			$this->LastChange = null;
			$this->activeEntry = false;
			$this->linkToFile = '';
			$this->breads = '';
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
	public function getlinkToFile()
	{
		if ($this->isValid())
		{
			return $this->linkToFile;
		}
		return null;
	}

	private function getBreads ()
	{
		global $caption;

        $ID = $this->ID;
        $title = $caption['home_page'];
		$href = "index.php?page=document/list";
		$breads = "<a href='$href'>$title</a>";
		
		if( $this->activeEntry )
		{
			$names = explode(DIRECTORY_SEPARATOR,$this->Name);
			$name = '';
            foreach ($names as $idx => $title) 
            {
				$name .= ($name != '') ? DIRECTORY_SEPARATOR:'';
				$name .= $title;
				$breads .= ($breads != '') ? ' > ':'';
				$ID = $this->registry->GetObject('file')->getIdByName($name);
				$breads .= "<a href='$href/$ID'>$title</a> ";
			}
		}
		return $breads;
	}
	
}
?>