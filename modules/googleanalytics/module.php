<?php
/*
* @author O. PORTIER
* @version $Id: module.php 43902 2009-04-20 21:06:36Z gg $
* @license
* @copyright
*/


$Module = array( 'name' => 'eZGoogleAnalyticsAdminInterface' );

$ViewList['admin'] = array(
    'script' => 'ga_interface.php',
    'functions' => array( 'admin' ),
    'default_navigation_part' => 'ezgoogleanalytics' );

$ViewList['autologin'] = array(
    'script' => 'ga_autologin.php',
    'functions' => array( 'admin' ),
    'default_navigation_part' => 'ezgoogleanalytics' );


$ViewList['setup'] = array(
    'script' => 'ga_setup.php',
    'functions' => array( 'admin' ),
    'default_navigation_part' => 'ezgoogleanalytics' );


$FunctionList = array();
$FunctionList['admin'] = array();

?>
