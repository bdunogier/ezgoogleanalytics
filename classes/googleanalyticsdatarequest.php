<?php
class GoogleAnalyticsDataRequest
{
	/**
	* Adds a dimension to the request
	* @param string $dimension
	* @see http://code.google.com/intl/fr-FR/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html#dimensions
	* @throws DuplicateParameterException
	**/
	public function addDimension( $dimension )
	{
		$dimension = "ga:$dimension";
		if ( in_array( $dimension, $this->dimensions ) )
		{
			throw new DuplicateParameterException( "The dimension $dimension is already set" );
		}
		$this->dimensions[] = $dimension;
	}

	/**
	 * Adds a metric to the request
	 *
	 * @param string $metric
	 * @see http://code.google.com/intl/fr-FR/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html#metrics
	 * @throws DuplicateParameterException
	 */
	public function addMetric( $metric )
	{
		$metric = "ga:$metric";
		if ( in_array( $metric, $this->metrics ) )
		{
			throw new DuplicateParameterException( "The metric $metric is already set" );
		}
		$this->metrics[] = $metric;
	}

	/**
	 * Sets the request start date
	 * @param string $startDate The starting date. Format: YYYY-MM-DD
	 * @throws InvalidArgumentException If the date has an invalid format
	 **/
	public function setStartDate( $startDate )
	{
		if ( !$this->validateDateFormat( $startDate ) )
		{
			throw new InvalidArgumentException( "Invalid date format $startDate. Expected: YYYY-MM-DD" );
		}
		$this->startDate = $startDate;
	}

	/**
	 * Sets the request end date
	 * @param string $endDate The end date. Format: YYYY-MM-DD
	 * @throws InvalidArgumentException If the date has an invalid format
	 **/
	public function setEndDate( $endDate )
	{
		if ( !$this->validateDateFormat( $endDate ) )
		{
			throw new InvalidArgumentException( "Invalid date format $endDate. Expected: YYYY-MM-DD" );
		}
		$this->endDate = $endDate;
	}

	/**
	 * Adds a sort criteria
	 *
	 * @param string $sortField The field to sort on. Must be a metric or a dimension.
	 * @param string $sortDirection asc or desc
	 * @see http://code.google.com/intl/fr-FR/apis/analytics/docs/gdata/gdataReference.html#sorting
	 * @throws DuplicateParameterException
	 */
	public function addSort( $sortField, $sortDirection = 'asc' )
	{
		if ( $sortDirection == 'desc' )
		{
			$sort = urlencode( '-' ) . "ga:$sortField";
		}
		else
		{
			$sort = "ga:$sortField";
		}
		if ( in_array( $sort, $this->sort ) )
		{
			throw new DuplicateParameterException( "The sort parameter $sort is already set" );
		}
		$this->sort[] = $sort;
	}

	/**
	 * Adds a filter to the request
	 *
	 * @param string $field
	 * @param string $operator
	 * @param string $value
	 * @see http://code.google.com/intl/fr-FR/apis/analytics/docs/gdata/gdataReference.html#filtering
	 * @todo Distinguish metric & dimension filters
	 * @todo Handle AND / OR filters
	 */
	public function addFilter( $field, $operator, $value )
	{
		$this->filters = "ga:$field" . urlencode( $operator ) . urlencode( $value );
	}

	/**
	 * Returns the URL GET parameters for the current requester
	 *
	 * @return string
	 */
	public function __toString()
	{
		if ( !$this->startDate )
			throw new Exception( "start-date is not defined" );
		if ( !$this->endDate )
			throw new Exception( "end-date is not defined" );

		$URLParameters = array();

		$this->addToGetString( 'dimensions', $this->dimensions, $URLParameters );
		$this->addToGetString( 'metrics', $this->metrics, $URLParameters );
		$this->addToGetString( 'start-date', $this->startDate, $URLParameters );
		$this->addToGetString( 'end-date', $this->endDate, $URLParameters );
		$this->addToGetString( 'sort', $this->sort, $URLParameters );
		$this->addToGetString( 'filters', $this->filters, $URLParameters );

		return implode( '&', $URLParameters );
	}

	/**
	 * Adds get parameters from a variable to an array
	 *  - $array = array( 'a', 'b', 'c' )
	 *  - $getParameterName = 'foo'
	 *  - adds foo=a,b,c to $URLParameters
	 * @param array $array GET parameter values
	 * @param string $getParameterName
	 * @param array $urlParameters reference array the parameter should be added to
	 * @return void
	 **/
	protected function addToGetString( $getName, $getValues, &$URLParameters )
	{
		if ( !is_array( $getValues ) and $URLParameters)
		{
			$URLParameters[] = $getName . '=' . $getValues;
		}
		elseif ( is_array( $getValues ) and count( $getValues ) > 0 )
		{
			$URLParameters[] = $getName . '=' . implode( ',', $getValues );
		}
	}

	/**
	* Validates a date parameter against YYYY-MM-DD
	* @param string $date
	* @return bool
	**/
	protected function validateDateFormat( $date )
	{
		return preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date );
	}

	protected $metrics = array();
	protected $dimensions = array();
	protected $startDate, $endDate;
	protected $sort = array();
	protected $filters = array();
}

class DuplicateParameterException extends InvalidArgumentException
{}
?>