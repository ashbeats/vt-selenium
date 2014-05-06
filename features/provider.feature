Feature: Providerları test etmek için kullanılacak.
  vitringez.com/arama üzerindeki provider ve ürün sayısı.

  Background:
    Given I am on homepage

    @javascript
  Scenario: Arama sayfasına geç
    Then I go to "/arama"
    When I select all brand

