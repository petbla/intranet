<?php
/**
 * Objekt stránky pro našeho šablonového správce
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    1.7.2011 
 */
class page {


	// elementy stránky

	// titulek stránky 
	private $title = '';
	// značky šablony
	private $tags = array();
	// značky, které by se měli zpracovat po analýze stránky 
	// důvod: co když je v databázi více značek šablon - musíme analyzovat stránku 
  //        a poté ji analyzovat znovu kvůli značkám přidaným po analýze
	private $postParseTags = array();
	// části šablony
	private $bits = array();
	// obsah stránky
	private $content = "";
	
 	/**
	 * obsah stránky
	 */
    function __construct() { }

    /**
     * Získá titulek stránky 
     * @return String
     */
    public function getTitle()
    {
    	return $this->title;
    } // end function getTitle
    
    /**
     * Nastaví titulek stránky 
     * @param String $titulek titulek stránky 
     * @return void
     */
    public function setTitle( $title )
    {
	    $this->title = $title;
    } // end function setTitle
    
    /**
     * Nastavení obsahu stránky 
     * @param String $obsah obsah stránky 
     * @return void
     */
    public function setContent( $content )
    {
	    $this->content = $content;
    } // end function setContent
    
    /**
     * Přidej do stránky značku šablony a její hodnotu/data 
     * @param String $key - klíč, pod kterým se v poli značka uloží 
     * @param String $data - data (může se také jednat o pole) 
     * @return void
     */
    public function addTag( $key, $data )
    {
	    $this->tags[$key] = $data;
    } // end function addTag
    
    /**
     * Získej značky spojené se stránkou 
     * @return void
     */
    public function getTags()
    {
	    return $this->tags;
    } // end function getTags
    
    /**
     * Přidej značku vkládanou po analýze
     * @param String $klic klíč, který se vloží do pole
     * @param String $data data 
     * @return void
     */
    public function addPPTag( $key, $data )
    {
	    $this->postParseTags[$key] = $data;
    } // end function addPPTag
    
    /**
     * Získej značky, které se analyzují po provedení první analýzy
     * @return array
     */
    public function getPPTags()
    {
	    return $this->postParseTags;
    } // end function getPPtags
    
    /**
     * Přidej do stránky část šablony – momentálně se ještě nepřidává 
     * samotný obsah 
     * @param String tag - značka, do které se šablona přidá 
     * @param String bit - název souboru šablony 
     * @return void
     */
    public function addTemplateBit( $tag, $bit )
    {
	    $this->bits[ $tag ] = $bit;
    } // end function addTemplateBit
    
    /**
     * Získej části šablon, které se vloží do stránky 
     * @return array pole značek šablon a názvů souborů šablon 
     */
    public function getBits()
    {
	    return $this->bits;
    } // end function getBits
    
    /**
     * Získej blok obsahu stránky 
     * @param String tag - obalující blok ( <!-- START tag --> tor <!-- END tag --> ) 
     * @return String tor obsahu 
     */
    public function getBlock( $tag )
    {
      global $deb;
  		preg_match ('#<!-- START '. $tag . ' -->(.+?)<!-- END '. $tag . ' -->#si', $this->content, $tor);
 		
      $tor = str_replace ('<!-- START '. $tag . ' -->', "", $tor[0]);
  		$tor = str_replace ('<!-- END '  . $tag . ' -->', "", $tor);
  		
  		return $tor;
    } // end function getBlock
    
    public function getContent()
    {
	    return $this->content;
    } // end function getContent
}
?>