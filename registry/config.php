<?php
/**
 * Config files 
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    18.11.2018
 */

 // načti informace o připojení k databázi 
require_once('_private/config.php');


/**
 *  Web setting
 */ 
$config['skin']             = 'classic';
$config['metakeywords']		= '';
$config['Copyright']        = '&copy; DMS 2019';

$registry->storeSetting('lang','cs');
require_once('lang/'.$registry->getSetting('lang').'.php');

/**
 *  Save to Registry
 */
$registry->storeObject('db', 'mysql.database');
$registry->storeObject('template', 'template');
$registry->storeObject('authenticate', 'authentication');
$registry->storeObject('fce', 'usefulfunction');
$registry->storeObject('document', 'document');
$registry->storeObject('file','file');
$registry->storeObject('upgrade','upgrademanagement');
$registry->storeObject('log','login');

/**
 *  PEAR modul and set library
 */ 

ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.'../'.PATH_SEPARATOR.$_SERVER["DOCUMENT_ROOT"]);
 
/**
 *  Setting System Information
 */
$registry->storeSetting('view','classic');
$registry->storeSetting('skin', $config['skin']);
$registry->storeSetting('sitename',$config['sitename']);
$registry->storeSetting('siteshortname', $config['siteshortname']);
$registry->storeSetting('siteurl',$config['siteurl']);

$registry->getObject('template')->getPage()->addTag( 'headbaseURL', $registry->getSetting('siteurl') );
$registry->getObject('template')->getPage()->addTag( 'sitename', $registry->getSetting('sitename') );
$registry->getObject('template')->getPage()->addTag( 'metadescription', $config['metadescription'] );
$registry->getObject('template')->getPage()->addTag( 'metakeywords', $config['metakeywords'] );
$registry->getObject('template')->getPage()->addTag( 'headtitle', $config['headtitle'] );
$registry->getObject('template')->getPage()->addTag( 'imagesPath', 'views/'.$config['skin'].'/images/' );

$registry->getObject('template')->dataToTags( $config, 'cfg_' );
$registry->getObject('template')->dataToTags( $caption, 'lbl_' );

?>
