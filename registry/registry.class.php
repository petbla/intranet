<?php
/**
 * Registry object
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018 
 */
 
class Registry {
	
	/**
	 * pole objektů uložených v registru
	 * @access private
	 */
	private static $objects = array();
	
	/**
	 * pole nastavení uložených v registru
	 * @access private
	 */
	private static $settings = array();
	
	
	/**
	 * instance registru
	 * @access private
	 */
	private static $instance;
	
	private static $level;
	private static $entryNo;
	private static $urlPath;
	private static $urlParam;
	private static $urlBits = array();
	
	/**
	 * Soukromý konstrutkor zabrání přímému vytvoření
	 * @access private
	 */
	private function __construct()
	{
	
	}
		
	/**
	 * metoda singleton pro přístup k objektu
	 * @access public
	 * @return 
	 */
	public static function singleton()
	{
		if( !isset( self::$instance ) )
		{
			$obj = __CLASS__;
			self::$instance = new $obj;
		}
		
		return self::$instance;
	} // end function singleton
	
	/**
	 * zabrání kolonování objektu: vyvolá chybu E_USER_ERROR
	 */
	public function __clone()
	{
	 global $caption;
    trigger_error( $caption['reg_error_clone'], E_USER_ERROR );
	}
	
	/**
	 * Uloží objekt do registru
	 * @param String $object název objektu
	 * @param String $key klíč do pole
	 * @return void
	 */
	public function storeObject( $key, $object )
	{
    if( strpos( $object, 'database' ) !== false )
		{
			$object_a = str_replace( '.database', 'database', $object);
			$object = str_replace( '.database', '', $object);
			require_once('databaseobjects/' . $object . '.database.class.php');
			$object = $object_a;
		}
		else
		{
			require_once('objects/' . $object . '.class.php');
		}
		
		self::$objects[ $key ] = new $object( self::$instance );
	} // end function storeObject
	
	/**
	 * Vrátí objekt z registru
	 * @param String $key klíč do pole použitý při uložení objektu
	 * @return object - objekt
	 */
	public function getObject( $key )
	{
		if( is_object ( self::$objects[ $key ] ) )
		{
			return self::$objects[ $key ];
		}
	} // end function getObject 
	
	/**
	 * Uloží nastavení do registru
	 * @param String $data the setting we wish to store
	 * @param String $key klíč pro přístup k nastavení
	 * @return void
	 */
	public function storeSetting( $key, $data )
	{
		self::$settings[ $key ] = $data;
	} // end function storeSetting
	
	/**
	 * Vrátí nastavení z registru
	 * @param String $key klíč použitý pro uložení nastavení
	 * @return String nastavení
	 */
	public function getSetting( $key )
	{
		return self::$settings[ $key ];
	} // end function getSetting
	
	
	/**
	 * Vrátí data z aktuální adresy URL
	 * @return void
	 */
	public function getURLData()
	{
		/* 
		   0 - controller   (document,archiv,news,contact)
		   1 - action       (list,view,edit,print,send)
		   2 - id           (<GUID>)
		   ..index.php?page=document/list
		   ..index.php?page=document/list/<GUID>
		   ..index.php?page=document/view/<GUID>
		   ..index.php?searchDocument=text&x=99&y=99
	    */
		

		$user =  getenv("username");
		$guid = self::getObject('fce')->GUID();

		$urldata = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '' ;
		if (!$urldata)
		{
			if (isset($_REQUEST['searchDocument']))
			{
				$urldata = $_REQUEST['searchDocument'];
				$urldata = "document/search/$urldata"; 
			} 
			else
			{
				$urldata = '';
			}
		}		
		// $urldata = document/[list,view,search]/[GUID,text]
		self::$urlPath = $urldata;
		
		$data = explode( '?', $_SERVER["REQUEST_URI"] );
		$urlparam = (isset($data[1])) ? $data[1] : '' ;
		// $urlparam = 
		// page           : page=document/[list,view]/[GUID]
		// searchDocument : text&x
		self::$urlParam = $urlparam;

		if( $urldata == '' )
		{
			self::$urlBits[] = 'document';
			self::$urlPath = 'document';
		}
		else
		{
			// $urldata = document/[list,view]/[GUID]
			$data = explode( '/', $urldata );
			while ( !empty( $data ) && strlen( reset( $data ) ) === 0 ) 
			{
				array_shift( $data );
			}
			while ( !empty( $data ) && strlen( end( $data ) ) === 0) 
			{
				array_pop($data);
			}
			self::$urlBits = $this->array_trim( $data );
		}
	} // end function getURLDate
	
	public function redirectUser( $urlPath, $header, $message, $admin = false)
	{
    self::getObject('template')->buildFromTemplates('header.tpl.php', 'redirect.tpl.php','footer.tpl.php');
		self::getObject('template')->getPage()->addTag( 'header', $header );
		self::getObject('template')->getPage()->addTag( 'message', $message );
    self::getObject('template')->getPage()->addTag( 'redirectURL', $_SERVER['HTTP_REFERER'] );
		if( $admin != true )
		{
			self::getObject('template')->getPage()->addTag('url', $urlPath );
		}
		else
		{
			//
    }
	}

	public function gotoURL( $urlPath, $header, $message)
	{
 	  self::getObject('template')->buildFromTemplates('header.tpl.php', 'gotoURL.tpl.php','footer.tpl.php');
		self::getObject('template')->getPage()->addTag( 'header', $header );
		self::getObject('template')->getPage()->addTag( 'message', $message );
		self::getObject('template')->getPage()->addTag( 'gotoURL', $urlPath );
	}
	
	public function getURLBits()
	{
    return self::$urlBits;
	}
	
	public function getURLBit( $whichBit )
	{
		return self::$urlBits[ $whichBit ];
	}
	
	public function getURLPath()
	{
		return self::$urlPath;
	}

	public function getURLParam()
	{
		return self::$urlParam;
	}
	
	public function getLevel()
	{
		return self::$level;
	}

	public function setLevel($level)
	{
		self::$level = $level;
	}
	public function getEntryNo()
	{
		return self::$entryNo;
	}

	public function setEntryNo($entryNo)
	{
		self::$entryNo = $entryNo;
	}


	private function array_trim( $array ) 
	{
	    while ( ! empty( $array ) && strlen( reset( $array ) ) === 0) 
	    {
	        array_shift( $array );
	    }
	    
	    while ( !empty( $array ) && strlen( end( $array ) ) === 0) 
	    {
	        array_pop( $array );
	    }
	    return $array;
	}
}
?>