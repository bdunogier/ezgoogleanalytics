<?php
/**
* Class GoogleAnalyticsTrackerOperator
*
* Generates the tracking code required to record Google analytics statistics
**/
class GoogleAnalyticsOperators
{
    /**
     * Lists operators implemented in this class
     * @return array of implemented operators names
    **/
    function operatorList()
    {
        return array( 'googleanalytics_tracker' );
    }

    /**
     * Tells the template engine that the parameter list exists per operator
     * type. This is needed for operator classes that have multiple operators.
     *
     * @return bool true
     **/
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * Provides the list of parameters for each operator
     *
     * @return array
     **/
    function namedParameterList()
    {
        return array( 'googleanalytics_tracker' => array() );
    }

    /**
     * Actual operator body. Changes the value of $operatorValue to the final
     * value.
     **/
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {
        switch ( $operatorName )
        {
            case 'googleanalytics_tracker':
            {
                $analyticsINI = eZINI::instance( 'googleanalytics.ini' );

        		list( $trackerID, $trackingCode ) =
        			$analyticsINI->variableMulti(
        				'TrackerSettings',
        				array( 'TrackerID', 'TrackingCode' ) );

        		// ga: "new" analytics tracker code
				if ( $trackingCode == 'ga' )
				{
					$operatorValue =
						"<script type=\"text/javascript\">\n" .
						"var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n" .
						"document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n" .
						"</script>\n" .
						"<script type=\"text/javascript\">\n" .
						"try {\n" .
						"    var pageTracker = _gat._getTracker(\"$trackerID\");\n" .
						"    pageTracker._trackPageview();\n" .
						"} catch(err) {}\n</script>\n";
				}
            	// urchin: legacy analytics tracker code
				elseif ( $trackingCode == 'urchin' )
				{
					$operatorValue =
						"<script src=\"http://www.google-analytics.com/urchin.js\" type=\"text/javascript\">\n" .
						"</script>\n" .
						"<script type=\"text/javascript\">\n" .
						"try {\n" .
						"    _uacct = \"$trackerID\";\n" .
						"    urchinTracker();\n" .
						"} catch(err) {}\n</script>\n";
				}
				else
				{
					eZDebug::writeError( "$trackingCode is not a valid value for TrackerSettings.TrackingCode. Use either ga or urchin",
						"Google analytics tracker operator" );
					$operatorValue = '';
				}
            } break;
        }
    }
}
?>