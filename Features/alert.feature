Feature: User sets alarm feature
  In order to set alarm as a member
  As a member
  I need to be able to set alarm

  @javascript
  Scenario: login site
    Given I am on homepage
    And I should not see "Hesabım"
    Then I follow "loginRegisterButton"
    And I wait "1" second
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    When I press "_submit"
    And I wait "1" second
    Then I should see "Hesabım"

    When I go to "/arama"
    When I set the fashion alert
    #When I set the discount alert
    And I send report mail




