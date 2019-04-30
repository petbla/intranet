<?php
/**
 * Views: Správce šablon 
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    5.7.2011 
 */

class template {

	private $page;
	
	/**
 	 * Připojí soubor s definicí třídy page a vytvoří instanci této třídy, která bude sloužit pro správu obsahu a struktury stránky
	 */
  public function __construct( $registry ) 
  {
		$this->registry = $registry;
    include( FRAMEWORK_PATH . '/registry/objects/page.class.php');
    $this->page = new Page();
  }
  
  /**
   * Přidá do stránky část šablony
   * @param String $tag - značka, do které se vloží část šablony – např. {ahoj} 
   * @param String $bit - část šablony (cesta k souboru nebo jeho název) 
   * @return void
   */
  public function addTemplateBit( $tag, $bit )
  {
		if( strpos( $bit, 'views/' ) === false )
		{
		    $bit = 'views/' . $this->registry->getSetting('view') . '/templates/' . $bit;
		}
		$this->page->addTemplateBit( $tag, $bit );
  } // end function addTemplateBit
  
  /**
   * Načte části šablon stránky a vloží je to do obsahu stránky
   * Aktualizuje obsah stránek
   * @return void
   */
  private function replaceBits()
  {
    $bits = $this->page->getBits();
    // cyklus přes části šablony
    foreach( $bits as $tag => $template )
    {
	    $templateContent = file_get_contents( $template );
	    $newContent = str_replace( '{' . $tag . '}', $templateContent, $this->page->getContent() );
	    $this->page->setContent( $newContent );
    }
  } // end function replaceBits
  
  /**
   * Nahradí značky ve stránce požadovaným obsahem 
   * @return void
   */
  private function replaceTags( $pp = false )
  {
    // získej značky ve stránce 
    if( $pp == false )
    {
	     $tags = $this->page->getTags();
    }
    else
    {
	     $tags = $this->page->getPPTags();
    }
    // cyklus přes značky 
    foreach( $tags as $tag => $data )
    {
	    // pokud je značka pole, je zapotřebí víc než prosté „vyhledej a nahraď“
	    if( is_array( $data ))
      {
        if (isset($data[0]))
		    {
			    if( $data[0] == 'SQL' )
			    {
				    // jedná se o výsledek dotazu uložený v mezipaměti, značky se nahradí tímto výsledkem
				    $this->replaceDBTags( $tag, $data[1] );
			    }
			    elseif( $data[0] == 'DATA' )
			    {
				     // jedná se o data uložená v mezipaměti, značky se nahradí daty z mezipaměti 
				    $this->replaceDataTags( $tag, $data[1] );
			    }
    	  }
      }
    	else
    	{	
	    	// nahraď obsah    	
	    	$newContent = str_replace( '{' . $tag . '}', $data, $this->page->getContent() );
	    	// aktualizuj obsah stránky
	    	$this->page->setContent( $newContent );
    	}
    }
  } // end function replaceTags
  
  /**
   * Nahradí obsah stránky daty z databáze 
   * @param String $znacka značka definující oblast nahrazovaného obsahu 
   * @param int $idMezipameti identifikátor dotazu v mezipaměti 
   * @return void 
   */
  private function replaceDBTags( $tag, $cacheId )
  {
    global $deb;
    
    $block = '';
		$blockOld = $this->page->getBlock( $tag );
	
		// cyklus přes jednotlivé záznamy výsledku dotazu 
		while ($tags = $this->registry->getObject('db')->resultsFromCache( $cacheId ) )
		{
			$blockNew = $blockOld;
			// vytvoří nový blok s vloženými výsledky 
			foreach ($tags as $ntag => $data) 
	       	{
            $blockNew = str_replace("{" . $ntag . "}", $data, $blockNew); 
	        }
	        $block .= $blockNew;
		}
		
    $pageContent = $this->page->getContent();
		// odstraní oddělovač ze šablony => čistší kód HTML 
		$newContent = str_replace( '<!-- START ' . $tag . ' -->' . $blockOld . '<!-- END ' . $tag . ' -->', $block, $pageContent );
		// aktualizace obsahu stránky
		$this->page->setContent( $newContent );
	} // end function replaceDBTags
  
  /**
   * Nahradí obsah stránky daty z mezipaměti 
   * @param String $znacka značka definující oblast nahrazovaného obsahu 
   * @param int $idMezipameti identifikátor dat v mezipaměti 
   * @return void
   */
  private function replaceDataTags( $tag, $cacheId )
  {

		$block = '';
    $blockOld = $this->page->getBlock( $tag );
		$tags = $this->registry->getObject('db')->dataFromCache( $cacheId );
		
    foreach( $tags as $key => $tagsdata )
		{
			$blockNew = $blockOld;
			foreach ($tagsdata as $taga => $data) 
	       	{
	        	$blockNew = str_replace("{" . $taga . "}", $data, $blockNew); 
	        }
	        $block .= $blockNew;
		}

		$pageContent = $this->page->getContent();
		$newContent = str_replace( '<!-- START '.$tag.' -->'.$blockOld.'<!-- END '.$tag.' -->', $block, $pageContent );
		$this->page->setContent( $newContent );
  } // end function replaceDataTags 
  
  /**
   * Získá objekt strákny 
   * @return Object 
   */
  public function getPage()
  {
    return $this->page;
  } //end function getPage
  
  /**
   * Sestaví obsah stránky na základě několika šablon 
   * umístění jednotlivých šablon se předávají ve formě argumentů 
   * @return void
   */
  public function buildFromTemplates()
  {
    $bits = func_get_args();
    $content = "";
    foreach( $bits as $bit )
    {
	    if( strpos( $bit, 'views/' ) === false )
	    {
		    $bit = 'views/' . $this->registry->getSetting('view') . '/templates/' . $bit;
	    }
	    if( file_exists( $bit ) == true )
	    {
		    $content .= file_get_contents( $bit );
	    }
    }
    $this->page->setContent( $content );
  }  // end function buildFromTemplates
  
  /**
   * Převede pole dat na značky
   * @param array data
   * @param string prefix, který se přidá k názvu vytvářených značek
   * @return void
   */
  public function dataToTags( $data, $prefix )
  {
    global $deb;
    foreach( $data as $key => $content )
    {
	    $this->page->addTag( $prefix.$key, $content);
    }
  } // end function dataToTags
  
  /**
   * Načte titulek nastavený v objektu stránky a vloží ho do pohledu 
   */
  public function parseTitle()
  {
    $newContent = str_replace('<title>', '<title>'. $this->page->getTitle(), $this->page->getContent() );
    $this->page->setContent( $newContent );
  } // end function parseTitle
  
  /**
   * Analyzuje objekt stránky a vytvoří výstup 
   * @return void
   */
  public function parseOutput()
  {
    $this->replaceBits();            // Načte části šablon stránky a vloží je to do obsahu stránky
    $this->replaceTags(false);       // Nahradí značky ve stránce požadovaným obsahem
    $this->replaceTags(true);        // Nahradí značky ve stránce požadovaným obsahem - postParse
    $this->parseTitle();             // Načte titulek nastavený v objektu stránky a vloží ho do pohledu
  } // end function parseOutput

  public function getPageCounter( $iActualPage, $iCount ){
  global $config;

    $iMax = $config['maxVisibleLines'];

    // Page Navigator
    $sPageNavigator = "";      
    $iPages = $iCount / $iMax;
    $iPages = (int) $iPages; 
    if (($iCount % $iMax) > 0)
      $iPages++;
    
    if ($iPages > 1){
      $sPageNavigator = "&nbsp;&nbsp;";
      if ($iActualPage > 1){
        $sPageNavigator .= "<a href=\"?page=1\">&nbsp;<img src=\"files/image/navigate/firstpage.png\" title=\"$caption[first]\" alt=\"$caption[first]\"/>&nbsp;</a>";
        $iPageNo = $iActualPage - 1;
        $sPageNavigator .= $tpl->tbHtml( $sFile, 'PAGE_PREV'); 
      }
      
      if ($iPages > 7){
        $iPageNo = (($iActualPage - 4) < 1) ? 1 : $iActualPage - 4;
        $iPages = (($iActualPage + 7) > $iPages) ? $iPages : $iActualPage + 7; 
      }else{
        $iPageNo=1;
      }
      
      for ($iPageNo; $iPageNo<=$iPages; $iPageNo++){
        if ($iPageNo == $iActualPage)         
          $sPageNavigator .= $tpl->tbHtml( $sFile, 'PAGE_ACTUAL');
        else
          $sPageNavigator .= $tpl->tbHtml( $sFile, 'PAGE_LINK');
        if ($iPageNo < $iPages) 
          $sPageNavigator .= "|";
      }  
      if ($iActualPage < $iPages){
        $iPageNo = $iActualPage + 1;
        $sPageNavigator .= $tpl->tbHtml( $sFile, 'PAGE_NEXT');     
        $iPageNo = $iPages; 
        $sPageNavigator .= $tpl->tbHtml( $sFile, 'PAGE_LAST');
      } 
    }
    return $sPageNavigator;  
  }
  
  public function NavigateElement( $pageNo, $countPage ){
    global $caption;
    
    if ($countPage == 0)
      return ''; 
      
    $navigate = "";
    
    if ($pageNo > 2)
      $navigate = $this->NavigateBit( $pageNo, $countPage, 'first' ); 
    if ($pageNo > 1)
      $navigate .= $this->NavigateBit( $pageNo, $countPage, 'prev' ); 
    
    
    $fromPage = ($pageNo > 10) ? $pageNo - ($pageNo % 10) : 1;
    $toPage = ($countPage < $fromPage + 9) ? $countPage : $fromPage + 9;
    if (($toPage - $fromPage) < 9)
      $fromPage = ($toPage - 9 < 1) ? 1 : $toPage - 9;  
    
    if ($fromPage > 1){
        $navigate .= $this->NavigateBit( $pageNo, $countPage, 1 );
        $navigate .= '...';
    }
    
    if ($countPage > 1){
      for ($i=$fromPage ; $i<=$toPage ; $i++)
        $navigate .= $this->NavigateBit( $pageNo, $countPage, $i );
    }
    if ($toPage < $countPage){
        $navigate .= '...';
        $navigate .= $this->NavigateBit( $pageNo, $countPage, $countPage );
    }
    
    if ($pageNo < $countPage )
      $navigate .= $this->NavigateBit( $pageNo, $countPage, 'next' ); 
    if ($pageNo < $countPage-1 )
      $navigate .= $this->NavigateBit( $pageNo, $countPage, 'last' );
      
    return $navigate; 
  }    
  
  private function NavigateBit( $actualPage, $countPage, $symbol ){
    global $caption;
    $element = 'page';
     
    $imgPath = 'views/' . $this->registry->getSetting('view') . '/images/navigate/';
    if ( isset($_GET["page"]) )
      $urlPath = $_GET["page"];
    else{
      if ( isset($_GET["search"]) ){
        $urlPath = $_GET["search"];
        $element = 'search';
      }
      else
        $urlPath = '';            
    }

    switch ($symbol)
    {
      case 'first':
        $actualPage = 1;
        $symbol = "<img src=\"$imgPath/firstpage.png\""
                  . " title=\"" . $caption['btn_firstpage']. "\"" 
                  . " alt=\"" . $caption['btn_firstpage'] . "\" />";
        break;
      case 'prev':
        $actualPage = ($actualPage < 2) ? 1 : $actualPage - 1;
        $symbol = "<img src=\"$imgPath/prevpage.png\""
                  . " title=\"" . $caption['btn_prevpage']. "\"" 
                  . " alt=\"" . $caption['btn_prevpage'] . "\" />";
        break;
      case 'next':
        $actualPage = ($actualPage > $countPage) ? $countPage : $actualPage + 1;
        $symbol = "<img src=\"$imgPath/nextpage.png\""
                  . " title=\"" . $caption['btn_nextpage']. "\"" 
                  . " alt=\"" . $caption['btn_nextpage'] . "\" />";
        break;
      case 'last':
        $actualPage = $countPage;
        $symbol = "<img src=\"$imgPath/lastpage.png\""
                  . " title=\"" . $caption['btn_lastpage']. "\"" 
                  . " alt=\"" . $caption['btn_lastpage'] . "\" />";
        break;
      default:
        // create image with $symbol 
        if ( $actualPage == $symbol ) 
          $symbol = "<strong>" . $symbol . "</strong>";
        else
          $actualPage = $symbol;
        $symbol = "<span>" . $symbol . "</span>";
        
        break;
    }
    $element = "<a href=\"index.php?$element=$urlPath&p=$actualPage\">" . $symbol . "</a>";
    return $element;  
  }
}
?>