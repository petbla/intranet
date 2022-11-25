<?php
/**
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.11.2022
 */
class Zobcontroller{
	
	private $registry;

	/**
	 * @param Registry $registry 
	 * @param bool $directCall – jedná se o přímé volání konstruktoru frameworkem (true) anebo jiným řadičem (false) 
	 */
	public function __construct( Registry $registry, $directCall )
	{
		global $config, $caption;
		$this->registry = $registry;
		$this->perSet = $this->registry->getObject('authenticate')->getPermissionSet();
        $this->prefDb = $config['dbPrefix'];
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();     

			if( !isset( $urlBits[1] ) )
			{		
				$this->pageNotFound();
			}
			else
			{
				$ID = '';
				switch ($urlBits[1]) {
					case 'list':
						$this->pageNotFound();
						break;
					default:
						$this->pageNotFound();
						break;
				}
			}
		}
	}
    /**
     * Zobrazení chybové stránky, pokud agenda nebyla nalezem 
     * @return void
     */
	private function pageNotFound()
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Pokus o zobrazení neznámé agendy",'agenda','');
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page-notfound.tpl.php', 'footer.tpl.php');
	}

    /**
     * Zobrazení chybové stránky s uživatelským textem
	 * @param String $message = text zobrazen jako chyba
     * @return void
     */
	private function error( $message )
	{
		// Logování
		$this->registry->getObject('log')->addMessage("Chyba: $message",'agenda','');
		// Nastavení parametrů
		$this->registry->getObject('template')->getPage()->addTag('message',$message);
		// Search BOX
		$this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
		// Sestavení stránky
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'page.tpl.php', 'footer.tpl.php');
	}
}