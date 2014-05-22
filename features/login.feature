Feature: login feature
  In order to sign in to vitringez.com
  As a vitringez.com member
  I need to be able to log in to vitringez.com successfully

  Background:
    Given I am on homepage

  @javascript
  Scenario: user login
    When I should not see "Hesabım"
    Then I follow "loginRegisterButton"
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    When I press "_submit"
    And I wait "1" second
    Then I should see "Hesabım"








