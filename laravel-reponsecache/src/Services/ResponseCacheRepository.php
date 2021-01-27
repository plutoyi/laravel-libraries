<?php

namespace Patpat\ResponseCache\Services;


use Illuminate\Cache\Repository;
use Symfony\Component\HttpFoundation\Response;

class ResponseCacheRepository
{
    protected $cache;

    protected $responseSerializer;

    public function __construct(ResponseSerializer $responseSerializer, Repository $cache)
    {
        $this->cache = $cache;
        $this->responseSerializer = $responseSerializer;
    }

    public function getCache($tag)
    {
        $tag && $this->cache = $this->cache->tags($tag);
        return $this->cache;
    }

    public function put($tag, $key, $response, $minutes)
    {
        $cache = $this->getCache($tag);
        $cache->put($key, $this->responseSerializer->serialize($response), $minutes);
    }

    public function has($tag, $key)
    {
        $cache = $this->getCache($tag);
        return $cache->has($key);
    }

    public function get($tag, $key)
    {
        $cache = $this->getCache($tag);
        return $this->responseSerializer->unserialize($cache->get($key));
    }

    public function pull($tag, $key)
    {
        $cache = $this->getCache($tag);
        $cache->pull($key);
    }

    public function flush($tag)
    {
        $cache = $this->getCache($tag);
        $cache->flush();
    }
}
