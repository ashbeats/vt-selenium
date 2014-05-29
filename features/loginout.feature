Feature: log in/out feature
  In order to log in then log out from vitringez.com
  As a vitringez.com member
  I need to be able to log in then log out vitringez.com successfully

  Background:
    Given I am on homepage

  @javascript
  Scenario: login then logout from site
    And I should not see "Hesabım"
    And I follow "loginRegisterButton"
    And I wait "1" second
    And I fill in "username" with "testhesabi"
    And I fill in "password" with "test1234"
    Then I press "_submit"
    And I wait "1" second
    Then I should see "Hesabım"
    And I should not see "Nedeni bilinmeyen bir hata oluştu."
    Then I go to "/kullanici/cikis"
    And I should not see "Hesabım"
