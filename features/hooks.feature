Feature: Hook mocking
  In order to test common WordPress functionality
  As a developer
  I need to be able to mock actions and filters

  Scenario: expectActionAdded sets up expectation
    Given I expect the following actions added:
      | action | callback | priority | arguments |
      | foobar | bazbat   | 10       | 2         |
    When I add the following actions:
      | action | callback | priority | arguments |
      | foobar | bazbat   | 10       | 2         |
    Then tearDown should not fail

  Scenario: expectActionAdded fails when not met
    Given I expect the following actions added:
      | action | callback |
      | foobar | bazbat   |
    When I do nothing
    Then tearDown should fail

  Scenario: expectActionAdded fails when argument count is different
    Given I expect the following actions added:
      | action | callback | priority | arguments |
      | foobar | bazbat   | 10       | 2         |
    When I add the following actions:
      | action | callback | priority | arguments |
      | foobar | bazbat   | 10       | 3         |
    Then tearDown should fail

  Scenario: expectActionAdded fails when priority is different
    Given I expect the following actions added:
      | action | callback | priority |
      | foobar | bazbat   | 10       |
    When I add the following actions:
      | action | callback | priority |
      | foobar | bazbat   | 11       |
    Then tearDown should fail

  Scenario: expectActionNotAdded fails when action added
    Given I expect the following actions not to be added:
      | action | callback |
      | foobar | bazbat   |
    When I add the following actions:
      | action | callback |
      | foobar | bazbat   |
    Then tearDown should fail

  Scenario: expectActionNotAdded passes when action not added
    Given I expect the following actions not to be added:
      | action | callback |
      | foobar | bazbat   |
    When I do nothing
    Then tearDown should not fail

  Scenario: expectFilterAdded sets up expectation
    Given I expect the following filters added:
      | filter | callback | priority | arguments |
      | foobar | bazbat   | 10       | 2         |
    When I add the following filters:
      | filter | callback | priority | arguments |
      | foobar | bazbat   | 10       | 2         |
    Then tearDown should not fail

  Scenario: expectFilterAdded fails when not met
    Given I expect the following filters added:
      | filter | callback |
      | foobar | bazbat   |
    When I do nothing
    Then tearDown should fail

  Scenario: expectFilterAdded fails when argument count is different
    Given I expect the following filters added:
      | filter | callback | priority | arguments |
      | foobar | bazbat   | 10       | 2         |
    When I add the following filters:
      | filter | callback | priority | arguments |
      | foobar | bazbat   | 10       | 3         |
    Then tearDown should fail

  Scenario: expectFilterAdded fails when priority is different
    Given I expect the following filters added:
      | filter | callback | priority |
      | foobar | bazbat   | 10       |
    When I add the following filters:
      | filter | callback | priority |
      | foobar | bazbat   | 11       |
    Then tearDown should fail

  Scenario: expectAction sets up expectation
    Given I expect the "foobar" action
    When I do the "foobar" action
    Then tearDown should not fail

  Scenario: expectAction fails when unmet
    Given I expect the "foobar" action
    When I do nothing
    Then tearDown should fail

  Scenario: expectAction with extra arguments
    Given I expect the "foobar" action with:
      | some | extra | data |
    When I do the "foobar" action with:
      | some | extra | data |
    Then tearDown should not fail

  Scenario: action with the wrong arguments fails
    Given I expect the "bazbat" action with:
      | the correct data |
    When I do the "bazbat" action with:
      | Invalid information |
    Then tearDown should fail

  Scenario: action with extra arguments fails
    Given I expect the "bazbat" action with:
      | data |
    When I do the "bazbat" action with:
      | data | plus |
    Then tearDown should fail

  Scenario: expectFilter sets up expectation
    Given I expect the "foobar" filter with "bazbat"
    When I apply the filter "foobar" with "bazbat"
    Then tearDown should not fail

  Scenario: expectFilter fails when unmet
    Given I expect the "foobar" filter with "bazbat"
    When I do nothing
    Then tearDown should fail

  Scenario: expectFilter with extra arguments
    Given I expect the "foobar" filter with:
      | some | extra | data |
    When I apply the filter "foobar" with:
      | some | extra | data |
    Then tearDown should not fail

  Scenario: filter with the wrong arguments fails
    Given I expect the "bazbat" filter with:
      | the correct data |
    When I apply the filter "bazbat" with:
      | Invalid information |
    Then tearDown should fail

  Scenario: expectFilter fails when called with wrong argument
    Given I expect the "foobar" filter with "bazbat"
    When I apply the filter "foobar" with "bimbam"
    Then tearDown should fail

  Scenario: filter with extra arguments fails
    Given I expect the "bazbat" filter with:
      | data |
    When I apply the filter "bazbat" with:
      | data | plus |
    Then tearDown should fail

  @strictmode
  Scenario: Unexpected action fails in strict mode
    Given strict mode is on
    When I do nothing
    Then I expect an error when I run do_action with args:
      | bimbam | bazbat |

  Scenario: unexpected action does not fail tests
    Given I do nothing
    When I add the following actions:
      | action | callback |
      | foobar | bazbat   |
    Then tearDown should not fail

  @strictmode
  Scenario: Unexpected filter fails in strict mode
    Given strict mode is on
    When I do nothing
    Then I expect an error when I run apply_filters with args:
      | foobar | bazbat |

  Scenario: unexpected filter does not fail tests
    Given I do nothing
    When I add the following filters:
      | filter | callback |
      | foobar | bazbat   |
    Then tearDown should not fail

  @strictmode
  Scenario: unexpected action fails in strict mode
    Given strict mode is on
    When I do nothing
    Then I expect an error when I run add_action with args:
      | foobar | bazbat |

  @strictmode
  Scenario: unexpected action fails in strict mode
    Given strict mode is on
    When I do nothing
    Then I expect an error when I run add_filter with args:
      | foobar | bazbat |

  Scenario: filter responder works
    Given I expect filter "the_content" to respond to "Test content" with "Responder works"
    When I apply the filter "the_content" with "Test content"
    Then The filter "the_content" should return "Responder works"

  Scenario: filter returns default value when no filter defined
    Given I do nothing
    When I apply the filter "the_content" with "Apple"
    Then The filter "the_content" should return "Apple"

  Scenario: filter returns default value when unexpected value used
    Given I expect filter "the_content" to respond to "Windows" with "OS X"
    When I apply the filter "the_content" with "Linux"
    Then The filter "the_content" should return "Linux"

  Scenario: expectFilterNotAdded fails when filter added
    Given I expect the following filters not to be added:
      | filter | callback |
      | foobar | bazbat   |
    When I add the following filters:
      | filter | callback |
      | foobar | bazbat   |
    Then tearDown should fail

  Scenario: expectFilterNotAdded passes when filter not added
    Given I expect the following filters not to be added:
      | filter | callback |
      | foobar | bazbat   |
    When I do nothing
    Then tearDown should not fail
