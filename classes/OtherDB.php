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
    private $_id;
    private $_dbName;
    private $_dbUser;
    private $_dbPrefix;
    private $_dbPassword;
    private $_otherURL;

    /**
     * OtherDB constructor
     *
     * @param string $id        ID of OtherDatabase in Database
     * @param string $dbName    Other database name
     * @param string $otherURL  Other WordPress base URL
     * */
    function __construct($id, $dbName, $dbUser, $dbPrefix, $dbPassword, $otherURL)
    {
        $this->_id = $id;
        $this->_dbName = $dbName;
        $this->_dbUser = $dbUser;
        $this->_dbPrefix = $dbPrefix;
        $this->_dbPassword = $dbPassword;
        $this->_otherURL = $otherURL;
    }

    function getId() {
        return $this->_id;
    }
    
    function getDbName() {
        return $this->_dbName;
    }

    function getDbUser() {
        return $this->_dbUser;
    }

    function getDbPrefix() {
        return $this->_dbPrefix;
    }

    function getDbPassword($show = false) {
        if ($show) {
            return $this->_dbPassword;
        } else {
            return '**********';
        }
    }

    function getUrl() {
        return $this->_otherURL;
    }
   
}
