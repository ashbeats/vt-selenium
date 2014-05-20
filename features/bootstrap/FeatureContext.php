<?php

use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    /**
     * @Then /^I fill in registration form$/
     */
    public function iFillInRegistrationForm()
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $handler = $session->getSelectorsHandler();

        $registration_form = $page->findById("newUserLink");
        $registration_form->click();

        $namefield = $page->find("xpath",
            $handler->selectorToXpath('xpath', "/html/body/div[4]/div/div/div/form/div/input"));

        $surnamefield = $page->find("xpath",
            $handler->selectorToXpath("xpath", "/html/body/div[4]/div/div/div/form/div[2]/input"));

        $namefield->setValue($this->generateRandomString(rand(3, 12)));
        $surnamefield->setValue($this->generateRandomString(rand(3, 12)));
        /*$usernamefield;
        $emailfield;
        $passwordfield;
        $passwordagain;*/

    }

    public function generateRandomString($length = 6)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


    /**
     * @When /^I select all provider$/
     */
    public function iSelectAllProvider()
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $handler = $session->getSelectorsHandler();
        $totalproduct = $page->findById("filterProgressBar")->getText();
        echo "\n\nToplam " . $totalproduct . "\n";

        $innerdiv = $page->find('css', 'html.js body.layout1 div#contentHolder div#mainContent div.catalogListing div.catalogContainer div#catalogResult
        div.productListingContainer div.productListing div#topFilterContainer div.filterInner div.filterItemsContainer div.selectBoxes
        div#filterProvider.filterBox div.selectContainer div.selectDropdown div.inner');
        $providerdiv = $innerdiv->findAll('css', 'div');

        $totalprovider = count($providerdiv);
        echo "Provider sayısı:" . $totalprovider . "\n\n";

        $provider_name = array();
        $provider_product = array();
        for ($i = 1; $i < $totalprovider; $i++) {
            $pr = $providerdiv[$i]->find('css', 'input');
            $data_url = $pr->getAttribute("data-url");
            $providerurl = "/arama/" . $data_url . "-magazasi";
            $session->visit("http://vitringez.com" . $providerurl);

            $provider_name[] = $pr->getAttribute("data-name");
            $subproduct = $page->findById("filterProgressBar")->getText();
            $provider_product[] = $subproduct;
            echo $data_url . "   -> " . $subproduct . " var\n";
            $session->visit("http://vitringez.com/arama");

        }

    }

    /**
     * @Given /^I wait "([^"]*)" millisecond$/
     */
    public function iWaitMillisecond($time)
    {
        $this->getSession()->wait(intval($time));
    }
}
