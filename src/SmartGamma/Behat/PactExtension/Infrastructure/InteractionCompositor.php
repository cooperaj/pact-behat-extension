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
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var array
     */
    private $matchingStructure = [];

    /**
     * @var array
     */
    private $providerEntityCollection;


    public function __construct(MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

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
     * @param InteractionRequestDTO $requestDTO
     * @param array                 $headers
     *
     * @return ConsumerRequest
     */
    public function createRequestFromDTO(InteractionRequestDTO $requestDTO, array $headers = []): ConsumerRequest
    {
        $request = new ConsumerRequest();

        $request
            ->setMethod($requestDTO->getMethod())
            ->setPath($requestDTO->getUri());

        if (isset($this->authHeaders[$requestDTO->getProviderName()])) {
            $request->setHeaders($this->authHeaders[$requestDTO->getProviderName()]);
        }

        if (null !== $requestDTO->getQuery()) {
            $request->setQuery($requestDTO->getQuery());
        }

        foreach ($headers as $key => $value) {
            $request->addHeader($key, $value);
        }

        if (\count($requestDTO->getBody() > 0)) {
            $request->setBody($requestDTO->getBody());
        }

        return $request;
    }

    /**
     * @param int        $status
     * @param array|null $bodyParameters
     *
     * @return \PhpPact\Consumer\Model\ProviderResponse
     */
    public function createResponse(int $status, array $rawParameters = []): ProviderResponse
    {
        $response = new ProviderResponse();
        $response
            ->setStatus($status);

        $bodyParameters = $this->buildResponseBodyWithMatchers($rawParameters);

        if (sizeof($bodyParameters)) {
            $response->setBody($bodyParameters);
        }

        return $response;
    }

    /**
     * @param InteractionResponseDTO $responseDTO
     *
     * @return ProviderResponse
     */
    public function createResponseFromDTO(InteractionResponseDTO $responseDTO): ProviderResponse
    {
        $response = new ProviderResponse();
        $response
            ->setStatus($responseDTO->getStatus());

        $bodyParameters = $this->buildResponseBodyWithMatchers($responseDTO->getRawParameters());

        if (\count($bodyParameters) > 0) {
            $response->setBody($bodyParameters);
        }

        return $response;
    }

    public function addMatchingStructure(string $objectName, array $structure)
    {
        $this->matchingStructure[$objectName] = $structure;
    }

    /**
     * Initializes this class with the given options.
     *
     * @param array $hash {
     *     @var string $parameter
     *     @var string $value
     *     @var string $match
     * }
     *
     * @return array
     */
    private function buildResponseBodyWithMatchers(array $hash): array
    {
        return array_reduce(
            $hash,
            function (array $carry, array $bodyItem) {

                $matchType = $bodyItem['match'] ? $bodyItem['match'] : MatcherInterface::EXACT_TYPE;
                $value = $matchType == MatcherInterface::EACH_LIKE_TYPE ? $this->matchingStructure[$bodyItem['value']] : $bodyItem['value'];

                if ('null' !== $value ) {
                    $carry[$bodyItem['parameter']] = $this->matcher->$matchType($value);
                }

                return $carry;
            },
            []
        );
    }

    public function registerEntityOnProvider(ProviderStateDTO $stateDTO)
    {

    }
}