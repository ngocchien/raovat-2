<?php
namespace My\View\Helper;

use Zend\View\Helper\AbstractHelper;

class WordLimit extends AbstractHelper {

    public function __construct() {
        
    }

    public function __invoke($string, $length, $ellipsis = '...') {
        return $this->wordLimit($string, $length, $ellipsis);
    }
    
    public function wordLimit($string, $length, $ellipsis) {
        return (count($words = explode(' ', $string)) > $length) ? implode(' ', array_slice($words, 0, $length)) . $ellipsis : $string;
    }
    
}