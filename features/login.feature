Feature: Kullanıcı giriş ve çıkış.
  Bu test vitringez.com sitesine kullanıcı giriş ve çıkışını test eder.

  Background:
    Given I am on homepage

  @javascript
  Scenario: Kullanıcı girişi yap.
    Given I am on "http://vitringez.com"
	Then I follow "loginRegisterButton"
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    Then I press "_submit"

  Scenario: Cikis yap.

    Then I go to "/kullanici/cikis"





