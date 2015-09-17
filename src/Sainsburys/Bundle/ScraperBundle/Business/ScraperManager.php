<?php

namespace Sainsburys\Bundle\ScraperBundle\Business;

use Sainsburys\Bundle\ScraperBundle\Model\Item;
use Goutte\Client;

/**
 * Service which implements all the relevant methods to request and follow
 * the pages links and fetch their textual content 
 */
class ScraperManager {

    private $_client;
    private $_groceryUrl;
    private $_total = 0.00;
    private $_jsonArray = array();
    
    public function __construct(Client $client) {
        $this->_client = $client;
    }

    /**
     * Add an grocery item to the internal array _jsonArray 
     * @param Item $item
     */
    public function add(Item $item) {
        $this->_jsonArray['result'][] = $item;
        $this->_total += $item['unit_price'];
    }
    
    /**
     * Adds the key/value of the total price item to the internal array
     * Returns the internal Item array
     * @return array
     */
    public function getResult() {
        $this->_jsonArray['total'] = $this->_total;
        return $this->_jsonArray;
    }
    
    /**
     * Returns a Json string representation of the internal items array
     * @return string
     */
    public function getJson() {
        return json_encode($this->getResult(), JSON_PRETTY_PRINT);
    }
    
    /**
     * Returns the total price_unit of the items
     * @return double
     */
    public function getTotal() {
        return $this->_total;
    }
    
    /**
     * Gets the text content of a link
     * 
     * @param Crawler $link
     * @param string $exp
     * @return string
     */
    public function getItemProperty(\Symfony\Component\DomCrawler\Crawler $link, $exp) {
        if ($link->filter($exp)->count() != 0) {
            $propertyText = $link->filter($exp)->first()->text();
            if (strstr($exp, 'price')) {
                preg_match_all('!\d+!', $propertyText, $match);
                return implode('.', $match[0]);
            }
            return $propertyText;
        }
        return "N/A";
    }
    
    /**
     * Returns a string representation of the document content size in kilobytes
     * @param Crawler $link
     * @return type
     */
    public function getItemDOMSize(\Symfony\Component\DomCrawler\Crawler $link) {
        $size = mb_strlen($link->html(), 'utf8');
        return round(($size / 1024), 2) . 'kb';
    }

    /**
     * Sets and returns the initial grocery page URL
     * @return string 
     */
    public function setUrl() {
        $this->_groceryUrl = "http://www.sainsburys.co.uk/webapp/wcs/stores/servlet/CategoryDisplay?"
                . "listView=true&orderBy=FAVOURITES_FIRST&parent_category_rn=12518&top_category=12518"
                . "&langId=44&beginIndex=0&pageSize=20&catalogId=10137&searchTerm=&categoryId=185749"
                . "&listId=&storeId=10151&promotionId=#langId=44&storeId=10151&catalogId=10137"
                . "&categoryId=185749&parent_category_rn=12518&top_category=12518&pageSize=20"
                . "&orderBy=FAVOURITES_FIRST&searchTerm=&beginIndex=0&hideFilters=true";
        return $this->_client->request('GET', $this->_groceryUrl);
    }

    /**
     * Fetches all the grocery items link on the main page
     * 
     * @param Crawler $crawler
     * @return array
     */
    public function getMainPageLinks(\Symfony\Component\DomCrawler\Crawler $crawler) {
   
        $arrayLinks = array();
        $result = $crawler->filter('h3 > a')->each(function ($node) use ($arrayLinks) {
            $textLink = $node->text();
            $arrayLinks[] = trim((string) $textLink);
            return $arrayLinks;
        });
        return $result;
    }

    /**
     * 
     * @param Crawler $crawler
     * @param string $text
     * @return Crawler $follow
     */
    public function getLink(\Symfony\Component\DomCrawler\Crawler $crawler, $text) {
        $link = $crawler->selectLink($text)->link();
        $uri = $link->getUri();
        $follow = $this->_client->request('GET', $uri);
        return $follow;
    }
}
