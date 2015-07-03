<?php

namespace DanBruce\RestBaseTest;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class SampleRequestWithDefinedCallbacks extends SampleRequest
{
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
