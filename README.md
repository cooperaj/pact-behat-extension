# Smart Gamma Behat Pact Extension

The extension allows to use contract test framework pact.io in BDD style with Behat

## Install

``
composer require --dev smart-gamma/pact-behat-extension
`` 

## Configure

Add to behat.yml / behat.yml.dist

    default:
        suites:
            default:
                contexts:
                    - SmartGamma\Behat\PactExtension\Context\PactContext
                
    extensions:
        SmartGamma\Behat\PactExtension\Extension:
            common:
                PACT_CONSUMER_NAME: consumer_name
                PACT_CORS: false
                PACT_BROKER_URI: https://pact.youdomain.com
                PACT_OUTPUT_DIR: var/pact/
                PACT_BROKER_HTTP_AUTH_USER: ci
                PACT_BROKER_HTTP_AUTH_PASS: cipass
                PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT: 10
            providers:
                - provider1_name: localhost:9090
                # in case of you have to communicate with more providers from the consumer 
                #- provider2_name localhost:8889


> Remove  PACT_BROKER_HTTP_AUTH_USER, PACT_BROKER_HTTP_AUTH_PASS: cipass if your pact broker is not http auth  protected

## Usage

- March Scenario with @pact tag


        @pact
        Scenario: My cool Pact contract test scenario


- Start Pact Mock Server

        Background:
        Given "provider1 name" API is available
   

- Define Pact Interaction

        Given "provider1 name" request 'GET' to '/api/some/1' should return response with 200 and body:
          | parameter    | value                     |
          | id           | 5af55347c9764a6a01684228  |
          | field1       | 35373808218O868           |
          | blocked      | false                     |
          | createdAt    | 2018-05-11T11:00:00+00:00 |

- Execute your consumer scenario steps  

        When I send a 'GET' request to '/api/entry' with parameters:
          | key      | value           |
          | imei     | 35373808218O868 |
          | aux_data | demo[]          |
        Then the response status code should be 200   
        
Configure your app work with mock server on "localhost:9090" instead of your real provider url 
      