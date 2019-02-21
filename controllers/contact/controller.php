<?php

class Contactcontroller {

	private $registry;
	private $urlBits;
	
	public function __construct( Registry $registry, $directCall )
	{
		global $caption;

		$this->registry = $registry;
		$perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if( $directCall == true )
		{
      		$urlBits = $this->registry->getURLBits();     
			
			if($perSet == 0)
			{
				$this->registry->getObject('log')->addMessage($caption['msg_unauthorized'],'contact','');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
				return;
			}

			if( !isset( $urlBits[1] ) )
			{		
		        $this->listContacts();
			}
			else
			{
				switch( $urlBits[1] )
				{				
					case 'list':
						$this->listContacts();
						break;
					case 'view':
						$ID = isset( $urlBits[2] ) ? $urlBits[2] : '';
						$this->viewContact($ID);
						break;
					case 'edit':
						$ID = isset( $urlBits[2] ) ? $urlBits[2] : '';
						if($perSet < 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->viewContact($ID);
							break;
						}
						$this->editContact($ID);
						break;
					case 'delete':
						$ID = isset( $urlBits[2] ) ? $urlBits[2] : '';
						if(($perSet < 5) && ($perSet != 1)) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->error($caption['msg_unauthorized']);
							break;
						}
						$this->deleteContact($ID);
						break;
					case 'new':
						$this->addContact();
						break;
					case 'save':
						$ID = isset( $urlBits[2] ) ? $urlBits[2] : '';
						if($perSet >= 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->saveContact($ID);
						}
						break;
					case 'importCsv':
						if($perSet >= 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->importCsv();
							$this->listContacts();
						}
						else
							$this->error($caption['Error'].' - '.$caption['msg_unauthorized']);
						break;
					case 'exportCsv':
						if($perSet >= 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->exportCsv();
							exit('OK');	
						}
						else
							exit($caption['Error'].' - '.$caption['msg_unauthorized']);	
						break;
					case 'search':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchContacts($searchText);
						}
						break;
					case 'logview':
						// Je voláno jako XMLHttpRequest (function.js) a pouze loguje zobrazené položky
						$ID = isset($urlBits[2]) ? $urlBits[2] : '';
						$this->logViewContact($ID);
						break;
					default:				
						$this->listContacts();
						break;		
				}
			}
		}
	}

	private function notFound()
	{
		//TOTO: doplnit šablonu
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-contact.tpl.php', 'footer.tpl.php');
	}
	private function error( $message )
	{
		$this->registry->getObject('log')->addMessage("Chyba: $message",'contact','');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
	}

	private function viewContact( $ID )
	{
		global $config, $caption;
        
		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, $ID );
		if( $this->model->isActive() )
		{
			$contact = $this->model->getData();
			foreach ($contact as $property => $value) {
				$this->registry->getObject('template')->getPage()->addTag( $property, $value );
			}
			$this->registry->getObject('log')->addMessage("Zobrazení kontaktu ".$contact['FullName'],'contact',$ID);
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'view-contact.tpl.php', 'footer.tpl.php');
		}
		else
		{
			// File Not Found
			$this->notFound();
		}
	}	

	private function importCsv()
	{
		if(isset($_FILES["fileToUpload"]) ) {
			$file = $_FILES['fileToUpload'];
			if(!empty($file))
			{
				$target_file = 'tmp/' . basename($file["name"]);
				move_uploaded_file($file['tmp_name'],$target_file);
			}	
		}

		// If you need to parse XLS files, include php-excel-reader
		require_once( FRAMEWORK_PATH . 'vendor/spreadsheet-reader/php-excel-reader/excel_reader2.php');
		require_once( FRAMEWORK_PATH . 'vendor/spreadsheet-reader/SpreadsheetReader.php');
	
		$Reader = new SpreadsheetReader($target_file);
		$idValidCsv = false;
		$lineno = 0;
		foreach ($Reader as $Row)
		{
			$lineno++;
			if ($lineno == 1){
				if(($Row[0] === 'Jméno') &&
				   ($Row[1] === 'Příjmení') &&
				   ($Row[2] === 'Titul') &&
				   ($Row[3] === 'Funkce') &&
				   ($Row[4] === 'Společnost') &&
				   ($Row[5] === 'Telefon') &&
				   ($Row[6] === 'Email') &&
				   ($Row[7] === 'Adresa'))
				{
					$idValidCsv = true;
				}
			}
			if (! $idValidCsv){
				return;
			}
			if ($lineno > 1){
				$ID = $this->registry->getObject('fce')->GUID();
				$data['ID'] = $ID;
				$data['FirstName'] = $this->registry->getObject('db')->sanitizeData($Row[0]);
				$data['LastName'] = $this->registry->getObject('db')->sanitizeData($Row[1]);
				$data['Title'] = $this->registry->getObject('db')->sanitizeData($Row[2]);
				$data['Function'] = $this->registry->getObject('db')->sanitizeData($Row[3]);
				$data['Company'] = $this->registry->getObject('db')->sanitizeData($Row[4]);
				$data['Email'] = $Row[6];
				$data['Phone'] = str_replace(' ','',$Row[5]);
				$data['Address'] = $this->registry->getObject('db')->sanitizeData($Row[7]);
				$data['Close'] = '0';
				if(($data['FirstName'] !== '') && ($data['LastName'] == ''))
				{
					// Jméno obsahuje jméno + příjmní + tituly
					$name = explode(' ',$data['FirstName']);
					$data['FirstName'] = $name[0];
					array_shift($name);
					if($name[0] !== ''){
						$data['LastName'] = $name[0];
						array_shift($name);
					}
					if($name[0] !== ''){
						$data['Title'] = implode(' ',$name);
					}
				}
				else if(($data['FirstName'] == '') && ($data['LastName'] !== ''))
				{
					// Jméno obsahuje jméno + příjmní + tituly
					$name = explode(' ',$data['LastName']);
					$data['LastName'] = $name[0];
					array_shift($name);
					if($name[0] !== ''){
						$data['FirstName'] = $name[0];
						array_shift($name);
					}
					if($name[0] !== ''){
						$data['Title'] = implode(' ',$name);
					}
				}
				$data['FullName'] = $this->makeFullName($data);
				if($data['FullName'] !== ''){
					$this->registry->getObject('db')->initQuery('contact');
					$this->registry->getObject('db')->setFilter('FullName',$data['FullName']);
					if($this->registry->getObject('db')->isEmpty()){
						$this->registry->getObject('log')->addMessage("Nový kontakt ".$data['FullName'],'Contact',$ID);
						$this->registry->getObject('db')->insertRecords('contact',$data);
					}
				}
			}
		}
	}

	private function exportCsv()
	{
		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, '' );
		$contact = $this->model->getData();

		$filepath = "c:\\temp\\export.csv";
		$filename = "Sablona_kontaktu.csv";
		$csvFile = fopen($filepath, "wb");
		if ($csvFile === false) {
			die("Cannot create file");
		}
		fwrite($csvFile, "\xEF\xBB\xBF");// UTF-8 boom
		fputcsv($csvFile, ['Jméno','Příjmení','Titul','Funkce','Společnost','Telefon','Email','Adresa'], ";");
		fclose($csvFile);

		if( !file_exists($filepath) ) 
			die ("File not found");
		// Force the download

		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		readfile($filepath);
	}

	private function deleteContact( $ID )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];

		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, $ID );
		if( $this->model->isActive() )
		{
			$condition = "ID = '$ID'";
			$data['Close'] = 1;
			$this->registry->getObject('log')->addMessage("Uzavření kontaktu",'contact',$ID);
			$this->registry->getObject('db')->updateRecords('contact',$data,$condition);			
		}
		$this->listContacts();
	}

	private function editContact( $ID )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];

		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, $ID );
		if( $this->model->isActive() )
		{
			$contact = $this->model->getData();
			foreach ($contact as $property => $value) {
				$this->registry->getObject('template')->getPage()->addTag( $property, $value );
			}
			$groupList = $this->model->getGroupList();
			$this->registry->getObject('log')->addMessage("Editace kontaktu ".$contact['FullName'],'contact',$ID);
			$cache = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
			$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache) );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'edit-contact.tpl.php', 'footer.tpl.php');
		}
		else
		{
			// File Not Found
			$this->notFound();
		}
	}	

	private function addContact( )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];

		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, '' );
		$contact = $this->model->getData();
		foreach ($contact as $property => $value) {
			$this->registry->getObject('template')->getPage()->addTag( $property, $value );
		}
		$groupList = $this->model->getGroupList();
		$cache = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
		$this->registry->getObject('log')->addMessage("Nový kontaktu ".$contact['FullName'],'contact',$contact['ID']);
		$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache) );
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'edit-contact.tpl.php', 'footer.tpl.php');
	}	

	private function saveContact( $ID )
	{
		global $config, $caption;
		if( isset($_POST['back_id']) )
		{
			$this->listContacts();
		}
		else 
		{
			$ID = isset($_POST['ID']) ? $_POST['ID'] : null;

			if(($ID == '') || ($ID == 'newcontact'))
			{
				$ID = $this->registry->getObject('fce')->GUID();
				$data['ID'] = $ID;
				$data['FullName'] = $ID;
				$this->registry->getObject('log')->addMessage("Nový kontakt",'Contact',$ID);
				$this->registry->getObject('db')->insertRecords('contact',$data);
			}
			
			if ($ID)
			{
				require_once( FRAMEWORK_PATH . 'models/contact/model.php');
				$this->model = new Contact( $this->registry, $ID );
				if( $this->model->isActive() )
				{
					$contact = $this->model->getData();
					$data['LastName'] = $contact['LastName'];
					$data['FirstName'] = $contact['FirstName'];
					$data['Title'] = $contact['Title'];
					$data['Company'] = $contact['Company'];

					if(isset($_POST['newFirstName'])) 
					{
						if($contact['FirstName'] !== $_POST['newFirstName'])
						{
							$data['FirstName'] = $_POST['newFirstName'];
						}
					}
					if(isset($_POST['newLastName']))
					{
						if($contact['LastName'] !== $_POST['newLastName'])
						{
							$data['LastName'] = $_POST['newLastName'];
						}
					}
					if(isset($_POST['newTitle']))
					{
						if($contact['Title'] !== $_POST['newTitle'])
						{
							$data['Title'] = $_POST['newTitle'];
						}
					}
					if(isset($_POST['newFunction']))
					{
						if($contact['Function'] !== $_POST['newFunction'])
						{
							$data['Function'] = $_POST['newFunction'];
						}
					}
					if(isset($_POST['newCompany']))
					{
						if($contact['Company'] !== $_POST['newCompany'])
						{
							$data['Company'] = $_POST['newCompany'];
						}
					}
					if(isset($_POST['newEmail']))
					{
						if($contact['Email'] !== $_POST['newEmail'])
						{
							$data['Email'] = $_POST['newEmail'];
						}
					}
					if(isset($_POST['newPhone']))
					{
						if($contact['Phone'] !== $_POST['newPhone'])
						{
							$data['Phone'] = str_replace(' ','',$_POST['newPhone']);
						}
					}
					if(isset($_POST['newWeb']))
					{
						if($contact['Web'] !== $_POST['newWeb'])
						{
							$data['Web'] = $_POST['newWeb'];
						}
					}
					if(isset($_POST['newNote']))
					{
						if($contact['Note'] !== $_POST['newNote'])
						{
							$data['Note'] = $_POST['newNote'];
						}
					}
					if(isset($_POST['ContactGroups']))
					{
						if($contact['ContactGroups'] !== $_POST['ContactGroups'])
						{
							$data['ContactGroups'] = $_POST['ContactGroups'];
						}
					}
					if(isset($_POST['newAddress']))
					{
						if($contact['Address'] !== $_POST['newAddress'])
						{
							$data['Address'] = $_POST['newAddress'];
						}
					}
					if(isset($_POST['Close']))
					{
						if($contact['Close'] !== $_POST['Close'])
						{
							$data['Close'] = ($_POST['Close'] === '1') ? 1 : 0;
						}
					}

					$data['FullName'] = $this->makeFulName($data);
				

					$condition = "ID = '$ID'";
					$this->registry->getObject('log')->addMessage("Aktualizace kontaktu",'contact',$ID);
					$this->registry->getObject('db')->updateRecords('contact',$data,$condition);
				}
				$searchText = isset($_POST['sqlrequest']) ? $_POST['sqlrequest'] : '';
				if($searchText !== '')
				{
					$this->searchContacts($searchText);
				}
				else
				{
					$this->listContacts();
				}
			}
			else
			{
				$this->notFound();
			}
		}
	}	

	private function listContacts()
	{
		global $config;
        $pref = $config['dbPrefix'];
		
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, c.Note, c.ContactGroups ".
					"FROM ".$pref."Contact c ".
					"WHERE  Close=0 ".
					"ORDER BY c.FullName ";
		$isHeader = true;
		$isFooter = true;
		$pageLink = '';
		$this->listResult($sql, $pageLink, $isHeader, $isFooter );
	}

	private function listResult( $sql, $pageLink , $isHeader, $isFooter, $template = 'list-contact.tpl.php')
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
        
		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, '' );
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if($perSet > 0)
		{

			// Stránkování
			$cacheFull = $this->registry->getObject('db')->cacheQuery( $sql );
			$records = $this->registry->getObject('db')->numRowsFromCache( $cacheFull );
			$pageCount = (int) ($records / $config['maxVisibleItem']);
			$pageCount = ($records > $pageCount * $config['maxVisibleItem']) ? $pageCount + 1 : $pageCount;  
			$pageNo = ( isset($_GET['p'])) ? $_GET['p'] : 1;
			$pageNo = ($pageNo > $pageCount) ? $pageCount : $pageNo;
			$pageNo = ($pageNo < 1) ? 1 : $pageNo;
			$fromItem = (($pageNo - 1) * $config['maxVisibleItem']);    
			$navigate = $this->registry->getObject('template')->NavigateElement( $pageNo, $pageCount ); 
			$this->registry->getObject('template')->getPage()->addTag( 'navigate_menu', $navigate );
			$sql .= " LIMIT $fromItem," . $config['maxVisibleItem']; 
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			if (!$this->registry->getObject('db')->isEmpty( $cache )){
				$this->registry->getObject('template')->getPage()->addTag( 'ContactList', array( 'SQL', $cache ) );
				$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );
				$this->registry->getObject('template')->getPage()->addTag( 'pageTitle', '' );

				$this->registry->getObject('template')->addTemplateBit('editcard', 'list-contact-editcard.tpl.php');
				$this->registry->getObject('template')->addTemplateBit('editIcon', 'list-contact-editicon.tpl.php');
				$this->registry->getObject('template')->getPage()->addTag( 'dmsClassName', 'contact' );
				$this->registry->getObject('template')->getPage()->addTag( 'ID', 'newcontact' );
				$this->registry->getObject('template')->getPage()->addTag( 'Address', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'Note', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'ContactGroups', '' );
	
				$cache2 = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
				$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache2) );
					
				$this->registry->getObject('log')->addMessage("Zobrazení seznamu kontaktů",'Contact','');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
			}
			else
			{
				$this->registry->getObject('log')->addMessage("Zobrazení seznamu kontaktů",'Contact','');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
			}
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
    }	

	private function searchContacts( $searchText )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];

        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$searchText = htmlspecialchars($searchText);
		$searchText = str_replace('*','',$searchText);
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, c.Note, c.ContactGroups ".
				"FROM ".$pref."Contact c ".
				"WHERE Close = 0 AND MATCH(FullName,Function,Company,Address,Note,Phone,Email,ContactGroups) AGAINST ('*$searchText*' IN BOOLEAN MODE) ".
				"ORDER BY FullName";
		$isHeader = true;
		$isFooter = true;
		$pageLink = '';
		$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', $searchText );
		$this->registry->getObject('log')->addMessage("Zobrazení vyhledaných kontaktů `$searchText`",'Contact','');
		$this->listResult($sql, $pageLink, $isHeader, $isFooter );
	}	

	private function logViewContact( $ID )
	{
		// Je voláno jako XMLHttpRequest (function.js) a pouze loguje zobrazené položky

		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		$this->model = new Contact( $this->registry, $ID );
		if( $this->model->isActive() )
		{
			$contact = $this->model->getData();
			$this->registry->getObject('log')->addMessage("Zobrazení kontaktu. ".$contact['FullName'],'Contact',$ID);
		}
		print "<h1>Page Not Found.<h1>";
		exit();		
	}		

	private function makeFullName ($data)
	{
		$FullName = '';
		$FullName = isset($data['LastName']) ? $data['LastName'] : "";
		if($data['FirstName'] !== "")
		{
			$sp = ($FullName !== "") ? " " : "";
			$FullName = $FullName . $sp . $data['FirstName'];
		}
		if($data['Title'] !== "")
		{
			$sp = ($FullName !== "" ) ? " " : "";
			$FullName = $FullName . $sp . $data['Title'];
		}
		$FullName = ($FullName !== "" ) ? $FullName : $data['Company'];
		return($FullName);
	}
}
?>