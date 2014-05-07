<?php

use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
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

        $providerselect = $page->find('css', 'html.js body.layout1 div#contentHolder div#mainContent div.catalogListing div.catalogContainer div#catalogResult
        div.productListingContainer div.productListing div#topFilterContainer div.filterInner div.filterItemsContainer div#filterProvider select');

        $provideropt = $providerselect->findAll('named', array('option', $handler->selectorToXpath('css', 'select')));
        $totalprovider = count($provideropt) - 1;
        echo "\n\nToplam " . $totalprovider . " provider var\n\n";

        for ($i = 1; $i < $totalprovider; $i++) {
            $data_url = $provideropt[$i]->getAttribute('data-url');
            $providerurl = "/arama/" . $data_url . "-magazasi";
            $session->visit("http://vitringez.com" . $providerurl);
            $subproduct = $page->findById("filterProgressBar")->getText();
            echo $data_url . "   -> " . $subproduct . " var\n";
            $session->visit("http://vitringez.com/arama");

        }
    }
}
