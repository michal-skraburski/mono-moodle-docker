@qtype @qtype_coderunner @javascript @deletetestcasetest
Feature: Delete a test case in the CodeRunner author form
  In order to remove an unwanted test case without disturbing the others
  As a teacher editing a CodeRunner question
  I need a per-row delete button that keeps the surviving test cases intact

  Background:
    Given the CodeRunner test configuration file is loaded
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | questioncategory | name          |
      | Course       | C1        | Top              | Behat Testing |
    # Disable the Ace UI before the form loads so the test case fields are
    # plain textareas whose values Behat can set and read directly.
    And I am on the "Course 1" "core_question > course question bank" page logged in as teacher1
    And I disable UI plugins in the CodeRunner question type
    And I press "Create a new question ..."
    And I click on "input#item_qtype_coderunner" "css_element"
    And I press "submitbutton"
    And I set the field "id_coderunnertype" to "python3"
    And I set the field "name" to "Deletion test"
    And I set the field "id_questiontext" to "Delete the middle test case"

  Scenario: Deleting the middle test case keeps the other two intact
    When I set the field "id_testcode_0" to "ALPHA_CASE"
    And I set the field "id_expected_0" to "ALPHA"
    And I set the field "id_testcode_1" to "BETA_CASE"
    And I set the field "id_expected_1" to "BETA"
    And I set the field "id_testcode_2" to "GAMMA_CASE"
    And I set the field "id_expected_2" to "GAMMA"
    And I delete CodeRunner test case "1"
    # The two surviving cases keep their data (Moodle may renumber the rows,
    # so this checks the values, not their row indices); the deleted one is gone.
    Then a CodeRunner test case should contain "ALPHA_CASE"
    And a CodeRunner test case should contain "GAMMA_CASE"
    And no CodeRunner test case should contain "BETA_CASE"
