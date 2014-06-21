Feature: register feature
  In order to register as new member on vitringez.com
  As a new member of vitringez.com
  I need to be able to fill in the registration form and submit successfully

  @javascript
  Scenario: open registraction form
    Given I am on homepage
    Then I should not see "Hesabım"
    And I follow "loginRegisterButton"
    And I wait "2" second
    When I fill in registration form
    Then I wait "2" second
#    Then I should see "Hesabım"
    And I sent report mail






