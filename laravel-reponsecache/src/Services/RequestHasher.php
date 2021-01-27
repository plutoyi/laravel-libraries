<?php

namespace Patpat\ResponseCache\Services;


use Illuminate\Http\Request;
use Patpat\ResponseCache\CacheProfiles\CacheProfile;

class RequestHasher
{
    protected $cacheProfile;

    public function __construct(CacheProfile $cacheProfile)
    {
        $this->cacheProfile = $cacheProfile;
    }

    public function getHashFor(Request $request, $cacheKey)
    {   
        return 'responsecache-'.md5(
            $this->cacheProfile->cacheName($request, $cacheKey)
        );
    }
}
