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

	Scenario: return value works correctly
		Given I mock function wpMockTest to return "test value"
		Then I expect "test value" when I run wpMockTest

	Scenario: expectations cause error on unexpected use
		Given I mock passthru function wpMockTest with args:
			| bazbat | foobar |
		Then I expect an error when I run wpMockTest with args:
			| bimbam | bobeep |
