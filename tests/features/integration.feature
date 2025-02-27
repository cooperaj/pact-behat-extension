Feature: We can make calls to APIs defined in behat feature files

  @pact
  Scenario: Service responds to hello uri with success
    Given 'test-service' request 'GET' to '/hello' should return response with 200
    When I make the request
    Then I should get a success response


  @pact
  Scenario: Service accepts complex requests and returns complex responses
    Given 'test-service' request 'POST' to '/api/device/5af55347c9764a6a01684228/first-frame' with parameters:
      | parameter    | value                     |
      | id           | 5af55347c9764a6a01684228  |
      | imei         | 35373808218O868           |
    And request above to 'test-service' should return response with 200 and body:
      | parameter    | value                     |
      | typeName     | SOME                      |
      | activated    | true                      |
      | blocked      | false                     |
    When I make the complex request
    Then I should get a success response