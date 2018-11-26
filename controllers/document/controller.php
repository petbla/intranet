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
	
	// Příkaz SQL pro filtrované doklady
	private $filterSQL = '';
	
	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
        $this->listDocuments('');
			}
			else
			{
				if( !isset( $urlBits[2] ) )
				{		
					$ID = '';
				}
				else
				{
					$ID = $urlBits[2];
				}
					switch( $urlBits[1] )
				{				
					case 'list':
						$this->listDocuments($ID);
						break;
					case 'view':
						//TOTO: doplnit
						$this->listDocuments($ID);
						break;
					case 'edit':
						//TOTO: doplnit
						$this->listDocuments($ID);
						break;
					case 'search':
						//TOTO: doplnit ??
						$this->searchDocument();
						break;
					case 'setfilter':
						//TOTO: doplnit ??
						$this->setFilterDocument();
						break;
					default:				
						$this->listDocuments('');
						break;		
				}
			}
			//TOTO: opravit vyhledávání
  		$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'Document/search');
		}
	}
	
	/**
	 * @return void
	 */
	private function documentNotFound()
	{
		//TOTO: doplnit šablonu
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-document.tpl.php', 'footer.tpl.php');
	}
	
	private function listDocuments( $ID )
	{
		global $config, $caption;

		// Find level
		$level = 0;
		if ($ID != '')
		{
			$sql = "SELECT level FROM DmsEntry WHERE ID = '{$ID}'";
			$this->registry->getObject('db')->executeQuery( $sql );			
			if( $this->registry->getObject('db')->numRows() != 0 )
			{
				$dmsEntry = $this->registry->getObject('db')->getRows();
				$level = $dmsEntry['level'];
			}
		}

    $sql = "SELECT title, type
							FROM DmsEntry AS d
							WHERE d.Archived = 0 AND 
										d.level={$level}";
		if ($level == 0)
		{
			$sql .= " AND d.Type <> 20"; 	// Not Directory od root
		}
		$sql .= " ORDER BY Level,Parent,Type,LineNo";
		
		$this->registry->getObject('document')->listDocuments($sql,'');
	}	
	
	
  private function searchdocument()
	{
		global $caption, $config;
		
    // kontrola jestli uživatel odeslal vyhledávací formulář 
		$searchdocument = null;
    if( isset( $_POST['search_text'] ) && $_POST['search_text'] != '' )
		{
		  $searchdocument = $_POST['search_text']; 
      $expire=time()+60*60*1;
      setCookie( 'document_search_text', $searchdocument, $expire );
    }else{
      if (isset($_COOKIE["document_search_text"]))
        $searchdocument = $_COOKIE["document_search_text"];
    }
    	
    if ( isset( $searchdocument ))
    {  
      // vyčistění hledané fráze 
			$searchPhrase = $this->registry->getObject('db')->sanitizeData( $searchdocument );
			$this->registry->getObject('template')->getPage()->addTag( 'filter_reference', $caption['filter'].': '.$searchdocument );
      
			// vyhledání, uložení výsledků do mezipaměti, tak aby byly připravené pro šablonu výsledků vyhledávání
			
			$sql = "SELECT prod.contentID,prod.document_path,prod.document_name,prod.description,
              prod.document_price,prod.code,prod.stock,prod.reserve,prod.document_id,
              prod.document_path,prod.document_name,prod.document_image,IF(basketQty is NULL,0,basketQty) as basketQty,
              IF(basketQty >= stock,'reserve',prod.document_status) as document_status,
              IF(document_name LIKE '%{$searchPhrase}%', 0, 1) as priorityb, 
              IF(description LIKE '%{$searchPhrase}%', 0, 1) as priorityc, 
              IF(code LIKE '%{$searchPhrase}%', 0, 1) as priority, 
              prod.metakeywords,prod.metadescription,prod.metarobots
        FROM (SELECT c.ID as contentID,c. path as document_path, v.name as document_name, v.heading as description,
                      p.price as document_price, p.code, p.stock, p.reserve, p.status  as document_status,
                      cat.ID as document_id, cat.path as document_path, catv.name as document_name, 
                      img.image as document_image,v.metakeywords, v.metadescription, v.metarobots
                FROM content c, content_types t, content_versions v, content_types_document p, content as cat, 
                     content_versions as catv, content_types_document_in_categories pic, document_images img
                WHERE c.active=1 AND c.secure=0 AND t.ID=c.type AND t.name like 'Produkty' AND c.current_versionID=v.ID 
                      AND p.content_versionID=v.ID AND pic.contentID=c.ID AND cat.ID=pic.document_id 
                      AND catv.ID=cat.current_versionID AND img.ID=p.main_imageID AND p.status = 'stock'
                ORDER BY c.ID DESC) prod
        LEFT JOIN (SELECT contentID,sum(quantity) basketQty FROM basket_contents GROUP BY contentID) bas
          ON (bas.contentID = prod.contentID)
        WHERE ( document_name LIKE '%{$searchPhrase}%' OR description LIKE '%{$searchPhrase}%' 
          OR code LIKE '%{$searchPhrase}%')
        ORDER BY priority, priorityb, priorityc ";
      
      $this->filterSQL = $sql;
      
      $cache = $this->registry->getObject('db')->cacheQuery( $sql );
			if( $this->registry->getObject('db')->numRowsFromCache( $cache ) == 0 )
			{
				$this->listdocument( '' );			
			}
			else
			{
				$this->listdocument( '' );			
			}
		}
		else
		{
			// uživatel neodeslal vyhledávací formulář, zobrazí se tedy pouze stránka s vyhledávacím polem 			
      $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'document-searchform.tpl.php', 'footer.tpl.php');
		}
	}
	
	private function relateddocument( $contentID )
	{
    $sql = "SELECT c.ID as contentID, v.name as document_name, c.path as document_path, img.image as document_image 
              FROM content c, content_versions v, document_sets ps, document_images as img,
                   content_types_document p                                 
              WHERE v.ID=c.current_versionID AND c.ID=ps.contentID AND c.ID<>{$contentID} 
                AND ps.setID=(SELECT setID FROM `document_sets` WHERE contentID={$contentID})
                AND p.content_versionID=v.ID AND img.ID=p.main_imageID ";
		$relateddocumentCache = $this->registry->getObject('db')->cacheQuery( $sql );
    $rows = $this->registry->getObject('db')->numRowsFromCache( $relateddocumentCache );
    if ($rows > 0){ 
  		$this->registry->getObject('template')->addTemplateBit( 'relateddocument_bit', 'document-related-img.tpl.php' );
		  $this->registry->getObject('template')->getPage()->addTag('relateddocument', array( 'SQL', $relateddocumentCache ) );
		}else
		  $this->registry->getObject('template')->getPage()->addTag('relateddocument_bit', '' );
	}

	private function detaileddocument( $contentID )
	{
		$sql = "SELECT c.path as document_path,img.image as document_image, img.description as description 
              FROM document_images img, content c
              WHERE img.contentID = $contentID AND c.ID=img.contentID
              ORDER BY img.position";

    $detaileddocumentCache = $this->registry->getObject('db')->cacheQuery( $sql );
    $rows = $this->registry->getObject('db')->numRowsFromCache( $detaileddocumentCache );
    if ($rows > 1){ 
  		$this->registry->getObject('template')->addTemplateBit( 'detaileddocument_bit', 'document-detail-img.tpl.php' );
      $this->registry->getObject('template')->getPage()->addTag('detaileddocument', array( 'SQL', $detaileddocumentCache ) );
    }else
      $this->registry->getObject('template')->getPage()->addTag('detaileddocument_bit', '' );
	}
	
	private function generateFilterOptions()
	{
		global $deb;
    
    // 1. Dotaz na typy atributů 
		$sql = "SELECT ID, reference, name FROM document_filter_attribute_types";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() != 0 )
		{
			$attributeValues = array();
			$attributeTypes = array();
			while( $attributeTypeData = $this->registry->getObject('db')->getRows() )
			{
				$attributeValues[ $attributeTypeData['reference'] ] = array();
				$attributeTypes[] = array( 'filter_attr_reference' => $attributeTypeData['reference'], 
                                   'filter_attr_name' => $attributeTypeData['name'], 
                                   'filter_attr_id' => $attributeTypeData['ID'] );
			}
			// 2. Uložení výsledků dotazu do mezipaměti 
			$attributeTypesCache = $this->registry->getObject('db')->cacheData( $attributeTypes );
			
      // 3. Výsledek z mezipaměti se uloží do značky šablony 
			$this->registry->getObject('template')->getPage()->addTag( 'filter_attribute_types', array( 'DATA', $attributeTypesCache ) );
			
			// 4. Dotaz na všechny typy atributů, seřazené podle jejich vlastního řazení 
			$sql = "SELECT v.name as attrName, t.reference as attrType, v.ID as attrID 
                FROM document_filter_attribute_values v, document_filter_attribute_types t 
                WHERE t.ID=v.attributeType 
                ORDER BY v.order ASC";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() != 0 )
			{
				// 5. Projdeme výsledky a každou z hodnot uložíme do pole pro odpovídající typ atributu 
				while( $attributeValueData = $this->registry->getObject('db')->getRows() )
				{
					$data = array();
					$data['attribute_value'] = $attributeValueData['attrName'];
					$data['attribute_URL_extra'] = 'document/filter/' . $attributeValueData['attrType'] . '/' . $attributeValueData['attrID'];
					$attributeValues[ $attributeValueData['attrType'] ][] = $data;
				}
			}
			// 6. Pole jednotlivých typů atributů uložíme do mezipaměti a každému z nich přiřadíme značku šablony. Díky tomu může každá skupina hodnot vyplnit patřičný seznam typu atributu. 
			foreach( $attributeValues as $type => $data )
			{
				$cache = $this->registry->getObject('db')->cacheData( $data );
				$this->registry->getObject('template')->getPage()->addPPTag( 'attribute_values_' . $type, array( 'DATA', $cache ) );
			}
		}

			
		// Rozšířený filtr
		$sql = "SELECT ID, name, description FROM document_attributes";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() != 0 ){
      $this->registry->getObject('template')->addTemplateBit('extendedfilter', 'extendedfilter.tpl.php');  		  
    
			$attributeValues = array();
			$attributes = array();
			while( $attributesData = $this->registry->getObject('db')->getRows() )
			{
      	$attributeValues[ $attributesData['ID'] ] = array();
				$attributes[] = array( 'extfilter_attr_name' => $attributesData['name'], 
                               'extfilter_attr_description' => $attributesData['description'], 
                               'extfilter_attr_id' => $attributesData['ID'] );
			}
      $attributesCache = $this->registry->getObject('db')->cacheData( $attributes );
			$this->registry->getObject('template')->getPage()->addTag( 'extfilter_attribute', array( 'DATA', $attributesCache ) );
			
			// Dotaz na všechny typy atributů, seřazené podle jejich vlastního řazení 
			$sql = "SELECT * FROM document_attribute_values ORDER BY 'order',name ASC";
			$this->registry->getObject('db')->executeQuery( $sql );
			
      if( $this->registry->getObject('db')->numRows() != 0 )
			{
				$tempData = array();
        while( $attributeValueData = $this->registry->getObject('db')->getRows() )
				{
					$data = array();
					$data['extattribute_parentId'] = $attributeValueData['attribute_id'];					
					$data['extattribute_id'] = $attributeValueData['ID'];					
					$data['extattribute_value'] = $attributeValueData['name'];
          $data['extattribute_attr_style'] = $attributeValueData['style'];
          $key = 'filtr_' . $attributeValueData['attribute_id'] . '_'.$attributeValueData['ID'];
          
          if ( isset($_POST[$key]) )
            $data['extattribute_checked'] = "checked=\"checked\"";
          else
            $data['extattribute_checked'] = '';					
          $tempData[] = $data;    
				}                                              
        
        foreach( $tempData as $data )
        {			
          $attrID = $data['extattribute_parentId'];
          $attrValueID = $data['extattribute_id'];
    			
          $sql = "SELECT  a.document_ID as attr_document_ID,a.attribute_value_id, p.status,p.code
                  FROM document_attribute_value_association AS a 
                  LEFT JOIN (SELECT ID,code,status FROM content_types_document) p ON (p.ID = a.document_ID)
                  WHERE a.attribute_id = $attrID AND a.attribute_value_id = $attrValueID AND p.status = 'stock'";
          
          $this->registry->getObject('db')->executeQuery( $sql );
    			if( $this->registry->getObject('db')->numRows() != 0 )
          {
            $deb->Trace('Existuje');
            $attributeValues[$attrID][] = $data;    
          }else{$deb->Trace('NeExistuje');}
        }
			}
      
      foreach( $attributeValues as $type => $data )
			{			
        $cache = $this->registry->getObject('db')->cacheData( $data );
		    $this->registry->getObject('template')->getPage()->addPPTag( 'extattribute_values_' . $type, array( 'DATA', $cache ) );
			}
    
    }else{
			$this->registry->getObject('template')->getPage()->addTag( 'extendedfilter', '' );
    }

	
  }
	
	/**
	 * Vygeneruje příkaz SQL pro filtrování produktů na základě parametrů adresy URL
	 * @param array $bits bity obsažené v adrese URL
	 * @return void
	 */
	private function filterdocument( $bits )
	{
		// určení typů atributů 
		$sql = "SELECT ID, reference, name, documentContainedAttribute 
              FROM  document_filter_attribute_types ";
		$this->registry->getObject('db')->executeQuery( $sql );
		while( $type = $this->registry->getObject('db')->getRows() )
		{
			$this->filterTypes[ $type['reference'] ] = array( 'ID' => $type['ID'], 'reference'=>$type['reference'], 
          'name' => $type['name'], 'documentContainedAttribute'=>$type['documentContainedAttribute'] );
		}
		
		// určení hodnot atributů
		$sql = "SELECT ID, name, lowerValue, upperValue FROM document_filter_attribute_values";
		$this->registry->getObject('db')->executeQuery( $sql );
		while( $value = $this->registry->getObject('db')->getRows() )
		{
			$this->filterValues[ $value['ID'] ] = array( 'ID' => $value['ID'], 'name' => $value['name'], 'lowerValue' => $value['lowerValue'], 'upperValue' => $value['upperValue'] );
		}
		
		// zpracování adresy URL
		foreach( $bits as $position => $bit )
		{
			// jedná se o filtrovací část adresy URL? 
			if( $bit == 'filter' )
			{
				// dvě následující se předají metodě addToFilter 
				$this->addToFilter( $bits[ $position+1], $bits[ $position+2] );
			}
		}
		
		// předpokládáme, že k filtrování nedojde
		$somethingToFilter = false;
		
    // základní dotaz na databázi
		$sql = "SELECT prod.contentID,prod.document_path,prod.document_name,prod.description,
            prod.document_price,prod.code,prod.stock,prod.reserve,prod.document_id,
            prod.document_path,prod.document_name,prod.document_image,IF(basketQty is NULL,0,basketQty) as basketQty,
            IF(basketQty >= stock,'reserve',prod.document_status) as document_status,
            prod.metakeywords,prod.metadescription,prod.metarobots
      FROM (SELECT c.ID as contentID,c. path as document_path, v.name as document_name, v.heading as description,
                    p.price as document_price, p.code, p.stock, p.reserve, p.status  as document_status,
                    cat.ID as document_id, cat.path as document_path, catv.name as document_name, 
                    img.image as document_image,v.metakeywords, v.metadescription, v.metarobots
              FROM content c, content_types t, content_versions v, content_types_document p, content as cat, 
                   content_versions as catv, content_types_document_in_categories pic, document_images img
              WHERE c.active=1 AND c.secure=0 AND t.ID=c.type AND t.name like 'Produkty' AND c.current_versionID=v.ID 
                    AND p.content_versionID=v.ID AND pic.contentID=c.ID AND cat.ID=pic.document_id 
                    AND catv.ID=cat.current_versionID AND img.ID=p.main_imageID
              ORDER BY c.ID DESC) prod
      LEFT JOIN (SELECT contentID,sum(quantity) basketQty FROM basket_contents GROUP BY contentID) bas
        ON (bas.contentID = prod.contentID)";
		
    $filtr = '';
		
    if( !empty( $this->filterAssociations ) )
		{
			// bude se filtrovat 
			$somethingToFilter = true;
			// sestavení dotazu
			$filtr = " WHERE ( SELECT COUNT( * ) FROM document_filter_attribute_associations pfaa WHERE ( ";
			$assocs = implode( " AND ", $this->filterAssociations );
			$filtr .= $assocs;
			$filtr .= " )AND pfaa.document = prod.contentID )={$this->filterCount}";
		}
		if( !empty( $this->filterDirect ) )
		{
			// bude se filtrovat
			$somethingToFilter = true;
			// sestavení dotazu
			if ($filtr == '')
        $filtr = ' WHERE ';
      else
        $filtr .= " AND ";
			$assocs = implode( " AND ", $this->filterDirect );
			$filtr .= $assocs;
		}
  	$sql .= $filtr;
		
		if( $somethingToFilter )
		{
			// proběhne filtrování, uložíme výsledný dotaz
			$this->filterSQL = $sql;
		}else{
      $this->registry->getObject('template')->getPage()->addTag( 'filter_reference', '' );    
    } 
	}
	
	/**
	 * Přidá části kódu SQL do filtrovacích polí pro snazší 
	 * sestavení výsledného dotazu 
	 * @param String $filterType typ atributu, podle kterého se filtruje 
	 * @param int $filterValue identifikátor hodnoty atributu 
 	 * @return void
	 */
	private function addToFilter( $filterType, $filterValue )
	{
		global $caption, $deb;
		
    if( $this->filterTypes[ $filterType ]['documentContainedAttribute'] == 1 )
		{
			$lower = $this->filterValues[ $filterValue ]['lowerValue'];
			$upper = $this->filterValues[ $filterValue ]['upperValue'];
			$sql = " {$filterType} >= {$lower} AND {$filterType} < {$upper}";
			$this->filterDirect[] = $sql;
	    $filterText = $caption['filter'] .': <span>'. $this->filterTypes[ $filterType ]['name']; 
      $filterText .= ' '.$this->filterValues[ $filterValue ]['name'] .'</span>';  
      $this->registry->getObject('template')->getPage()->addTag( 'filter_reference', $filterText );
		}
		else
		{
			$this->filterCount++;
			$sql = " pfaa.attribute={$filterValue} ";
			$this->filterAssociations[] = $sql;
		}
	}

  private function setFilterdocument()
	{
		global $caption, $config, $deb;
		
    if ( !isset($_POST) )
      return;    
		
    $searchPhrase = null;
    foreach ($_POST as $k => $v){
      if (substr($k,0,6) == 'filtr_'){       
        $filter = explode('_',$k);
        if ( isset($filter[2]) ) 
          $searchPhrase .= ($searchPhrase != '')?','.$filter[2]:$filter[2];
      }
    }

    if( !isset( $searchPhrase ) )
		{
      if (isset($_COOKIE["document_searchPhrase"]))
        $searchPhrase = $_COOKIE["document_searchPhrase"];
    }else{
      $expire=time()+60*60*1;
      setCookie( 'document_searchPhrase', $searchPhrase, $expire );
    }
    
    if ( isset( $searchPhrase ))
    {  
      $searchPhraseText = '';
      $sql = "SELECT v.ID, v.attribute_id, v.name as value, a.description as name 
                FROM  document_attribute_values v, document_attributes a  
                WHERE a.ID=v.attribute_id AND v.ID IN ($searchPhrase) 
                ORDER BY v.attribute_id";
      $this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				$lastName = '';
        while( $data = $this->registry->getObject('db')->getRows() )
				{
					if ($lastName <> $data['name']){
            $searchPhraseText .= '<br />'.$data['name'].': ';
          }else{
            $searchPhraseText .= ($searchPhraseText != '')?',':'';
          }
          $searchPhraseText .= $data['value'];					
  				$lastName = $data['name'];
        }
      }
      
      $this->registry->getObject('template')->getPage()->addTag( 'filter_reference', 'Filtr: '.$searchPhraseText );
		
			$sql = "
        SELECT DISTINCT a.document_ID as attr_document_ID,a.attribute_value_id,
                b.contentID,b.document_path,b.document_name,b.description,b.versionID,b.document_ID,b.document_price,
                b.code,b.stock,b.reserve,b.document_id,b.document_path,b.document_name,b.document_image,b.basketQty,
                b.document_status,b.metakeywords,b.metadescription,b.metarobots         
          FROM document_attribute_value_association a 
  			  LEFT JOIN (
            SELECT prod.contentID,prod.document_path,prod.document_name,prod.description,prod.versionID,
                  prod.document_ID,prod.document_price,prod.code,prod.stock,prod.reserve,prod.document_id,
                  prod.document_path,prod.document_name,prod.document_image,IF(basketQty is NULL,0,basketQty) as basketQty,
                  IF(basketQty >= stock,'reserve',prod.document_status) as document_status,
                  prod.metakeywords,prod.metadescription,prod.metarobots
            FROM (SELECT c.ID as contentID,c. path as document_path, v.name as document_name, v.heading as description,
                          p.ID as document_ID,p.price as document_price, p.code, p.stock, p.reserve, p.status  as document_status,
                          v.ID as versionID,cat.ID as document_id, cat.path as document_path, catv.name as document_name, 
                          img.image as document_image,v.metakeywords, v.metadescription, v.metarobots
                    FROM content c, content_types t, content_versions v, content_types_document p, content as cat, 
                         content_versions as catv, content_types_document_in_ pic, document_images img
                    WHERE c.active=1 AND c.secure=0 AND t.ID=c.type AND t.name like 'Produkty' AND c.current_versionID=v.ID 
                          AND p.content_versionID=v.ID AND pic.contentID=c.ID AND cat.ID=pic.document_id 
                          AND catv.ID=cat.current_versionID AND img.ID=p.main_imageID
                    ORDER BY c.ID DESC) prod
            LEFT JOIN (SELECT contentID,sum(quantity) basketQty FROM basket_contents GROUP BY contentID) bas
              ON (bas.contentID = prod.contentID)) b
            ON (a.document_ID=b.document_ID)
          WHERE a.attribute_value_id IN ($searchPhrase) AND stock > 0";
      
      $this->filterSQL = $sql;
      
      $cache = $this->registry->getObject('db')->cacheQuery( $sql );
			if( $this->registry->getObject('db')->numRowsFromCache( $cache ) == 0 )
			{
				// množina výsledků dotazu je prázdná, zobrazí se šablona bez výsledků
         
        $this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['msg_ItemNotFound'] );
        $this->registry->getObject('template')->getPage()->addTag('pagecontent', $searchPhraseText);
      }
			else
			{
				$this->listdocument( '' );			
			}
		}
		else
		{
			// uživatel neodeslal vyhledávací formulář, zobrazí se tedy pouze stránka s vyhledávacím polem 			
      $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'document-searchform.tpl.php', 'footer.tpl.php');
		}
	}
}
?>