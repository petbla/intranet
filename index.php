<?php
/**
 * Framework
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018 
 */
 
// Start session
session_start();

// Variable definition
// cesta k aplikace, abychom mohli jednoduše připojit soubory umístěné
// v jiných adresářích
define( "FRAMEWORK_PATH", dirname( __FILE__ ) ."/" );


// Debug
require_once('debug/classDebug.php');
if (file_exists("mu.exe"))
  $deb = new debug('error',FRAMEWORK_PATH . 'debug/logFile.txt');  // info,trace,error
else
  $deb = new debug('info',FRAMEWORK_PATH . 'debug/logFile.txt');  // info,trace,error

// true - aktivace debugeru
// false - deaktivace logu
$deb->active(true);

// Load registry and config
require_once('registry/registry.class.php');
$registry = Registry::singleton();
require_once('registry/config.php');
$registry->getURLData();

$deb->trace('getURLData');


// Set Cookies
if( isset($_COOKIE["maxVisibleItem"]) ){
  $config['maxVisibleItem'] = $_COOKIE["maxVisibleItem"];
}else
  $config['maxVisibleItem'] = 30;  

if( isset($_COOKIE["HideHandledNote"]) ){
  $config['HideHandledNote'] = $_COOKIE["HideHandledNote"];
}

// Connect to database
$registry->getObject('db')->newConnection($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$deb->trace('Connect to database');

// Check database Update
$registry->getObject('db')->CheckPortal();
$registry->getObject('db')->SetPortal(0);
$registry->getObject('upgrade')->checkUpgrade();

$deb->trace('Check update');

// zkontroluj data požadavku POST pro uživatele snažící se přihlásit a data relace 
// pro uživatele, kteří jsou přihlášení
$registry->getObject('authenticate')->checkForAuthentication();

// vyplnění objektu stránky ze šablony
$registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php', 'footer.tpl.php');


// Přihlášení 
if (($registry->getObject('authenticate')->isLoggedIn()) || ($registry->getObject('authenticate')->isAdmin())) 
{  
  if ( $registry->getObject('authenticate')->isLoggedIn() ) 
  {
	if ($registry->getURLBit( 0 ) == 'logout'){
		$registry->getObject('authenticate')->logout();
		$registry->getObject('template')->addTemplateBit('logininfo',  'login.tpl.php');
	}else{
		$registry->getObject('template')->addTemplateBit('logininfo', 'logout.tpl.php');
	}
  }
  else
  {
	$registry->getObject('template')->getPage()->addTag('logininfo','');
}
}else{
  $registry->getObject('template')->addTemplateBit('logininfo','login.tpl.php');
}
$registry->getObject('template')->addTemplateBit('loginform','login-form.tpl.php');

$registry->getObject('template')->getPage()->addTag('Version',$registry->getObject('upgrade')->getVersion());
$registry->getObject('template')->getPage()->addTag('UserName',$registry->getObject('authenticate')->getUserName());


// Check Active Controllers
$activeControllers = array();
$activeControllers[] = 'document';
$activeControllers[] = 'contact';
$activeControllers[] = 'agenda';
$activeControllers[] = 'zob';
$activeControllers[] = 'general';
$activeControllers[] = 'admin';
$activeControllers[] = 'todo';
$currentController = $registry->getURLBit( 0 );  // controller

$deb->trace('Check active controllers');

if( in_array( $currentController, $activeControllers ) )
{
	require_once( FRAMEWORK_PATH . 'controllers/' . $currentController . '/controller.php');
	$controllerInc = $currentController.'controller';

  $controller = new $controllerInc( $registry, true );
  
}
else
{
	require_once( FRAMEWORK_PATH . 'controllers/document/controller.php');
	$controller = new Documentcontroller( $registry, true );
}


// Today
$dateText = $caption['TodayIs'].' ' . $registry->getObject('fce')->Date2FullText();
$registry->getObject('template')->getPage()->addTag( 'dateText', $dateText );

$deb->trace('Get Today');

// Category Menu
//$registry->getObject('document')->createCategoryMenu();

// Barmenu 
$perSet = $registry->getObject('authenticate')->getPermissionSet();
$isAdmin = $registry->getObject('authenticate')->isAdmin();
$contactBarMenuItem = $perSet > 0 ? "<li><a href='index.php?page=contact/list'>".$caption['Contacts']."</a></li>" : '';
$PortalCounter = $perSet == 9 ? $registry->getObject('db')->GetPortalCount() : 0;
switch ($perSet) {
	case 9:
		$calendarBarMenuItem = "<li><a href='https://teamup.com/ks7xn3roxm7uo5r44z' target='_blank'>Kalendář</a></li>";
		break;
	case 5:
		$calendarBarMenuItem = "<li><a href='https://teamup.com/ksekgkc9ebu9deai3e' target='_blank'>Kalendář</a></li>";
		break;
 	case 4:
 	case 3:
 	case 2:
		$calendarBarMenuItem = "<li><a href='https://teamup.com/ksx5ivfw8yrnn6gbqy' target='_blank'>Kalendář</a></li>";
		break;
	case 1:
		$calendarBarMenuItem = "<li><a href='https://teamup.com/ksmoedn1dphw6gy7vf' target='_blank'>Kalendář</a></li>";
		break;
	default:
		$calendarBarMenuItem = "";
		break;
}
$adminBarMenuItem = $isAdmin ? "<li><a href='index.php?page=admin'>Administrace</a></li>" : '';

$registry->getObject('template')->getPage()->addTag( 'adminBarMenuItem', $adminBarMenuItem );
$registry->getObject('template')->getPage()->addTag( 'contactBarMenuItem', $contactBarMenuItem );
$registry->getObject('template')->getPage()->addTag( 'calendarBarMenuItem', $calendarBarMenuItem );
$registry->getObject('template')->getPage()->addTag('compName',$config['compName']);

$deb->trace('Add tag to MENU');

// vše analyzuj a zobraz výsledek
$registry->getObject('template')->parseOutput();

print $registry->getObject('template')->getPage()->getContent();

$deb->trace('After PRINT');

exit();

?>