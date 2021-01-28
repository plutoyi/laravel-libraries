<?php

namespace App\Handlers\Upload;

use Closure;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as FilesystemManager;
use App\Handlers\Upload\Contracts\Provider;
use App\Handlers\Upload\Contracts\Uploader as UploaderContract;

class Uploader implements UploaderContract
{
    public $disk;

    public $filename;

    public $visibility;

    public $keepOriginName = false;

    public $folder = '';

    protected $config;

    protected $provider;

    protected $filesystem;

    public function __construct(Config $config, FilesystemManager $filesystem, Provider $provider)
    {
        $this->config = $config;
        $this->provider = $provider;
        $this->filesystem = $filesystem;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function uploadTo($disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function toFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    public function renameTo($newName)
    {
        $this->filename = $newName;

        return $this;
    }

    public function keepOriginName($bool = false)
    {
        $this->keepOriginName = $bool;

        return $this;
    }

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function upload($file, Closure $callback = null)
    {
        $uploadedFile = $this->runUpload($file);

        if (! $uploadedFile) {
            return false;
        }

        if ($callback) {
            $callback($uploadedFile);
        }

        return $uploadedFile;
    }

    public function uploads($files, Closure $callback = null)
    {
        $uploadedFiles = [];

        foreach($files as $file){
            $uploadedFiles[] = $this->upload($file, $callback);
        }

        return $uploadedFiles;
    }

    public function getVisibility()
    {
        return $this->visibility ?: $this->getDefaultVisibility();
    }

    public function getDefaultVisibility()
    {
        return $this->config->get('uploader.visibility') ?: 'public';
    }

    protected function runUpload($file)
    {
        $this->provider->setFile($file);

        if (! $this->provider->isValid()) {
            throw new InvalidFileException("Given file [{$file}] is invalid.");
        }

        $filename = $this->getFullFileName($this->provider);

        if ($this->filesystem->disk($this->disk)->put($filename, $this->provider->getContents(), $this->getVisibility())) {
            return clone $this->provider;
        }

        return false;
    }

    protected function getFullFileName(Provider $provider)
    {
        $folder = $this->folder ? rtrim($this->folder, '/').'/' : '';

        if($this->keepOriginName){
            $fullfilename = $provider->getOriginalName();
        } else {
            $filename = $this->filename ? $this->filename : md5(uniqid(microtime(true), true));
            $fullfilename = $filename . '.' . $provider->getExtension();
        }

        $filepath = $folder . $fullfilename;

        $provider->setFilename($fullfilename);

        $provider->setFilepath($filepath);

        return $filepath;
    }

    protected function dynamicUploadTo($uploadTo)
    {
        $disk = Str::snake(substr($uploadTo, 8));

        return $this->uploadTo($disk);
    }

    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'uploadTo')) {
            return $this->dynamicUploadTo($method);
        }

        $className = static::class;

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }
}
