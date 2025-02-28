<?php

declare(strict_types=1);

namespace FeatureTests\SmartGamma\Behat;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class FeatureContext implements Context
{
    /** @var ResponseInterface[] */
    private array $response;

    #[When('I make the complex request')]
    public function iMakeTheComplexRequest(): void
    {
        $client = new Client(['base_uri' => 'http://localhost:9090/']);

        $request          = new Request(
            'POST',
            '/api/device/5af55347c9764a6a01684228/first-frame',
            [
                'Content-Type' => 'application/json',
            ],
            json_encode(
                [
                    'id'   => '5af55347c9764a6a01684228',
                    'imei' => '35373808218O868',
                ],
            ),
        );
        $this->response[] = $client->send($request);
    }

    #[When('I make the request')]
    public function iMakeTheRequest(): void
    {
        $client           = new Client(['base_uri' => 'http://localhost:9090/']);
        $this->response[] = $client->request('GET', 'hello');
    }

    #[When('I make the request with a query')]
    public function iMakeTheRequestWithAQuery(): void
    {
        $client           = new Client(['base_uri' => 'http://localhost:9090/']);
        $this->response[] = $client->request('GET', 'hello?name=bob');
    }

    #[When('I make all requests')]
    public function iMakeAllRequests(): void
    {
        $client           = new Client(['base_uri' => 'http://localhost:9090/']);
        $this->response[] = $client->request('GET', 'hello?name=bob');

        $client           = new Client(['base_uri' => 'http://localhost:9090/', 'http_errors' => false]);
        $this->response[] = $client->request('GET', 'hello');
    }

    #[Then('I should get a success response')]
    public function iShouldGetASuccessResponse(): void
    {
        if ($this->response[0]->getStatusCode() !== 200) {
            throw new Exception('Expected a 200 status code');
        }
    }

    #[Then('I should get a mixed response')]
    public function iShouldGetAMixedResponse(): void
    {
        if ($this->response[0]->getStatusCode() !== 200) {
            throw new Exception('Expected a 200 status code');
        }

        if ($this->response[1]->getStatusCode() !== 401) {
            throw new Exception('Expected a 401 status code');
        }
    }
}
