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
     * @Given I expect the :action action
     */
    public function iExpectTheAction($action)
    {
        $this->iExpectTheActionWith($action, new TableNode(array()));
    }

    /**
     * @When I expect the :action action with:
     */
    public function iExpectTheActionWith($action, TableNode $table)
    {
        $args = array($action);
        $rows = $table->getRows();
        if (isset( $rows[0] ) && is_array($rows[0])) {
            $args = array_merge($args, $rows[0]);
        }
        call_user_func_array(array('WP_Mock', 'expectAction'), $args);
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
     * @When I do the :action action
     */
    public function iDoTheAction($action)
    {
        $this->iDoTheActionWith($action, new TableNode(array()));
    }

    /**
     * @When I do the :action action with:
     */
    public function iDoTheActionWith($action, TableNode $table)
    {
        $args = array($action);
        $rows = $table->getRows();
        if (isset( $rows[0] ) && is_array($rows[0])) {
            $args = array_merge($args, $rows[0]);
        }
        call_user_func_array('do_action', $args);
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
