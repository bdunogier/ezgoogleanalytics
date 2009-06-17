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

$pagedataGenerator = new eZGoogleAnalyticsPageDataGenerator();

$pagedataGenerator->begin();

// last update operation date
$lastUpdate = $pagedataGenerator->lastUpdate();

$today = date( 'Y-m-d', time() );
$yesterday = date( 'Y-m-d', strtotime( 'yesterday' ) );
$firstDate = eZINI::instance( 'googleanalytics.ini' )->variable( 'TrackerSettings', 'StartDate' );

// No last update: we get data from the beggining and save it in the total table
if ( $lastUpdate === false )
{
	$cli->output( "First run of pagedata.php, grabbing history" );

	// "total" data request, from the date configured in INI to yesterday
	try {

		$pagedataGenerator->setDateRange( $firstDate, $yesterday );
		$data = $pagedataGenerator->getAnalyticsData();
		$count = $pagedataGenerator->insertData( $data, 'total' );

	} catch( Exception $e ) {
		$cli->error( "Data request exception: " . $e->getMessage() );
		$script->shutdown( 1 );
	}

	$cli->output( "\thistory creation: inserted $count rows" );
}

// if the last update date is different from today's date, we need to merge
// previous yesterday's data with total data
if ( $lastUpdate != $today )
{
	$cli->output( "New day, merging total & incremental..." );

	try {
		$pagedataGenerator->setDateRange( $yesterday, $yesterday );
		$data = $pagedataGenerator->getAnalyticsData();

		// yes, we could have saved directly to total... I just loved my SQL query too much. Sorry :)
		$count = $pagedataGenerator->insertData( $data, 'incremental' );
		$pagedataGenerator->mergeIncrementalToTotal();
	} catch( Exception $e ) {
		$cli->error( "Error: " . $e->getMessage() );
		$script->shutdown( 1 );
	}

	$cli->output( "\tincremental merge: merged $count records" );
}

// finally, we fetch data for today, and add them to the incremental table
try {
	$cli->output( "Updating today's data..." );
	$pagedataGenerator->setDateRange( $today, $today );
	$data = $pagedataGenerator->getAnalyticsData();
	$count = $pagedataGenerator->insertData( $data, 'incremental' );
	$cli->output("\tprocessed $count incremental analytics records" );
} catch( Exception $e ) {
	$cli->error( 'Error: ' . $e->getMessage() );
	$script->shutdown( 1 );
}

$pagedataGenerator->end();

$pagedataGenerator->setLastUpdate( $today );

class eZGoogleAnalyticsPageDataGenerator
{

	/**
	 * @var eZDBInterface
	 **/
	var $db;

	/**
	 * @var eZGoogleAnalytics
	 **/
	var $analytics;

	/**
	 * @var eZGoogleAnalyticsDataRequest
	 **/
	var $request;

	/**
	* @var string
	**/
	var $cacheFile;

	/**
	* Constructor
	* Initialize the analytics and analytics data request objects, as well as
	* the database connection
	* @throw Exception
	**/
	function __construct()
	{
		// The analytics object is instancianted, and a common data request instance
		// is created
		try {
			$this->analytics = new eZGoogleAnalytics();
			$this->request = new GoogleAnalyticsDataRequest();
			$this->request->addDimension( 'pagePath' );

			$this->request->addMetric( 'pageViews' );
			$this->request->addMetric( 'uniquePageViews' );
			$this->request->addMetric( 'entrances' );
			$this->request->addMetric( 'exits' );
			$this->request->addMetric( 'timeOnPage' );
			$this->request->addMetric( 'bounces' );
			$this->request->addMetric( 'newVisits' );
		} catch( Exception $e ) {
			throw new Exception( $e );
		}

		$this->db = eZDB::instance();
	}

	/**
	* Begins the pagedata update operation
	**/
	public function begin()
	{
		$this->db->begin();
	}

	/**
	 * Terminates the pagedata update operation
	 **/
	public function end()
	{
		$this->db->commit();
	}

	/**
	 * Sends the analytics request and returns the data
	 **/
	public function getAnalyticsData()
	{
		// first data request for "total" table
		try {
			return $this->analytics->getData( $this->request );
		} catch( Exception $e ) {
			throw new Exception( $e );
		}
	}

	/**
	 * Attempts to resolve $url to a NodeID. If not found, returns a cleaned up
	 * version of the URL
	 *
	 * @param string $url
	 *
	 * @return array( $url, $nodeID )
	 **/
	public function resolveURL( $url )
	{
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
			$pagepathValue = $this->db->escapeString( trim( $url, '/' ) . '/' );
		}

		return array( $pagepathValue, $nodeID );
	}

	/**
	 * Checks if the URL is ignored
	 *
	 * @param string $url
	 *
	 * @return bool
	 **/
	public static function isURLignored( $url )
	{
		return preg_match( '#^/translate_c\?#', $url ) or preg_match( '#^/search\?q=#', $url );
	}

	/**
	* Gets the last update date from the cache file
	* @return string date, YYYY-MM-DD format
	**/
	public function lastUpdate()
	{
		// cache file: used to store the latest incremental update date
		$vardir = eZSys::varDirectory();
		$cacheDir = "$vardir/googleanalytics";

		if ( !file_exists( $cacheDir ) )
		{
			mkdir( $cacheDir );
		}

		$cacheFile = "$cacheDir/pagedata.php";
		if ( !file_exists( $cacheFile ) )
		{
			$lastUpdate = false;
		}
		else
		{
			if ( !$cacheData = file_get_contents( $cacheFile ) )
			{
				throw new Exception( "An error occured loading $cacheFile" );
				return false;
			}
			else
			{
				$cacheData = unserialize( $cacheData );
				$lastUpdate = $cacheData['pagedata-lastupdate'];
			}
		}

		$this->cacheFile = $cacheFile;

		return $lastUpdate;
	}

	/**
	 * Updates the last update date in the cache file
	 * @param string $date Update date, YYYY-MM-DD format
	 **/
	public function setLastUpdate( $date )
	{
		$cachedData = array( 'pagedata-lastupdate' => $date );
		file_put_contents( $this->cacheFile, serialize( $cachedData ) );
	}

	/**
	 * Sets the analytics data request date range
	 *
	 * @param mixed $startDate YYYY-MM-DD format
	 * @param mixed $endDate YYYY-MM-DD format
	 * @return void
	 */
	public function setDateRange( $startDate, $endDate )
	{
		$this->request->setStartDate( $startDate );
		$this->request->setEndDate( $endDate );
	}

	/**
	 * Inserts the analytics data set to one of the tables
	 *
	 * @param mixed $data array returned by the analytics request
	 * @param mixed $scope target table, total or incremental
	 * @return int Number of inserted rows
	 */
	public function insertData( $data, $scope )
	{
		if ( $scope != 'total' and $scope != 'incremental' )
		{
			throw new Exception( "Invalid table scope '$scope'. Valid: incremental, total" );
		}
		$totalInsert = 0;
		for( $i = 0, $count = count( $data ); $i < $count; $i++ )
		{
			$pagepath = $data[$i]['dimensions']['pagePath'];

			// dirty ignore. Should be changed for some URL transformation (see doc/TODO.txt)
			// @todo Crap too (see below)
			if ( $this->isURLignored( $pagepath ) )
			{
				continue;
			}

			// remove query string from URL
			// @todo Change, this is crap :)
			list( $url, $nodeID ) = $this->resolveURL( $pagepath );

			$pageData = array_merge(
				array( $nodeID, $url ),
				array_values( $data[$i]['metrics'] ) );

			$valueString = "'" . implode( "','", $pageData ) . "'";
			$query = <<< EOF
INSERT INTO ezgoogleanalytics_pagedata_{$scope}
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
			$this->db->query( $query );

			$totalInsert++;
		}
		return $totalInsert;
	}

	/**
	 * Merges incremental data to the total table, and empties the incremental table
	 **/
	public function mergeIncrementalToTotal()
	{
		// trust me or not: this works !
		// copies everything from incremental to total
		$query = <<< EOF
INSERT INTO ezgoogleanalytics_pagedata_total
( node_id, url, pageviews, uniquepageviews, entrances, exits, timeonpage, bounces, newvisits )
SELECT * FROM ezgoogleanalytics_pagedata_incremental AS incremental
ON DUPLICATE KEY UPDATE
	pageviews       = ezgoogleanalytics_pagedata_total.pageviews + VALUES( pageviews ),
	uniquepageviews = ezgoogleanalytics_pagedata_total.uniquepageviews + VALUES( uniquepageviews ),
	entrances       = ezgoogleanalytics_pagedata_total.entrances + VALUES( entrances ),
	exits           = ezgoogleanalytics_pagedata_total.exits + VALUES( exits ),
	timeonpage      = ezgoogleanalytics_pagedata_total.timeonpage + VALUES( timeonpage ),
	bounces         = ezgoogleanalytics_pagedata_total.bounces + VALUES( bounces ),
	newvisits       = ezgoogleanalytics_pagedata_total.newvisits + VALUES( newvisits )
EOF;

		// and empty the incremental table
		$this->db->query( "TRUNCATE TABLE ezgoogleanalytics_pagedata_incremental" );
	}
}
?>