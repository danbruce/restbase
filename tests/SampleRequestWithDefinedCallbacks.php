<?php

namespace DanBruce\RestBaseTest;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class SampleRequestWithDefinedCallbacks extends \DanBruce\RestBase\AbstractRestRequest
{
    protected function getHttpVerb()
    {
        return self::HTTP_VERB_GET;
    }

    protected function getUrl()
    {
        return 'https://api.github.com/users/danbruce/repos';
    }

    protected function getOptions()
    {
        return [];
    }

    protected function onSuccess(Response $response)
    {
        $response = parent::onSuccess($response);
        return $response[0]->id;
    }

    protected function onFailure(RequestException $exception)
    {
        return $exception->getMessage();
    }
}
