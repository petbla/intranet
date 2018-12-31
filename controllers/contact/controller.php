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
						if($perSet == 0)  
						{
							$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
							$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
							break;
						}
						if($perSet < 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->viewContact($ID);
							break;
						}
						$this->editContact($ID);
						break;
					case 'save':
						$ID = isset( $urlBits[2] ) ? $urlBits[2] : '';
						if($perSet == 0)  
						{
							$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
							$this->registry->getObject('template')->getPage()->addTag('message',$caption['msg_unauthorized']);
							break;
						}
						if($perSet >= 5) // změna pouze pro Starosta(5), Adninistrátor(9)
						{
							$this->saveContact($ID);
						}
						break;
					case 'search':
						$searchText = isset($urlBits[2]) ? $urlBits[2] : '';
						if ($searchText){
							$this->searchContacts($searchText);
						}
						break;
					default:				
						$this->listContacts();
						break;		
				}
			}
		}
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
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'view-contact.tpl.php', 'footer.tpl.php');
		}
		else
		{
			// File Not Found
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-contact.tpl.php', 'footer.tpl.php');
		}
	}	
	private function editContact( $ID )
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
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'edit-contact.tpl.php', 'footer.tpl.php');
		}
		else
		{
			// File Not Found
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-contact.tpl.php', 'footer.tpl.php');
		}
	}	

	private function saveContact( $ID )
	{
		global $config, $caption;
		if( isset($_POST['submitEditContact']) )
		{
			$ID = isset($_POST['ID']) ? $_POST['ID'] : null;
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
					if(isset($_POST['Function']))
					{
						if($contact['Function'] !== $_POST['Function'])
						{$data['Function'] = $_POST['Function'];}
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
						{$data['Email'] = $_POST['Email'];}
					}
					if(isset($_POST['Phone']))
					{
						if($contact['Phone'] !== $_POST['Phone'])
						{$data['Phone'] = $_POST['Phone'];}
					}
					if(isset($_POST['Web']))
					{
						if($contact['Web'] !== $_POST['Web'])
						{$data['Web'] = $_POST['Web'];}
					}
					if(isset($_POST['Note']))
					{
						if($contact['Note'] !== $_POST['Note'])
						{$data['Note'] = $_POST['Note'];}
					}
					if(isset($_POST['Address']))
					{
						if($contact['Address'] !== $_POST['Address'])
						{$data['Address'] = $_POST['Address'];}
					}
					if(isset($_POST['Close']))
					{
						if($contact['Close'] !== $_POST['Close'])
						{$data['Close'] = $_POST['Close'];}
					}

					$data['FullName'] = isset($data['LastName']) ? $data['LastName'] : "";
					if($data['FirstName'] !== "")
					{
						$sp = ($data['FullName'] !== "") ? " " : "";
						$data['FullName'] = $data['FullName'] . $sp . $data['FirstName'];
					}
					if($data['Title'] !== "")
					{
						$sp = ($data['FullName'] !== "" ) ? " " : "";
						$data['FullName'] = $data['FullName'] . $sp . $data['Title'];
					}
					$data['FullName'] = ($data['FullName'] !== "" ) ? $data['FullName'] : $data['Company'];
					

					$condition = "ID = '$ID'";
					$this->registry->getObject('db')->updateRecords('contact',$data,$condition);
				}
				$this->editContact($ID);
			}
		}
		else
		{
			// File Not Found
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-contact.tpl.php', 'footer.tpl.php');
		}
	}	
	
	private function listContacts()
	{
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, ".
						"(SELECT GROUP_CONCAT( cg.GroupCode SEPARATOR ',' ) FROM contactgroups cg ".
						" WHERE cg.ContactID = c.ID) AS Groups ".
					"FROM Contact c ".
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
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');			
			}
			else
			{
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-contact.tpl.php', 'footer.tpl.php');
			}
		}
        if ($perSet > 0)
        {
        }
        else
        {
        }
    }	

	private function searchContacts( $searchText )
	{
		global $config, $caption;
        $perSet = $this->registry->getObject('authenticate')->getPermissionSet();

		$searchText = htmlspecialchars($searchText);
		$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
						"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, ".
				"(SELECT GROUP_CONCAT( cg.GroupCode SEPARATOR ',' ) FROM contactgroups cg WHERE cg.ContactID = c.ID) AS Groups ".
				"FROM Contact c ".
				"WHERE Close = 0 AND MATCH(FullName,Function,Company,Address,Note,Phone,Email) AGAINST ('*$searchText*' IN BOOLEAN MODE) ".
				"ORDER BY FullName";
		$isHeader = true;
		$isFooter = true;
		$pageLink = '';
		$this->listResult($sql, $pageLink, $isHeader, $isFooter );
	}	


	private function oprava ()
	{
		$line[0]='01D295B0-4931-4009-8AD2-7CE4897270C8,Jánoš Vlastimil Ing.,starosta,zastupitel obce Mistřice';
		$line[1]='03C484B5-099D-4102-902E-FF4C7D692F9A,Blažek Petr,místostarosta,zastupitel obce Mistřice';
		$line[2]='0561B059-01E5-4469-82AD-B93E81122114,Huňka Jiří Ing.,člen rady,zastupitel obce Mistřice';
		$line[3]='06C8E3A8-2185-40FF-8359-9E2EDA2D0A81,Tománek Petr,,zastupitel obce Mistřice';
		$line[4]='083071CF-DAEF-4A32-A44E-3B683237E496,Omelka Robert,,zastupitel obce Mistřice';
		$line[5]='08990340-9A07-4EE5-9FC5-D1ECEA29CDDF,Špičáková Ivana,člen rady,zastupitel obce Mistřice';
		$line[6]='092C49F8-E013-4C05-9F9D-56B67B4C4819,Vaněk Pavel,předseda finančního výboru,zastupitel obce Mistřice';
		$line[7]='0B897271-BE65-4153-A5D4-C7ECEB4FF448,Trčková Hana Mgr.,člen rady,zastupitel obce Mistřice';
		$line[8]='13038ADF-6C2F-4D9B-84B0-D1CCA26CF2D8,Bořuta Stanislav Mgr.,,zastupitel obce Mistřice';
		$line[9]='1387A932-3C98-4B6D-B846-3383E0D7C617,Ondroušek Martin,,zastupitel obce Mistřice';
		$line[10]='142A4AAB-67C9-4EAA-8CC7-AFA7A123CEC4,Ševčík Pavel,,zastupitel obce Mistřice';
		$line[11]='14C49AE4-CD51-4175-88F7-F2A09412357C,Magdálek Zbyněk,,zastupitel obce Mistřice';
		$line[12]='1595F376-7DE2-4A2B-9821-4F72510B8B00,Kocáb Petr,,zastupitel obce Mistřice';
		$line[13]='18A9E7CA-A081-4AEB-9933-5BE23507A1A5,Malina Miloš,,zastupitel obce Mistřice';
		$line[14]='1A5D597B-EA3D-4A15-AD68-7EC7947F1C9D,Vandírková Vladimíra,předsedkyně kontrolního výbru,zastupitel obce Mistřice';
		$line[15]='20F6DD6C-0A84-4EF7-9E88-9D6B8D5A36AA,Novotný Ing.,projektant,Centroprojekt Zlín';
		$line[16]='247A5C61-9862-4C52-9BC4-F48B2FDA8015,Hanškut Josef,,CETIN';
		$line[17]='302A1816-8BA9-403E-8812-9C1E252D0B33,Kutra Karel,,CETIN';
		$line[18]='329A6296-4755-43EC-9E18-02283844CBEA,Vlk Pavel,,CETIN';
		$line[19]='32D9BB06-857B-4501-AC41-74DA3719BC4A,EON servis,,EON';
		$line[20]='34114BE4-5B62-43EF-B674-EB940C830B47,Hašpica,,EON';
		$line[21]='39D5144D-E335-4999-A886-5FFE7F1E3984,Novák Ladislav,,EON';
		$line[22]='3B9635BC-F66B-4472-8F7E-ED2A2392BF4D,Truca Vlastimil,,EON';
		$line[23]='3C09468B-2B51-490E-9361-F772C651072C,Zahradník,,EON';
		$line[24]='3D080573-6F9F-4DF4-9F56-B056BEEEB4C4,Drábková Adriana,,Hortehůber';
		$line[25]='3D3CA181-0DBC-4530-AB50-9526A7740531,Hruboš Josef,projektant,INTOS';
		$line[26]='44B59258-FA28-4A0A-9530-4815757681BC,Nestroj Karel,projektant,INTOS';
		$line[27]='45EEE664-9452-4E1D-94FF-27227FC48ACF,Fuglík Daniel,bezpečnostní technik,IS PROJECT';
		$line[28]='477FEF1F-C31F-4498-8476-5B30A133DE11,Peprna Ladislav,jednatel,Jednota COOP';
		$line[29]='49E46B33-8EA0-4319-BD91-AECC6CC504E8,Polák František,vedoucí,Jednota COOP';
		$line[30]='4A06CA9C-4988-41A4-A0DA-80734C0DE824,Minulaštíková Darja,,Kovosteel';
		$line[31]='4EB9D41E-6E14-4942-88DF-B857C1ECC082,Šobáň Jan,vedoucí,MAS Dolní Pomoraví';
		$line[32]='50DE513F-D696-4AC6-A453-0F616E423461,Marek Václav,,MOEL';
		$line[33]='51C94EB0-7B51-4363-8C03-F32DDB59BB48,Šdivec,,MOEL';
		$line[34]='53882D53-1525-4035-A41D-18E7B56659C7,Liška,projektant,PERFECT';
		$line[35]='53E433B0-0492-4325-AE2F-C84063498D84,Valášek,projektant,PERFECT';
		$line[36]='5434EB7B-1105-48A0-A04C-5204154190EC,Bania Milan Ing.,,Policie ČR';
		$line[37]='544C3089-A6BA-4C13-9880-94EAFFB7335F,Budař Martin,ředitel,PROMOT';
		$line[38]='5584D7CB-2037-461C-8B67-9F84CA0685DF,Mahdal Josef,pojišťovatel,Renomia';
		$line[39]='576D3758-28E5-4498-876C-7CE7414D37F9,Kleperlíková Martina,,Rovina';
		$line[40]='5A3A0260-0AF8-4F8B-B28D-5D80029EC522,Lušovjan Radek,projektant,Rovina';
		$line[41]='5C1802A9-E907-460F-B869-119864E38B91,Smolík Ladislav,,Rovina';
		$line[42]='5C3A9102-0E03-41BE-A150-4E597401ABA1,Dostálek,,ŘSZK';
		$line[43]='5C534DC8-92C2-4FFB-A7EC-C5825E3675FE,Kměť Petr,,ŘSZK';
		$line[44]='5CAF2837-13D8-4EEC-B238-E56D8D6F3D95,Škrabalová Jitka Ing.,náměstek pro investiční výstavbu,ŘSZK';
		$line[45]='5D6F1810-2B59-455B-80DF-54E5C45398A6,Beníčková,,Sběrné suroviny UH';
		$line[46]='64662C22-E7EA-418A-B9FD-9D10E47F90CC,Hošková Markéta,,Sběrné suroviny UH';
		$line[47]='651A7451-31E3-466C-84E5-EB5D3D7BFB68,Čejka Karel,,SVAK';
		$line[48]='69B42593-3BCC-42CC-A33D-3389D82412C1,Černý Tomáš,,SVAK';
		$line[49]='6A4A68E0-30D8-475C-A7B1-880649926A8A,Hampl Tomáš,,SVAK';
		$line[50]='6A9D279E-F1C6-4137-A34E-D91A327E61DD,Jordánová Renata,,SVAK';
		$line[51]='6BD8C232-F54B-4878-A7E5-F57779BBCAF5,Mazurek Antonín,technik,SVAK';
		$line[52]='6BFD15DE-AB17-4D76-AE01-F3D6A4505CE2,Ošívka František,,SVAK';
		$line[53]='7137F7F4-4DFC-427B-A710-EE37212C227E,Mychnaková Anna,,Územní plánování';
		$line[54]='73511780-FEB0-4646-A5B2-986D6B520E89,Pastyříková,,Územní plánování';
		$line[55]='7558C643-C48E-47B5-B3D4-CB38CE8EB1BC,Špok Radek Ing.,projektant,Územní plánování';
		$line[56]='76A17AE9-6BB1-4066-8473-F81CBCC45742,Vávra,,Územní plánování';
		$line[57]='78AE210E-FDEB-4208-B188-CE8F5401FAEA,Bartas Libor,,';
		$line[58]='78D0A104-DE19-46CF-802D-6FA924AF05F5,Bílka,,';
		$line[59]='7946B041-183B-43F7-B697-576C5B37313D,Dohnal Marek,geodet,';
		$line[60]='79570AEC-EE99-47F3-9D45-C530AD088466,Holý,architekt,';
		$line[61]='7D55FA87-EC8C-4593-B54A-54B023402776,Hubáček,,';
		$line[62]='84EEE81B-FB23-4B7C-A1D7-C45B012FB9F4,Košina Ing. ,projektant,';
		$line[63]='86357673-E7ED-41D2-8EC7-CCCE701309C2,Ondroušek Michal,projektant,';
		$line[64]='887C9A41-2966-4A08-A523-9762E0798ADF,Pavliš Josef,projektant,';
		$line[65]='8E803B2A-9DDD-4306-BD8A-33D4ED8E664C,Pecha Zdeněk,elektrikář,';
		$line[66]='90B2B724-1B1C-4C8A-A0DF-EB20E095AB01,Šico Josef Ing.,technický dozor investora,';
		$line[67]='9202BEDB-B3C8-4D4A-8F27-97E2BB3A6D25,Šilhavík Pavel,,';
		$line[68]='94DFD52B-DD49-4029-B22E-54308C1BDF16,Široký,,';
		$line[69]='96C82A01-0D77-4E32-BFE7-2BD970E51E6D,Škrabal Ing.,projektant,';
		$line[70]='995C04E8-D4B4-45D9-BA60-B47E5FF36233,Šťastná Jitka JUDr.,právník,';
		$line[71]='99CEEB5B-3B15-44A9-9432-6D88815D000D,Valouch,projektant,';
		$line[72]='9AADB661-9EF8-4B87-B4DC-6DBB5EF8A8EC,Vaněk Pavel,,';
		$line[73]='9B05A3C1-80D7-45B2-989B-52F04D6801C1,Zeman Jiří,konzultant SW,Alis';
		$line[74]='9BD7B901-F20B-45C4-A831-8D70E1DCA486,Červínek Svatopliuk,servisní technik,ČSV - servis ELEKTRO';
		$line[75]='A23D2EC0-34AE-4654-979C-711690A19DD6,Běhůnek Pavel,,klempířství';
		$line[76]='A46C0A76-AD0C-4098-8064-E7984EA01D8F,Sládková Jarmila,pověřenec pro ochranu osobních údajů pro OÚ,SMS ČR';
		$line[77]='A59D5574-2FF2-4C3B-818B-53B00AFCEC58,Pospíšilová,pověřenec,SMS ČR';
		$line[78]='A77CD7A8-03EB-4507-BE72-35694EA57830,Brázda Petr,,vydavatelství Brázda';
		$line[79]='A9129183-CE55-42A6-9A6A-917850B8901D,Chdík Radek,vedoucí a obchodní zástupce,HRATES a.s.';
		$line[80]='AD3E5233-FBFE-4262-9DAC-71E5AB2DD5A0,Vendolský Zdeněk Ing.,jednatel,DESACON';
		$line[81]='B0521BA8-5481-4066-BF77-64C8B68AD9B6,Knotová Růžena,rozpočtářka,Stavební úřad Bílovice';
		$line[82]='B53E4A8A-D663-4C21-A71A-BEEB64949FE5,Dohnal,,Stavební úřad Bílovice';
		$line[83]='B6D7F51A-D98E-4F4D-ADAB-5D9F747729B4,Křivák,technik,TEROSAT';
		$line[84]='BA38AFC7-7BFA-4BCC-9B59-E118031EB4D9,Mikoška Pavel,,EURONICS, KASA';
		$line[85]='C0B41573-283E-4513-9804-FA5BC4AACA51,Sedláček Ladislav,,';
		$line[86]='C2B01C82-1854-4198-96BE-82EC3F718717,Špičák Milan,,Obec Mistřice';
		$line[87]='C2B2EE68-9847-44A0-8FBB-B27EDA1674B1,Ondroušek Vojtěch,,Obec Mistřice';
		$line[88]='C929CC22-CC20-46A7-BE21-EF524B89DC80,Tomečková Jana,účetní,Obec Mistřice';
		$line[89]='D659FF4C-5C8B-48DC-8B4B-052A3123AA5B,Trojková Ilona,administrativní pracovnice,Obec Mistřice';
		$line[90]='D7D109C3-81E3-4A3F-952A-DEA39DBB424C,Fusková Marcela,ředitelka,Mateřská škola Mistřice';
		$line[91]='DD43285B-A1AA-461F-9FA4-EDD58B4B2D5A,Hrdinová Vladimíra,ředitelka,Základ škola Mistřice';
		$line[92]='DE4B54DC-0158-4BD8-A3A7-E669D7A67749,Říha Miroslav,,';
		$line[93]='E26D2346-EA60-409A-9154-EB6EF5D27CB5,Kašná Vendula,,Spolek rodičů z Mistřic';
		$line[94]='E43BE2FF-1268-4943-B5D5-36FDFF19443D,Trojka Milan,,Spolek rybářů Mistřice';
		$line[95]='E7B3BBBC-4552-4B14-954A-D616B0487A14,Lapčík Vlastimil Ing.,,TJ Sokol Mistřice z.s.';
		$line[96]='E7BFEAEA-D19E-4844-B6EB-5BD35D43E079,Hráček Ladislav Ing.,,Myslivecké sdružení Kopec Mistřice z.s.';
		$line[97]='EF02C9F5-95D2-45F3-A116-54F9776EE8C7,Schön Martin,předseda,Sbor dobrovolných hasičů';
		$line[98]='F3D7D71F-4FBA-4692-AC16-F7B712CE56A2,Abrhám František,,Včelaři';
		$line[99]='F4074C09-3FF9-4DC8-8D2B-EAA2E953A762,Huňková Barbora Mgr.,,Mistřické frajárky';
		$line[100]='F48A8798-622D-46D9-8F2E-B23653D07E73,Osoha Zdeněk,,Stavební a výkopové práce';
		$line[101]='F90BB133-24DC-4614-9142-B7A6ABBD2C1A,Kloupar Karel Ing. Arch.,předseda představenstva,GG Archico a.s.';
		$line[102]='FBD1D6C8-CD8D-4D01-B093-E61BD190F364,Kačerová Jana Ing.,,Zlínský kraj';
		$line[103]='FD84106F-8403-4B7C-9169-B1A06D3A4BC9,Kinc Ondřej,,Envipartner';
		$line[104]='FED833BF-FC76-4910-B006-A0D87DEB7D03,EMPEMONT s.r.o,,EMPEMONT s.r.o';
		$line[105]='FFA3590F-5671-4BF5-81AE-3E27211E607F,Huňka Michal,správce webu,';

		for ($i=0; $i < 106; $i++) { 
			$entry = explode(',',$line[$i]);
			$ID = $entry[0];
			$data['ID'] = $ID;
			$data['Function'] = $entry[2];
			$data['Company'] = $entry[3];

			$fullname = explode(' ',$entry[1]);
			$data['LastName'] = isset($fullname[0]) ? $fullname[0] : '';
			$data['FirstName'] = isset($fullname[1]) ? $fullname[1] : '';
			$data['Title'] = isset($fullname[2]) ? $fullname[2] : '';
			$condition = "ID = '$ID'";
			$this->registry->getObject('db')->updateRecords('contact',$data,$condition);
		}		
	}
}
?>