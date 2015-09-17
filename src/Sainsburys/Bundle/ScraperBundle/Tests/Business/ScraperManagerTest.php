<?php

namespace Sainsburys\Bundle\ScraperBundle\Tests\Business;
use Sainsburys\Bundle\ScraperBundle\Model\Item;

class ScraperManagerTest extends \PHPUnit_Framework_TestCase {

    private $_scraperManager;

    protected function setUp() {
      $client = new \Goutte\Client();  
      $this->_scraperManager = new \Sainsburys\Bundle\ScraperBundle\Business\ScraperManager($client);
    }

    protected function tearDown() {
        $this->_scraperManager = null;
    }
    
    public function testMainPageLinksNotEmpty() {
        $crawler = $this->_scraperManager->setUrl();
        $links = $this->_scraperManager->getMainPageLinks($crawler);
        $this->assertTrue(count($links) > 0);
        return $links;
    }

    /**
     * @depends testMainPageLinksNotEmpty
     */
    public function testGroceryUrl(array $links) {
        $crawler = $this->_scraperManager->setUrl();
        foreach ($links as $textLink) {
            list($text) = $textLink;
            $link = $this->_scraperManager->getLink($crawler, $text);
            $this->assertTrue($link->filter(Item::EXP_TITLE)->count() > 0);
        }
    }
    
    public function testTotal() {
        $crawler = $this->_scraperManager->setUrl();
        $arrayLinks = $this->_scraperManager->getMainPageLinks($crawler);

        foreach ($arrayLinks as $textLink) {
            list($text) = $textLink;
            $link = $this->_scraperManager->getLink($crawler, $text);
            $this->_scraperManager->add(
                    new Item(
                        $this->_scraperManager->getItemProperty($link, Item::EXP_TITLE), 
                        $this->_scraperManager->getItemProperty($link, Item::EXP_DESC), 
                        $this->_scraperManager->getItemProperty($link, Item::EXP_PRICE),
                        $this->_scraperManager->getItemDOMSize($link)
                    )
            );
        }
        $result = $this->_scraperManager->getResult();
        $json = $this->_scraperManager->getJson();
        $this->assertNotEquals(count($result), 0);
        $this->assertTrue($result['total']>0);
        $this->assertArrayHasKey('result', $result, 'No key result was defined');
        $this->assertArrayHasKey('total', $result, 'No key total was defined');
        $this->assertJson($json,'Not a json format');  
    }
}
