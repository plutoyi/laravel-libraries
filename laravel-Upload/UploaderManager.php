<?php

namespace App\Handlers\Upload;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container;
use App\Handlers\Upload\Providers\UrlProvider;
use App\Handlers\Upload\Providers\LocalProvider;
use App\Handlers\Upload\Providers\HttpRequestProvider;
use App\Handlers\Upload\Providers\EmailProvider;
use App\Handlers\Upload\Providers\ImportEmailProvider;
use App\Handlers\Upload\Uploader;
use App\Handlers\Upload\Contracts\Factory as FactoryContract;

class UploaderManager implements FactoryContract
{
    protected $app;

    protected $providers = [
        'local'         => LocalProvider::class,
        'request'       => HttpRequestProvider::class,
        'url'           => UrlProvider::class,
        'email'         => EmailProvider::class,
        'importEmail'   => ImportEmailProvider::class
    ];

    protected $customProviders = [];

    protected $resolvedProviders = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function extend($provider, Closure $callback)
    {
        if ($this->isProviderAliasExists($provider)) {
            throw new InvalidArgumentException("Alias provider is already reserved [{$provider}]");
        }

        $this->customProviders[$provider] = $callback;

        return $this;
    }

    public function from($provider = null)
    {
        $provider = $provider ?: $this->getDefaultProvider();

        return new Uploader(
            $this->app->make('config'), $this->app->make('filesystem'), $this->createProviderInstance($provider)
        );
    }

    public function getDefaultProvider()
    {
        return $this->app->make('config')->get('uploader.default');
    }

    protected function createProviderInstance($provider)
    {
        if (! $this->isProviderAliasExists($provider)) {
            throw new InvalidArgumentException("File provider [{$provider}] is invalid.");
        }

        if (! isset($this->resolvedProviders[$provider])) {
            $this->resolvedProviders[$provider] = isset($this->customProviders[$provider]) ? $this->callCustomProvider($provider) : $this->app->make($this->providers[$provider]);
        }

        return $this->resolvedProviders[$provider];
    }

    protected function callCustomProvider($provider)
    {
        return $this->customProviders[$provider]($this->app);
    }

    protected function dynamicFrom($from)
    {
        $provider = Str::snake(substr($from, 4));

        return $this->from($provider);
    }

    protected function isProviderAliasExists($provider)
    {
        return array_key_exists($provider, $this->providers) || array_key_exists($provider, $this->customProviders);
    }

    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'from')) {
            return $this->dynamicFrom($method);
        }

        return call_user_func_array([$this->from(), $method], $parameters);
    }
}
