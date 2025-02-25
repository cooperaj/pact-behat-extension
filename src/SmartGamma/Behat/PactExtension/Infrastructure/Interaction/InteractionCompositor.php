<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;

use stdClass;

use function count;

class InteractionCompositor
{
    private MatcherInterface $matcher;

    public function __construct(MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    public function createRequestFromDTO(InteractionRequestDTO $requestDTO): ConsumerRequest
    {
        $request = new ConsumerRequest();

        $request
            ->setMethod($requestDTO->getMethod())
            ->setPath($requestDTO->getUri());

        if (null !== $requestDTO->getQuery()) {
            $request->setQuery($requestDTO->getQuery());
        }

        foreach ($requestDTO->getHeaders() as $key => $value) {
            $request->addHeader($key, $value);
        }

        if (count($requestDTO->getBody()) > 0) {
            $request->setBody($requestDTO->getBody());
        }

        return $request;
    }

    public function createResponseFromDTO(InteractionResponseDTO $responseDTO): ProviderResponse
    {
        $response = new ProviderResponse();
        $response
            ->setStatus($responseDTO->getStatus());

        $bodyParameters = $this->buildResponseBodyWithMatchers($responseDTO);

        if ($bodyParameters instanceof stdClass || count($bodyParameters) > 0) {
            $response->setBody($bodyParameters);
        }

        return $response;
    }

    /**
     * @param InteractionResponseDTO $responseDTO
     *
     * @return mixed[]|stdClass
     */
    private function buildResponseBodyWithMatchers(InteractionResponseDTO $responseDTO): array|stdClass
    {
        $parameters = $responseDTO->getRawParameters();

        if (is_array($parameters)) {
            return array_reduce(
                $parameters,
                function(array $carry, array $bodyItem) use ($responseDTO){

                    $matchType = !empty($bodyItem['match']) ? $bodyItem['match'] : MatcherInterface::EXACT_TYPE;
                    $value = $matchType == MatcherInterface::EACH_LIKE_TYPE
                        ? $responseDTO->getMatchingObjectStructure($bodyItem['value'])
                        : $bodyItem['value'];

                    if ('null' !== $value) {
                        $carry[$bodyItem['parameter']] = $this->matcher->$matchType($value);
                    }

                    return $carry;
                },
                []
            );
        }

        return $parameters;
    }
}
