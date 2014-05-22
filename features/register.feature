Feature: register feature
  In order to register as new member on vitringez.com
  As a new member of vitringez.com
  I need to be able to fill in the registration form and submit successfully

  @javascript
  Scenario: open registraction form
    Given I am on homepage
    Then I should not see "Hesabım"
    And I follow "loginRegisterButton"
    When I fill in registration form
    Then I wait "3" second
        Then I should see "Hesabım"

#    Then I go to "/arama"







