<?php
/**
 * @author Mustafa Hasturk
 * @site http://github.com/muhasturk
 *
 */
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    public $base_url = "http://vitringez.com/";

    /**
     * related time
     * Generated with setTime methof after construct has been run
     */
    protected $now;

    /**
     * Will be used as exception
     * @type string
     */
    public $exception_message = '';
    /**
     * Will be used as warninng
     * @type string
     */
    public $warning_message = '';

    public $mailSubject = '';

    function __construct()
    {
        $this->setTime();
    }


    /**
     * @Then /^I mix some filter$/
     */
    public function iMixSomeFilter()
    {

        $mail_message = '';
        $this->mailSubject = 'MixFuture Report_' . $this->now->getTimestamp();

        try {
            $session = $this->getSession();
            $page = $session->getPage();

            echo "\e[31m===========\nGenel Site\n===========\n\e[0m";
            $mail_message .= "<h1 style=\"color:#CB0C0C;\">Genel Site</h2><br>\n";

            $providers_container = $page->find("css", "#filterProvider > div > div > div");
            if (!is_object($providers_container))
                $this->setException('providers_container');


            $providers = $providers_container->findAll("css", "div");
            if (count($providers) == 0)
                $this->setException('providers');

            $total_providers = count($providers);
            echo "Provider sayısı: <" . $total_providers . ">\n";
            $mail_message .= "<div id='generalinfo'>\n<span>Provider sayısı: \"" . $total_providers . "\"</span><br>\n";

            $brands_container = $page->find('css', '#filterBrands > div > div > div');
            if (!is_object($brands_container))
                $this->setException('brand_container');

            $brands = $brands_container->findAll('css', 'div');
            $total_brand = count($brands);
            if ($total_brand == 0) {
                $this->exception_message .= "<span class='exception'>__! Check brands path or there is no brand on site !__\n</span>";
                throw new Exception($this->exception_message);
            }
            echo "Brand sayısı: <" . $total_brand . ">\n";

            $total_product = intval($this->getFilterProgressBar($page));
            if ($total_product == 0) {
                $this->exception_message .= "<span class='exception'>__! There is no product on site !__\n</span>";
                throw new Exception($this->exception_message);
            }
            echo "Toplam ürün: <$total_product> \n\n";

            $mail_message .= "<span> Brand sayısı:  '$total_brand' </span><br>\n
                <span> Toplam ürün:  '$total_product' </span><br>\n</div>\n";

            $colors_container = $page->find('css', '#filterColors > div > div > div > ul');
            if (!is_object($colors_container))
                $this->setException('color_container');
            $colors = $colors_container->findAll('css', 'li');
            if (count($colors) == 0) {
                $this->exception_message .= "<span class='exception'>__! There is no color on site or check path !__\n</span>";
                throw new Exception($this->exception_message);
            }
            // one color
            $acolor = $this->getRandColor($colors);
            $session->visit($this->base_url . $acolor['url']);

            echo "\e[34m=============\nRenk Filtresi\n=============\n\e[0m";
            $mail_message .= "\n<h2 id='colorfilter'> Renk Filtresi </h2>\n";

            $product = intval($this->getFilterProgressBar($page));
            echo "'{$acolor['data-name']}' seçili iken <$product> ürün var.\n";
            $mail_message .= "<span> '{$acolor['data-name']}' seçili iken '$product' ürün var.</span>\n";

            // more than one color
            $color1 = $this->getRandColor($colors);
            $color2 = $this->getRandColor($colors);
            $session->visit($this->base_url . $color1['data-key'] . "-ve-" . $color2['data-key'] . "-renkli");

            $product = intval(($this->getFilterProgressBar($page)));

            echo "\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken <" .
                $product . "> ürün var.\n\n";
            $mail_message .= "<span>\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken \"" .
                $product . "\" ürün var.</span><br>\n\n";

            // price filter
            $color1 = $this->getRandColor($colors);
            $color2 = $this->getRandColor($colors);
            $session->visit($this->base_url . $color1['data-key'] . "-ve-" . $color2['data-key'] . "-renkli");

            $range_div = $page->find('css', '#filterPrice > div > div.range-slider-input');
            if (!is_object($range_div))
                $this->setException('rangeDiv');
            $range_inputs = $range_div->findAll('css', 'input');
            if (count($range_inputs) == 0)
                $this->setException('randeInputs');

            if (!$range_inputs[0]->hasAttribute('value'))
                $this->setException('ranndeMin');
            $range_min = $range_inputs[0]->getAttribute('value');
            if (!$range_inputs[1]->hasAttribute('value'))
                $this->setException('rangeMax');
            $range_max = $range_inputs[1]->getAttribute('value');

            $min_price = rand($range_min, $range_max);
            $max_price = rand($min_price, $range_max);
            $criteria_url = '?criteria%5Bfacet_price%5D=%5B' . $min_price . '+TO+' . $max_price . '%5D';

            $session->visit($this->base_url . $color1['data-key'] . "-ve-" . $color2['data-key'] . "-renkli" . $criteria_url);

            echo "\e[35m==================\nRenk+Fiyat Filtresi\n==================\n\e[0m";
            $mail_message .= "<h3 id='color+price'> Renk+Fiyat Filtresi  </h3>\n";

            $product = intval($this->getFilterProgressBar($page));
            echo "\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken, [" .
                $min_price . " - " . $max_price . "] fiyat aralığında: <" .
                $product . "> ürün var.\n\n";
            $mail_message .= "<span>\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken, [" .
                $min_price . " - " . $max_price . "] fiyat aralığında: \"" .
                $product . "\" ürün var.</span><br>\n\n";

            // brand
            $session->visit($this->base_url . "arama/");
            $brand_attr = $this->getRandBrand($brands);
            $session->visit($this->base_url . $brand_attr['url']);

            echo "\e[36m==============\nMarka Filtresi\n==============\n\e[0m";
            $mail_message .= "<h4 id='brandfilter'> Marka Filtresi </h4> ";

            $product = intval($this->getFilterProgressBar($page));
            echo "\"" . $brand_attr['data-name'] . "\" seçili iken: <$product> ürün var.\n";
            $mail_message .= "<span> '{$brand_attr['data-name']}' seçili iken: '$product' ürün var.</span><br>\n";

            // more than one brand
            $brand1 = $this->getRandBrand($brands);
            $brand2 = $this->getRandBrand($brands);

            $session->visit($this->base_url . $brand1['data-url'] . "-ve-" . $brand2['url']);
            $product = intval($this->getFilterProgressBar($page));
            echo "\"" . $brand1['data-name'] . "\" ve \"" . $brand2['data-name'] . "\" seçili iken: <" .
                $product . "> ürün var.\n";
            $mail_message .= "<span>\"" . $brand1['data-name'] . "\" ve \"" . $brand2['data-name'] . "\" seçili iken \"" .
                $product . "\" ürün var.</span><br>\n";

            // brand + provider
            $session->visit($this->base_url . "arama/");
            $brand_attr = $this->getRandBrand($brands);
            $prov_cont = $page->find("css", "#filterProvider > div > div > div");
            if (!is_object($prov_cont))
                $this->setException('providerContainer');
            $providers = $prov_cont->findAll("css", "div");
            if (count($providers) == 0)
                $this->setException('providers');

//            $fl_provider_name = $fl_provider_url = '';
            for ($i = 0; $i < count($providers); $i++) {
                $provider_span = $providers[$i]->find('css', 'span');
                if (!is_object($provider_span))
                    $this->setException('providerSpan');

                if (intval(str_replace("(", "", ($provider_span->getText())))) { // higher zero
                    $provider_input = $providers[$i]->find('css', 'input');
                    if (!is_object($provider_input))
                        $this->setException('providerInput');

                    if (!($provider_input->hasAttribute("data-url")))
                        $this->setException('providerInput_data-url');
                    $fl_provider_url = $provider_input->getAttribute("data-url") . "-magazasi";

                    if (!($provider_input->hasAttribute("data-name")))
                        $this->setException('providerInput_data-name');
                    $fl_provider_name = $provider_input->getAttribute("data-name");
                    break;
                }
            }

            /*            for ($i = 0; $i < count($providers); $i++) {
                            if (intval(str_replace("(", "", ($providers[$i]->find('css', 'span')->getText())))) {
                                $fl_provider_url = $providers[$i]->find('css', 'input')->getAttribute("data-url") . "-magazasi";
                                $fl_provider_name = $providers[$i]->find('css', 'input')->getAttribute("data-name");
                            }
                        }*/

            $session->visit($this->base_url . $brand_attr['url'] . $fl_provider_url);

            $product = intval($this->getFilterProgressBar($page));
            echo "\"" . $brand_attr['data-name'] . "\" ile \"" . $fl_provider_name . "\" mağazası seçili iken <" .
                $product . "> ürüm var.\n";
            $mail_message .= "<span> \"" . $brand_attr['data-name'] . "\" ile  \"" . $fl_provider_name . "\" mağazası seçili iken \"" .
                $product . "\" ürün var.</span><br>\n";

            $this->sendMail($mail_message);

        } catch (Exception $e) {
            if ($e->getMessage() != $this->exception_message)
                $this->exception_message .= $e->getMessage();
            $this->sendMail($mail_message);
            throw new Exception($e->getMessage());
        }
    }

    private function sendMail($msg)
    {
        /**
         * You have to setup PHPMailer to use this method
         * @link https://github.com/PHPMailer/PHPMailer
         */
        if ($this->exception_message == '')
            $this->exception_message = 'There is no exception';
        if ($this->warning_message == '')
            $this->warning_message = 'No warning';

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 1;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->FromName = 'Mustafa Hasturk';
        $mail->addAddress('tzzzf@droplar.com', 'muhasturk');
        $mail->WordWrap = 50;
        $mail->isHTML(true);
        $mail->Subject = $this->mailSubject;
        $mail->Body = <<<DOC
        <!DOCTYPE html>
        <html>
            <head>
                <title> Report </title>
                <meta charset='utf-8'>
            </head>
            <body>
                <header>
                    <p> generated on {$this->now->format('Y-m-d H:i:s')} </p>
                </header>

                <div id='container'>

                    <section id='exception'>
                    <h1> Exception </h1>
                    $this->exception_message;
                    </section>

                    <hr>
                    <section id='warning'>
                    <h2> Warning </h2>
                    $this->warning_message;
                    </section>

                    <hr><section id='report'>
                    <h3> BDD Test Report </h3>
                    $msg
                    </section>
                </div>
                <footer>
                    <p> created by muhasturk </p>
                </footer>
            </body>
        </html>

DOC;
        $mail->AltBody = "<strong>You have to get modern mail client!</strong>\n";

        if (!$mail->send())
            echo "Message could not be sent.\n" . 'Mailer Error: ' . $mail->ErrorInfo . "\n";
        else
            echo 'Message has been sent';

    }

    private function getFilterProgressBar($page)
    {
        $progressBar = $page->findById("filterProgressBar");
        if (!is_object($progressBar))
            $this->setException('filterProgressBar');
        return $progressBar->getText();
    }

    private function getRandBrand($brands)
    {
        $brand = $brands[rand(0, (count($brands) - 1))];
        $brand_input = $brand->find("css", "input");
        if (!is_object($brand_input)) {
            $this->exception_message .= "<span class='exception'>__! Check brands path !__</span>\n";
            throw new Exception($this->exception_message);
        }
        $attr = array();
        $br_data_name = $brand_input->getAttribute("data-name");
        if ($br_data_name == null) {
            $this->warning_message .= "<span class='warning'>\e[35m__? Check brand data-name attribute ?__\n\e[0m\n</span>\n";
            $attr['data-name'] = "\e[35m__! Missing !__\n\e[0m";
        } else
            $attr['data-name'] = $br_data_name;

        $br_data_url = $brand_input->getAttribute("data-url");
        if ($br_data_url == null) {
            $this->exception_message .= "<span class='exceptions'> __! Check brand data-url attribute !__ </span>\n";
            throw new Exception($this->exception_message);
        }
        $attr['data-url'] = $br_data_url;
        $attr['url'] = $attr['data-url'] . "-modelleri/";
        return $attr;
    }


    private function getRandColor($colors)
    {
        $color = $colors[rand(0, (count($colors) - 1))];
        $attr = array();
        $cl_data_name = $color->getAttribute("data-name");
        if ($cl_data_name == null) {
            $this->warning_message .= "<span class='warning'>\e[35m__? Check data-name attribute of colors ?__\e[0m</span>\n";
            $attr['data-name'] = "\e[35m__! Missing !__ \e[0m";
        } else
            $attr['data-name'] = $cl_data_name;
        $cl_data_key = $color->getAttribute("data-key");
        if ($cl_data_key == null) {
            $this->exception_message .= "<span class='exception'>\e[35m__? Check data-key attribute of colors ?__\e[0m</span>\n";
            throw new Exception($this->exception_message);
        }
        $attr['data-key'] = $cl_data_key;
        $attr['url'] = $attr['data-key'] . "-renkli";
        return $attr;
    }


    /**
     * @When /^I check "([^"]*)" sort algorithm$/
     */
    public function iCheckSortAlgorithm($alg)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        if (($algorithm_url = $this->setAlgorithm($alg)) == ($search = $this->base_url . 'arama')) {
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

        $prices = [];
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
        return $this->base_url . $sort_url;

    }


    /**
     * @When /^I fill profile details$/
     */
    public function iFillProfileDetails()
    {
        $mail_message = "<strong class='test_feature'> Profile Detail Feature </strong> ";
        $this->mailSubject = 'ProfileDetails Report_'.$this->now->getTimestamp();
        try {
            $session = $this->getSession();
            $page = $session->getPage();

            $page->find('css', '#vitringez_user_profile_form_biography')
                ->setValue($this->generateRandomString(16));
            $page->find('css', '#vitringez_user_profile_form_city')
                ->setValue($this->generateRandomString(7));

            $page->find('xpath', '//*[@id="vitringez_user_profile_form_newsletterSubscribe"]')
                ->uncheck();

            $mail_message.="\n<p>profile details test ok</p>";
            $this->sendMail($mail_message);
        } catch (Exception $e) {
            if (($e->getMessage()) != $this->exception_message)
                $this->exception_message .= "\n<span class='generated_exception'> $e->getMessage() </span>";
            $this->sendMail($mail_message);
            throw new Exception($this->exception_message);
        }
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
        switch ($category) {
            case "kadın":
                $data_url = "kadin";
                break;
            case "erkek":
                $data_url = "erkek";
                break;
            case "çocuk":
                $data_url = "cocuk";
                break;
            case "ev":
                $data_url = "ev";
                break;
            default:
                $data_url = 'arama';
                break;
        }
        return $this->base_url . $data_url;
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
    public function iSetTheFashionAlert() //ok
    {
        $mail_message = "<strong class='test_feature'> Fashiın Akert </strong> ";
        $this->mailSubject = 'FashionnAlert Report_' . $this->now->getTimestamp();
        try {
            $session = $this->getSession();
            $page = $session->getPage();

            $first_product = $page->find('xpath', '//*[@id="catalogResult"]/div/div/div[3]');
            if (!is_object($first_product))
                $this->setException('firstProduct');

            if (!$first_product->hasAttribute('data-uri'))
                $this->setException('firstProduct_data-uri');
            $session->visit($first_product->getAttribute('data-uri'));

            $alertbutton = $page->find("xpath", '//*[@id="content"]/div[1]/div/div[2]/a[1]');
            if (!is_object($alertbutton))
                $this->setException('alertButtonn');
            $alertbutton->click();

            for ($i = 1; $i <= 3; $i++) {
                $alertLabel = $page->find("xpath", '//*[@id="simplemodal-data"]/form/div/label[' . $i . ']/input');
                if (!is_object($alertLabel))
                    $this->setException('alertLabel');
                $alertLabel->check();
            }
            $alertInput = $page->find("xpath", '//*[@id="simplemodal-data"]/form/input[1]');
            if (!is_object($alertInput))
                $this->setException('alertInput');
            $alertInput->click(); // send fashion alert request

            $mail_message .= "<p> 'FashionAlert' set successfully </p>";
            $this->sendMail($mail_message);

        } catch (Exception $e) {
            if (($e->getMessage()) != $this->exception_message)
                $this->exception_message .= "\n<span class='generated_exception'> $e->getMessage() </span>";
            $this->sendMail($mail_message);
            throw new Exception($this->exception_message);
        }
    }

    private function setTime() //ok
    {
        $this->now = new DateTime();
        $this->now->setTimezone(new DateTimeZone('Europe/Istanbul'));
    }

    /**
     * @When /^I fill in registration form$/
     */
    public function iFillInRegistrationForm() //ok
    {
        $mail_message = "<strong class='test_feature' style='color: #990000; font-style: oblique'> Register Test </strong>";
        $this->mailSubject = 'Register Feature Report_' . $this->now->getTimestamp();

        try {
            $session = $this->getSession();
//        $session->getDriver()->resizeWindow(1600,900,'current');
            $page = $session->getPage();

            $newUserLink = $page->findById("newUserLink");
            if (!is_object($newUserLink))
                $this->setException('newUserLink');

            $newUserLink->click();
            $session->wait(5000);

            $registerRow = $page->findAll("css", "div.row");
            if (count($registerRow) == 0)
                $this->setException('registerRow');

            $divRows = [];
            for ($i = 0; $i < count($registerRow); $i++) {
                $divRow = $registerRow[$i]->find("css", "input");
                if (!is_object($divRow))
                    $this->setException('divRow');
                $divRows[] = $divRow;
            }
            $divRows[0]->setValue($this->generateRandomString(rand(3, 12)));
            $divRows[1]->setValue($this->generateRandomString(rand(3, 12)));
            $divRows[2]->setValue($this->generateRandomString(rand(5, 12)));
            $divRows[3]->setValue($this->generateRandomEmail());
            $password = $this->generateRandomPassword(rand(6, 14));
            $divRows[4]->setValue($password);
            $divRows[5]->setValue($password);
            $userAgreement = $divRows[6]->find("css", "input");
            if (!is_object($userAgreement))
                $this->setException('userAgreement');
            $userAgreement->check();

            $submitForm = $divRows[7]->find("css", "input");
            if (!is_object($submitForm))
                $this->setException('submitForm');
            $submitForm->click();

            $mail_message .= "\n<mark class='ok'>Başarılı bir şekilde üye olundu.</mark>";
            $this->sendMail($mail_message);

        } catch (Exception $e) {
            if ($e->getMessage() != $this->exception_message)
                $this->exception_message .= "\n<span class='generated_exception'> {$e->getMessage()} </span>";
            $this->sendMail($mail_message);
            throw new Exception($this->exception_message);
        }

    }

    private function setException($obj)
    {
        $this->exception_message .= "<span class='exception'> __! Check '$obj' path | id | attribute !__ </span>";
        throw new Exception($this->exception_message);
    }

    /**
     * @Given /^I wait "([^"]*)" second$/
     */
    public function iWaitSecond($duration)
    {
        $this->getSession()->wait(intval($duration) * 1000,
            '(0 === jQuery.active && 0 === jQuery(\':animated\').length)');
//        $this->getSession()->wait($duration, '(0 === Ajax.activeRequestCount)');
    }

    public function generateRandomEmail()
    {
        return 'bdd_' . $this->generateRandomString() . '@yahoo.com';
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

