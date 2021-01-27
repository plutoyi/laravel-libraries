<?php
namespace Patpat\ResponseCache\Middleware;


use Closure;
use Illuminate\Http\Request;
use Patpat\ResponseCache\Services\ResponseCache;
use Patpat\ResponseCache\Events\CacheMissed;
use Symfony\Component\HttpFoundation\Response;
use Log;

class CacheResponse
{
    protected $responseCache;

    public function __construct(ResponseCache $responseCache)
    {
        $this->responseCache = $responseCache;
    }

    public function getCacheKey(Request $request)
    {
        return "{$request->getUri()}/{$request->getMethod()}";
    }

    public function handle(Request $request, Closure $next, $tag = '')
    {
        Log::info('cache response start');
        if ($this->responseCache->enabled($request)) {
            if ($this->responseCache->hasBeenCached($request, $tag, $this->getCacheKey($request))) {
                return $this->responseCache->getCachedResponseFor($request, $tag, $this->getCacheKey($request));
            }
        }
        $response = $next($request);
        if ($this->responseCache->enabled($request)) {
            if ($this->responseCache->shouldCache($request, $response, $tag, $this->getCacheKey($request))) {
                $this->responseCache->cacheResponse($request, $response, $tag, $this->getCacheKey($request));
            }
        }
        return $response;
    }
}