<?php

namespace Patpat\ResponseCache\CacheProfiles;


use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface CacheProfile
{

    public function enabled(Request $request);

    public function shouldCacheRequest(Request $request);

    public function shouldCacheResponse(Response $response);

    public function cacheName(Request $request, $cacheKey);
}
