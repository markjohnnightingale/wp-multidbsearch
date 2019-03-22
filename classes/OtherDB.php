<?php
/**
 * OtherDB Class
 *
 * PHP version 5.4
 *
 * @category Class
 * @package  MultiDB_Search
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://github.com/Rudloff/wp-multidbsearch
 * */

/**
 * Class used to search in another database
 *
 * PHP version 5.4
 *
 * @category Class
 * @package  MultiDB_Search
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://github.com/Rudloff/wp-multidbsearch
 * */
class OtherDB
{
    private $_dbName;
    private $_otherURL;

    /**
     * OtherDB constructor
     *
     * @param string $dbName   Other database name
     * @param string $otherURL Other WordPress base URL
     * */
    function __construct($id, $dbName, $otherURL)
    {
        $this->_id = $id;
        $this->_dbName = $dbName;
        $this->_otherURL = $otherURL;
    }

    function getId() {
        return $this->_id;
    }
    
    function getDbname() {
        return $this->_dbName;
    }

    function getUrl() {
        return $this->_otherURL;
    }
   
}
