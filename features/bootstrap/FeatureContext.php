<?php

use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    /**
     * @When /^I check "([^"]*)" sort algorithm$/
     */
    public function iCheckSortAlgorithm($alg)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        if (($algorithm_url = $this->setAlgorithm($alg)) == ($search = "http://vitringez.com/arama")) {
            $err = "there is no sorting algorithm called \"" . $alg . "\" on the site\n";
            throw new Exception($err);
        }

        $session->visit($algorithm_url);

        for ($i = 3; $i < 27; $i++) {
            $prices_em[] = $page->find('css',
                '#catalogResult > div > div > div:nth-child(' . $i . ') > div.productDetail > a > span.prices > em.new');
        }

        foreach ($prices_em as $n) {

            if ($n == null) {
                $err = "span.prices > em.new could not fetched...\ncheck span.prices > em.new css path!\n";
                throw new Exception($err);
            }
        }

        foreach ($prices_em as $d) {
            $prices[] = (float)str_replace(",", "", $d->getText());
        }

        $sorted = $prices; // copy new array

        $alg == "descending" ? arsort($sorted) : asort($sorted);

        echo ($sorted == $prices) ? "\e[34m" . $alg . " algorithm works properly\n" :
            "check \"" . $alg . "\" algorithm. It has a problem!\e[0m\n";

    }

    private function setAlgorithm($alg)
    {
        $base_url = "http://vitringez.com/";
        switch ($alg) {
            case "ascending":
                $sort_url = "arama?sort=price|asc";
                break;
            case "descending":
                $sort_url = "arama?sort=price|desc";
                break;
            default:
                $sort_url = "arama";
                break;
        }
        return $base_url . $sort_url;

    }


    /**
     * @When /^I fill profile details$/
     */
    public function iFillProfileDetails()
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $page->find('css', '#vitringez_user_profile_form_biography')
            ->setValue($this->generateRandomString(16));
        $page->find('css', '#vitringez_user_profile_form_city')
            ->setValue($this->generateRandomString(7));

        $page->find('xpath', '//*[@id="vitringez_user_profile_form_newsletterSubscribe"]')
            ->uncheck();
    }

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
        if ($productanno == null) {
            $err = "filterProgressBar could not fetched!\n";
            throw new Exception($err);
        } else
            $numofproduct = intval($productanno);

        echo ($category != "all") ? $category . " kategorisinde toplam: " . $numofproduct . " ürün var.\n" :
            "Sitede toplam: " . $numofproduct . " ürün var.\n";

        $innerDiv = $page->find('xpath', '//*[@id="filterProvider"]/div/div/div');
        if ($innerDiv == null) {
            $err = "innerDiv could not fetched!\n";
            throw new Exception($err);
        } else
            $providersdiv = $innerDiv->findAll('css', 'div');

        $totalprovider = count($providersdiv);
        echo "Provider sayısı: " . $totalprovider . "\n";

        echo ($category != "all") ? "\e[34m" . ucwords(strtolower($category . " Kategorisi\n________________\n")) . "\e[0m" :
            "\e[34mArama Sayfası\n_____________\n\e[0m";

        for ($i = 1; $i < $totalprovider; $i++) {
            $pr = $providersdiv[$i]->find('css', 'input');
            $data_url = $pr->getAttribute("data-url");
            $provider_name = $pr->getAttribute("data-name");
            $url = $category_url . "/" . $data_url . "-magazasi";
            $session->visit($url);

            $subproduct = intval($page->findById("filterProgressBar")->getText());
            $providers[$provider_name] = $subproduct; // log

            echo ($subproduct <= 0) ? $provider_name . "\033[01;31m de/da ürün yok! \033[0m\n" :
                $provider_name . "   -> " . $subproduct . " ürün var\n";
            $session->visit($category_url);

        }

    }

    private function setUrl($category)
    {
        $base_url = "http://vitringez.com/";
        switch ($category) {
            case "kadın":
                $data_url = "kadin";
                break;
            case "erkek":
                $data_url = "erkek";
                break;
            case "çocuk";
                $data_url = "cocuk";
                break;
            case "ev";
                $data_url = "ev";
                break;
            case "all";
                $data_url = "arama";
                break;

        }
        return $base_url . $data_url;
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
        if ($alertbutton == null) {
            $err = "alertButton could not fetched!\n";
            throw new Exception($err);
        } else
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

        $session->wait(3);

        $registerRow = $page->findAll("css", "html.js body.layout1 div#simplemodal-container.simplemodal-container div.simplemodal-wrap
        div#simplemodal-data.modalContent div.registrationFormContainer form.fos_user_registration_register div.row");

        $divRow = array();
        for ($i = 0; $i < count($registerRow); $i++) {
            $divRow[] = $registerRow[$i]->find("css", "input");
        }
        $divRow[0]->setValue($this->generateRandomString(rand(3, 12)));
        $divRow[1]->setValue($this->generateRandomString(rand(3, 12)));
        $divRow[2]->setValue($this->generateRandomString(rand(5, 12)));
        $divRow[3]->setValue($this->generateRandomEmail());
        $password = $this->generateRandomPassword(rand(6, 14));
        $divRow[4]->setValue($password);
        $divRow[5]->setValue($password);
        $divRow[6]->find("css", "input")->check();
        $divRow[7]->find("css", "input")->click();

    }

    /**
     * @Given /^I wait "([^"]*)" second$/
     */
    public function iWaitSecond($duration)
    {
//        $this->getSession()->wait(intval($duration) * 1000, '(0 === jQuery.active)');
        $this->getSession()->wait(intval($duration) * 1000,
            '(0 === jQuery.active && 0 === jQuery(\':animated\').length)');

//        $this->getSession()->wait($duration, '(0 === Ajax.activeRequestCount)');

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

