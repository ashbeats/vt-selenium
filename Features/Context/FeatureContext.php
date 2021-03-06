<?php
/**
 * @author Mustafa Hasturk
 * @site http://github.com/muhasturk
 */

namespace Acme\DemoBundle\Features\Context;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Behat\Symfony2Extension\Context\KernelDictionary;
use \DateTime;
use \DateTimeZone;
use Behat\MinkExtension\Context\MinkContext;
use PHPMailer;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;
use Behat\Mink\Exception\ElementNotFoundException;


class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /** @var  KernelInterface */
    public $kernel;

    /** @var  ContainerInterface */
    public $container;

    public $base_url;
    public $exception_message = '';
    public $warning_message = '';
    public $mailSubject = 'BDD Report';
    private $mail_message = '';

    private $totalProduct;
    private $subProduct;
    private $totalProvider;
    private $totalBrand;

    /** @var  DateTime */
    private $now;
    /** @var  Session */
    private $session;
    /** @var  DocumentElement */
    private $page;
    /** @var  PHPMailer */
    public $mail;


    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /*    public function setContainer()
        {
            $this->container = $this->kernel->getContainer();
        }

        public function getParameter($element)
        {
            return $this->container->getParameter($element);
        }*/


    function __construct()
    {
        $this->base_url = "http://vitringez.com/";
//        $this->setContainer();
        $this->setTime();
    }


    private function initSession()
    {
        $this->session = $this->getSession();
        $this->page = $this->session->getPage();
//        $this->getSession()->getDriver()->resizeWindow(1,1);
    }

    public function sendMail($addr)
    {
        $this->mail = new PHPMailer;

        if (!$this->decideSendMailorNot($addr))
            return;

        $this->mail->CharSet = 'utf-8';
        $this->setMailAuth();
        $this->setMailContact();
        $this->mail->addAddress($addr);
        $this->setMailText($addr);

        $this->decideAttachment($addr);

        echo((!$this->mail->send()) ? "\e[31mMessage could not be sent.\n 'Mailer Error: ' {$this->mail->ErrorInfo} \n\e[0m" :
            "\e[31mMessage has been sent\n\e[0m");

    }

    public function decideAttachment($addr)
    {
        if (strpos($this->mailSubject, 'ScanSite') !== false) {
            if ($addr == $this->kernel->getContainer()->getParameter('main_recipient')) {
                $this->mail->addAttachment(dirname(__FILE__) . "/../resource/report.csv",
                    "report_{$this->now->format('Y-m-d_H.i.s')}.csv");
            }
        }
    }

    public function decideSendMailorNot($addr)
    {
        if (strpos($this->exception_message, 'timeout') !== false)
            return false;
        if ($addr == $this->kernel->getContainer()->getParameter('main_recipient'))
            return true;
        else {
            if ($this->exception_message == 'No exception' && $this->warning_message == 'No warning')
                return false;
        }

    }

    public function setMailAuth()
    {
        $this->mail->isSMTP();
        $this->mail->Host = strval($this->kernel->getContainer()->getParameter("Host"));
        $this->mail->Port = intval($this->kernel->getContainer()->getParameter("Port"));
        $this->mail->SMTPAuth = true;
        $this->mail->Username = strval($this->kernel->getContainer()->getParameter("Username"));
        $this->mail->Password = strval($this->kernel->getContainer()->getParameter("Password"));
        $this->mail->SMTPSecure = strval($this->kernel->getContainer()->getParameter("SMTPSecure"));
    }

    public function setMailContact()
    {
        $this->mail->From = strval($this->kernel->getContainer()->getParameter("From"));
        $this->mail->FromName = strval($this->kernel->getContainer()->getParameter("FromName"));
    }

    /**
     * @When /^I start demo$/
     */
    public function iStartDemo()
    {
    }

    public function setMailText($addr)
    {
        $this->setNoProblemStatus();
        $this->mail->isHTML(true);
        $this->mail->Subject = $this->mailSubject;
        $this->mail->Body = $this->setMailBody($addr);
        $this->mail->AltBody = $this->setMailAltBody();
    }

    private function setNoProblemStatus()
    {
        if (empty($this->exception_message))
            $this->exception_message = 'No exception';
        if (empty($this->warning_message))
            $this->warning_message = 'No warning';
    }

    private function setMailBody($address)
    {
        if ($address == $this->kernel->getContainer()->getParameter("main_recipient"))
            return <<<DOC
            <body>
                    <p> generated on {$this->now->format('Y-m-d H:i:s')} </p>

                <div id='container'>

                    <section id='report'>
                    <h3> BDD Test Report </h3>
                    $this->mail_message
                    </section>

                    <hr><section id='exception'>
                    <h1> Exception </h1>
                    $this->exception_message
                    </section>

                    <hr><section id='warning'>
                    <h2> Warning </h2>
                    $this->warning_message
                    </section>

                </div>
            </body>
DOC;
        else
            return <<<DOC
            <body>
                    <p> generated on {$this->now->format('Y-m-d H:i:s')} </p>
                <div id='container'>

                    <hr><section id='exception'>
                    <h1> Exception </h1>
                    $this->exception_message
                    </section>
                    <hr><section id='warning'>
                    <h2> Warning </h2>
                    $this->warning_message
                    </section>
                </div>
            </body>
DOC;
    }

    private function setMailAltBody()
    {
        return <<<ALT
        <strong>\n You have to get modern mail client! \n</strong>\n
ALT;
    }

    /**
     * @Given /^I send report mail$/
     */
    public function iSendReportMail()
    {
        $this->mailSubject .= "_" . $this->now->getTimestamp();
        $this->sendMail(
            $this->kernel->getContainer()->getParameter("main_recipient")
        );
        $this->sendMail(
            $this->kernel->getContainer()->getParameter("other_recipient")
        );
    }

    /**
     * @Then /^close browser$/
     */
    public function closeBrowser()
    {
        $this->session->stop();
    }

    /**
     * @Given /^I send login-out report$/
     */
    public function iSendLoginOutReport()
    {
        $this->mailSubject = "Login-out Feature Report";
        $this->mail_message = "Login-out test ok!";
        $this->iSendReportMail();
    }


    private function getFilterProgressBar()
    {
        $progressBar = $this->page->findById("filterProgressBar");
        if (!is_object($progressBar))
            throw new Exception('filterProgressBar');
        return $progressBar->getText();
    }

    /**
     * @When /^I check "([^"]*)" sort algorithm$/
     */
    public function iCheckSortAlgorithm($alg)
    {
        $this->mailSubject = 'SortPrice Feature';
        try {
            $this->initSession();
            $url = $this->setAlgorithm($alg);
            $this->checkAlgorithm($alg, $url);
            $this->session->visit($url);
            $cond = $this->comparePrices($alg);
            $this->getSortAlgorithmResult($cond, $alg);
        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    private function getSortAlgorithmResult($condition, $algorithm)
    {
        if ($condition)
            $this->mail_message .= "<span class='ok'> $algorithm algorithm works properly </span><br>\n";
        else
            $this->mail_message .= "<span class='fail'> $algorithm algorithm has a problem </span><br>";
        echo $condition ? "\e[34m'$algorithm' algorithm works properly\n" :
            "'$algorithm' algorithm has a problem!\e[0m\n";
    }

    private function comparePrices($alg)
    {
        $prices = $this->getPrices();
        $sorted = $prices;
        $alg == "descending" ? arsort($sorted) : asort($sorted);
        return boolval($sorted == $prices);
    }


    public function getException($exception)
    {
        $this->exception_message .= "<span class='exception'> {$exception->getMessage()} </span>";
        $this->exception_message .= "<br><p class='line'> Exception occurred on: {$exception->getLine()} line number</p>";
        $this->iSendReportMail();
        if ($this->session)
            $this->session->stop();
        throw new Exception($this->exception_message);
    }


    private function getPrices()
    {
        $prices_em = [];
        for ($i = 3; $i < 27; $i++)
            /** @var array $prices_em */
            $prices_em = $this->page->find('css',
                "#catalogResult > div > div > div:nth-child($i) > div.productDetail > a > span.prices > em.new");
        $prices = [];
        foreach ($prices_em as $d)
            $prices[] = (float)str_replace(",", "", $d->getText());
        return $prices;
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

    private function checkAlgorithm($alg, $url)
    {
        if ($url == ($this->base_url . 'arama')) {
            $this->warning_message .= "<span class='warning'>
                There is no sorting algorithm called '$alg' on the site <br>
                Check test algorithm in .feature file </span>\n";
            throw new Exception('Check test algorithm in .feature file');
        }
    }

    /**
     * @When /^I fill profile details$/
     */
    public function iFillProfileDetails()
    {
        $this->mail_message = "<strong class='test_feature'> Profile Detail Feature </strong> ";
        $this->mailSubject = 'ProfileDetails Report';
        try {
            $this->initSession();

            $this->page->find('css', '#vitringez_user_profile_form_biography')
                ->setValue($this->generateRandomString(16));
            $this->page->find('css', '#vitringez_user_profile_form_city')
                ->setValue($this->generateRandomString(7));
            $this->page->find('xpath', '//*[@id="vitringez_user_profile_form_newsletterSubscribe"]')
                ->uncheck();

            $this->mail_message .= "\n<span class='ok'>profile details test ok</span>";

        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    /**
     * @When /^I scan "([^"]*)" category$/
     */
    public function iScanCategory($category)
    {
        $this->mailSubject = "ScanSite Report";
        try {
            $this->initSession();
            $this->session->visit($this->setUrl($category));
            $this->setGeneralVariable();
            $this->setGeneralInfo();
            $this->scanProviders($this->getProvidersORBrands('providers'));
            $this->iSendReportMail();
            unlink(dirname(__FILE__) . "/../resource/report.csv");
        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    private function scanProviders($providersDiv)
    {
        $this->mail_message .= "<div class='providers'>\n";
        for ($i = 1; $i < $this->totalProvider; $i++) {
            $providerDataName = strval($this->getProviderDataName($providersDiv[$i]));
            $providerSpan = $providersDiv[$i]->find('css', 'span');
            $subProductText = $providerSpan->getText();
            $this->subProduct = intval(str_replace('(', '', $subProductText));
            $this->checkSubProduct($providerDataName);
            $this->setCSVFile($this->setCSVFields($providerDataName));
        }
        $this->mail_message .= "<span style='color: #000066; font-style: oblique'>Report.csv is at attachment. </span></div>";
    }

    public function setCSVFields($provider)
    {
        $fieldContainer = [];
        $field = [$provider, $this->subProduct];
        $fieldContainer[] = $field;
        return $fieldContainer;
    }

    public function setCSVFile($fields)
    {
        $f = fopen(dirname(__FILE__) . "/../resource/report.csv", 'ab+');
        foreach ($fields as $line)
            fputcsv($f, $line);
        fclose($f);
    }


    private function checkSubProduct($providerDataName)
    {
        /*        if ($this->subProduct > 0) {
                    $sp = "<span class='ok'> '$this->subProduct' ürün var. </span>";
                    $tm = "<div class='provider'>'$providerDataName' de/da $sp</div>\n";
                    $this->mail_message .= $tm;
                } else {
                    $sp = "<span class='fail' style='color:red;'> ürün yok. </span>";
                    $tm = "<div class='provider'>'$providerDataName' de/da $sp</div>\n";
                    $this->warning_message .= $tm;
                }*/
        if ($this->subProduct <= 0) {
            $sp = "<span class='fail' style='color:red;'> ürün yok. </span>";
            $this->warning_message .= "<div class='provider'>'$providerDataName' de/da $sp</div>\n";;
        }
    }

    private function getProviderDataName($provider)
    {
        $providerInput = $provider->find('css', 'input');
        return $providerInput->getAttribute('data-name');
    }

    private function getProvidersORBrands($what)
    {
        switch ($what) {
            case 'providers':
                $path = '#filterProvider';
                break;
            case 'brands':
                $path = '#filterBrands';
                break;
            default:
                throw new Exception("getProviderORBrands method only supports providers or brands parameters");
        }
        $obj = $this->page->find('css', $path . ' > div > div > div')->findAll('css', 'div');
        if (count($obj) <= 0)
            throw new Exception("There is no $what site");
        return $obj;
    }

    private function setGeneralVariable()
    {
        $this->totalProduct = intval($this->getFilterProgressBar());
        $this->totalProvider = count($this->getProvidersORBrands('providers'));
        $this->totalBrand = count($this->getProvidersORBrands('brands'));
    }

    private function setGeneralInfo()
    {
        $this->mail_message .= <<<INFO
        <div id='general'>\n
        <span class='totalProduct'> Total product: {$this->totalProduct} </span><br>\n
        <span class='totalProvider'> Provider count: {$this->totalProvider} </span><br>\n
        <span class='totalBrands'> Brand count: {$this->totalBrand} </span><br>\n
        </div>\n
INFO;
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
        $this->mail_message = "<strong class='test_feature'> Fashiın Akert </strong><br>\n ";
        $this->mailSubject = 'FashionnAlert Report';
        try {
            $this->initSession();
            $this->session->visit($this->getXProduct(1)['data-uri']);
            $this->page->find('css', '#content > div.productDetail > div > div.productButtons > a.gradient.fashionAlert')
                ->click();
            $this->checkFashionInputs();
            $this->submitFashionAlert();
            $this->mail_message .= "<span class='ok'> 'FashionAlert' set successfully </span>";
        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    private function submitFashionAlert()
    {
        $this->page->find('xpath', '//*[@id="simplemodal-data"]/form/input[1]')
            ->click();
    }

    private function checkFashionInputs()
    {
        for ($i = 1; $i <= 3; $i++)
            $this->page->find('xpath', '//*[@id="simplemodal-data"]/form/div/label[' . $i . ']/input')
                ->check();
    }


    private function getXProduct($index)
    {
        $index += 3;
        $firstProduct = $this->page->find('xpath', "//*[@id='catalogResult']/div/div/div[$index]");

        if (!is_object($firstProduct))
            throw new Exception('firstProduct');

        if (!$firstProduct->hasAttribute('data-uri'))
            throw new Exception('firstProduct_data-uri');

        return ['firstProduct' => $firstProduct,
            'data-uri' => $firstProduct->getAttribute('data-uri')];
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
        $this->mail_message = "<strong class='test_feature' style='color: #990000; font-style: oblique'> Register Test </strong>";
        $this->mailSubject = 'Register Feature Report';

        try {
            $this->initSession();
            $this->runNewUserLink();
            $this->iWaitSecond("3");
            $this->setRegisterInputs($this->getRegisterInputs());
            $this->mail_message .= "\n<mark class='ok'>Başarılı bir şekilde üye olundu.</mark>";

        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    private function getRegisterInputs()
    {
        $divRows = $this->page->findAll('css', 'div.row');
        $registerInputs = [];
        for ($i = 0; $i < count($divRows); $i++)
            $registerInputs[] = $divRows[$i]->find('css', 'input');
        return $registerInputs;
    }

    private function runNewUserLink()
    {
        $newUserLink = $this->page->findById("newUserLink");
        if (!is_object($newUserLink))
            throw new Exception('newUserLink');
        $newUserLink->click();
    }

    private function setRegisterInputs($inputs)
    {
        $inputs[0]->setValue($this->generateRandomString(rand(3, 12)));
        $inputs[1]->setValue($this->generateRandomString(rand(3, 12)));
        $inputs[2]->setValue($this->generateRandomString(rand(5, 12)));
        $inputs[3]->setValue($this->generateRandomEmail());
        $password = $this->generateRandomString(rand(6, 14));
        $inputs[4]->setValue($password);
        $inputs[5]->setValue($password);
        $inputs[6]->find('css', 'input')->check();
        $inputs[7]->find('css', 'input')->click();
    }


    /**
     * @Given /^I wait "([^"]*)" second$/
     */
    public function iWaitSecond($duration)
    {
        $this->getSession()->wait(intval($duration) * 1000,
            '(0 === jQuery.active && 0 === jQuery(\':animated\').length)');
//        $this->getSession()->wait($duration, '(0 === Ajax.activeRequestCount)'); //for Prototypejs
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


    private function getRandColor($colors) //ok
    {
        $color = $colors[rand(0, (count($colors) - 1))];
        $attr = [];
        if (!$color->hasAttribute('data-name'))
            throw new Exception('color-data-name');
        if (!$color->hasAttribute('data-key'))
            throw new Exception('color-data-key');
        $attr['data-name'] = $color->getAttribute("data-name");
        $attr['data-key'] = $color->getAttribute("data-key");
        $attr['url'] = $attr['data-key'] . "-renkli";
        return $attr;
    }

    private function getRandBrand($brands) //ok
    {
        $brand = $brands[rand(0, (count($brands) - 1))];
        $brandInput = $brand->find("css", "input");
        if (!is_object($brandInput))
            throw new Exception('brandsInput');
        $attr = [];
        if (!$brandInput->hasAttribute('data-name'))
            throw new Exception('brand_data-name');
        if (!$brandInput->hasAttribute('data-url'))
            throw new Exception('brand_data-url');
        $attr['data-name'] = $brandInput->getAttribute("data-name");
        $attr['data-url'] = $brandInput->getAttribute("data-url");
        $attr['url'] = $attr['data-url'] . "-modelleri/";
        return $attr;
    }


    public function visitColorFilter()
    {
        $colors = $this->getFields('li', '#filterColors > div > div > div > ul');
        $acolor = $this->getRandColor($colors);
        $this->session->visit($this->base_url . $acolor['url']);
        echo "\e[34m=============\nRenk Filtresi\n=============\n\e[0m";
        $this->mail_message .= "\n<h2 id='colorfilter'> Renk Filtresi </h2>\n";

        $this->subProduct = intval($this->getFilterProgressBar());
        echo "'{$acolor['data-name']}' seçili iken <$this->subProduct> ürün var.\n";
        $this->mail_message .= "<span> '{$acolor['data-name']}' seçili iken '$this->subProduct' ürün var.</span>\n";
    }

    public function getFields($tag, $path)
    {
        $container = $this->page->find('css', $path);
        if (!is_object($container))
            throw new Exception($path);
        $fields = $container->findAll('css', $tag);
        if (count($fields) <= 0)
            throw new Exception($tag);
        return $fields;
    }

    public function getRanges($rangeInputs)
    {
        $ranges = [];
        if (!$rangeInputs[0]->hasAttribute('value'))
            throw new Exception('ranndeMin');
        $ranges['min'] = $rangeInputs[0]->getAttribute('value');
        if (!$rangeInputs[1]->hasAttribute('value'))
            throw new Exception('rangeMax');
        $ranges['max'] = $rangeInputs[1]->getAttribute('value');
        return $ranges;
    }

    public function getTwoColor()
    {
        $colors = $this->getFields('li', '#filterColors > div > div > div > ul');
        $color1 = $this->getRandColor($colors);
        $color2 = $this->getRandColor($colors);
        return [$color1, $color2];
    }


    public function visitPriceFilter()
    {
        $ranges = $this->getRanges($this->getFields('input', '#filterPrice > div > div.range-slider-input'));
        list($color1, $color2) = $this->getTwoColor();
//        $minPrice = rand($ranges['min'], $ranges['max']);
        $minPrice = rand($ranges['min'], 50);
        $maxPrice = rand($minPrice, $ranges['max']);
        $criteriaUrl = '?criteria%5Bfacet_price%5D=%5B' . $minPrice . '+TO+' . $maxPrice . '%5D';
        $this->session->visit($this->base_url . $color1['data-key'] . "-ve-" . $color2['data-key'] . "-renkli" . $criteriaUrl);
        echo "\e[35m==================\nRenk+Fiyat Filtresi\n==================\n\e[0m";
        $this->mail_message .= "<h3 id='color+price'> Renk+Fiyat Filtresi  </h3>\n";
        $this->subProduct = intval($this->getFilterProgressBar());
        echo "\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken, [" .
            $minPrice . " - " . $maxPrice . "] fiyat aralığında: <" .
            $this->subProduct . "> ürün var.\n\n";
        $this->mail_message .= "<span>\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken, [" .
            $minPrice . " - " . $maxPrice . "] fiyat aralığında: \"" .
            $this->subProduct . "\" ürün var.</span><br>\n\n";
    }

    public function visitBrandFilter()
    {
        $this->session->visit($this->base_url . "arama/");
        $brands = $this->getProvidersORBrands('brands');
        $brandAttr = $this->getRandBrand($brands);
        $this->session->visit($this->base_url . $brandAttr['url']);

        echo "\e[36m==============\nMarka Filtresi\n==============\n\e[0m";
        $this->mail_message .= "<h4 id='brandfilter'> Marka Filtresi </h4> ";

        $this->subProduct = intval($this->getFilterProgressBar());
        echo "\"" . $brandAttr['data-name'] . "\" seçili iken: <$this->subProduct> ürün var.\n";
        $this->mail_message .= "<span> '{$brandAttr['data-name']}' seçili iken: '$this->subProduct' ürün var.</span><br>\n";
    }

    public function getTwoBrand()
    {
        $brands = $this->getProvidersORBrands('brands');
        $brand1 = $this->getRandBrand($brands);
        $brand2 = $this->getRandBrand($brands);
        return [$brand1, $brand2];
    }

    public function visitMoreColorFilter()
    {
        list($color1, $color2) = $this->getTwoColor();
        $urlMoreColorFilter = $this->base_url . $color1['data-key'] . "-ve-" . $color2['data-key'] . "-renkli";

        $this->session->visit($urlMoreColorFilter);

        $this->subProduct = intval(($this->getFilterProgressBar()));

        echo "\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken <" .
            $this->subProduct . "> ürün var.\n\n";
        $this->mail_message .= "<span>\"" . $color1['data-name'] . "\" ve \"" . $color2['data-name'] . "\" seçili iken \"" .
            $this->subProduct . "\" ürün var.</span><br>\n\n";
    }

    public function visitMoreBrandFilter()
    {
        list($brand1, $brand2) = $this->getTwoBrand();
        $urlMoreColorFilter = $this->base_url . $brand1['data-url'] . "-ve-" . $brand2['url'];

        $this->session->visit($urlMoreColorFilter);

        $this->subProduct = intval($this->getFilterProgressBar());

        echo "\"" . $brand1['data-name'] . "\" ve \"" . $brand2['data-name'] . "\" seçili iken: <" .
            $this->subProduct . "> ürün var.\n";
        $this->mail_message .= "<span>\"" . $brand1['data-name'] . "\" ve \"" . $brand2['data-name'] . "\" seçili iken \"" .
            $this->subProduct . "\" ürün var.</span><br>\n";
    }

    public function callVisitMethods()
    {
        $this->visitColorFilter();
        $this->visitMoreColorFilter();
        $this->visitPriceFilter();
        $this->visitBrandFilter();
        $this->visitMoreBrandFilter();
//        $this->visitBrandandProbider();
    }

    public function visitBrandandProbider()
    {
        $this->session->visit($this->base_url . "arama/");
        $providers = $this->page->find("css", "#filterProvider > div > div > div")->findAll("css", "div");

        $brand_attr = $this->getRandBrand($this->getProvidersORBrands("brands")); // altta kullanılacak

        for ($i = 0; $i < count($providers); $i++) {
            $provider_span = $providers[$i]->find('css', 'span');

            if (intval(str_replace("(", "", ($provider_span->getText())))) { // higher zero
                $provider_input = $providers[$i]->find('css', 'input');
                $fl_provider_url = $provider_input->getAttribute("data-url") . "-magazasi";
                $fl_provider_name = $provider_input->getAttribute("data-name");
                break;
            }
        }

        $this->session->visit($this->base_url . $brand_attr['url'] . $fl_provider_url);
        $this->subProduct = intval($this->getFilterProgressBar($this->page));


    }


    /**
     * @Then /^I mix some filter$/
     */
    public function iMixSomeFilter()
    {
        $this->mailSubject = 'MixFuture Report';
        try {
            $this->initSession();
            $this->setGeneralVariable();
            $this->setGeneralInfo();

            echo "Provider sayısı: <$this->totalProvider>\nBrand sayısı: <$this->totalBrand>\nToplam ürün: $this->totalProduct \n\n";
            $this->callVisitMethods();
            $this->iSendReportMail();

        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    /**
     * @Then /^checkFilter$/
     */
    public function checkfilter()
    {
        try {
            $this->initSession();
            $this->session->visit($this->base_url . 'arama');
            $this->mailSubject = "Filter Check Test";
            $this->checkFilterBrands();
            $this->checkFilterProvider();
            $this->checkFilterColors();
            $this->checkFilterPrice();
            $this->iSendReportMail();
        } catch (Exception $e) {
            $this->getException($e);
        }

    }

    public function checkFilterPrice()
    {
        if (is_null($this->page->find('xpath', '//div[@class="range-slider-input"]')))
            throw new Exception('PriceFilter has a problem.');
        $this->mail_message .= "<p>Filter price ok.</p>";

    }

    public function checkFilterColors()
    {
        if (!count($this->getColors()))
            throw new Exception('There is no color on site');
        $this->mail_message .= "<p>Filter colors ok.</p>";
    }

    public function getColors()
    {
        return $this->page->findAll('xpath', '//div[@id="filterColors"]//div[@class="inner"]/ul/li');
    }

    public function checkFilterBrands()
    {
        if (!count($this->getBrands()))
            throw new Exception('There is no brand on site');
        $this->mail_message .= "<p>Filter brands is ok.</p>";

    }

    public function getBrands()
    {
        return $this->page->findAll('xpath', '//div[@id="filterBrands"]//div[@class="inner"]//div/input');
    }

    public function checkFilterProvider()
    {
        if (is_null($this->getProviders()) || !count($this->getProviders()))
            throw new Exception('There is no provider on site');
        $this->mail_message .= "<p>Filter provider is ok.</p>";
    }

    public function getProviders()
    {
        return $this->page->findAll('xpath', '//div[@id="filterProvider"]//div[@class="inner"]//div/input');
    }

    /**
     * @Then /^setDiscountAlert$/
     */
    public function setDiscountAlert()
    {
        try {
            $this->initSession();
            $this->mailSubject = "Deactive Alert Feature";
            $this->session->visit($this->base_url);
            $this->loginSite();
            $this->session->visit($this->base_url . 'kadin-giyim');
            $this->createDiscountAlert();
            $this->iSendReportMail();

        } catch (Exception $e) {
            $this->getException($e);
        }
    }

    public function loginSite()
    {
        $this->page->findById('loginRegisterButton')->click();
        $this->iWaitSecond(3);
        $this->page->fillField('_username', 'testhesabi');
        $this->page->fillField('_password', 'test1234');
        $this->page->pressButton('_submit');
        $this->iWaitSecond(3);
        $this->assertPageContainsText('Hesabım');

    }

    public function createDiscountAlert()
    {
        $this->page->findById('createDiscountAlertFromSearch')->click();
        $this->assertElementContains('#simplemodal-data > form > label', 'Bu alarma bir isim verin:');
        $this->page->find('xpath', '//*[@id="simplemodal-data"]/form/input')->setValue($this->generateRandomString(8));
        $this->page->find('xpath', '//*[@id="simplemodal-data"]/form/input[2]')->click();
        $this->iWaitSecond(2);
        $this->checkAlertSet();
    }

    public function checkAlertSet()
    {
        $this->page->findById('createDiscountAlertFromSearch')->click();
        $this->iWaitSecond(10);
        $text = $this->page->find('xpath', '//*[@id="simplemodal-data"]/form/div/div')->getText();;
        if ($text == 'Bu alarmı daha önce kurmuştunuz!') {
            $this->warning_message .= "<p>Kurulu olan bir alarm kurmaya çalışıldı ve
             <br><span style='color: darkred;'>$text</span><br>uyarısı başarılı bir şekilde alındı.";
            $this->mail_message .= "<p>Test başarılı ancak uyarılar var.</p>";
        } else
            $this->mail_message .= "<p>Test başarılı</p>";
    }


    /**
     * @Then /^checkCarousel$/
     */
    public function checkcarousel()
    {
        try {
            $this->initSession();
            $this->mailSubject = "Carousel Test Feature";
            $this->session->visit($this->base_url);
            $carouselLinks = $this->getCarouselLinks();
            $this->followCarouselLinks($carouselLinks);
            $this->iSendReportMail();

        } catch (Exception $e) {
            $this->getException($e);
        }

    }

    public function getCarouselLinks()
    {
        $carouselDiv = $this->page->findAll('xpath', '//div[@id="topCover"]//div[@class="bxSliderItem"]/div/figure/a');
        /** @var NodeElement $crs */
        $crs = null;
        $carouselLinks = [];
        foreach ($carouselDiv as $crs)
            $carouselLinks[] = $crs->getAttribute('href');
        return $carouselLinks;
    }

    public function followCarouselLinks($links)
    {
        foreach ($links as $l) {
            $this->session->visit($l);
            if (strpos($l, 'com/blog/') === false)
                $this->checkPageHasProduct($l);
            else {
                $statusCode = $this->session->getStatusCode();
                if ($statusCode != 200) {
                    $this->mail_message .= "<p style='color: darkred'>Blog page has problem!</p>";
                    $this->exception_message .= "<p><a href='$l'>$l</a> page has problem.<br>
                    It returns $statusCode status code. </p>";
                    echo $l . "has problem\n";
                } else {
                    $this->mail_message .= "<p><a href='$l'>$l</a> blog page works properly </p>";
                    echo $l . "works properly\n";
                }
            }
        }

    }

    public function checkPageHasProduct($link)
    {
        if (count($this->getProducts())) {
            $this->mail_message .= "<p><a href='$link'>$link</a> works properly.</p>";
            echo $link . "\tworks properly.\n";
        } else {
            $this->mail_message .= "<p style='color: darkred'>Carousel sayfasına gidildiğinde ürün gösterilmiyor.</p>";
            $this->exception_message .= "<p><a href='$link'>$link</a> carousel linkinde ürün gösterilmiyor. </p>";
            echo $link . "\thas problem.\n";
        }
    }

    public function getProducts()
    {
        return $this->page->findAll('xpath', '//div[@class="productItem"]');
    }

    /**
     * @Then /^removeAlert/
     */
    public function removeAlert()
    {
        try {
            $this->mailSubject = "Remove Alert Feature";
            $this->initSession();
            $this->visit($this->base_url);
            $this->loginSite();
            $this->visit($this->base_url . "kullanici/alarm");
            $this->removeAlertItem();
            $this->iSendReportMail();
        } catch (Exception $e) {
            $this->getException($e);
        }

    }

    public function removeAlertItem()
    {
        do {
            /** @var NodeElement $i */
            $items = $this->getAlertItem();
            if (!count($items)) {
                $this->mail_message .= "<p>Hesapta daha fazla kurulu alarm yok.</p>";
                echo "Silinecek alarm kalmadı\n";
                break;
            } else {
                $i = $items[array_rand($items)];
                $i->find('xpath', '//a[@class="deleteAlarmButton"]')->click();
                $this->iWaitSecond(1);
                $removedAlert = $i->find('xpath', '//div/span')->getText();
                $this->page->find('xpath', '//*[@id="simplemodal-data"]/form/input[1]')->click();
                $this->iWaitSecond(1);
                $this->mail_message .= "<p>$removedAlert\t alert silindi.</p>";
                echo $removedAlert . "\talert silindi.\n";
            }
        } while (true);

    }

    public function getAlertItem()
    {
        return $this->page->findAll('xpath', '//div[@class="alarmItem"]');
    }


}





