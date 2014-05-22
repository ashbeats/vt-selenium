<?php

use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    /**
     * @When /^I scan "([^"]*)" category$/
     */
    public function iScanCategory($category)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $category_url = $this->setUrl($category);
        $session->visit($category_url);

        $productanno = $page->findById("filterProgressBar")->getText();
        if($productanno==null)
            echo "check xpath of filterProgressBar";
        else
            $numofproduct = intval($productanno);

        if ($category != "all")
            echo $category . " kategorisinde toplam: " . $numofproduct . " ürün var.\n";
        else
            echo "Sitede toplam: " . $numofproduct . " ürün var.\n";

        $innerDiv = $page->find('xpath', '//*[@id="filterProvider"]/div/div/div');
        if($innerDiv==null)
            echo "check xpath of innerDiv";
        else
            $providersdiv = $innerDiv->findAll('css', 'div');

        $totalprovider = count($providersdiv);
        echo "Provider sayısı: " . $totalprovider . "\n\n";

        $providers = array();

        if ($category != "all")
            echo(ucwords(strtolower($category . " Kategorisi\n__________________\n")));
        else
            echo "\e[34mArama Sayfası\n_____________\n\e[0m";

        for ($i = 1; $i < $totalprovider; $i++) {
            $pr = $providersdiv[$i]->find('css', 'input');
            $data_url = $pr->getAttribute("data-url");
            $provider_name = $pr->getAttribute("data-name");
            $url = $category_url . "/" . $data_url . "-magazasi";
            $session->visit($url);

            $subproduct = intval($page->findById("filterProgressBar")->getText());
            $providers[$provider_name] = $subproduct; // log

            if ($subproduct <= 0) {
                echo $provider_name . "\033[01;31m de/da ürün yok! \033[0m\n";
//                throw new Exception("\nUrunler siteye eklenmemiş.\n");
            } else {
                echo $provider_name . "   -> " . $subproduct . " ürün var\n";
            }
            $session->visit($category_url);

        }

    }

    public function setUrl($category)
    {
        switch ($category) {
            case "kadın":
                $data_url = "kadin";
                $url = "http://vitringez.com/" . $data_url;
                break;
            case "erkek":
                $data_url = "erkek";
                $url = "http://vitringez.com/" . $data_url;
                break;
            case "çocuk";
                $data_url = "cocuk";
                $url = "http://vitringez.com/" . $data_url;
                break;
            case "ev";
                $data_url = "ev";
                $url = "http://vitringez.com/" . $data_url;
                break;
            case "all";
                $data_url = "arama";
                $url = "http://vitringez.com/" . $data_url;
                break;

        }
        return $url;
    }


    /**
     * @When /^I set the discount alert$/
     */
    public function iSetTheDiscountAlert()
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $session->visit("http://www.vitringez.com/urun/bisous-rose-metalik-canta-207258");
        $page->find("xpath", '//*[@id="content"]/div[1]/div/div[2]/a[2]')->click();

        for ($i = 1; $i <= 4; $i++) {
            $page->find("xpath", '//*[@id="simplemodal-data"]/form/div/label[' . $i . ']/input')->check();
        }
        $page->find("xpath", '//*[@id="simplemodal-data"]/form/input[1]')->click();

    }

    /**
     * @When /^I set the fashion alert$/
     */
    public function iSetTheFashionAlert() // mouseOver broken
    {
        $session = $this->getSession();
        $page = $session->getPage();

        /*$productItem = $page->find("css","html.js body.layout1 div#contentHolder div#mainContent div.catalogListing div.catalogContainer
        div#catalogResult div.productListingContainer div.productListing div.productItem");

        $productItem->mouseOver();*/

        $session->visit("http://www.vitringez.com/urun/zoopa-zoopa-mor-canta-814916");
        $alertbutton = $page->find("xpath", '//*[@id="content"]/div[1]/div/div[2]/a[1]');
        $alertbutton->click();

        for ($i = 1; $i <= 3; $i++) {
            $page->find("xpath", '//*[@id="simplemodal-data"]/form/div/label[' . $i . ']/input')->check();
        }
        $page->find("xpath", '//*[@id="simplemodal-data"]/form/input[1]')->click(); // send fashion alert request
    }

    /**
     * @When /^I fill in registration form$/
     */
    public function iFillInRegistrationForm()
    {
        $session = $this->getSession();
//        $session->getDriver()->resizeWindow(1600,900,'current');
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

        $innerdiv = $page->find("xpath", '//*[@id="filterProvider"]/div/div/div');
        $providerdiv = $innerdiv->findAll('css', 'div');

        $totalprovider = count($providerdiv);
        echo "Provider sayısı: " . $totalprovider . "\n\n";

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

    public function generateRandomEmail()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';

        $email = "bdd_" . $this->generateRandomString() . "@yahoo.com";

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
}

