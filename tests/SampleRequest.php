<?php

namespace DanBruce\RestBaseTest;

class SampleRequest extends \DanBruce\RestBase\AbstractRestRequest
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
}
