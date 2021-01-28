<?php

namespace App\Handlers\Upload\Providers;

use Illuminate\Http\Request;
use App\Handlers\Upload\Contracts\Provider;
use App\Handlers\Upload\Support\FileSetter;

class EmailProvider implements Provider
{
    use FileSetter;

    public function isValid()
    {
        return isset($this->file['name']) && isset($this->file['content']);
    }

    public function getContents()
    {
        return base64_decode($this->file['content']);
    }

    public function getExtension()
    {
        $filename = $this->file['name'];
        if(str_contains($filename, ['?B?'])){
            $filename = base64_decode(explode("?B?", $filename)[1]);
        }
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    public function getMimeType()
    {
        return isset($this->file['type']) ? $this->file['type'] : '';
    }

    public function getOriginalName()
    {
        return isset($this->file['contentId']) ? $this->file['contentId'] : $this->file['name'];
    }

    public function getClientSize()
    {
        
    }

    public function getFileurl()
    {
        return env('AWS_URL') . '/'. $this->getFilepath();
    }
}
