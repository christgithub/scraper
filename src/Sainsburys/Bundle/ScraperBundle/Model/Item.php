<?php

namespace Sainsburys\Bundle\ScraperBundle\Model;

/**
 * Array Object representation of a grocery item
 */
class Item extends \ArrayObject
{   
    const EXP_TITLE = 'div[class="productTitleDescriptionContainer"] > h1';
    const EXP_DESC = 'div[class="productText"] > p';
    const EXP_PRICE = 'p[class="pricePerUnit"]';
    
    private $_title;
    private $_description;
    private $_price;
    private $_domSize;
    
    public function __construct($title, $description, $price, $domSize) {
        
        $this->_title = $title;
        $this->_description = $description;
        $this->_price = $price;
        $this->_domSize = $domSize;
        
        $item = array();
        parent::__construct($item);
        $this->_init();
    }
    
    private function _init() {
        $this['title'] = $this->_title;
        $this['size'] = $this->_domSize;
        $this['unit_price'] = $this->_price;
        $this['description'] = $this->_description;
    }
    
    public function __toArray() {
        return array('item');
    }
}

