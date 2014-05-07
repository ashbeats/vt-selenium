Feature: Providerları test etmek için kullanılacak.
  vitringez.com/arama üzerindeki provider ve ürün sayısı.

  Background:
    Given I am on homepage

  @javascript
  Scenario: Arama sayfasına geç
    When I go to "/arama"
    Then I select all provider

