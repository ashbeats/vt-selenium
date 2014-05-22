Feature: User profile behaviour test
  In order to test behaviour of user profile page
  As a member of vitringez.com
  I need to able to change some field successfully

  @javascript
  Scenario: first login the site
    Given I am on homepage
    When I should not see "Hesabım"
    Then I follow "loginRegisterButton"
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    When I press "_submit"

  @javascript
  Scenario: go to profile page
    When I go to "/kullanici/profil"
    Then I should not see "ARADIĞINIZ SAYFAYA ULAŞILAMIYOR :("



