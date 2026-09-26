@local @local_nevertranslate
Feature: Choose which anti-translation techniques are used
  In order to control how the site resists automatic translation
  As an admin
  I need to switch techniques on and off, all at once if I want

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Never translate" in site administration

  Scenario: The enabled techniques are injected into the page
    Then I should see "Default: Page attribute: translate=\"no\" on the html element"
    # Checkbox labels are escaped in the Default line: HTML in them would show as literal tags.
    And I should not see "<code>"
    And "html[translate='no'].notranslate" "css_element" should exist
    And "body.notranslate" "css_element" should exist
    And "head meta[name='google'][content='notranslate']" "css_element" should exist

  Scenario: Switched-off techniques are no longer injected
    When I set the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_htmltranslate']" to "0"
    And I set the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_metagoogle']" to "0"
    And I press "Save changes"
    Then "html[translate='no']" "css_element" should not exist
    And "head meta[name='google']" "css_element" should not exist
    And "html.notranslate" "css_element" should exist

  @javascript
  Scenario: The select all/none toggle switches every technique
    When I set the field "Select all/none" to "1"
    Then the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_revert']" matches value "1"
    And the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_guard']" matches value "1"
    And I press "Save changes"
    And the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_revert']" matches value "1"
    And the field "Select all/none" matches value "1"
    When I set the field "Select all/none" to "0"
    Then the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_htmltranslate']" matches value "0"
    And the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_revert']" matches value "0"
    And I press "Save changes"
    And the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_guard']" matches value "0"
    And "html[translate='no']" "css_element" should not exist
    And "head meta[name='google']" "css_element" should not exist

  @javascript
  Scenario: The toggle reflects a partial selection
    When I set the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_guard']" to "0"
    Then the field "Select all/none" matches value "0"
    And I set the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_guard']" to "1"
    And I set the field with xpath "//input[@id='id_s_local_nevertranslate_techniques_revert']" to "1"
    And the field "Select all/none" matches value "1"
