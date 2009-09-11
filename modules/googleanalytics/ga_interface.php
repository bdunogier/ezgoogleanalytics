<?php
/**
 *
 *
 * @author P. PORTIER
 * @version $Id $
 * @copyright 2009
 *
 */


// display the iframe_based template
require_once( "kernel/common/template.php" );
$tpl = templateInit();
//$tpl->setVariable( 'query_string', $query_string );

$Result = array();
$Result['content'] = $tpl->fetch( "design:googleanalytics/ga_interface.tpl" );
$Result['left_menu'] = 'design:parts/googleanalytics/menu.tpl';
$Result['path'] = array(	array(	'url' => 'googleanalytics/admin',
                                      'text' => ezi18n( 'googleanalytics/ga_interface', 'Google Analytics' ) ),
                            array(	'text' => ezi18n( 'googleanalytics/ga_interface', 'Dashboard' ) ) );

?>