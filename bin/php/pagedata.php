<?php
/**
* Real world implementation of analytics in eZ publish.
*
* This script will populate and maintain pagedata from analytics statistics.
* For each page of your website, you will be able to access various information,
* like viewed pages, unique visits, total time spent, etc.
*
* It currently has to be ran using php bin/php/ezexec.php
**/

// cache file: used to store the latest incremental update date
$vardir = eZSys::varDirectory();
$cacheFile = "$vardir/googleanalytics/pagedata.php";
if ( !file_exists( "$vardir/googleanalytics/" ) )
{
	mkdir( "$vardir/googleanalytics/" );
}
if ( !file_exists( $cacheFile ) )
{
	$lastUpdate = false;
}
else
{
	if ( !$cacheData = file_get_contents( $cacheFile ) )
	{
		$cli->error( "An error occured loading $cacheFile" );
		$script->exit( 1 );
	}
	else
	{
		$cacheData = unserialize( $cacheData );
		$lastUpdate = $cacheData['pagedata-lastupdate'];
	}
}

// The analytics object is instancianted, and a common data request instance
// is created
try {
	$analytics = new eZGoogleAnalytics();
	$request = new GoogleAnalyticsDataRequest();
	$request->addDimension( 'pagePath' );

	$request->addMetric( 'pageViews' );
	$request->addMetric( 'uniquePageViews' );
	$request->addMetric( 'entrances' );
	$request->addMetric( 'exits' );
	$request->addMetric( 'timeOnPage' );
	$request->addMetric( 'bounces' );
	$request->addMetric( 'newVisits' );
} catch( Exception $e ) {
	die( "Fatal error: " . $e->getMessage() . "\n" );
}

$db = eZDB::instance();
$db->begin();

$today = date( 'Y-m-d', time() );

// No last update: we get data from the beggining and save it in the total table
if ( $lastUpdate === false )
{
	$cli->output( "First run of pagedata.php, grabbing history" );

	// first data request for "total" table
	try {
		$request->setStartDate( eZINI::instance( 'googleanalytics.ini' )->variable( 'TrackerSettings', 'StartDate' ) );
		$request->setEndDate( date( 'Y-m-d', strtotime( 'yesterday' ) ) );
	} catch( Exception $e ) {
		$cli->error( "Data request exception: " . $e->getMessage() );
		$script->exit( 1 );
	}

	//@todo REMOVE ME
	$totalInsert = 0;
	try {
		$data = $analytics->getData( $request );
		for( $i = 0, $count = count( $data ); $i < $count; $i++ )
		{
			$pagepath = $data[$i]['dimensions']['pagePath'];

			// dirty ignore. Should be changed for some URL transformation (see doc/TODO.txt)
			// @todo Crap too (see below)
			if ( isURLignored( $pagepath ) )
			{
				continue;
			}

			// remove query string from URL
			// @todo Change, this is crap :)
			list( $url, $nodeID ) = resolveURL( $pagepath );

			$pageData = array_merge(
				array( $nodeID, $url ),
				array_values( $data[$i]['metrics'] ) );

			$valueString = "'" . implode( "','", $pageData ) . "'";
			$query = <<< EOF
INSERT INTO ezgoogleanalytics_pagedata_total
(node_id, url, pageviews, uniquepageviews, entrances, exits, timeonpage, bounces, newvisits)
VALUES($valueString)
ON DUPLICATE KEY UPDATE
pageviews=pageviews+VALUES(pageviews),
uniquepageviews=uniquepageviews+VALUES(uniquepageviews),
entrances=entrances+VALUES(entrances),
exits=exits+VALUES(exits ),
timeonpage=timeonpage+VALUES(timeonpage),
bounces=bounces+VALUES(bounces),
newvisits=newvisits+VALUES(newvisits)
EOF;
			$db->query( $query );

			$totalInsert++;
		}
		$cli->output( "    history creation: inserted $totalInsert rows" );
	} catch( Exception $e ) {
		$cli->error( "getData exception: " . $e->getMessage() );
		$script->exit( 1 );
	}
}

// if the last update date is different from today's date, we need to merge
// previous incremental data with total data
if ( $lastUpdate != $today )
{
	$cli->output( "New day, merging total & incremental..." );

	// trust me or not: this works !
	// copies everything from incremental to total
	$query = <<< EOF
INSERT INTO ezgoogleanalytics_pagedata_total
(node_id, url, pageviews, uniquepageviews, entrances, exits, timeonpage, bounces, newvisits)
SELECT * FROM ezgoogleanalytics_pagedata_incremental AS incremental
ON DUPLICATE KEY UPDATE
	pageviews = ezgoogleanalytics_pagedata_total.pageviews + VALUES( pageviews ),
	uniquepageviews = ezgoogleanalytics_pagedata_total.uniquepageviews+VALUES(uniquepageviews),
	entrances = ezgoogleanalytics_pagedata_total.entrances+VALUES(entrances),
	exits =ezgoogleanalytics_pagedata_total. exits+VALUES(exits),
	timeonpage =ezgoogleanalytics_pagedata_total. timeonpage+VALUES(timeonpage),
	bounces = ezgoogleanalytics_pagedata_total.bounces+VALUES(bounces),
	newvisits = ezgoogleanalytics_pagedata_total.newvisits+VALUES(newvisits)
EOF;
	$db->query( $query );

	// and empty the incremental table
	$db->query( "TRUNCATE TABLE ezgoogleanalytics_pagedata_incremental" );

	$cli->output( "    done" );
}

// finally, we fetch data for today, and add them to the incremental table
$cli->output( "Updating today's data..." );
try {
	$request->setStartDate( $today );
	$request->setEndDate( $today );
} catch( Exception $e ) {
	$cli->error( 'Data request error: ' . $e->getMessage() );
	$script->exit( 1 );
}

try {
	$data = $analytics->getData( $request );

	for( $i = 0, $count = count( $data ); $i < $count; $i++ )
	{
		$pagepath = $data[$i]['dimensions']['pagePath'];

		// dirty ignore. Should be changed for some URL transformation (see doc/TODO.txt)
		// @todo Crap too (see below)
		if ( isURLignored( $pagepath ) )
		{
			continue;
		}

		// remove query string from URL
		// @todo Change, this is crap :)
		list( $url, $nodeID ) = resolveURL( $pagepath );

		$pageData = array_merge(
			array( $nodeID, $url ),
			array_values( $data[$i]['metrics'] ) );

		// we need the INSERT... ON DUPLICATE KEY UPDATE since the same URL might
		// be returned multiple times by analytics
		$valueString = "'" . implode( "','", $pageData ) . "'";
		$query = "INSERT INTO ezgoogleanalytics_pagedata_incremental " .
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
	}
	$cli->output("    processed $count incremental analytics records" );
} catch( Exception $e ) {
	$cli->error( "getData exception: " . $e->getMessage() );
	$script->exit( 1 );
}

$db->commit();

$cachedData = array( 'pagedata-lastupdate' => $today );
file_put_contents( $cacheFile, serialize( $cachedData ) );

/**
* Checks if the URL is ignored
*
* @param string $url
*
* @return bool
**/
function isURLignored( $url )
{
	return preg_match( '#^/translate_c\?#', $url ) or preg_match( '#^/search\?q=#', $url );
}

/**
* Attempts to resolve $url to a NodeID. If not found, returns a cleaned up
* version of the URL
*
* @param string $url
*
* @return array( $url, $nodeID )
**/
function resolveURL( $url )
{
	global $db;

	$url = '/' . trim( parse_url( "http://domain.com/$url", PHP_URL_PATH ), '/' ) . '/';
	$pagepathValue = '';
	$nodeID = 0;
	preg_replace( '#([/]{2,})#', '/', $url );
	if ( preg_match( "#/+content/+view/+full/+([0-9]+)#", $url, $matches ) )
	{
		$nodeID = $matches[1];
	}
	elseif ( $result = eZURLAliasML::fetchNodeIDByPath( $url ) )
	{
		$nodeID = $result;
	}
	else
	{
		$pagepathValue = $db->escapeString( trim( $url, '/' ) . '/' );
	}

	return array( $pagepathValue, $nodeID );
}
?>