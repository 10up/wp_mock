Feature: Function mocking
	In order to simulate the WordPress functional API
	As a developer
	I need to be able to mock WordPress core functions

	Scenario: userFunction creates functions that don't exist
		Given function wpMockTest does not exist
		When I mock function wpMockTest
		Then function wpMockTest should exist

	Scenario: Passthru function returns first argument
		Given I mock passthru function wpMockPassthruTest with args:
			| foobar | bazbat |
		Then I expect foobar when I run wpMockPassthruTest with args:
			| foobar | bazbat |
