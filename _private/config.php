<?php
$config = array();

/*
 *************************************************************************************************
 *                                  MySQL Database                                               *
 *************************************************************************************************
 */
$config['db_host'] = 'localhost';
$config['db_user'] = 'root';	
$config['db_pass'] = 'Heslo01!';
$config['db_name'] = 'intranet';

/*
 *************************************************************************************************
 *                                  Customer Settings  Webpage                                   *
 *************************************************************************************************
 */
$config['sitename']         = 'Intranet - DMS';
$config['metadescription']  = 'Intranet - správa dokumentů';
$config['siteshortname']    = 'Intranet';
$config['headtitle']        = 'Intranet';
$config['siteurl']          = 'http://petblanb/intranet/';

// OBEC
$config['fileserver']       = 'http://petblanb/FileServer/';
$config['webserver']         = 'http://petblanb/intranet/fileserver/';
$config['dbPrefix']         = 'mis_'; 
$config['compName']         = 'OBEC Mistřice';

// Testing
//$config['fileserver']       = 'http://venuse/eBook/MySQL/';                                                                        
//$config['webserver']        = 'http://venuse:5000/eBook/MySQL/';   
//$config['dbPrefix']         = 'test_'; 
//$config['compName']         = 'TEST Zkušební společnost (VENUSE)';

// Zahrádkáři
//$config['fileserver']       = 'ftp://venuse/users/petr/Job/Zahradkari/';
//$config['webserver']         = 'http://petblanb/intranet/fileserver/';
//$config['dbPrefix']         = 'czs_';   

/**
 *  Address
 */
$config['compAddress']   = 'Mistřice 9';
$config['compCity']      = 'Mistřice';
$config['compZip']       = '68712';
$config['compICO']       = '00267267';


?>