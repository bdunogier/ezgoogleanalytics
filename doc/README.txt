==========================
Google analytics extension
==========================

Features
========
 * Set of classes used to fetch analytics data from an analytics account
 * Binds these classes to eZ publish INI settings
 * Template operator that analytics tracking code to your templates. Both
   legacy (urchin) and new (ga) trackers are supported
 * Administration interface in the backoffice
 * Settings of configuration parameters in the backoffice

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

Pagedata script
===============

This script is the first "real world" feature implemented by this extension.
It is built on a script (bin/php/pagedata.php) and a fetch function.

Setup
-----
This script uses custom tables. Their definition can be found in doc/pagedata.sql::
    mysql -u<user> -p<pass> <database> < extension/ezgoogleanalytics/doc/pagedata.sql

The script
----------

Usage: php bin/php/ezexec.php extension/ezgoogleanalytics/bin/php/pagedata.php

This script will fetch from google analytics statistics about your website pages.
The statistics will start from the date TrackerSettings.StartDate that can be
configured in googleanalytics.ini. URIs will be resolved to node IDs if possible.
If an URI can not be resolved, statistics for the URL will be recorded.

The first time it is executed, it will build a database table (ezgoogleanalytics_pagedata_total)
featuring access statistics for every URL.
Each time the script is ran again, it will gather statistics for the current day,
and will aggregate these with the total when a new day starts.

You can for instance execute it every hour, or even less, since analytics data
are recorded in real time when using the API.

These statistics can then be used from your templates using the pagedata fetch
function implemented in the googleanalytics module

The fetch function
------------------

Usage: {def $pagedata=fetch( googleanalytics, pagedata, hash( node_id, $module_result.node_id ) )}

Returns an array with several keys for the given node:
 * pageviews
 * uniquepageviews
 * entrances
 * exits
 * bounces
 * timeonpage
 * newvisits

The function can be used with 3 different parameters:
 * node: an ezcontentobjecttreenode
 * node_id: a Node ID
 * url: a raw URL, relative to the site root