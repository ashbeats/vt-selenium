Feature: provider
  this feature will be used to testing providers on vitringez.com

  Background:
    Given I am on homepage

  @javascript
  Scenario: Jump on the search page
    When I go to "/arama"
    Then I select all provider

