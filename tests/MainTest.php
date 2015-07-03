<?php

namespace DanBruce\RestBaseTest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;

class MainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An overview of the class and basic tests to ensure it's minimally working
     * as expected.
     * @test
     */
    public function synopsis()
    {
        $request = new SampleRequest();
        $this->assertInstanceOf(
            'DanBruce\RestBaseTest\SampleRequest',
            $request
        );

        $client = $request->getClient();
        $this->assertInstanceOf('GuzzleHttp\Client', $client);

        // creates a mock 200 response
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-type', 'application/json'],
                Psr7\stream_for(file_get_contents(
                    __DIR__.'/mock/mainFixture.json'
                ))
            ),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $request->setClient($client);

        $response = $request->makeRequest();
        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/mock/mainFixture.json',
            json_encode($response)
        );
    }

    /**
     * Tests that the default failure callback throws the RequestException up.
     * @expectedException GuzzleHttp\Exception\RequestException
     * @expectedExceptionMessage Client error: 404
     */
    public function testSample404Response()
    {
        $request = new SampleRequest();
        $mock = new MockHandler([
            new Response(404)
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $request->setClient($client);

        // should generate an exception
        $request->makeRequest();
        $this->fail(); // shouldn't get here
    }

    /**
     * Tests a class that overrides the default callbacks.
     */
    public function testSubclassWithCallbackOverrides()
    {
        $request = new SampleRequestWithDefinedCallbacks();
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-type', 'application/json'],
                Psr7\stream_for(file_get_contents(
                    __DIR__.'/mock/mainFixture.json'
                ))
            ),
            new Response(403)
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $request->setClient($client);

        // the subclass extracts the mock user ID
        $response = $request->makeRequest();
        $this->assertEquals(38449301, $response);

        // the second mocked response is a failure
        $response = $request->makeRequest();
        $this->assertEquals('Client error: 403', $response);
    }

    /**
     * Tests that anonymous functions provided as callbacks override any
     * defaults.
     */
    public function testAnonymousCallbacks()
    {
        $request = new SampleRequestWithDefinedCallbacks();
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-type', 'application/json'],
                Psr7\stream_for(file_get_contents(
                    __DIR__.'/mock/mainFixture.json'
                ))
            ),
            new Response(403)
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $request->setClient($client);

        // successful response should be a string
        $response = $request->makeRequest(
            function (Response $response) {
                return (string)$response->getBody();
            }
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/mock/mainFixture.json',
            $response
        );

        // the failed response should just return the status code of 403
        $response = $request->makeRequest(
            null,
            function (RequestException $e) {
                if ($e->hasResponse()) {
                    return $e->getResponse()->getStatusCode();
                }
                return -1;
            }
        );
        $this->assertEquals(403, $response);
    }
}
