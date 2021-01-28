<?php

namespace App\Handlers\Upload\Support;

trait FileExtensionPathInfo
{
    public function getExtension()
    {
        return pathinfo($this->file, PATHINFO_EXTENSION);
    }
}
