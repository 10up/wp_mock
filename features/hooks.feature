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
