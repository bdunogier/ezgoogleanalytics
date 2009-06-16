<?php
/**
* Wrapper class that automatically authentication and authorization.
*
* Settings have to be customized in settings/googleanalytics.ini
**/
class eZGoogleAnalytics extends GoogleAnalytics
{
	/**
	* Instanciates a google analytics object, performing the required steps
	* to let you fetch analytics data
	**/
	function __construct()
	{
		if ( self::$preferences === null )
		{
			$INI = eZINI::instance( 'googleanalytics.ini' );
			list( $preferences['username'], $preferences['password'] ) =
				$INI->variableMulti( 'AnalyticsSettings', array( 'Username', 'Password' ) );
			$preferences['trackerID'] = $INI->variable( 'TrackerSettings', 'TrackerID' );
		}

		$this->auth( $preferences['username'], $preferences['password'] );
		$this->setTracker( $preferences['trackerID'] );
	}

	static $preferences = null;
}
?>