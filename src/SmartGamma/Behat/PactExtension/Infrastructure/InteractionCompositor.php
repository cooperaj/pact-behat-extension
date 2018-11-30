<?php
/**
 * Created by PhpStorm.
 * User: jekccs
 * Date: 30.11.18
 * Time: 16:18
 */

namespace SmartGamma\Behat\PactExtension\Infrastructure;


use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;

class InteractionCompositor
{
    /**
     * @var array
     */
    private $authHeaders = [];

    /**
     * @param string $authType
     * @param string $credentials
     * @param string $providerName
     *
     * @throws NoAuthTypeSupported
     */
    public function authorizeConsumerRequestToProvider(string $authType, string $credentials, string $providerName): void
    {
        switch ($authType) {
            case 'http':
                $this->authHeaders[$providerName] = ['Authorization' => 'Basic ' . base64_encode($credentials)];
                break;
            default:
                throw new NoAuthTypeSupported('No authorization type:' . $authType . ' is supported');
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $query
     * @param array  $headers
     * @param null   $body
     *
     * @return \PhpPact\Consumer\Model\ConsumerRequest
     */
    public function createRequest(
        string $providerName,
        string $method,
        string $path,
        string $query = null,
        array $headers = [],
        $body = null
    ): ConsumerRequest
    {
        $request = new ConsumerRequest();

        $request
            ->setMethod($method)
            ->setPath($path);

        if (isset($this->authHeaders[$providerName])) {
            $request->setHeaders($this->authHeaders[$providerName]);
        }

        if (null !== $query) {
            $request->setQuery($query);
        }

        foreach ($headers as $key => $value) {
            $request->addHeader($key, $value);
        }

        if (null !== $body) {
            $request->setBody($body);
        }

        return $request;
    }

    /**
     * @param int        $status
     * @param array|null $bodyParameters
     *
     * @return \PhpPact\Consumer\Model\ProviderResponse
     */
    public function createResponse(int $status, array $bodyParameters = null): ProviderResponse
    {
        $response = new ProviderResponse();
        $response
            ->setStatus($status);

        if (null !== $bodyParameters) {
            $response->setBody($bodyParameters);
        }

        return $response;
    }
}