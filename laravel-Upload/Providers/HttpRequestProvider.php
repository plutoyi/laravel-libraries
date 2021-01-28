<?php

namespace App\Handlers\Upload\Providers;

use Illuminate\Http\Request;
use App\Handlers\Upload\Contracts\Provider;
use App\Handlers\Upload\Support\FileSetter;
use Illuminate\Http\UploadedFile;

class HttpRequestProvider implements Provider
{
    use FileSetter;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getFileClient()
    {
        if($this->file instanceof UploadedFile){
            return $this->file;
        }
        return $this->request->file($this->file);
    }

    public function isValid()
    {
        return $this->getFileClient()->isValid();
    }

    public function getContents()
    {
        return file_get_contents($this->getFileClient()->getRealPath());
    }

    public function getExtension()
    {
        return $this->getFileClient()->getClientOriginalExtension();
    }

    public function getMimeType()
    {
        return $this->getFileClient()->getClientMimeType();
    }

    public function getOriginalName()
    {
        return $this->getFileClient()->getClientOriginalName();
    }

    public function getClientSize()
    {
        return $this->getFileClient()->getClientSize();
    }

    public function getFileurl()
    {
        return env('AWS_URL') . '/'. $this->getFilepath();
    }
}
