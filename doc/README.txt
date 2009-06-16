==========================
Google analytics extension
==========================

Features
========
 * Set of classes used to fetch analytics data from an analytics account
 * Binds these classes to eZ publish INI settings
 * Template operator that analytics tracking code to your templates. Both
   legacy (urchin) and new (ga) trackers are supported

Installation
============
Not much:

 * download the ezgoogleanalytics folder to your ezpublish extension folder
 * enable the extension in site.ini: ActiveExtensions[]=ezgoogleanalytics
 * copy googleanalytics.ini-dist as googleanalytics.ini, and edit the settings
   to match your analytics parameters. More information on the parameters can
   be found in the INI file

Usage
=====

Template operator
-----------------
The extension comes with a ``googleanalytics_tracker`` template operator. Based
on your INI settings, this operator will generate the appropriate google
analytics tracking code to your HTML.

It usually has to be placed right before the </body> tag of your pagelayout.tpl::

	{googleanalytics_tracker()}
	</body>

API
---
The gdata API is currently the main feature of this extension. It can be used
this way::

	<?php
	// instanciante the analytics class.
	// Authentication is performed automatically based on your INI settings
	try {
		$analytics = new eZGoogleAnalytics();
	} catch( Exception $e ) {
		eZDebug::writeError( "Fatal analytics error: " . $e->getMessage() );
	}

	// The GoogleAnalyticsDataRequest class has to be used to create gdata requests
	try {
		$request = new GoogleAnalyticsDataRequest();
		$request->setDimension( 'pagepath' );
		$request->setMetric( 'pageviews' );
		$request->setStartDate( '2009-05-01' );
		$request->setEndDate( '2009-05-31' );

		$data = $analytics->getData( $request );
	} catch( Exception $e ) {
		eZDebug::writeError( "Analytics data request error: " . $e->getMessage() );
	}

	foreach( $data as $record )
	{
		echo $record['dimension']['pagepath'] . ': ' . $record['metrics']['pageviews'] . "\n";
	}
	?>

The classes used by the extension are documented using the PHPDoc format.