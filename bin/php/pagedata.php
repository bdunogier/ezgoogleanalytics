<?php
try {
	$analytics = new eZGoogleAnalytics();
} catch( Exception $e ) {
	die( "Fatal error: " . $e->getMessage() . "\n" );
}

// first data request for "total" table
try {
	$request = new GoogleAnalyticsDataRequest();
	$request->addDimension( 'pagePath' );

	$request->addMetric( 'pageViews' );
	$request->addMetric( 'uniquePageViews' );
	$request->addMetric( 'entrances' );
	$request->addMetric( 'exits' );
	$request->addMetric( 'timeOnPage' );
	$request->addMetric( 'bounces' );
	$request->addMetric( 'newVisits' );

	$request->setStartDate( eZINI::instance( 'googleanalytics.ini' )->variable( 'TrackerSettings', 'StartDate' ) );
	$request->setEndDate( date( 'Y-m-d', strtotime( 'yesterday' ) ) );
} catch( Exception $e ) {
	die( "Data request exception: " . $e->getMessage() );
}

$db = eZDB::instance();

//@todo REMOVE ME
$cli->output( "Truncating 'total' table" );
$db->query( 'TRUNCATE TABLE ezgoogleanalytics_pagedata_total' );
$insert = 0;
try {
	$data = $analytics->getData( $request );
	for( $i = 0, $count = count( $data ); $i < $count; $i++ )
	{
		$pagepath = $data[$i]['dimensions']['pagePath'];
		// dirty ignore. Should be changed for some URL transformation (see doc/TODO.txt)
		// @todo Crap too (see below)
		if ( preg_match( '#^/translate_c\?#', $pagepath ) or preg_match( '#^/search\?q=#', $pagepath ) )
		{
			continue;
		}

		// remove query string from URL
		// @todo Change, this is crap :)
		$pagepath = '/' . trim( parse_url( "http://blog.ankh-morpork.net/$pagepath", PHP_URL_PATH ), '/' ) . '/';
		$pagepathValue = '';
		$nodeID = 0;
		preg_replace( '#([/]{2,})#', '/', $pagepath );
		if ( preg_match( "#/+content/+view/+full/+([0-9]+)#", $pagepath, $matches ) )
		{
			$nodeID = $matches[1];
		}
		elseif ( $result = eZURLAliasML::fetchNodeIDByPath( $pagepath ) )
		{
			$nodeID = $result;
			$cli->notice( "Translated $pagepath to node #$nodeID" );
		}
		else
		{
			$pagepathValue = $db->escapeString( trim( $pagepath, '/' ) . '/' );
		}


		$pageData = array_merge(
			array( $nodeID, $pagepathValue ),
			array_values( $data[$i]['metrics'] ) );

		$valueString = "'" . implode( "','", $pageData ) . "'";
		$query = "INSERT INTO ezgoogleanalytics_pagedata_total " .
				 "(node_id, url, pageviews, uniquepageviews, entrances, exits, timeonpage, bounces, newvisits) " .
				 "VALUES($valueString) " .
				 "ON DUPLICATE KEY UPDATE " .
				 "	pageviews=pageviews+VALUES(pageviews), " .
				 "	uniquepageviews=uniquepageviews+VALUES(uniquepageviews), " .
				 "	entrances=entrances+VALUES(entrances), " .
				 "	exits=exits+VALUES(exits ), " .
				 "	timeonpage=timeonpage+VALUES(timeonpage), " .
				 "	bounces=bounces+VALUES(bounces), " .
				 "	newvisits=newvisits+VALUES(newvisits)";
		$db->query( $query );

		$insert++;
	}
} catch( Exception $e ) {
	die( "getData exception: " . $e->getMessage() );
}

$cli->output( "Inserted $insert URLs for total data");
?>