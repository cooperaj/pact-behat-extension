<?php
/**
 * Created by PhpStorm.
 * User: jekccs
 * Date: 30.11.18
 * Time: 16:18
 */

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;


use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;

class InteractionCompositor
{
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
     * @param InteractionRequestDTO $requestDTO
     *
     * @return ConsumerRequest
     */
    public function createRequestFromDTO(InteractionRequestDTO $requestDTO): ConsumerRequest
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

        foreach ($requestDTO->getHeaders() as $key => $value) {
            $request->addHeader($key, $value);
        }

        if (\count($requestDTO->getBody()) > 0) {
            $request->setBody($requestDTO->getBody());
        }

        return $request;
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

        $bodyParameters = $this->buildResponseBodyWithMatchers($responseDTO);

        if (\count($bodyParameters) > 0) {
            $response->setBody($bodyParameters);
        }

        return $response;
    }

    /**
     * @param InteractionResponseDTO $responseDTO
     *
     * @return array
     */
    private function buildResponseBodyWithMatchers(InteractionResponseDTO $responseDTO): array
    {
        return array_reduce(
            $responseDTO->getRawParameters(),
            function (array $carry, array $bodyItem) use ($responseDTO){

                $matchType = $bodyItem['match'] ? $bodyItem['match'] : MatcherInterface::EXACT_TYPE;
                $value = $matchType == MatcherInterface::EACH_LIKE_TYPE ? $responseDTO->getMatchingObjectStructure($bodyItem['value']): $bodyItem['value'];

                if ('null' !== $value ) {
                    $carry[$bodyItem['parameter']] = $this->matcher->$matchType($value);
                }

                return $carry;
            },
            []
        );
    }
}