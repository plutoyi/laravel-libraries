<?php
namespace Patpat\ResponseCache\Services;


use Symfony\Component\HttpFoundation\Response;

class ResponseSerializer
{
    //序列化响应
    public function serialize(Response $response)
    {
        $content = $response->getContent();
        $statusCode = $response->getStatusCode();
        $headers = $response->headers;
        return serialize(compact('content', 'statusCode', 'headers'));
    }

    //反序列化响应
    public function unserialize($serializedResponse)
    {
        $responseProperties = unserialize($serializedResponse);
        $response = new Response($responseProperties['content'], $responseProperties['statusCode']);
        $response->headers = $responseProperties['headers'];
        return $response;
    }
}