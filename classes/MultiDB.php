<?php

class MultiDB {

    /**
     * Other Databases – array of OtherDB class objects
     *
     * @var array
     */
    public $otherDBs = [];

    /**
     * OtherDBs database table (initialised in constructor)
     *
     * @var string
     */
    public $otherDBsTable = '';

    /**
     * Constructor populates OtherDBs array and registers hooks
     */
    public function __construct() {
        global $wpdb;
        $this->otherDBsTable = $wpdb->prefix . 'other_dbs';

        $this->refreshOtherDBs();
        $this->registerAdminHooks();
    }

    /**
     * Hook run on plugin activation
     *
     * @return void
     */
    public function activate() {
        $this->create_tables();
    }

    /**
     * Hook run on plugin delete
     *
     * @return void
     */
    public function delete() {
        $this->drop_tables();
    }

    /**
     * Create database tables for plugin
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;
        $table_name = $this->otherDBsTable;
        $charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );        
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			dbname tinytext NOT NULL,
			url tinytext NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );
    }

    /**
     * Delete tables from Database for plugin
     *
     * @return void
     */
    public function drop_tables() {
        global $wpdb;
        $table_name = $this->otherDBsTable;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );        
		$sql = "DROP TABLE IF EXISTS  $table_name;";
		$wpdb->query($sql);
    }

    /**
     * Admin hooks
     *
     * @return void
     */
    public function registerAdminHooks() {
        add_action('admin_menu', array($this, 'registerSettings'));
    }

    /**
     * Main plugin hooks
     *
     * @return void
     */
    public function registerMainHooks() {
        $this->registerPostType();
    }

    /**
     * Refresh OtherDatabases Array from Database
     *
     * @return void
     */
    public function refreshOtherDBs() {
        $this->otherDBs = [];
        global $wpdb;
        $otherDBs = $wpdb->get_results("SELECT * from $this->otherDBsTable");
        foreach($otherDBs as $db) {
            $this->otherDBs[] = new OtherDB($db->id, $db->dbname, $db->url);
        }
    }
    
    /**
     * Register a custom post type
     *
     * @return void
     * */
    function registerPostType()
    {
        foreach($this->otherDBs as $db) {
            register_post_type('otherdb_post_'.$db->getId());
        }
        
    }

    /**
     * Add custom DB page in admin
     *
     * @return void
     * */
    function registerSettings()
    {
        add_menu_page(
            __('Autres bases de données'), 
            'Other DBs',
            'manage_options',
            'other_db_page',
            array($this, 'renderMenuPage')
        );
    }

    /**
     * Render function for the main DB page in admin
     *
     * @return void
     */
    public function renderMenuPage() { 
        $this->handleFormSubmit($_POST);
        ?>
        <h1><?php _e('Autres bases de données'); ?></h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>DBName</th>
                    <th>Wordpress URL</th>
                    <th></th>
                </tr>
            </thead>
            <tbody style="text-align: center">
                <?php foreach($this->otherDBs as $db) :?>
                    <tr>
                        
                        <td><?php echo $db->getId() ?></td>
                        <td><?php echo $db->getDbname() ?></td>
                        <td><a href="<?php echo $db->getUrl() ?>" target="_blank"><?php echo $db->getUrl() ?></a></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id" value="<?php echo $db->getId();?>">
                                <input type="hidden" name="action" value="delete">
                                <button><?php _e('Supprimer');?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="action" value="add">
                        <td></td>
                        <td><input type="text" name="dbname"></td>
                        <td><input type="text" name="url"></td>
                        <td><button><?php _e('Ajouter');?></button></td>
                    </form>
                </tr>
            </tbody>
        </table>  
    <?php }

    /**
     * Handle the form submission from the admin table in wordpress admin
     *
     * @param [array] $vars $_POST array of vars (or similar)
     * @return void
     */
    public function handleFormSubmit($vars) {
        global $wpdb;
        if (isset($vars['action'])) {
            if ($vars['action'] == 'delete' && isset($vars['id'])) {
                $id_to_delete = (int)$vars['id'];
                // Delete ID;
                $wpdb->query("DELETE FROM $this->otherDBsTable WHERE id = $id_to_delete");
                
            }
            if ($vars['action'] == 'add' && isset($vars['dbname']) && isset($vars['url'])) {
                $dbname = $this->sanitizeDBName($vars['dbname']);
                $url = $this->sanitizeUrl($vars['url']);
                if (!empty($dbname) && !empty($url)) {
                    $statement = $wpdb->prepare("INSERT INTO $this->otherDBsTable (dbname, url) VALUES (%s, %s)", 
                        [
                            $dbname,
                            $url
                        ]
                    );
                    $query = $wpdb->query($statement);
                }
            }
        }
        $this->refreshOtherDBs();
    }

    /**
     * Sanitize the DB name
     *
     * @param string $text to sanitize
     * @return void
     */
    public function sanitizeDBName($text) {
        return preg_replace("/[^a-zA-Z_]/", "", $text);
    }

    /**
     * Sanitize URL
     *
     * @param string $url
     * @return void
     */
    public function sanitizeUrl($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
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
     * Custom search
     *
     * @param array  $posts    Posts returned by search
     * @param object $wp_query Query
     *
     * @return array Posts
     * */
    function search($posts, WP_Query $wp_query)
    {  
        $searchquery = $wp_query->get('s');
        if (!empty($searchquery) && $wp_query->is_main_query()) {
            global $wpdb;
            $curwpdb = $wpdb;
            $curprefix = $wpdb->prefix;
            foreach($this->otherDBs as $db) {
                $wpdb = new wpdb(DB_USER, DB_PASSWORD, $db->getDbname(), DB_HOST);
                $wpdb->set_prefix($curprefix);
                $query = new WP_query($wp_query->query);
                $otherposts = $query->get_posts();
                foreach ($otherposts as &$post) {
                    $post->post_type = 'otherdb_post_'.$db->getId();
                }
                $posts = array_merge($posts, $otherposts);
            }
            //We restore the old wpdb
            $wpdb = $curwpdb;
        }
        return $posts;
    }


    /**
     * Hooks into post_type_link function and replaces permalink with link to
     * other DB website
     *
     * @param string $url = post_link 
     * @param WP_Post $post = current post
     * @return string
     */
    public function modifyPermalink($url, $post) {
        $matches = [];
        if (preg_match('/otherdb_post_([0-9]+)/', $url, $matches) === 1) {
            $id = (int)$matches[1];
            $db = $this->getOtherDBFromId($id);
            if ($db) {
                $url = str_replace(get_option('home'), $db->getUrl(), get_permalink( $post->ID ));
            }
        }
        return $url;
    }

    /**
     * Return the OtherDB from a given ID
     *
     * @param integer $id
     * @return OtherDB or null
     */
    public function getOtherDBFromId(int $id) {
        foreach ($this->otherDBs as $key => $db) {
            if ($db->getId() == $id) {
                return $db;
            }
        }
        return null;
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
}