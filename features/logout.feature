Feature: logout feature
  In order to logout from vitringez.com
  As a vitringez.com member
  I need to be able to log out from vitringez.com successfully

  @javascript
  Scenario: logout from site
    Given I am on homepage
    And I should not see "Hesabım"
    And I follow "loginRegisterButton"
    And I fill in "username" with "testhesabi"
    And I fill in "password" with "test1234"
    Then I press "_submit"

    And I wait "200" milisecond
    Then I should see "Hesabım"
    Then I go to "/kullanici/cikis"
