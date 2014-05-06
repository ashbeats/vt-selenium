<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @When /^I select all brand$/
     */
    public function iSelectAllBrand()
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $totalproduct = $page->findById("filterProgressBar")->getText();
        echo "\n\nToplam ".$totalproduct." var\n\n";

        $brandselect = $page->find("xpath" , "/html/body/div[2]/div/div/div[2]/div[2]/div/div/div/div[2]/div[3]/div/select");

        var_dump($brandselect);
    }

    /**
     * @Then /^I click on element with id "([^"]*)"$/
     */
    public function iClickOnElementWithId($id)
    {
        $this->getSession()->getPage()->clickLink("id");
    }

    /**
     * @When /^I click on the element with css "([^"]*)"$/
     */
    public function iClickOnTheElementWithCss($css)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $page->find('css',$css)->click();
    }
    
    public function iClickOnTheElementWithXpath($xpath)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $page->find('xpath',$xpath)->click();
    }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        doSomethingWith($argument);
//    }
//
}
