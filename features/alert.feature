Feature: User sets alarm feature
  In order to set alarm as a member
  As a member
  I need to be able to set alarm

  @javascript
  Scenario: login site
    Given I am on homepage
    And I should not see "Hesabım"
    Then I follow "loginRegisterButton"
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    When I press "_submit"
    And I wait "1" second
    Then I should see "Hesabım"

    When I go to "/arama"
    And I should not see "Nedeni bilinmeyen bir hata oluştu."
    When I set the fashion alert
    When I set the discount alert



