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


require_once('pear/fpdf/fpdf.php');


// Load registry and config
require_once('registry/registry.class.php');
$registry = Registry::singleton();
require_once('registry/config.php');
$registry->getURLData();


// Set Cookies
if( isset($_COOKIE["maxVisibleItem"]) ){
  $config['maxVisibleItem'] = $_COOKIE["maxVisibleItem"];
}else
  $config['maxVisibleItem'] = 12;  


// Connect to database
$registry->getObject('db')->newConnection($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// zkontroluj data požadavku POST pro uživatele snažící se přihlásit a data relace 
// pro uživatele, kteří jsou přihlášení
$registry->getObject('authenticate')->checkForAuthentication();

// vyplnění objektu stránky ze šablony
$registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php', 'footer.tpl.php');


/*
$registry->getObject('template')->addTemplateBit('categories', 'categorymenu.tpl.php');

$activeControllers = array();
$registry->getObject('db')->executeQuery('SELECT controller FROM controllers WHERE active=1');
while( $activeController = $registry->getObject('db')->getRows() )
{
	$activeControllers[] = $activeController['controller'];
}
$currentController = $registry->getURLBit( 0 );

if( in_array( $currentController, $activeControllers ) )
{
	require_once( FRAMEWORK_PATH . 'controllers/' . $currentController . '/controller.php');
	$controllerInc = $currentController.'controller';

  $controller = new $controllerInc( $registry, true );
  
}
else
{
	require_once( FRAMEWORK_PATH . 'controllers/page/controller.php');
	$controller = new Pagecontroller( $registry, true );
}


// showItems Bits 
for ($i=12;$i<=84;$i=$i+12){ 
  if( $i == $config['maxVisibleItem'])
    $class='pageAct';
  else
    $class='pageNo';
  $aItem[] = array( 'showItem_items' => $i, 'showItem_class' => $class); 
}

*/

// Today
$dateText = $caption['TodayIs'].' ' . $registry->getObject('fce')->Date2FullText();
$registry->getObject('template')->getPage()->addTag( 'dateText', $dateText );


// vše analyzuj a zobraz výsledek
$registry->getObject('template')->parseOutput();

print $registry->getObject('template')->getPage()->getContent();

exit();

?>