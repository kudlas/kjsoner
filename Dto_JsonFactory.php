<?php

namespace Kudlas;

/**
 * Class Dto_JsonFactory dto for json factory (you wouldnt guess that by name :D )
 * @author jan kudlacek
 */
class Dto_JsonFactory
{

    CONST RENAME_FUNCTION = 'renameCol';
    CONST FILTER_FUNCTION = 'filterCols';
    CONST ADD_FUNCTION = 'addById';
    CONST ADD_COL_FUNCTION = 'add';
    CONST REMOVE_FUNCTION = 'removeCol';
    CONST APPLY_FUNCTION = 'apply';
    CONST GET_DATA_FUNCTION = 'getData';

    private $data;

    public function __get( $key )
    {

        return (array_key_exists($key, $this->data)) ? $this->data[ $key ] : null;
    }

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function filterCols(array $cols) {
        $keys = array_flip($cols);
        $this->data = array_intersect_key( $this->data, $keys );
    }

    public function renameCol($new, $old)
    {
        $this->data[$new] = $this->data[$old];
        unset($this->data[$old]);
    }

    /**
     * @param $column string column title
     */
    public function removeCol($column) {
        unset($this->data[$column]);
    }

    /**
     * @param $sourceData
     * @param $column
     */
    public function addById($sourceData, $column) {
        $myData = $sourceData[ $this->data[$column] ]; // pick right data from whole batch
        $this->data = array_merge($this->data, $myData);
    }

    public function apply($column, $function)
    {
        $this->data[$column] = $function( $this->data[$column] );
    }

    public function getData()
    {
        return $this->data;
    }

    public function add($colName,$function)
    {
       $this->data = array_merge($this->data, array($colName => $function($this->data) ) );
    }

}
