<?php
/**
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 */
class Categorycontroller{
	
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
      #$this->filterCategory( $urlBits );

			if( !isset( $urlBits[1] ) )
			{		
        $this->categoryNotFound();
			}
			else
			{
				if( !isset( $urlBits[2] ) )
				{		
					$ID = 0;
				}
				else
				{
					$ID = $urlBits[2];
				}
					switch( $urlBits[1] )
				{
					case 'view':
						$this->listDocuments($ID);
						break;
					case 'search':
						$this->searchCategory();
						break;
					case 'setfilter':
						$this->setFilterCategory();
						break;
					default:				
						$this->listDocuments('');
						break;		
				}
			}
  		$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'Document/search');
		}
	}
	
	/**
	 * @return void
	 */
	private function categoryNotFound()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-category.tpl.php', 'footer.tpl.php');
	}
	
	private function listDocuments( $categoryId )
	{
		global $config, $caption;

		
    $sql = "SELECT name
							FROM katalog AS k
	            WHERE close = 0 AND k.categoryId={$categoryId}";

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
		
		$this->registry->getObject('template')->getPage()->addTag( 'CategoryItems', array( 'SQL', $cache ) );
		$this->registry->getObject('template')->getPage()->addTag( 'pageLink', 'Zápisy z rady a představenstva' );
		
    $cacheCategory = $this->registry->getObject('db')->cacheQuery( $sql );
		if( $this->registry->getObject('db')->numRowsFromCache( cacheCategory ) != 0 )
		{
			while ($category = $this->$registry->getObject('db')->resultsFromCache( $cacheCategory ) )
			{
				$this->$registry->getObject('template')->getPage()->addTag( 'name', $category['name'] );
			}
		}
		    	
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-category.tpl.php', 'footer.tpl.php');
	}	
	
	
  private function searchCategory()
	{
		global $caption, $config;
		
    // kontrola jestli uživatel odeslal vyhledávací formulář 
		$searchcategory = null;
    if( isset( $_POST['search_text'] ) && $_POST['search_text'] != '' )
		{
		  $searchcategory = $_POST['search_text']; 
      $expire=time()+60*60*1;
      setCookie( 'category_search_text', $searchcategory, $expire );
    }else{
      if (isset($_COOKIE["category_search_text"]))
        $searchcategory = $_COOKIE["category_search_text"];
    }
    	
    if ( isset( $searchcategory ))
    {  
      // vyčistění hledané fráze 
			$searchPhrase = $this->registry->getObject('db')->sanitizeData( $searchcategory );
			$this->registry->getObject('template')->getPage()->addTag( 'filter_reference', $caption['filter'].': '.$searchcategory );
      
			// vyhledání, uložení výsledků do mezipaměti, tak aby byly připravené pro šablonu výsledků vyhledávání
			
			$sql = "SELECT prod.contentID,prod.category_path,prod.category_name,prod.description,
              prod.category_price,prod.code,prod.stock,prod.reserve,prod.category_id,
              prod.category_path,prod.category_name,prod.category_image,IF(basketQty is NULL,0,basketQty) as basketQty,
              IF(basketQty >= stock,'reserve',prod.category_status) as category_status,
              IF(category_name LIKE '%{$searchPhrase}%', 0, 1) as priorityb, 
              IF(description LIKE '%{$searchPhrase}%', 0, 1) as priorityc, 
              IF(code LIKE '%{$searchPhrase}%', 0, 1) as priority, 
              prod.metakeywords,prod.metadescription,prod.metarobots
        FROM (SELECT c.ID as contentID,c. path as category_path, v.name as category_name, v.heading as description,
                      p.price as category_price, p.code, p.stock, p.reserve, p.status  as category_status,
                      cat.ID as category_id, cat.path as category_path, catv.name as category_name, 
                      img.image as category_image,v.metakeywords, v.metadescription, v.metarobots
                FROM content c, content_types t, content_versions v, content_types_Category p, content as cat, 
                     content_versions as catv, content_types_Category_in_categories pic, category_images img
                WHERE c.active=1 AND c.secure=0 AND t.ID=c.type AND t.name like 'Produkty' AND c.current_versionID=v.ID 
                      AND p.content_versionID=v.ID AND pic.contentID=c.ID AND cat.ID=pic.category_id 
                      AND catv.ID=cat.current_versionID AND img.ID=p.main_imageID AND p.status = 'stock'
                ORDER BY c.ID DESC) prod
        LEFT JOIN (SELECT contentID,sum(quantity) basketQty FROM basket_contents GROUP BY contentID) bas
          ON (bas.contentID = prod.contentID)
        WHERE ( category_name LIKE '%{$searchPhrase}%' OR description LIKE '%{$searchPhrase}%' 
          OR code LIKE '%{$searchPhrase}%')
        ORDER BY priority, priorityb, priorityc ";
      
      $this->filterSQL = $sql;
      
      $cache = $this->registry->getObject('db')->cacheQuery( $sql );
			if( $this->registry->getObject('db')->numRowsFromCache( $cache ) == 0 )
			{
				$this->listcategory( '' );			
			}
			else
			{
				$this->listcategory( '' );			
			}
		}
		else
		{
			// uživatel neodeslal vyhledávací formulář, zobrazí se tedy pouze stránka s vyhledávacím polem 			
      $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'Category-searchform.tpl.php', 'footer.tpl.php');
		}
	}
	
	private function relatedCategory( $contentID )
	{
    $sql = "SELECT c.ID as contentID, v.name as category_name, c.path as category_path, img.image as category_image 
              FROM content c, content_versions v, category_sets ps, category_images as img,
                   content_types_Category p                                 
              WHERE v.ID=c.current_versionID AND c.ID=ps.contentID AND c.ID<>{$contentID} 
                AND ps.setID=(SELECT setID FROM `category_sets` WHERE contentID={$contentID})
                AND p.content_versionID=v.ID AND img.ID=p.main_imageID ";
		$relatedCategoryCache = $this->registry->getObject('db')->cacheQuery( $sql );
    $rows = $this->registry->getObject('db')->numRowsFromCache( $relatedCategoryCache );
    if ($rows > 0){ 
  		$this->registry->getObject('template')->addTemplateBit( 'relatedCategory_bit', 'category-related-img.tpl.php' );
		  $this->registry->getObject('template')->getPage()->addTag('relatedCategory', array( 'SQL', $relatedCategoryCache ) );
		}else
		  $this->registry->getObject('template')->getPage()->addTag('relatedCategory_bit', '' );
	}

	private function detailedCategory( $contentID )
	{
		$sql = "SELECT c.path as category_path,img.image as category_image, img.description as description 
              FROM category_images img, content c
              WHERE img.contentID = $contentID AND c.ID=img.contentID
              ORDER BY img.position";

    $detailedCategoryCache = $this->registry->getObject('db')->cacheQuery( $sql );
    $rows = $this->registry->getObject('db')->numRowsFromCache( $detailedCategoryCache );
    if ($rows > 1){ 
  		$this->registry->getObject('template')->addTemplateBit( 'detailedCategory_bit', 'category-detail-img.tpl.php' );
      $this->registry->getObject('template')->getPage()->addTag('detailedCategory', array( 'SQL', $detailedCategoryCache ) );
    }else
      $this->registry->getObject('template')->getPage()->addTag('detailedCategory_bit', '' );
	}
	
	private function generateFilterOptions()
	{
		global $deb;
    
    // 1. Dotaz na typy atributů 
		$sql = "SELECT ID, reference, name FROM category_filter_attribute_types";
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
                FROM category_filter_attribute_values v, category_filter_attribute_types t 
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
					$data['attribute_URL_extra'] = 'Category/filter/' . $attributeValueData['attrType'] . '/' . $attributeValueData['attrID'];
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
		$sql = "SELECT ID, name, description FROM category_attributes";
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
			$sql = "SELECT * FROM category_attribute_values ORDER BY 'order',name ASC";
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
    			
          $sql = "SELECT  a.category_ID as attr_category_ID,a.attribute_value_id, p.status,p.code
                  FROM category_attribute_value_association AS a 
                  LEFT JOIN (SELECT ID,code,status FROM content_types_Category) p ON (p.ID = a.category_ID)
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
	private function filterCategory( $bits )
	{
		// určení typů atributů 
		$sql = "SELECT ID, reference, name, categoryContainedAttribute 
              FROM  category_filter_attribute_types ";
		$this->registry->getObject('db')->executeQuery( $sql );
		while( $type = $this->registry->getObject('db')->getRows() )
		{
			$this->filterTypes[ $type['reference'] ] = array( 'ID' => $type['ID'], 'reference'=>$type['reference'], 
          'name' => $type['name'], 'categoryContainedAttribute'=>$type['categoryContainedAttribute'] );
		}
		
		// určení hodnot atributů
		$sql = "SELECT ID, name, lowerValue, upperValue FROM category_filter_attribute_values";
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
		$sql = "SELECT prod.contentID,prod.category_path,prod.category_name,prod.description,
            prod.category_price,prod.code,prod.stock,prod.reserve,prod.category_id,
            prod.category_path,prod.category_name,prod.category_image,IF(basketQty is NULL,0,basketQty) as basketQty,
            IF(basketQty >= stock,'reserve',prod.category_status) as category_status,
            prod.metakeywords,prod.metadescription,prod.metarobots
      FROM (SELECT c.ID as contentID,c. path as category_path, v.name as category_name, v.heading as description,
                    p.price as category_price, p.code, p.stock, p.reserve, p.status  as category_status,
                    cat.ID as category_id, cat.path as category_path, catv.name as category_name, 
                    img.image as category_image,v.metakeywords, v.metadescription, v.metarobots
              FROM content c, content_types t, content_versions v, content_types_Category p, content as cat, 
                   content_versions as catv, content_types_Category_in_categories pic, category_images img
              WHERE c.active=1 AND c.secure=0 AND t.ID=c.type AND t.name like 'Produkty' AND c.current_versionID=v.ID 
                    AND p.content_versionID=v.ID AND pic.contentID=c.ID AND cat.ID=pic.category_id 
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
			$filtr = " WHERE ( SELECT COUNT( * ) FROM category_filter_attribute_associations pfaa WHERE ( ";
			$assocs = implode( " AND ", $this->filterAssociations );
			$filtr .= $assocs;
			$filtr .= " )AND pfaa.category = prod.contentID )={$this->filterCount}";
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
		
    if( $this->filterTypes[ $filterType ]['categoryContainedAttribute'] == 1 )
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

  private function setFilterCategory()
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
      if (isset($_COOKIE["category_searchPhrase"]))
        $searchPhrase = $_COOKIE["category_searchPhrase"];
    }else{
      $expire=time()+60*60*1;
      setCookie( 'category_searchPhrase', $searchPhrase, $expire );
    }
    
    if ( isset( $searchPhrase ))
    {  
      $searchPhraseText = '';
      $sql = "SELECT v.ID, v.attribute_id, v.name as value, a.description as name 
                FROM  category_attribute_values v, category_attributes a  
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
        SELECT DISTINCT a.category_ID as attr_category_ID,a.attribute_value_id,
                b.contentID,b.category_path,b.category_name,b.description,b.versionID,b.category_ID,b.category_price,
                b.code,b.stock,b.reserve,b.category_id,b.category_path,b.category_name,b.category_image,b.basketQty,
                b.category_status,b.metakeywords,b.metadescription,b.metarobots         
          FROM category_attribute_value_association a 
  			  LEFT JOIN (
            SELECT prod.contentID,prod.category_path,prod.category_name,prod.description,prod.versionID,
                  prod.category_ID,prod.category_price,prod.code,prod.stock,prod.reserve,prod.category_id,
                  prod.category_path,prod.category_name,prod.category_image,IF(basketQty is NULL,0,basketQty) as basketQty,
                  IF(basketQty >= stock,'reserve',prod.category_status) as category_status,
                  prod.metakeywords,prod.metadescription,prod.metarobots
            FROM (SELECT c.ID as contentID,c. path as category_path, v.name as category_name, v.heading as description,
                          p.ID as category_ID,p.price as category_price, p.code, p.stock, p.reserve, p.status  as category_status,
                          v.ID as versionID,cat.ID as category_id, cat.path as category_path, catv.name as category_name, 
                          img.image as category_image,v.metakeywords, v.metadescription, v.metarobots
                    FROM content c, content_types t, content_versions v, content_types_Category p, content as cat, 
                         content_versions as catv, content_types_Category_in_ pic, category_images img
                    WHERE c.active=1 AND c.secure=0 AND t.ID=c.type AND t.name like 'Produkty' AND c.current_versionID=v.ID 
                          AND p.content_versionID=v.ID AND pic.contentID=c.ID AND cat.ID=pic.category_id 
                          AND catv.ID=cat.current_versionID AND img.ID=p.main_imageID
                    ORDER BY c.ID DESC) prod
            LEFT JOIN (SELECT contentID,sum(quantity) basketQty FROM basket_contents GROUP BY contentID) bas
              ON (bas.contentID = prod.contentID)) b
            ON (a.category_ID=b.category_ID)
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
				$this->listcategory( '' );			
			}
		}
		else
		{
			// uživatel neodeslal vyhledávací formulář, zobrazí se tedy pouze stránka s vyhledávacím polem 			
      $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'Category-searchform.tpl.php', 'footer.tpl.php');
		}
	}
}
?>