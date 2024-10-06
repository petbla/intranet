<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    26.04.2019
 */
class Contactcontroller {

	private $registry;
	private $urlBits;
	private $message;
	private $errorMessage;
	private $model;
	
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
				$this->error($caption['msg_unauthorized']);
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
					case 'edit':
						$ID = isset( $urlBits[2] ) ? $urlBits[2] : '';
						if($perSet < 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->error($caption['msg_unauthorized']);
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
					case '':
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
					case 'group':
						$action = isset($urlBits[2]) ? $urlBits[2] : '';
						$Code = isset($_POST["Code"]) ? $_POST["Code"] : '';
						$action = isset($_POST["action"]) ? $_POST["action"] : $action;
						switch ($action) {
							case 'list':
								$this->listContactGroup();
								break;
							case 'add':
								$this->addContactGroup( $Code );
								break;
							case 'modify':
								if($Code !== ''){
									$this->modifyContactGroup( $Code );
								}else{
									$this->pageNotFound();
								}
								break;
							case 'delete':
								$Code = isset($urlBits[3]) ? $urlBits[3] : '';
								if($Code !== ''){
									$this->deleteContactGroup( $Code );
								}else{
									$this->pageNotFound();
								}
								break;
							default:
								$this->pageNotFound();
								break;
						}						
						break;
					default:				
						$this->listContacts();
						break;		
				}
			}
		}
	}

    /**
     * Sestavení stránky
     * @return void
     */
	private function build( $template = 'page.tpl.php' )
	{
		// Category Menu
		$this->createCategoryMenu();

		// Page message
		$this->registry->getObject('template')->getPage()->addTag('message',$this->message);
		$this->registry->getObject('template')->getPage()->addTag('errorMessage',$this->errorMessage);

		// Build page
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		$this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-contact.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');
	}

	/**
     * Zobrazení chybové stránky, pokud kontakt nebyl nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->error("Pokus o zobrazení neznámého kontaktu");		
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param string $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'contact','');
		
		$this->errorMessage = $message;
		$this->build();
	}

    /**
	 * Generování menu
	 * @return void
	 */
	public function createCategoryMenu()
    {
		global $config;
		$urlBits = $this->registry->getURLBits();
		$typeID = isset( $urlBits[1]) ? $urlBits[1] : '';
		$typeID .= isset( $urlBits[2]) ? '/'.$urlBits[2] : '';

		$rec['idCat'] = 'list';
		$rec['titleCat'] = 'Kontakty';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;

		$rec['idCat'] = 'group/list';
		$rec['titleCat'] = 'Skupiny kontaktů';
		$rec['activeCat'] = $rec['idCat'] == $typeID ? 'active' : '';
		$table[] = $rec;

		$cache = $this->registry->getObject('db')->cacheData( $table );
		$this->registry->getObject('template')->getPage()->addTag( 'categoryList', array( 'DATA', $cache ) );
    }


	/**
	 * Akce vyvolaná z webového formuláře, která načte CSV soubor 
	 * ze kterého se pokusí provést import nových kontaktů. Jako CSV sobor
	 * je očekávám přesně definovaný obsah a požadí sloupců (viz níže)
	 * Po dokončení se zobrazí seznam kontaktů
	 * @return void
	 */
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
				   ($Row[7] === 'Adresa') &&
				   ($Row[8] === 'Web') &&
				   ($Row[9] === 'Poznámka'))
				{
					$idValidCsv = true;
				}
			}
			if (($idValidCsv) && ($lineno > 1)){
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
				$data['Web'] = $this->registry->getObject('db')->sanitizeData($Row[8]);
				$data['Note'] = $this->registry->getObject('db')->sanitizeData($Row[9]);
				$data['Close'] = '0';
				if(($data['FirstName'] !== '') && ($data['LastName'] === ''))
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
				else if(($data['FirstName'] === '') && ($data['LastName'] !== ''))
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
		$this->listContacts();
	}

	/**
	 * Akce vyvolaná z webového formuláře, která odstraní kontakt, a to tak
	 * že nastavení hodotu pole "Close = 1"
	 * Po dokončení se zobrazí seznam kontaktů
	 * @param string $ID = ID kontaktu pro odstranění
	 * @return void
	 */
	private function deleteContact( $ID )
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];

		$post = $_POST;

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

	/**
	 * Zobrazení stránky s editací karty kontaktu
	 * @param string $ID = ID kontaktu pro editaci
	 * @return void
	 */
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
			$cache = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
			$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache) );
			
			// Logování
			$this->registry->getObject('log')->addMessage("Editace kontaktu ".$contact['FullName'],'contact',$ID);
			$this->build('contact-edit.tpl.php');
		}
		else
		{
			$this->pageNotFound();
		}
	}	

	/**
	 * Založení nového kontaktu, které zobrazí stránku s editací karty nového kontaktu
	 * @return void
	 */
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
		$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache) );
		
		// Logování
		$this->registry->getObject('log')->addMessage("Nový kontaktu ".$contact['FullName'],'contact',$contact['ID']);
		$this->build('contact-edit.tpl.php');
	}	

	/**
	 * Akce vyvolaná webovým formulářem - Editace karty kontaktu,
	 * která zajistí uložení modifikovaných hodnot
	 * Po provední akce uložení/storno se zobrazí seznam kontaktů
	 * @param string $ID = ID editovaného kontaktu
	 * @return void
	 */
	private function saveContact( $ID )
	{
		global $config, $caption;
		if( isset($_POST['back_id']) )
		{
			$this->listContacts();
		}
		else 
		{
			$post = $_POST;

			$ID = isset($_POST['ID']) ? $_POST['ID'] : null;

			if(($ID === '') || ($ID == 'contact'))
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

					$post = $_POST;

					if(isset($_POST['FirstName'])) 
					{
						if($contact['FirstName'] !== $_POST['FirstName'])
						{
							$data['FirstName'] = $_POST['FirstName'];
						}
					}
					if(isset($_POST['LastName']))
					{
						if($contact['LastName'] !== $_POST['LastName'])
						{
							$data['LastName'] = $_POST['LastName'];
						}
					}
					if(isset($_POST['Title']))
					{
						if($contact['Title'] !== $_POST['Title'])
						{
							$data['Title'] = $_POST['Title'];
						}
					}
					if((isset($_POST['BirthDate'])) && ($_POST['BirthDate'] !== ''))
					{
						if($contact['BirthDate'] !== $_POST['BirthDate'])
						{
							$data['BirthDate'] = $_POST['BirthDate'];
						}
					}
					if(isset($_POST['Function']))
					{
						if($contact['Function'] !== $_POST['Function'])
						{
							$data['Function'] = $_POST['Function'];
						}
					}
					if(isset($_POST['Company']))
					{
						if($contact['Company'] !== $_POST['Company'])
						{
							$data['Company'] = $_POST['Company'];
						}
					}
					if(isset($_POST['Email']))
					{
						if($contact['Email'] !== $_POST['Email'])
						{
							$data['Email'] = $_POST['Email'];
						}
					}
					if(isset($_POST['DataBox']))
					{
						if($contact['DataBox'] !== $_POST['DataBox'])
						{
							$data['DataBox'] = $_POST['DataBox'];
						}
					}
					if(isset($_POST['Phone']))
					{
						if($contact['Phone'] !== $_POST['Phone'])
						{
							$data['Phone'] = str_replace(' ','',$_POST['Phone']);
						}
					}
					if(isset($_POST['Web']))
					{
						if($contact['Web'] !== $_POST['Web'])
						{
							$data['Web'] = $_POST['Web'];
						}
					}
					if(isset($_POST['Note']))
					{
						if($contact['Note'] !== $_POST['Note'])
						{
							$data['Note'] = $_POST['Note'];
						}
					}
					if(isset($_POST['ContactGroups']))
					{
						if($contact['ContactGroups'] !== $_POST['ContactGroups'])
						{
							$data['ContactGroups'] = $_POST['ContactGroups'];
						}
					}
					if(isset($_POST['Address']))
					{
						if($contact['Address'] !== $_POST['Address'])
						{
							$data['Address'] = $_POST['Address'];
						}
					}
					if(isset($_POST['Close']))
					{
						if($contact['Close'] !== $_POST['Close'])
						{
							$data['Close'] = ($_POST['Close'] === '1') ? 1 : 0;
						}
					}

					$data['FullName'] = $this->makeFullName($data);
					$condition = "ID = '$ID'";
					$this->registry->getObject('log')->addMessage("Aktualizace kontaktu",'contact',$ID);
					$this->registry->getObject('db')->updateRecords('contact',$data,$condition);
				}
				
				$searchText = isset($_POST['searchText'])? $_POST['searchText'] : '';
				$searchType = isset($_POST['searchType'])? ($_POST['searchType'] == 'general' ? '' : 'contact') : 'contact';
				if ($searchText != '{searchText}'){
					require_once( FRAMEWORK_PATH . 'controllers/general/controller.php');
					$general = new Generalcontroller( $this->registry, true );
					$general->searchGlobal($searchText, $searchType);
				}else{
					$this->listContacts();
				}
			}
			else
			{
				$this->pageNotFound();
			}
		}
	}	

    /**
     * Zobrazení seznamu všech aktivních kontaktů
     * @return void
     */
	private function listContacts($template = 'contact-list.tpl.php')
	{
		global $config, $caption;
        $pref = $config['dbPrefix'];
		require_once( FRAMEWORK_PATH . 'models/contact/model.php');
		
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, c.Note, c.ContactGroups, c.BirthDate, c.DataBox ".
					"FROM ".$pref."Contact c ".
					"WHERE  Close=0 ".
					"ORDER BY c.FullName ";
		$isHeader = true;
		$isFooter = true;
		$pageLink = '';

		// Zobrazení seznamu
        
		$this->model = new Contact( $this->registry, '' );
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		if($perSet > 0)
		{
			// Group records by Page
			$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
			// Save SQL result to $cache (array type) AND modify record
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			
			$result = null;
			if (!$this->registry->getObject('db')->isEmpty( $cache ))
			{
				while( $rec = $this->registry->getObject('db')->resultsFromCache( $cache ) )
				{
					$model = new Contact( $this->registry, $rec['ID'] );
					$contact = $model->getData();
					$rec['Title'] = ($contact['Title'] <> "")? $contact['Title'].'&nbsp;' : '';
					$rec['FirstName'] = $contact['FirstName'];
					$rec['LastName'] = $contact['LastName'];
					$rec['Function'] = $contact['Function'];
					$rec['Company'] = $contact['Company'];
					$rec['Address'] = $contact['Address'];
					$rec['Note'] = $contact['Note'];
					$rec['Phone'] = $contact['Phone'];
					$rec['Email'] = $contact['Email'];
					$rec['DataBox'] = $contact['DataBox'];
					$rec['Web'] = $contact['Web'];
					$rec['ContactGroups'] = $contact['ContactGroups'];
					
					$rec['dmsClassName'] = 'contact';
					$rec['viewContactCardID'] = 'viewContactCard'.$rec['ID'];					
					$rec['editContactCardID'] = 'editContactCard'.$rec['ID'];					
					$rec['deleteLink'] = "contact/delete";

					$result[] = $rec;
				}
			}else{
				$this->pageNotFound();
				return;
			};
			$cache = $this->registry->getObject('db')->cacheData( $result );
	
			// Build page 		
			$this->registry->getObject('template')->getPage()->addTag( 'ContactList', array( 'DATA', $cache ) );
			$this->registry->getObject('template')->getPage()->addTag( 'pageLink', $pageLink );

			// Onclick forms
			$this->registry->getObject('template')->addTemplateBit('viewcardContact', 'contact-view.tpl.php');
			$this->registry->getObject('template')->addTemplateBit('editcardContact', 'contact-edit.tpl.php');
			$this->registry->getObject('template')->addTemplateBit('newcardContact', 'contact-edit.tpl.php');
			
			// For new contact
			$this->registry->getObject('template')->getPage()->addTag( 'ID', 'contact' );			
			$this->registry->getObject('template')->getPage()->addTag( 'Title', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'FirstName', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'LastName', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Function', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Company', '' );
			$this->registry->getObject('template')->getPage()->addTag( '', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Address', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Web', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Phone', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Email', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'DataBox', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'Note', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'ContactGroups', '' );

			$cache2 = $this->registry->getObject('db')->cacheQuery("SELECT * FROM ".$pref."contactgroup");
			$this->registry->getObject('template')->getPage()->addTag( 'GroupList', array('SQL' , $cache2) );
				

			$this->registry->getObject('template')->getPage()->addTag( 'sqlrequest', '' );
			// Log
			$this->registry->getObject('log')->addMessage("Zobrazení seznamu kontaktů",'Contact','');
			
			$this->build( $template );			
		}
        else
        {
			$this->error($caption['msg_unauthorized']);
        }
    }	

    /**
     * Zobrazení skupin kontaktů
     * @return void
     */
	private function listContactGroup( )
	{
		global $caption,$config;
		$pref = $config['dbPrefix'];

    	$sql = "SELECT * FROM ".$pref."contactgroup ";

		// Zobrazení výsledku
		$sql = $this->registry->getObject('db')->getSqlByPage( $sql );
		// Save SQL result to $cache (array type) AND modify record
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if ($this->registry->getObject('db')->isEmpty( $cache ))
		{
			$this->pageNotFound();
			return;
		};
		$this->registry->getObject('template')->getPage()->addTag( 'ContactGroupList', array( 'SQL', $cache ) );
		$this->build('contact-group-list.tpl.php');
	}

	/**
	 * Založení nové skupiny kontaktů
	 * @return void
	 */
	private function addContactGroup( $Code )
	{
		global $config, $caption;

		if ($Code == ''){
			$this->error('Kód musí být vyplněn!');
			return;
		};

		$Code = strtoupper($Code);
		$Code = str_replace(' ','_', strtoupper($Code));
		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Název musí být vyplněn!');
			return;
		};

		$this->registry->getObject('db')->initQuery('contactgroup');
		$this->registry->getObject('db')->setFilter('Code',$Code);
		if (!$this->registry->getObject('db')->isEmpty()){
			$this->error("Kód $Code již existuje!");
			return;
		}

		$data = array();
		$data['Name'] = $Name;
		$data['Code'] = $Code;
		$this->registry->getObject('db')->insertRecords('contactgroup',$data);
		$this->listContactGroup();
	}	

	/**
	 * Editace skupiny kontaktů
	 * @return void
	 */
	private function modifyContactGroup( $Code )
	{
		$Name = isset($_POST['Name']) ? $_POST['Name'] : '';
		if ($Name == ''){
			$this->error('Název musí být vyplněn!');
			return;
		};

		$this->registry->getObject('db')->initQuery('contactgroup');
        $this->registry->getObject('db')->setFilter('Code',$Code);
		if ($this->registry->getObject('db')->findFirst())
		{
            // Update
            $changes = array();
            $changes['Name'] = $Name;
            $condition = "Code = '$Code'";
            $this->registry->getObject('db')->updateRecords('contactgroup',$changes, $condition);
			$this->listContactGroup();
		}else{
			$this->pageNotFound();
		}
		return;
	}

	/**
	 * Výmaz skupiny kontaktů
	 * @return void
	 */
	private function deleteContactGroup( $Code )
	{	
		global $caption,$config;
		$pref = $config['dbPrefix'];

    	$sql = "SELECT * FROM ".$pref."contact ".
					"WHERE (ContactGroups like '%$Code,%') OR (ContactGroups like '$Code') ";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		if (!$this->registry->getObject('db')->isEmpty( $cache ))
		{
			$this->error("Kód $Code se používá, nelze jej odstranit.");
			return;
		}
		$condition = "Code = '$Code'";
		$this->registry->getObject('db')->deleteRecords( 'contactgroup', $condition, 1); 
		$this->listContactGroup();
	}

	/**
	 * Lokální funkce pro sestavení jednotného tvaru jména kontaktu
	 * ve tvaru [Příjmení][Jméno][Titul]
	 * 			nebo
	 *          [Společnost]
	 * @param array<mixed><mixed> $data
	 * @return string $FullName
	 */
	public function makeFullName ($data)
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

	public function getContact ( $ID )
	{
		$contact = null;
		$this->registry->getObject('db')->initQuery('contact');
		$this->registry->getObject('db')->setFilter('ID',$ID);
		if ($this->registry->getObject('db')->findFirst())
			$contact = $this->registry->getObject('db')->getResult();			
		return $contact;
	}

	public function getContactID( $value )
	{
		$contact = null;
		$contactID = null;
		$this->registry->getObject('db')->initQuery('contact');
		$this->registry->getObject('db')->setFilter('ID',$value);
		if ($this->registry->getObject('db')->findFirst())
			$contact = $this->registry->getObject('db')->getResult();
		else{
			$this->registry->getObject('db')->initQuery('contact');
			$this->registry->getObject('db')->setFilter('FullName',$value);
			if ($this->registry->getObject('db')->findFirst())
				$contact = $this->registry->getObject('db')->getResult();	
		}			
		if($contact)
			$contactID = $contact['ID'];
		return $contactID;
	}

	public function readFromData()
	{
		$doc = array();
		$post = $_POST;
		
		// File and Parent Name
		//
		$doc['FileName'] = isset($_POST['FileName']) ? $_POST['FileName'] : '';
		$doc['ParentID'] = isset($_POST['ParentID']) ? $_POST['ParentID'] : null;
		$doc['ParentName'] = isset($_POST['ParentName']) ? $_POST['ParentName'] : '';
		$doc['DocumentNo'] = isset($_POST['DocumentNo']) ? $_POST['DocumentNo'] : '';
		$doc['Parent'] = null;
		if ($doc['ParentID']) {
			$doc['Parent'] = $this->getDmsEntry($doc['ParentID']);
		}

		// Company information
		//
		$doc['PresenterName'] = isset($_POST['PresenterName']) ? $_POST['PresenterName'] : '';
		$doc['PresenterPhone'] = isset($_POST['PresenterPhone']) ? $_POST['PresenterPhone'] : '';
		$doc['AtDate'] = isset($_POST['AtDate']) ? $_POST['AtDate'] : '';
		
		// Contact information, name, address
		//
		$doc['FirstName'] = isset($_POST['FirstName']) ? $_POST['FirstName'] : '';
		$doc['LastName'] = isset($_POST['LastName']) ? $_POST['LastName'] : '';
		$doc['Title'] = '';
		$doc['Company'] = isset($_POST['Company']) ? $_POST['Company'] : '';
		
		$doc['FullName'] = $this->makeFullName($doc);
		$doc['Title'] = isset($_POST['Title']) ? $_POST['Title'] : '';
		if ($doc['Title'])
			$doc['FullName'] = $doc['Title'] . " " . $doc['FullName'];	
		$doc['Address'] = isset($_POST['Address']) ? $_POST['Address'] : '';
		$doc['Email'] = isset($_POST['Email']) ? $_POST['Email'] : '';
		$doc['DataBox'] = isset($_POST['DataBox']) ? $_POST['DataBox'] : '';
		$doc['Phone'] = isset($_POST['Phone']) ? $_POST['Phone'] : '';

		// Contact (array) from database
		//
		$doc['ContactID'] = isset($_POST['ContactID']) ? $_POST['ContactID'] : null;
		$doc['Contact'] = null;
		if($doc['ContactID']){
			$doc['Contact'] = $this->getContact($doc['ContactID']);
		}

		// Full HTML format address for print
		//
		$doc['FullHtmlAddress'] = '';
		$doc['FullHtmlAddress'] .= $doc['Company'] ? '<b>'.$doc['Company'].'</b><br>' : '';
		$doc['FullHtmlAddress'] .= $doc['FullName'] ? '<b>'.$doc['FullName'].'</b><br>' : '';
		$doc['FullHtmlAddress'] .= str_replace(chr(13), '<br>', $doc['Address'].'<br>'.'<br>');
		$doc['FullHtmlAddress'] .= $doc['Email'] ? 'Email: '.$doc['Email'].'<br>' : '';
		$doc['FullHtmlAddress'] .= $doc['Phone'] ? 'Tel.: '.$doc['Phone'].'<br>' : '';
		$doc['FullHtmlAddress'] .= $doc['DataBox'] ? 'Datová schránka: '.$doc['DataBox'].'<br>' : '';

		// Document informations
		//
		$doc['Subject'] = isset($_POST['Subject']) ? $_POST['Subject'] : '';
		$doc['Content'] = isset($_POST['Content']) ? $_POST['Content'] : '';
		$doc['SignatureName'] = isset($_POST['SignatureName']) ? $_POST['SignatureName'] : '';
		$doc['SignatureFunction'] = isset($_POST['SignatureFunction']) ? $_POST['SignatureFunction'] : '';

		return $doc;
	}

	/**
	 * Get record Dmsentry by ID
	 * @param mixed $ID
	 * @return array
	 */
	function getDmsEntry($ID)
	{
		require_once( FRAMEWORK_PATH . 'models/entry/model.php');
		$this->model = new Entry( $this->registry, $ID );
		if ($this->model->isValid()) {
			$entry = $this->model->getData();
		}else{
			$entry = null;
		}
		return $entry;
	}

}