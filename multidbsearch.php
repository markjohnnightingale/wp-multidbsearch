<?php
/**
 * Plugin Name: MultiDB Search
 * Description: Search in multiple databases
 * Author: Pierre Rudloff and Mark Nightingale
 * Version: 0.1
 * Author URI: https://marknightingale.net/
 * Plugin URI: https://github.com/markjohnnightingale/wp-multidbsearch
 * 
 * PHP version 7
 * 
 * @category Plugin
 * @package  MultiDB_Search
 * @author   Mark Nightingale <hello@marknightingale.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://github.com/markjohnnightingale/wp-multidbsearch
 * */
require_once 'classes/OtherDB.php';
require_once 'classes/MultiDB.php';

$multiDB = new MultiDB();

register_activation_hook( __FILE__, array($multiDB, 'activate') );
register_uninstall_hook( __FILE__, array($multiDB, 'delete') );

add_action('init', array($multiDB, 'registerMainHooks'));
add_filter('the_posts', array($multiDB, 'search'), 10, 2);
add_filter('post_type_link', array($multiDB, 'modifyPermalink'), 10, 2);
add_filter('edit_post_link', array($multiDB, 'removeEditLink'), 10, 3);
?>
