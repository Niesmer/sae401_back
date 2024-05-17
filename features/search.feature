# features/search.feature
Feature: Search
  In order to see a word definition
  As a website user
  I need to be able to search for a word

  @javascript
  Scenario: Searching for a page that does exist
    Given I am on "/wiki/Main_Page"
    And I change window size to "1200" x "800"
    And I wait for "1" s
    When I fill in "search" with "Behavior Driven Development"
    And I wait for "1" s
    #And I press "Rechercher"
    And I press on "button.cdx-button" "css"
    Then I wait for "1" s
    And I should see "agile software development"