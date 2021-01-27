<?php

namespace Patpat\ResponseCache\CacheProfiles;


use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

abstract class BaseCacheProfile implements CacheProfile
{
    public function enabled(Request $request)
    {
        return config('responsecache.enabled');
    }

    public function cacheName(Request $request, $cacheKey)
    {
        if( ! $cacheKey ) return '';

        if($cacheNamePrefix = config('responsecache.cache_prefix')){
            $cacheKey = implode('/', $cacheNamePrefix) . '/' . $cacheKey;
        }

        if($cacheNameSuffix = config('responsecache.cache_suffix')){
            $cacheKey = $cacheKey . '/' . implode('/', $cacheNameSuffix);
        }
        
        return $cacheKey;
    }
}

