Feature: Sort price ascending and descending
  In order to observe sorting
  As a visitor
  I need to be able to sort successfully

  Scenario:
    Given I am on homepage
    Then I go to "/arama"
    When I check "descending" sort algorithm
    And I check "ascending" sort algorithm
#    following sort algoritm is not exist on site so it must throw an error
#    Then I check "bubble" sort algorithm
    And I send report mail


