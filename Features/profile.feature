Feature: User profile behaviour test
  In order to test behaviour of user profile page
  As a member of vitringez.com
  I need to able to change some field successfully

  @javascript
  Scenario: first login the site
    Given I am on homepage
    When I should not see "Hesabım"
    Then I follow "loginRegisterButton"
    And I wait "1" second
    Then I fill in "username" with "testhesabi"
    Then I fill in "password" with "test1234"
    When I press "_submit"
    And I wait "1" second
    Then I should see "Hesabım"
    When I go to "/kullanici/profil"
    Then I should not see "ARADIĞINIZ SAYFAYA ULAŞILAMIYOR :("
    And  I should see "Hesabım"
    Then I attach the file "jean-luc-picat.jpg" to "vitringez_user_profile_form[profilePictureFile]"
    When I fill profile details
    Then I press "Güncelle"
    Then I should see "Profiliniz başarıyla güncellendi" in the "#content > div > section > div.alert.alert-success" element
    Then I reload the page
    And I send report mail





