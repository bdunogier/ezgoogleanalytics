<?php
/**
* Fetch functions for the googleanalytics module
**/
class eZGoogleAnalyticsFunctionCollection
{
	/**
	* Function definition for googleanalytics/pagedata
	* @param eZContentObjectTreeNode $node
	* @param int $nodeID
	* @param string $url
	* @return array of pagedata
	**/
	public static function fetchPagedata( $node = false, $nodeID = false, $url = false )
	{
		if ( $node === false and $nodeID === false and $url === false)
		{
			eZDebug::writeError("You have to provide either node, node_id or url", __METHOD__ );
			return array( 'result' => false );
		}

		if ( $node !== false && $node instanceof eZContentObjectTreeNode )
		{
			$nodeID = $node->attribute( 'node_id' );
		}
		elseif( $nodeID !== false )
		{
			if ( !eZContentObjectTreeNode::fetch( $nodeID ) )
			{
				eZDebug::writeError( "Node $nodeID could not be fetched", __METHOD__ );
				return array( 'result' => false );
			}
		}

		$db = eZDB::instance();
		if ( $nodeID !== false )
		{
			$where = "node_id = $nodeID";
		}
		else
		{
			$url = $db->escapeString( $url );
			$where = "url LIKE '$url'";
		}

		$query = <<< EOF
SELECT SUM(aggregate.pageviews) AS pageviews,
       SUM(aggregate.uniquepageviews) AS uniquepageviews,
       SUM(aggregate.entrances) AS entrances,
       SUM(aggregate.exits) AS exits,
       SUM(aggregate.timeonpage) AS timeonpage,
       SUM(aggregate.bounces) AS bounces,
       SUM(aggregate.newvisits) AS newvisits
FROM (
       (SELECT * FROM ezgoogleanalytics_pagedata_total AS total WHERE $where)
       UNION
       (SELECT * FROM ezgoogleanalytics_pagedata_incremental AS incremental WHERE $where) ) AS aggregate
EOF;
		$res = $db->arrayQuery( $query );
		if ( $res == false or $res[0]['pageviews'] === null )
		{
			$result = false;
		}
		else
		{
			$result = $res[0];
		}
		return array( 'result' => $result );
	}
}
?>