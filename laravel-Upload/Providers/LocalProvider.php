<?php

namespace App\Handlers\Upload\Providers;

use App\Handlers\Upload\Contracts\Provider;
use App\Handlers\Upload\Support\FileSetter;
use App\Handlers\Upload\Support\FileExtensionPathInfo;

class LocalProvider implements Provider
{
    use FileExtensionPathInfo, FileSetter;

    /**
     * Returns whether the file is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return file_exists($this->file);
    }

    /**
     * Get the file's contents.
     *
     * @return resource|string
     */
    public function getContents()
    {
        return fopen($this->file, 'r');
    }

    public function getMimeType()
    {

    }

    public function getOriginalName()
    {

    }

    public function getClientSize()
    {
        
    }
}
