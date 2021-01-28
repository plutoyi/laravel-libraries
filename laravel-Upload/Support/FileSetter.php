<?php

namespace App\Handlers\Upload\Support;

trait FileSetter
{
    protected $file;

    protected $filename;

    protected $filepath;

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    public function getFilepath()
    {
        return $this->filepath;
    }
}
