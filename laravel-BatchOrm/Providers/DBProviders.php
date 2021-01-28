<?php

namespace App\Handlers\BatchOrm\Providers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Handlers\BatchOrm\Contracts\Provider;

class DBProviders implements Provider
{ 
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function batchInsert($batchInsertInfo)
    {
        return DB::table($this->handler->model)->insert($batchInsertInfo);
    }

    public function batchUpdate($batchUpdateInfo)
    {
        foreach($batchUpdateInfo as $id => $updateValue){
            DB::table($this->handler->model)->where($this->handler->getPrimaryKey(), $id)->update($updateValue);
        }
    }

    public function batchDelete($batchDeleteInfo)
    {
        return DB::table($this->handler->model)->whereIn($this->handler->getPrimaryKey(), $batchDeleteInfo)->update(['deleted_at' => Carbon::now()]);    
    }
}
