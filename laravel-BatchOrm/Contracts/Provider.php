<?php

namespace App\Handlers\BatchOrm\Contracts;

interface Provider
{
    public function batchInsert($batchInsertInfo);

    public function batchUpdate($batchUpdateInfo);

    public function batchDelete($batchDeleteInfo);
}
