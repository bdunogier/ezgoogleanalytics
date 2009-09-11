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
$Result['content'] = $tpl->fetch( "design:googleanalytics/ga_autologin.tpl" );
$Result['pagelayout'] = 'popup_pagelayout.tpl';

//eZExecution::cleanExit();
?>