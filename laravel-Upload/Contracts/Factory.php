<?php

namespace App\Handlers\Upload\Contracts;

use Closure;

interface Factory
{
    public function extend($provider, Closure $callback);

    public function from($provider = null);
}
