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
    function __construct($dbName, $otherURL)
    {
        $this->_dbName = $dbName;
        $this->_otherURL = $otherURL;
    }

    /**
     * Custom search
     *
     * @param array  $posts    Posts returned by search
     * @param object $wp_query Query
     *
     * @return array Posts
     * */
    function search($posts, &$wp_query)
    {
        $searchquery = $wp_query->get('s');
        if (!empty($searchquery) && $wp_query->is_main_query()) {
            global $wpdb;
            $curwpdb = $wpdb;
            $wpdb = new wpdb(DB_USER, DB_PASSWORD, $this->_dbName, DB_HOST);
            $wpdb->set_prefix('wp_');
            $query = new WP_query($wp_query->query);
            $otherposts = $query->get_posts();
            foreach ($otherposts as &$post) {
                $post->post_type = 'otherdb_post';
            }
            $posts = array_merge($posts, $otherposts);
            //We restore the old wpdb
            $wpdb = $curwpdb;
        }
        return $posts;
    }

    /**
     * Register a custom post type
     *
     * @return void
     * */
    function registerPostType()
    {
        register_post_type('otherdb_post');
    }

    /**
     * Redirect to other WordPress
     *
     * @param object $request Request
     *
     * @return object Request
     * */
    function redirect($request)
    {
        if (isset($request['otherdb_post'])) {
            global $wpdb;
            $curwpdb = $wpdb;
            $wpdb = new wpdb(DB_USER, DB_PASSWORD, $this->_dbName, DB_HOST);
            $wpdb->set_prefix('wp_');
            $query = new WP_query($request);
            $posts = $query->get_posts();
            header(
                'Location: '.str_replace(
                    get_option('home'), $this->_otherURL,
                    get_permalink($posts[0]->ID)
                )
            );
            die;
            $wpdb = $curwpdb;
        }
        return $request;
    }

    /**
     * Remove edit link from posts
     *
     * @param string $link HTML
     * @param int    $id   Post ID
     * @param string $text Link content
     *
     * @return false
     * @todo   Find a way to remove only for distant posts
     * */
    function removeEditLink($link, $id, $text)
    {
        return false;
    }

    /**
     * Add setting input field
     *
     * @param array $setting Setting
     *
     * @return void
     * */
    function addSetting($setting)
    {
        echo '<input name="'.$setting['name'].'"
            value="'.$setting['value'].'" type="text" />';
    }

    /**
     * Add custom settings
     *
     * @return void
     * */
    function settings()
    {
        add_settings_field(
            'otherdb_name',
            'Other Database name',
            array($this, 'addSetting'),
            'general', 'default',
            array('name'=>'otherdb_name', 'value'=>$this->_dbName)
        );
        add_settings_field(
            'otherdb_url',
            'Other WordPress URL',
            array($this, 'addSetting'),
            'general', 'default',
            array('name'=>'otherdb_url', 'value'=>$this->_otherURL)
        );
        register_setting('general', 'otherdb_name');
        register_setting('general', 'otherdb_url');
    }
}
