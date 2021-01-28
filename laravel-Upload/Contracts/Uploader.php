<?php

namespace App\Handlers\Upload\Contracts;

use Closure;

interface Uploader
{
    public function uploadTo($disk);

    public function toFolder($folder);

    public function renameTo($newName);

    public function setVisibility($visibility);

    public function upload($file, Closure $callback = null);
}
