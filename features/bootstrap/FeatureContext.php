<?php

use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{
    /**
      * @Given /^I wait for "([^"]*)" s$/
      */
    public function iWait($seconds)
    {
		if ($this->running_javascript()) {
			$this->getSession()->wait(1000*$seconds);
		}
    }
	
    /**
      * @When /^I click on "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
      * @param string $element Element we look for
      * @param string $selectortype The type of what we look for
      */
    public function i_click_on($element, $selectortype)
    {
		$e = $this->getSession()->getPage()->find($selectortype, $element);
        $e->click();
    }
	
    /**
      * @When /^I press on "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
      * @param string $element Element we look for
      * @param string $selectortype The type of what we look for
      */
    public function i_press_on($element, $selectortype)
    {
		$e = $this->getSession()->getPage()->find($selectortype, $element);
        $e->press();
    }
	
    /**
      * @When /^I check on "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
      * @param string $element Element we look for
      * @param string $selectortype The type of what we look for
      */
    public function i_check_on($element, $selectortype)
    {
		$e = $this->getSession()->getPage()->find($selectortype, $element);
        $e->check();
    }
	
    /**
      * @When /^I change window size to "([^"]*)" x "([^"]*)"$/
      */
    public function i_change_window_size($width, $height)
    {
		$this->getSession()->resizeWindow((int)$width, (int)$height, 'current');
    }
	
    /**
     * Returns whether the scenario is running in a browser that can run Javascript or not.
     *
     * @return boolean
     */
    protected function running_javascript() {
        return get_class($this->getSession()->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
    }
	
	
    /**
     * @Transform /^random@random.random$/
     * Generate a "random" alpha-numeric string.
     * @return string
     */
    public function random()
    {
        $length = 6 ;
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        return $str."@".$str.".".$str ;
    }
}
