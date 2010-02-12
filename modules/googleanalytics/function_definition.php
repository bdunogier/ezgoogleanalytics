<?php
$FunctionList = array();
$FunctionList['pagedata'] = array(
    'name' => 'pagedata',
    'operation_types' => array( 'read' ),
    'call_method' => array( 'class' => 'eZGoogleAnalyticsFunctionCollection',
                            'method' => 'fetchPagedata' ),
    'parameter_type' => 'standard',
    'parameters' => array( array( 'name' => 'node',
                                  'type' => 'object',
                                  'required' => false ),
                           array( 'name' => 'node_id',
                                  'type' => 'integer',
                                  'required' => false ),
                           array( 'name' => 'url',
                                  'type' => 'string',
                                  'required' => false ) ) );
?>