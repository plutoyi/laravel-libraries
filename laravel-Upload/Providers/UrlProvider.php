<?php

namespace App\Handlers\Upload\Providers;

use App\Handlers\Upload\Contracts\Provider;
use App\Handlers\Upload\Support\FileSetter;
use App\Handlers\Upload\Support\FileExtensionPathInfo;

class UrlProvider implements Provider
{
    use FileExtensionPathInfo, FileSetter;

    public function isValid()
    {
        $fileHeaders = @get_headers($this->file);

        return $fileHeaders && $fileHeaders[0] !== 'HTTP/1.1 404 Not Found';
    }

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
