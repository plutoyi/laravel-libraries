<?php

namespace App\Handlers\BatchOrm\Providers;

use Carbon\Carbon;
use App\Handlers\BatchOrm\Contracts\Provider;

class OrmProviders implements Provider
{
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function batchInsert($batchInsertInfo)
    {
        return $this->handler->model->insert($batchInsertInfo);
    }

    public function batchUpdate($batchUpdateInfo)
    {
        foreach($batchUpdateInfo as $id => $updateValue){
            $this->handler->model->where($this->handler->getPrimaryKey(), $id)->update($updateValue);
        }
    }

    public function batchDelete($batchDeleteInfo)
    {
        return $this->handler->model->whereIn($this->handler->getPrimaryKey(), $batchDeleteInfo)->update(['deleted_at' => Carbon::now()]);    
    }

}
