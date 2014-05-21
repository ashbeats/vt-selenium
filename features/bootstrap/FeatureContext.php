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
        $session->getDriver()->resizeWindow(1600,900,'current');
        $page = $session->getPage();
        $handler = $session->getSelectorsHandler();

        $page->findById("newUserLink")->click();

        $divrow = array();
        $divrow = $page->findAll("css", "html.js body.layout1 div#simplemodal-container.simplemodal-container div.simplemodal-wrap
        div#simplemodal-data.modalContent div.registrationFormContainer form.fos_user_registration_register div.row");


        $namefield = $divrow[0]->find("css", "input");
        $namefield->setValue($this->generateRandomString(rand(3, 12)));

        $surnamefield = $divrow[1]->find("css", "input");
        $surnamefield->setValue($this->generateRandomString(rand(3, 12)));

        $usernamefield = $divrow[2]->find("css", "input");
        $usernamefield->setValue($this->generateRandomString(rand(5, 12)));

        $emailfield = $divrow[3]->find("css", "input");
        $emailfield->setValue($this->generateRandomEmail());

        $passwordfield = $divrow[4]->find("css", "input");
        $password = $this->generateRandomPassword(rand(6, 14));
        $passwordfield->setValue($password);

        $passwordfield2 = $divrow[5]->find("css", "input");
        $passwordfield2->setValue($password);

        $divrow[6]->find("css", "input")->check();

        $divrow[7]->find("css", "input")->click(); // submit form

    }

    public function generateRandomEmail()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';

        $email = "bdd_".$this->generateRandomString()."@yahoo.com";

/*        $dom = $n = '';

        do {
            $n = $dom = '';
            for ($i = 0; $i < rand(5, 15); $i++) {
                $n .= $characters[rand(0, strlen($characters) - 1)];
            }
            for ($i = 0; $i < rand(6, 12); $i++) {
                $dom .= $characters[rand(0, strlen($characters) - 1)];
            }
            $email = $n . "@" . $dom . ".com";
        } while (filter_var($email, FILTER_VALIDATE_EMAIL));*/
        return $email;

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

    public function generateRandomPassword($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRSTUVYWZ*,./\\#-_0123456789';
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomPassword;

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
