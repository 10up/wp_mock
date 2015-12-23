<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

class HooksContext implements Context
{

    /**
     * @Given I expect the following actions added:
     */
    public function iExpectTheFollowingActionsAdded(TableNode $table)
    {
        foreach ($this->getActionsWithDefaults($table) as $action) {
            WP_Mock::expectActionAdded(
                $action['action'],
                $action['callback'],
                $action['priority'],
                $action['arguments']
            );
        }
    }

    /**
     * @When I add the following actions:
     */
    public function iAddTheFollowingActions(TableNode $table)
    {
        foreach ($this->getActionsWithDefaults($table) as $action) {
            add_action(
                $action['action'],
                $action['callback'],
                $action['priority'],
                $action['arguments']
            );
        }
    }

    /**
     * @Given I expect the following filters added:
     */
    public function iExpectTheFollowingFiltersAdded(TableNode $table)
    {
        $filters  = $table->getHash();
        $defaults = array(
            'filter'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($filters as $filter) {
            $filter += $defaults;
            WP_Mock::expectFilterAdded(
                $filter['filter'],
                $filter['callback'],
                $filter['priority'],
                $filter['arguments']
            );
        }
    }

    /**
     * @When I add the following filters:
     */
    public function iAddTheFollowingFilters(TableNode $table)
    {
        $filters  = $table->getHash();
        $defaults = array(
            'filter'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($filters as $filter) {
            $filter += $defaults;
            add_filter(
                $filter['filter'],
                $filter['callback'],
                $filter['priority'],
                $filter['arguments']
            );
        }
    }

    private function getActionsWithDefaults(TableNode $table)
    {
        $actions  = $table->getHash();
        $defaults = array(
            'action'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($actions as &$action) {
            $action += $defaults;
        }
        unset( $action );

        return $actions;
    }

}
