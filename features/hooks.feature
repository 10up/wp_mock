Feature: Hook mocking
	In order to test common WordPress functionality
	As a developer
	I need to be able to mock actions and filters

	Scenario: expectActionAdded sets up expectation
		Given I expect the following actions:
			| action | callback | priority | arguments |
			| foobar | bazbat   | 10       | 2         |
		When I add the following actions:
			| action | callback | priority | arguments |
			| foobar | bazbat   | 10       | 2         |
		Then tearDown should not fail
