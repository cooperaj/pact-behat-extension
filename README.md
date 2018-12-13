# Smart Gamma Behat Pact Extension

<img src="https://travis-ci.org/smart-gamma/pact-behat-extension.svg?branch=master" />

[![SymfonyInsight](https://insight.symfony.com/projects/e38bcae8-1b27-472f-bd31-284811f61a61/big.svg)](https://insight.symfony.com/projects/e38bcae8-1b27-472f-bd31-284811f61a61)

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
                PACT_BROKER_HTTP_AUTH_USER: user
                PACT_BROKER_HTTP_AUTH_PASS: pass
                PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT: 10
            providers:
                - provider1_name: localhost:9090
                # in case of you have to communicate with more providers from the consumer 
                #- provider2_name localhost:8889


> Remove  PACT_BROKER_HTTP_AUTH_USER, PACT_BROKER_HTTP_AUTH_PASS if your pact broker is not http auth  protected

### Consumer version

You should define you consumer version as:

    common:
        PACT_CONSUMER_VERSION: 1.0.0

But if you are using Symfony framework you can skip this and define the version at Kernel const 

    App\Kernel::PACT_CONSUMER_VERSION
    
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

 - Define Pact Interaction for complex request(POST body)
 
        Given "provider1 name" request 'POST' to '/api/device/5af55347c9764a6a01684228/first-frame' with parameters:
          | parameter    | value                     |
          | id           | 5af55347c9764a6a01684228  |
          | imei         | 35373808218O868           |
        And request above to "provider1 name" should return response with 200 and body:
          | parameter    | value                     | 
          | typeName     | SOME                      | 
          | activated    | true                      | 
          | blocked      | false                     | 

- Define nested structure

        Given "<device>" object should have follow structure:
          | parameter | value                    |
          | id        | 5af55347c9764a6a01684228 |
          | imei      | 35373808218O868          |
          | iccid     | 89883O3000000277040      |
        Given "device registry" request 'GET' to '/api/devices' should return response with 200 and body:
          | parameter | value    | match    |
          | count     | 2        | integer  |
          | devices   | <device> | eachLike |
          
- Execute your consumer scenario steps  

        When I send a 'GET' request to '/api/entry' with parameters:
          | key      | value           |
          | imei     | 35373808218O868 |
          | aux_data | demo[]          |
        Then the response status code should be 200   
        
### Matchers
        
You can define you response accoring to Postel law with matchers as:
        
        Given "provider1 name" request 'GET' to '/api/some/1' should return response with 200 and body:
          | parameter    | value                     | match           |
          | id           | 5af55347c9764a6a01684228  | like            |
          | field1       | 35373808218O868           |                 | 
          | blocked      | false                     | boolean         |
          | createdAt    | 2018-05-11T11:00:00+00:00 | dateTimeISO8601 |
 
 - like - will define type matching
 - empty value - will use exact value
 - boolean - will check bool type
 - dateTimeISO8601 - will match to date format
 - eachLike - will match against defined structure
 
 