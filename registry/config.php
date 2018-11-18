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
 *  Set Language
 */ 
$registry->storeSetting('lang','cs');
require_once('lang/'.$registry->getSetting('lang').'.php');


/**
 *  Setting Webpage
 */
$config['skin']             = 'classic';
$config['sitename']         = 'Intranet - DMS';
$config['siteshortname']    = 'Intranet';
$config['siteurl']          = 'http://localhost/intranet/';
$config['metadescription']  = 'Intranet - správa dokumentů';
$config['metakeywords']		  = '';
$config['headtitle']        = 'Intranet';
$config['Copyright']        = 'Copyright &bull; Petbla';
$config['CopyrightYear']    = 'petbla 2018';

/**
 *  Setting Personal Information
 */
$config['compName']      = 'OBEC Mistřice';
$config['compAddress']   = 'Mistřice 9';
$config['compCity']      = 'Mistřice';
$config['compZip']       = '68712';
$config['compICO']       = '00267267';

/**
 *  Save to Registry
 */
$registry->storeObject('db', 'mysql.database');
$registry->storeObject('template', 'template');
$registry->storeObject('authenticate', 'authentication');
$registry->storeObject('fce', 'usefulfunction');

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
