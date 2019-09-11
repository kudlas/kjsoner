<?php

namespace Kudlas;
/**
 * Class Kohana_JsonFactory for parsing json
 * @author jan kudlacek
 */
class JsonFactory
{
    private $data;
    private $dtos;

    /**
     * @param $json string of a json
     * @param null $key column name, where to get data from json, null means take everything
     * @param null $idColumn which column to use as array keys (of returned data)
     * @return $this
     * @throws
     */
    public function create($json, $key = null, $idColumn = null)
    {
        $this->data = json_decode($json);

        if (!$this->data) {
            throw new \Exception("Passed JSON is not valid! " . print_r($json, true));
        }

        if ($key !== null) {
            $this->data = $this->data->$key;
        }

        $this->createFromObject($idColumn, $this->data);

        return $this;
    }

    public function createFromObject($idColumn, $data)
    {

        $this->data = $data; // so I can call this standalone

        foreach ($data as $object) {

            // just drop objects into array
            if (!$idColumn) {
                $this->dtos[] = new Dto_JsonFactory((array)$object);
            } else {
                // or drop them by column stated
                $this->dtos[$object->$idColumn] = new Dto_JsonFactory((array)$object);
            }
        }

        return ($this->isEmpty()) ? null : $this;
    }

    public function isEmpty()
    {
        return (empty($this->data) && !$this->dtos);
    }

    /**
     * traverse thgrough data and returns new
     * @param $params colName => desiredValue pair to filter data
     */
    public function filterData(array $params)
    {
        $return = [];

        foreach ($this->dtos as $dto) {

            // getting same fields as params has, from the Dto
            $dtoValues = array_intersect_key($dto->getData(), $params);

            // difference in arrays, if empty, arrays are the same and the dto is the one we need
            $diff = array_diff($dtoValues, $params);

            if (empty($diff)) {
                $return[] = $dto->getData();
            }
        }

        // return new object
        $returnObject = new self();
        $returnObject->createFromObject(null, $return);
        return $returnObject;
    }

    /**
     * filters values by column keys (remove columns not in array)
     * @param $cols Array of strings, which columns to keep
     */
    public function filterCols($cols)
    {
        $this->applyToDtos(Dto_JsonFactory::FILTER_FUNCTION, $cols);
        return $this;
    }

    public function rename($newName, $oldName)
    {
        $this->applyToDtos(Dto_JsonFactory::RENAME_FUNCTION, $newName, $oldName);
        return $this;
    }

    public function addColFunction($colName, $function)
    {
        $this->applyToDtos(Dto_JsonFactory::ADD_COL_FUNCTION, $colName, $function);
        return $this;
    }

    public function addById($data, $column)
    {
        $this->applyToDtos(Dto_JsonFactory::ADD_FUNCTION, $data, $column);
        return $this;
    }

    public function remove($column)
    {
        $this->applyToDtos(Dto_JsonFactory::REMOVE_FUNCTION, $column);
        return $this;
    }

    public function apply($col, $func)
    {
        $this->applyToDtos(Dto_JsonFactory::APPLY_FUNCTION, $col, $func);
        return $this;
    }

    /**
     * If Dtos contain object with values (eg. dtos are polls, that contain question objects) and you need to inject
     * values into inner objects (eg. id of poll into every question) you can use this function.
     * @param string toChangeColName name of col, that contains inner objects
     * @param string valueColName name of col from outer object, you want to inject
     * */
    public function plungeValue($toChangeColName, $valueColName, $newInnerName = null)
    {
        // cant use applyToDtos because i need index of the Dto
        // $this->applyToDtos(Dto_JsonFactory::ADD_COL_FUNCTION, $toChangeColName, function ($dtoData) use ($valueColName, $data, $toChangeColName) {

        foreach ($this->dtos as $id => $dto) {
            $newObjects = array();
            $dtoData = $dto->getData();
            $newCol = ($newInnerName) ? $newInnerName : $valueColName;

            if (!array_key_exists($toChangeColName, $dtoData)) continue; // if it does not exists in dataset, just skip it

            if (is_array($dtoData[($toChangeColName)])) {

                foreach ($dtoData[($toChangeColName)] as $key => $row) {
                    $row->{$newCol} = $dtoData[$valueColName];
                    $newObjects[] = $row;
                }

                $dto->add($toChangeColName, function () use ($newObjects) {
                    return $newObjects;
                });
            } elseif (is_object($dtoData[($toChangeColName)])) {

                $formerObj = $dto->{$toChangeColName};
                $formerObj->{$newCol} = $this->data[$id]->{$valueColName};

                $dto->apply($toChangeColName, function () use ($formerObj) {
                    return $formerObj;
                });
            }
        }

        return $this;
        //});
    }


    private function applyToDtos($functionName, ...$params)
    {
        foreach ($this->dtos as $key => $row) {
            $row->$functionName(...$params);

            // $this->data[$key] = $row->getData();
        }
    }

    private function applyToDtosReturn($functionName, ...$params)
    {
        $return = [];

        foreach ($this->dtos as $id => $row) {
            $return[$id] = $row->$functionName(...$params);
            //$this->data[$id] = $row->getData();
        }

        return $return;
    }

    public function getData()
    {
        return $this->dtos;
    }

    public function toArray()
    {

        return $this->applyToDtosReturn(Dto_JsonFactory::GET_DATA_FUNCTION);

    }

    public function colToArray($col)
    {
        $return = array();

        foreach ($this->dtos as $row) {
            if ($row->$col)
                $return[] = $row->$col;
        }

        return $return;
    }

    public function colToFactory($col)
    {
        $data = $this->colToArray($col);
        if (!$data) return null;
        $data = $this->flattenData($data);

        $returnObject = new self();
        $returnObject->createFromObject(null, $data);
        return $returnObject;
    }

    private function flattenData($data)
    {
        $return = array();
        array_walk_recursive($data, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }

}
