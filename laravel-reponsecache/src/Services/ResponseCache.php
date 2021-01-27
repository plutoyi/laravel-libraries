<?php
namespace Patpat\ResponseCache\Services;


use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Patpat\ResponseCache\CacheProfiles\CacheProfile;
use Log;

class ResponseCache
{
    /** @var ResponseCache */
    protected $cache;

    /** @var RequestHasher */
    protected $hasher;

    /** @var CacheProfile */
    protected $cacheProfile;

    public function __construct(ResponseCacheRepository $cache, RequestHasher $hasher, CacheProfile $cacheProfile)
    {
        $this->cache = $cache;
        $this->hasher = $hasher;
        $this->cacheProfile = $cacheProfile;
    }

    public function enabled(Request $request)
    {   
        return $this->cacheProfile->enabled($request);
    }

    public function shouldCache(Request $request, Response $response)
    {
        if (! $this->cacheProfile->shouldCacheRequest($request)) {
            return false;
        }
        Log::info("should cache response:".$this->cacheProfile->shouldCacheResponse($response));
        return $this->cacheProfile->shouldCacheResponse($response);
    }

    public function cacheResponse(Request $request, Response $response, $tag = '', $cacheKey = '')
    {
        if (config('responsecache.add_cache_time_header')) {
            $response = $this->addCachedHeader($response);
        }
        $this->cache->put(
            $tag,
            $this->hasher->getHashFor($request, $cacheKey),
            $response,
            config('responsecache.cache_time')
        );
        return $response;
    }

    public function hasBeenCached(Request $request, $tag = '', $cacheKey = '')
    { 
        return config('responsecache.enabled') ? $this->cache->has($tag, $this->hasher->getHashFor($request, $cacheKey)): false;
    }

    public function getCachedResponseFor(Request $request, $tag = '', $cacheKey = '')
    {
        return $this->cache->get($tag, $this->hasher->getHashFor($request, $cacheKey));
    }

    public function pull(Request $request, $tag = '', $cacheKey = '')
    {
        $this->cache->pull($tag, $this->hasher->getHashFor($request, $cacheKey));
    }

    public function flush($tag)
    {
        $this->cache->flush($tag);
    }

    protected function addCachedHeader(Response $response)
    {
        $clonedResponse = clone $response;
        $clonedResponse->headers->set('laravel-responsecache', 'cached on '.date('Y-m-d H:i:s'));
        return $clonedResponse;
    }
}