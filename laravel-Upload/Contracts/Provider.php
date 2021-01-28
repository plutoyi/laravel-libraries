<?php

namespace App\Handlers\Upload\Contracts;

interface Provider
{
    public function isValid();

    public function getContents();

    public function getExtension();

    public function getMimeType();

    public function getOriginalName();

    public function getClientSize();

    public function setFile($file);
}
