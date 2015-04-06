<?php
/**
 * Plugin Name: MultiDB Search
 * Description: Search in multiple databases
 * Author: Pierre Rudloff
 * Version: 0.1
 * Author URI: https://rudloff.pro/
 * Plugin URI: https://github.com/Rudloff/wp-multidbsearch
 * 
 * PHP version 5.4
 * 
 * @category Plugin
 * @package  MultiDB_Search
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://github.com/Rudloff/wp-multidbsearch
 * */
require_once 'classes/OtherDB.php';
$otherDB = new OtherDB(get_option('otherdb_name'), get_option('otherdb_url'));

add_action('admin_init', array($otherDB, 'settings'));
add_action('init', array($otherDB, 'registerPostType'));
add_filter('the_posts', array($otherDB, 'search'), 10, 2);
add_filter('request', array($otherDB, 'redirect'));
add_filter('edit_post_link', array($otherDB, 'removeEditLink'), 10, 3);
?>
