Feature: User sets alarm feature
  In order to set alarm as a member
  As a member
  I need to be able to set alarm

  @javascript
  Scenario: login site
    Given I am on homepage
    Then I follow "loginRegisterButton"
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    When I press "_submit"

    Then I wait "300" millisecond
    When I go to "/arama"
    When I set the fashion alert
    And I wait "300" millisecond

    When I set the discount alert
    And I wait "300" millisecond



