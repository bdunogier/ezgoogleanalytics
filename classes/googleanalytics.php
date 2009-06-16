<?php
/**
* Google analytics API implementation
*
* Usage:
* <?php
*
* // authentication
* try {
*   $ga = new GoogleAnalytics();
* 	$ga->auth( 'user@gmail.com', 'password' );
*   $ga->setTracker( 'UA-123456-1' );
* } catch ( Exception $e ) {
*   die( 'Error: ' . $e->getMessage() );
* }
*
* // getting data
* $request = new GoogleAnalyticsDataRequest();
* $request->setDimension( 'pagepath' );
* $request->setMetric( 'pageviews' );
*
* try {
*   $data = $analytics->getData( $request );
* }
*
* @todo Implement auth (shared) caching
**/
class GoogleAnalytics
{
	public function __construct()
	{}

	/**
	* Authenticates with the given credentials
	*
	* Will also look for the provided webID, and get the proper analytics API ID
	* we can use for further requests
	*
	* @param string $username
	* @param string $password
	* @throw Exception Again, just exceptions.
	**/
	public function auth( $username, $password )
	{
		$postData = array(
			'accountType' => 'GOOGLE',
			'Email' => $username,
			'Passwd' => $password,
			'service' => 'analytics',
			'source' => 'eZ-ezga-1' );

		$ch = curl_init( 'https://www.google.com/accounts/ClientLogin' );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$res = curl_exec( $ch );
		if ( $res === false )
		{
			throw new Exception( "CURL error: " . curl_error( $ch ) );
		}
		else
		{
			$HTTPCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			if ( $HTTPCode == 200 )
			{
				$this->authData = array();
				$resultLines = explode( "\n", $res );
				foreach( $resultLines as $resultLine )
				{
					if ( trim( $resultLine ) == "" )
						continue;
					list( $variable, $value ) = explode( '=', trim( $resultLine ) );
					if ( $variable == 'Auth' )
						$this->token = $value;
				}
			}
			elseif( $HTTPCode == 403 )
			{
				throw new Exception( "Authorization failed, check username and/or password" );
			}
			elseif( $HTTPCode == 401 )
			{
				throw new Exception( "Authentication failed, check that you can access this service" );
			}
			else
			{
				throw new Exception( "Unknown HTTP return status [$HTTPCode]" );
			}
		}
	}

	/**
	* Sets the active tracker
	*
	* @param string $webID The web tracking ID (UA-xxxxx-x)
	**/
	public function setTracker( $webID )
	{
		$res = $this->sendHTTPRequest( 'https://www.google.com/analytics/feeds/accounts/default' );
		$XML = simplexml_load_string( $res );

		// we scan each returned entry, and look for the given WebID
		foreach( $XML->entry as $itemXML )
		{
			// ID: $itemXML->id
			$children = $itemXML->children( 'http://schemas.google.com/analytics/2009' );
			foreach( $children->property as $child )
			{
				$attributes = $child->attributes();
				if ( $attributes['name'] == 'ga:webPropertyId' and $attributes['value'] == $webID )
				{
					$this->tableID = $children->tableId;
					break;
				}
			}
		}
		// if we have no tableID, it means that this webID was not found for this user
		if ( $this->tableID === null )
		{
			throw new Exception( "The tracker ({$webID}) was not found. Does this account have access to it ?" );
		}
	}

	/**
	* Sends an HTTP request using CURL
	*
	* @param string $url
	* @param array $postData An array of extra POST data for the request
	* @return string The HTML result
	* @throw Exception that's a stupid comment, but it does throw Exception...
	*        1. when curl fails
	*        2. when an unhandled HTTP error code (!= 200) is returned
	**/
	protected function sendHTTPRequest( $url, $postData = false )
	{
		$ch = curl_init( $url );
		$headers = array( "Authorization: GoogleLogin auth={$this->token}" );

		if ( $postData !== false )
		{
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
		}
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$res = curl_exec( $ch );
		if ( $res === false )
		{
			throw new Exception( "CURL error: " . curl_error( $ch ) );
		}
		else
		{
			$HTTPCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			if ( $HTTPCode == 200 )
			{
				return $res;
			}
			else
			{
				throw new Exception( "Unhandled return code: $HTTPCode\nBODY:\n" . $res );
			}
		}
	}

	/**
	 * Send a data request to analytics.
	 *
	 * @see Documentation for dimensions & metrics:
	 *      http://code.google.com/intl/fr-FR/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html
	 *
	 * @param mixed $dimensions
	 *        Set to false to disable
	 * @param mixed $metrics
	 *        Set to false to disable
	 * @param mixed $startDate
	 *        YYYY-MM-DD
	 * @param mixed $endDate
	 *        YYYY-MM-DD
	 * @return string
	 */
	public function getData( GoogleAnalyticsDataRequest $req )
	{
		$dataURL = 'https://www.google.com/analytics/feeds/data?' .
			       'ids=' . $this->tableID .
			       '&' . (string)$req;

		try {
			$xmlString = $this->sendHTTPRequest( $dataURL );
		} catch( Exception $e ) {
			throw new Exception( $e->getMessage() );
		}

		$xml = simplexml_load_string( $xmlString );
		$data = array();
		foreach( $xml->entry as $entry )
		{
			$entryData = array( 'dimensions' => array(), 'metrics' => array() );
			$entryChildren = $entry->children( 'http://schemas.google.com/analytics/2009' );

			foreach( $entryChildren->dimension as $dimension )
			{
				$attributes = $dimension->attributes();
				$dimensionName = str_replace( 'ga:', '', (string)$attributes->name );
				$entryData['dimensions'][$dimensionName] = (string)$attributes->value;
			}

			foreach( $entryChildren->metric as $metric )
			{
				$attributes = $metric->attributes();
				$metricName = str_replace( 'ga:', '', (string)$attributes->name );
				$entryData['metrics'][$metricName] = (string)$attributes->value;
			}
			$data[] = $entryData;
		}
		return $data;
	}

	/**
	* Authentication token
	* @var string
	* @see auth
	**/
	protected $token = null;

	/**
	* Actual google analytics ID used to access statistics
	* @var string
	**/
	protected $tableID = null;
}
?>