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
	private $Level;
	private $Parent;
	private $Type;
	private $Multimedia;
	private $LineNo;
	private $Title;
	private $Name;
	private $Path;
	private $FileExtension;
	private $Url;
	private $ModifyDateTime;
	private $CreateDate;
	private $Archived;
	private $NewEntry;
	private $PermissionSet;
	private $LastChange;
	private $Remind;
	private $RemindClose;
	private $RemindFromDate;
	private $RemindLastDate;
	private $RemindUserGroup;
	private $Content;
	private $RemindResponsiblePerson;
	private $RemindUserID;
	private $RemindContactID;
	private $RemindState;
	private $Private;

	private $activeEntry;
	private $linkToFile;
	private $breads;
	private $isHeader;
	private $isFooter;
	private $isFolder;
	private $isFile;
	private $isBlock = FALSE;
	private $isNote = FALSE;
	private $isAudio = FALSE;
	private $isVideo = FALSE;
	private $isImage = FALSE;
	private $jetoAudio = FALSE;
	private $jeToVideo = FALSE;
		
	public function __construct( Registry $registry, $id )
	{
		global $config;
        $pref = $config['dbPrefix'];
		$root = $config['fileserver'];

		$this->registry = $registry;
		$this->activeEntry = FALSE;
		$this->isHeader = FALSE;
		$this->isFooter = FALSE;
		$this->isFolder = FALSE;
		$this->isFile = FALSE;
		$this->isBlock = FALSE;
		$this->isNote = FALSE;
		$this->isAudio = FALSE;
		$this->isVideo = FALSE;
		$this->isImage = FALSE;
	
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
				$this->Path = $data['Path'];
				$this->FileExtension = $data['FileExtension'];
				$this->Url = $data['Url'];
				$this->ModifyDateTime = $data['ModifyDateTime'];
				$this->CreateDate = $data['CreateDate'];
				$this->Archived = $data['Archived'];
				$this->NewEntry = $data['NewEntry'];
				$this->PermissionSet = $data['PermissionSet'];
				$this->LastChange = $data['LastChange'];
				$this->Content = $data['Content'];
				$this->Remind = $data['Remind'];
				$this->RemindClose = $data['RemindClose'];
				$this->RemindFromDate = $data['RemindFromDate'];
				$this->RemindLastDate = $data['RemindLastDate'];
				$this->RemindUserGroup = $data['RemindUserGroup'];
				$this->RemindResponsiblePerson = $data['RemindResponsiblePerson'];
				$this->RemindUserID = $data['RemindUserID'];
				$this->RemindContactID = $data['RemindContactID'];
				$this->RemindState = $data['RemindState'];
				$this->Private = $data['Private'];
								
				$this->activeEntry = true;
				
				$link = str_replace(DIRECTORY_SEPARATOR,'/', $data['Name']);  //iconv("windows-1250","utf-8",
				$link = $root.$link;
				$this->linkToFile = $link;
		
				$this->breads = $this->getBreads();

				if(($this->Type == 20) || ($this->Type == 25))
				{
					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',10);
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isHeader = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',40);
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isFooter = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','audio');
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isAudio = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','video');
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isVideo = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','image');
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isImage = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',20);
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isFolder = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',30);
					$this->registry->getObject('db')->setFilter('Multimedia','');
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isFile = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',25);
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isBlock = $this->registry->getObject('db')->findFirst();

					$this->registry->getObject('db')->initQuery('dmsentry');
					$this->registry->getObject('db')->setFilter('Parent',$this->EntryNo);
					$this->registry->getObject('db')->setFilter('Type',35);
					$this->registry->getObject('db')->setFilter('Archived',0);
					$this->isNote = $this->registry->getObject('db')->findFirst();
				}

				$this->registry->getObject('db')->initQuery('dmsentry');
				$this->registry->getObject('db')->setFilter('EntryNo',$this->Parent );
				if ($this->registry->getObject('db')->findFirst())
				{
					$entryParent = $this->registry->getObject('db')->getResult();
					$this->Parent = $entryParent['ID'];
				}
			}
		}
		else
		{
			// Init empty
			$this->initNew();
		}
	}

	public function isValid()
	{
		return $this->activeEntry;
	}
	
	public function getData( $onlyCulons = false )
	{
		$data = array();
		foreach( $this as $field => $fdata )
		{
			if( !is_object( $fdata ) )
			{
				$data[ $field ] = $fdata;
			}
		}
		if ($onlyCulons)
		{
			unset($data['activeEntry']);
			unset($data['linkToFile']);
			unset($data['breads']);
			unset($data['isHeader']);
			unset($data['isFooter']);
			unset($data['isFolder']);
			unset($data['isFile']);
			unset($data['isBlock']);
			unset($data['isNote']);
			unset($data['isAudio']);
			unset($data['isVideo']);
			unset($data['isImage']);
			unset($data['jetoAudio']);
			unset($data['jeToVideo']);
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
	public function initNew()
	{
		$this->EntryNo = 0;
		$this->ID = '';
		$this->Level = 0;
		$this->Parent = 0;
		$this->Type = 0;
		$this->Multimedia = '';
		$this->LineNo = 0;
		$this->Title = '';
		$this->Name = '';
		$this->Path = '';
		$this->FileExtension = '';
		$this->Url = '';
		$this->ModifyDateTime = date("Y-m-d H:i:s");
		$this->CreateDate = date("Y-m-d H:i:s");
		$this->Archived = 0;
		$this->NewEntry = 1;
		$this->PermissionSet = 0;
		$this->LastChange = date("Y-m-d H:i:s");
		$this->Content = '';
		$this->Remind = 0;
		$this->RemindClose = 0;
		$this->RemindFromDate = date("Y-m-d H:i:s");
		$this->RemindLastDate = date("Y-m-d H:i:s");
		$this->RemindUserGroup = 0;
		$this->RemindResponsiblePerson = '';
		$this->RemindUserID = '';
		$this->RemindContactID = '';
		$this->RemindState = '';
		$this->Private = 0;

		$this->activeEntry = FALSE;
		$this->linkToFile = '';
		$this->breads = '';
		$this->isHeader = FALSE;
		$this->isFooter = FALSE;
		$this->isFolder = FALSE;
		$this->isFile = FALSE;
		$this->isBlock = FALSE;
		$this->isNote = FALSE;
		$this->isAudio = FALSE;
		$this->isVideo = FALSE;
		$this->isImage = FALSE;
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