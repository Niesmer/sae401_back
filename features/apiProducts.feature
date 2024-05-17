# features/apiProducts.feature
Feature: testApiProducts
  In order to manage articles
  As a tester user
  I need to be able to create a new article
  
  Scenario: Create a new article
    Given I have the payload
      """
      {
        "article_type": "livre",
        "titre": "Harry Potter, I : Harry Potter à l'école des sorciers",
        "prix" : "8.95",
        "disponibilite": "8",
        "image": "https://m.media-amazon.com/images/I/717GP+JqcTL._AC_UY327_FMwebp_SL140_.jpg"
      }
      """
    When I request "POST /wp-json/wc/v3/products"
    Then the response code should be 201
    And the "0.prix" property should equal "8.95"