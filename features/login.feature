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
   # Then I should see "Çıkış" in the "html.js body.layout1 div#contentHolder header#header div#headerContainer aside#headerRight a.borderOnRight" element







