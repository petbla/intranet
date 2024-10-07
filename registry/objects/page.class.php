<?php
/**
 * Page templates management
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    7.4.20123
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
	private $postParseBits = array();
	// obsah stránky
	private $content = "";
	
 	/**
	 * obsah stránky
	 */
    function __construct() { }

    /**
     * Získá titulek stránky 
     * @return string
     */
    public function getTitle()
    {
    	return $this->title;
    } // end function getTitle
    
    /**
     * Nastaví titulek stránky 
     * @param string $titulek titulek stránky 
     * @return void
     */
    public function setTitle( $title )
    {
	    $this->title = $title;
    } // end function setTitle
    
    /**
     * Nastavení obsahu stránky 
     * @param string $obsah obsah stránky 
     * @return void
     */
    public function setContent( $content )
    {
	    $this->content = $content;
    } // end function setContent
    
    /**
     * Přidej do stránky značku šablony a její hodnotu/data 
     * @param string $key - klíč, pod kterým se v poli značka uloží 
     * @param string $data - data (může se také jednat o pole) 
     * @return void
     */
    public function addTag( $key, $data )
    {
	    $this->tags[$key] = $data;
    } // end function addTag
    
    /**
     * Získej značky spojené se stránkou 
     * @return array<string>
     */
    public function getTags()
    {
	    return $this->tags;
    } // end function getTags
    
    /**
     * Přidej značku vkládanou po analýze
     * @param string $klic klíč, který se vloží do pole
     * @param string $data data 
     * @return void
     */
    public function addPPTag( $key, $data )
    {
	    $this->postParseTags[$key] = $data;
    } // end function addPPTag
    
    /**
     * Získej značky, které se analyzují po provedení první analýzy
     * @return array<mixed>
     */
    public function getPPTags()
    {
	    return $this->postParseTags;
    } // end function getPPtags
    
    /**
     * Přidej do stránky část šablony – momentálně se ještě nepřidává 
     * samotný obsah 
     * @param string tag - značka, do které se šablona přidá 
     * @param string bit - název souboru šablony 
     * @return void
     */
    public function addTemplateBit( $tag, $bit )
    {
	    $this->bits[ $tag ] = $bit;
    } // end function addTemplateBit
    
    public function addPPTemplateBit( $tag, $bit )
    {
	    $this->postParseBits[ $tag ] = $bit;
    }
    
    /**
     * Získej části šablon, které se vloží do stránky 
     * @return array<mixed> pole značek šablon a názvů souborů šablon 
     */
    public function getBits()
    {
	    return $this->bits;
    } // end function getBits
    
    public function getPPBits()
    {
	    return $this->postParseBits;
    } 
    
    /**
     * Získej blok obsahu stránky 
     * @param string tag - obalující blok ( <!-- START tag --> tor <!-- END tag --> ) 
     * @return string tor obsahu 
     */
    public function getBlock( $tag )
    {
      global $deb;
  		preg_match ('#<!-- START '. $tag . ' -->(.+?)<!-- END '. $tag . ' -->#si', $this->content, $tor);
      if(!$tor)
        return '';

      $tor = str_replace ('<!-- START '. $tag . ' -->', "", $tor[0]);
  		$tor = str_replace ('<!-- END '  . $tag . ' -->', "", $tor);
  		
  		return $tor;
    } // end function getBlock
    
    public function getContent()
    {
	    return $this->content;
    } // end function getContent
}