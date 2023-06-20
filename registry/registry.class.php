<?php

/**
 * Registry object
 *
 * @author  Petr Blažek
 * @version 2.0
 * @date    7.4.2023
 */

class Registry
{

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
	private static $urlBase;
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
	 * @return object
	 */
	public static function singleton()
	{
		if (!isset(self::$instance)) {
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
		trigger_error($caption['reg_error_clone'], E_USER_ERROR);
	}

	/**
	 * Uloží objekt do registru
	 * @param string $object název objektu
	 * @param string $key klíč do pole
	 */
	public function storeObject($key, $object)
	{
		if (strpos($object, 'database') !== false) {
			$object_a = str_replace('.database', 'database', $object);
			$object = str_replace('.database', '', $object);
			require_once('databaseobjects/' . $object . '.database.class.php');
			$object = $object_a;
		} else {
			require_once('objects/' . $object . '.class.php');
		}

		self::$objects[$key] = new $object(self::$instance);
	} // end function storeObject

	/**
	 * Vrátí objekt z registru
	 * @param string $key klíč do pole použitý při uložení objektu
	 * @return mixed - objekt
	 */
	public function getObject($key)
	{
		if (is_object(self::$objects[$key])) {
			return self::$objects[$key];
		}
		return null;
	} // end function getObject 

	/**
	 * Uloží nastavení do registru
	 * @param string $data the setting we wish to store
	 * @param string $key klíč pro přístup k nastavení
	 */
	public function storeSetting($key, $data)
	{
		self::$settings[$key] = $data;
	} // end function storeSetting

	/**
	 * Vrátí nastavení z registru
	 * @param string $key klíč použitý pro uložení nastavení
	 * @return string nastavení
	 */
	public function getSetting($key)
	{
		return self::$settings[$key];
	} // end function getSetting


	/**
	 * Vrátí data z aktuální adresy URL
	 * 0 - controller   (document,archiv,news,contact)
	 * 1 - action       (list,view,edit,print,send)
	 * 2 - id           (<GUID>)
	 *    ..index.php?page=document/list
	 *    ..index.php?page=document/list/<GUID>
	 *    ..index.php?searchDocument=text&x=99&y=99
	 * @return void
	 */
	public function getURLData()
	{

		$urlPath = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '';
		$urlparam = (isset($_REQUEST['param'])) ? $_REQUEST['param'] : '';
		if(($urlparam == '') && ($urlPath != '')){
			$data = explode('/', $urlPath);
			$urlPath = $data[0]; 
			array_shift($data);
			$urlparam = implode('/', $data);	
		};
		$host = $_SERVER['HTTP_HOST'];
		$phpself = $_SERVER['PHP_SELF'];
		$urlBase = preg_replace('/(.)?index.php/i', '${1}', 'http://' . $host . $phpself);

		self::$urlPath = $urlPath;
		self::$urlParam = $urlparam;
		self::$urlBase = $urlBase;

		// $urlBits[0] ... Controller (Default='')  // ==> Empty
		if ($urlPath === '') {
			self::$urlBits[] = '';
			self::$urlPath = '';
		} else {
			// $urlPath = document/[list,view]/[GUID]
			// $urlBits[1,..] ... parametry
			$data = explode('/', $urlparam);
			$this->array_trim($data);
			if (gettype($data) === "array") {
				array_unshift($data, $urlPath);
			}
			self::$urlBits = $this->array_trim($data);
		}

		if ($urlPath) 
			return;
		
		if (isset($_REQUEST['search'])) {
			if (isset($_REQUEST['searchcontact_x'])) {
				$urldata = $_REQUEST['search'];
				$urldata = "general/searchContact/$urldata";
			} elseif (isset($_REQUEST['searchitem_x'])) {
				$urldata = $_REQUEST['search'];
				$urldata = "general/searchItem/$urldata";
			} elseif (isset($_REQUEST['searchglobal_x'])) {
				$urldata = $_REQUEST['search'];
				$urldata = "general/searchGlobal/$urldata";
			} else {
				$urldata = '';
			}
		} elseif (isset($_REQUEST['searchGlobal'])) {
			$urldata = $_REQUEST['searchGlobal'];
			$urldata = "general/searchGlobal/$urldata";
		} elseif (isset($_REQUEST['searchItem'])) {
			$urldata = $_REQUEST['searchItem'];
			$urldata = "general/searchItem/$urldata";
		} elseif (isset($_REQUEST['searchContact'])) {
			$urldata = $_REQUEST['searchContact'];
			$urldata = "general/searchContact/$urldata";
		} else {
			$urldata = '';
		}
		// $urldata = document/[list,view,search]/[GUID,text]
		self::$urlPath = $urldata;

		$data = explode('?', $_SERVER["REQUEST_URI"]);
		$urlparam = (isset($data[1])) ? $data[1] : '';
		// $urlparam = 
		// page           : page=document/[list,view]/[GUID]
		// searchDocument : text&x
		self::$urlParam = $urlparam;

		if ($urldata === '') {
			self::$urlBits[] = 'document';
			self::$urlPath = 'document';
		} else {
			// $urldata = document/[list,view]/[GUID]
			$data = explode('/', $urldata);
			while (!empty($data) && strlen(reset($data)) === 0) {
				array_shift($data);
			}
			while (!empty($data) && strlen(end($data)) === 0) {
				array_pop($data);
			}
			self::$urlBits = $this->array_trim($data);
		}


	} // end function getURLDate

	/**
	 * Summary of redirectUser
	 * @param string $urlPath
	 * @param string $header
	 * @param string $message
	 * @param bool $admin
	 * @return void
	 */
	public function redirectUser($urlPath, $header, $message, $admin = false)
	{
		self::getObject('template')->buildFromTemplates('header.tpl.php', 'redirect.tpl.php', 'footer.tpl.php');
		self::getObject('template')->getPage()->addTag('header', $header);
		self::getObject('template')->getPage()->addTag('message', $message);
		self::getObject('template')->getPage()->addTag('redirectURL', $_SERVER['HTTP_REFERER']);
		if ($admin != true) {
			self::getObject('template')->getPage()->addTag('url', $urlPath);
		} else {
			//
		}
	}

	/**
	 * Summary of gotoURL
	 * @param string $urlPath
	 * @param string $header
	 * @param string $message
	 * @return void
	 */
	public function gotoURL($urlPath, $header, $message)
	{
		self::getObject('template')->buildFromTemplates('header.tpl.php', 'gotoURL.tpl.php', 'footer.tpl.php');
		self::getObject('template')->getPage()->addTag('header', $header);
		self::getObject('template')->getPage()->addTag('message', $message);
		self::getObject('template')->getPage()->addTag('gotoURL', $urlPath);
	}

	/**
	 * Summary of getURLBits
	 * @return array<mixed><string>
	 */
	public function getURLBits()
	{
		return self::$urlBits;
	}

	/**
	 * Summary of getURLBit
	 * @param string $whichBit
	 * @return string
	 */
	public function getURLBit($whichBit)
	{
		return self::$urlBits[$whichBit];
	}

	/**
	 * Summary of getURLPath
	 * @return string
	 */
	public function getURLPath()
	{
		return self::$urlPath;
	}

	/**
	 * Summary of getURLParam
	 * @return string
	 */
	public function getURLParam()
	{
		return self::$urlParam;
	}

	/**
	 * Summary of getLevel
	 * @return mixed
	 */
	public function getLevel()
	{
		return self::$level;
	}

	/**
	 * Summary of setLevel
	 * @param mixed $level
	 * @return void
	 */
	public function setLevel($level)
	{
		self::$level = $level;
	}
	/**
	 * Summary of getEntryNo
	 * @return mixed
	 */
	public function getEntryNo()
	{
		return self::$entryNo;
	}

	/**
	 * Summary of setEntryNo
	 * @param mixed $entryNo
	 * @return void
	 */
	public function setEntryNo($entryNo)
	{
		self::$entryNo = $entryNo;
	}

	/**
	 * Summary of array_trim
	 * @param array<mixed><mixed> $array
	 * @return mixed
	 */
	private function array_trim($array)
	{
		while (!empty($array) && strlen(reset($array)) === 0) {
			array_shift($array);
		}

		while (!empty($array) && strlen(end($array)) === 0) {
			array_pop($array);
		}
		return $array;
	}
}
