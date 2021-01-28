<?php

namespace App\Handlers\BatchOrm;

use App\Handlers\BatchOrm\Providers\DBProviders;
use App\Handlers\BatchOrm\Providers\OrmProviders;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use BadMethodCallException;

class BatchOrmHandler
{
    public $primaryKey = 'id';

    public $searchKey = 'search';

    public $replaceKey = 'replace';

    public $label = '-';

    public function __construct($model, $dataSource, $dataCondition)
    {
        $this->model = $model;
        $this->dataSource = $dataSource;
        $this->dataCondition = $dataCondition;
    }

    public function getProvider()
    {
        if($this->model instanceof \Illuminate\Database\Eloquent\Model){
            return new OrmProviders($this);
        }
        return new DBProviders($this);
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey($key)
    {
        $this->primaryKey = $key;
        return $this;
    }

    public function getSearchKey()
    {
        return $this->searchKey;
    }

    public function getReplaceKey()
    {
        return $this->replaceKey;
    }

    public function getDataSearchCondition()
    {
        if(!isset($this->dataCondition[$this->getSearchKey()])){
            throw new \Exception("Given Data Search Condition is invalid.");
        }
        return $this->dataCondition[$this->getSearchKey()];
    }

    public function getDataReplaceCondition()
    {
        if(!isset($this->dataCondition[$this->getReplaceKey()])){
            throw new \Exception("Given Data Replace Condition is invalid.");
        }
        return $this->dataCondition[$this->getReplaceKey()];
    }

    public function getDataSource()
    {
        return Collect($this->dataSource)->keyBy($this->getPrimaryKey())->toArray();
    }

    public function getBatchSearch()
    {
        $search = $searchTransform = [];
        $searchKeys = array_keys(current($this->getDataSearchCondition()));
        foreach($this->getDataSource() as $key => $value){
            foreach($searchKeys as $sValue){
                $search[$key][] = strtolower(trim($value[$sValue]));
                $searchTransform[$key] = implode($this->label, $search[$key]);
            }
        }
        return $searchTransform;
    }

    public function getBatchInfo()
    {
        $insert = $update = $origin = [];
        $data = $this->getDataSource();
        $multiSearch = $this->getDataSearchCondition();
        $multiReplace = $this->getDataReplaceCondition();
        $searchTransform = $this->getBatchSearch();
        $replaceKeys = array_keys(current($multiReplace));
        foreach($multiSearch as $key => $value){
            if($value = array_map('trim', array_map('strtolower', $value))){
                if($id = array_search(implode($this->label, $value), $searchTransform)){
                    $origin[] = $id;
                    foreach($replaceKeys as $field){
                        if((string)$data[$id][$field] !== (string)$multiReplace[$key][$field]){
                            $update[$id] = $multiReplace[$key];
                            break;
                        }
                    }
                    continue;
                }
            }
            $insert[] = $multiReplace[$key];
        }
        return ['insert' => array_unique($insert, SORT_REGULAR), 'update' => $update, 'delete' => array_diff(array_keys($data), $origin)];
        
    }

    public function dynamicBatch($method)
    {
        try {
            $provider = $this->getProvider();
            $batchInfo = $this->getBatchInfo();
            $methodArray = array_filter(explode('_', $method));
            foreach($methodArray as $methodValue){
                $provider->{'batch'.ucfirst($methodValue)}($batchInfo[$methodValue]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'batch')) {
            return $this->dynamicBatch($method = Str::snake(substr($method, 5)));
        }
        $className = static::class;
        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }


}
