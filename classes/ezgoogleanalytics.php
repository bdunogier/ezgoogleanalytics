<?php
class eZGoogleAnalytics extends GoogleAnalytics
{
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