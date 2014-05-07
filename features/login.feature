Feature: log in-out
  this feature will be used to testing log in/out vitringez.com

  Background:
    Given I am on homepage

  @javascript
  Scenario: user login
    Given I am on "http://vitringez.com"
	Then I follow "loginRegisterButton"
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    Then I press "_submit"

  Scenario: log out

    Then I go to "/kullanici/cikis"





