<?php
/**
 *
 *
 * @author P. PORTIER
 * @version $Id $
 * @copyright 2009
 *
 */

$http = eZHTTPTool::instance();
$ini = eZINI::instance( 'googleanalytics.ini.append.php', 'extension/ezgoogleanalytics/settings', null, false, null, true );

if( $http->hasPostVariable( 'TrackerID' ) )
{
    $ini->setVariable('TrackerSettings', 'TrackerID', $http->postVariable('TrackerID') );
    $saveINI = true;
}

if( $http->hasPostVariable( 'TrackingCode' ) )
{
    $ini->setVariable('TrackerSettings', 'TrackingCode', $http->postVariable('TrackingCode') );
    $saveINI = true;
}

if( $http->hasPostVariable( 'InterfaceID' ) )
{
    $ini->setVariable('AnalyticsSettings', 'GoogleAdminInterfaceID', $http->postVariable('InterfaceID') );
    $saveINI = true;
}

if( $http->hasPostVariable( 'Username' ) )
{
    $ini->setVariable('AnalyticsSettings', 'Username', $http->postVariable('Username') );
    $saveINI = true;
}

if( $http->hasPostVariable( 'Password' ) )
{
    $ini->setVariable('AnalyticsSettings', 'Password', $http->postVariable('Password') );
    $saveINI = true;
}

if( $saveINI )
{
    $ini->save();
}


$trackerid = $ini->variable( 'TrackerSettings', 'TrackerID' );
$trackingcode = $ini->variable( 'TrackerSettings', 'TrackingCode' );
$interfaceid = $ini->variable( 'AnalyticsSettings', 'GoogleAdminInterfaceID' );
$username= $ini->variable( 'AnalyticsSettings', 'Username' );
$password= $ini->variable( 'AnalyticsSettings', 'Password' );

// display the iframe_based template
require_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'trackerid', $trackerid );
$tpl->setVariable( 'trackingcode', $trackingcode );
$tpl->setVariable( 'interfaceid', $interfaceid );
$tpl->setVariable( 'username', $username );
$tpl->setVariable( 'password', $password );

$Result = array();
$Result['content'] = $tpl->fetch( "design:googleanalytics/ga_setup.tpl" );
$Result['left_menu'] = 'design:parts/googleanalytics/menu.tpl';
$Result['path'] = array(	array(	'url' => 'googleanalytics/admin',
                                    'text' => ezi18n( 'googleanalytics/ga_interface', 'Google Analytics' ) ),
                            array(	'text' => ezi18n( 'googleanalytics/ga_interface', 'Settings' ) ) );

?>