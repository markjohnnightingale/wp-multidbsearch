<?php

class MultiDB
{

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
    public function __construct()
    {
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
    public function activate()
    {
        $this->create_tables();
    }

    /**
     * Hook run on plugin delete
     *
     * @return void
     */
    public static function delete()
    {
        self::drop_tables();
    }

    /**
     * Create database tables for plugin
     *
     * @return void
     */
    public function create_tables()
    {
        global $wpdb;
        $table_name = $this->otherDBsTable;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			dbname tinytext NOT NULL,
			dbuser tinytext NOT NULL,
			dbprefix tinytext NOT NULL,
			dbpassword tinytext NOT NULL,
			url tinytext NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    /**
     * Delete tables from Database for plugin
     *
     * @return void
     */
    public static function drop_tables()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'other_dbs';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "DROP TABLE IF EXISTS  $table_name;";
        $wpdb->query($sql);
    }

    /**
     * Admin hooks
     *
     * @return void
     */
    public function registerAdminHooks()
    {
        add_action('admin_menu', array($this, 'registerSettings'));
    }

    /**
     * Main plugin hooks
     *
     * @return void
     */
    public function registerMainHooks()
    {
        $this->registerPostType();
    }

    /**
     * Refresh OtherDatabases Array from Database
     *
     * @return void
     */
    public function refreshOtherDBs()
    {
        $this->otherDBs = [];
        global $wpdb;
        $otherDBs = $wpdb->get_results("SELECT * from $this->otherDBsTable");
        foreach ($otherDBs as $db) {
            $this->otherDBs[] = new OtherDB($db->id, $db->dbname, $db->dbuser, $db->dbprefix, $db->dbpassword, $db->url);
        }
    }

    /**
     * Register a custom post type
     *
     * @return void
     * */
    public function registerPostType()
    {
        foreach ($this->otherDBs as $db) {
            register_post_type('otherdb_post_' . $db->getId());
        }

    }

    /**
     * Add custom DB page in admin
     *
     * @return void
     * */
    public function registerSettings()
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
    public function renderMenuPage()
    {
        $this->handleFormSubmit($_POST);
        ?>
        <h1><?php _e('Autres bases de données');?></h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>DBName</th>
                    <th>DBUser</th>
                    <th>DBPrefix</th>
                    <th>DBPassword</th>
                    <th>Wordpress URL</th>
                    <th></th>
                </tr>
            </thead>
            <tbody style="text-align: center">
                <?php foreach ($this->otherDBs as $db): ?>
                    <tr>
                        <td><?php echo $db->getId() ?></td>
                        <td><?php echo $db->getDbName() ?></td>
                        <td><?php echo $db->getDbUser() ?></td>
                        <td><?php echo $db->getDbPrefix() ?></td>
                        <td><?php echo $db->getDbPassword() ?></td>
                        <td><a href="<?php echo $db->getUrl() ?>" target="_blank"><?php echo $db->getUrl() ?></a></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id" value="<?php echo $db->getId(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="button"><?php _e('Supprimer');?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="action" value="add">
                        <td></td>
                        <td><input type="text" name="dbname"></td>
                        <td><input type="text" name="dbuser"></td>
                        <td><input type="text" name="dbprefix"></td>
                        <td><input type="text" name="dbpassword"></td>
                        <td><input type="text" name="url"></td>
                        <td><button class="button"><?php _e('Ajouter');?></button></td>
                    </form>
                </tr>
            </tbody>
        </table>
        <div class="panel">
            <form method="post">
                <h2>
                    <?php _e('Réglages');?>
                </h2>
                <p><strong><?php _e('Utiliser des redirections 301 dans les résultats de recherche');?></strong> <input type="checkbox" name="use_redirect_permalinks" value="1" <?php echo checked(get_option('mdb_use_redirect_permalinks', false), '1'); ?> ><br>
                <?php _e('Si vos sites ont des structures de permalien différents, cochez cette case pour que les permaliens dans les résultats de recherche soient modifiés en example.com/?p={post_id}');?><br></p>

                <input type="hidden" name="action" value="settings">
                <button class="button button-primary"><?php _e('Enregistrer les modifications');?></button>
            </form>
        </div>
    <?php }

    /**
     * Handle the form submission from the admin table in wordpress admin
     *
     * @param [array] $vars $_POST array of vars (or similar)
     * @return void
     */
    public function handleFormSubmit($vars)
    {
        global $wpdb;
        if (isset($vars['action'])) {
            if ($vars['action'] == 'delete' && isset($vars['id'])) {
                $id_to_delete = (int) $vars['id'];
                // Delete ID;
                $wpdb->query("DELETE FROM $this->otherDBsTable WHERE id = $id_to_delete");

            }
            if ($vars['action'] == 'add' && isset($vars['dbname']) && isset($vars['url']) && isset($vars['dbuser']) && isset($vars['dbprefix']) && isset($vars['dbpassword'])) {

                $dbname = $this->sanitizeDbInfo($vars['dbname']);
                $dbuser = $this->sanitizeDbInfo($vars['dbuser']);
                $dbprefix = $this->sanitizeDbInfo($vars['dbprefix']);
                $dbpassword = $this->sanitizeDbPassword($vars['dbpassword']);
                $url = $this->sanitizeUrl($vars['url']);
                if (!empty($dbname) && !empty($dbuser) && !empty($dbprefix) && !empty($dbpassword) && !empty($url)) {

                    $statement = $wpdb->prepare("INSERT INTO $this->otherDBsTable (dbname, dbuser, dbprefix, dbpassword, url) VALUES (%s, %s, %s, %s, %s)",
                        [
                            $dbname,
                            $dbuser,
                            $dbprefix,
                            $dbpassword,
                            $url,
                        ]
                    );
                    $query = $wpdb->query($statement);
                }
            }
            if ($vars['action'] == 'settings') {
                if (isset($vars['use_redirect_permalinks']) && $vars['use_redirect_permalinks'] == 1) {
                    update_option('mdb_use_redirect_permalinks', 1);
                } else {
                    update_option('mdb_use_redirect_permalinks', 0);
                }
            }
        }
        $this->refreshOtherDBs();
    }

    /**
     * Sanitize the DB name, user
     *
     * @param string $text to sanitize
     * @return void
     */
    public function sanitizeDbInfo($text)
    {
        return preg_replace("/[^a-zA-Z0-9_]/", "", $text);
    }

    /**
     * Sanitize the DB password
     *
     * @param string $text
     * @return string
     */
    public function sanitizeDbPassword($text)
    {
        return $text;
    }

    /**
     * Sanitize URL
     *
     * @param string $url
     * @return string
     */
    public function sanitizeUrl($url)
    {
        if (preg_match('=(\b(https?)://)[-A-Za-z0-9+&#/%~_|!:,.;]+[-A-Za-z0-9+&@#/%~_|]=', $url) > 0) {
            $url = preg_replace('!/$!', '', $url);
            return filter_var($url, FILTER_SANITIZE_URL);
        } else {
            echo 'URL is not correctly formatted. Make sure it includes http(s)://';
            return '';
        }
    }
    /**
     * Add setting input field
     *
     * @param array $setting Setting
     *
     * @return void
     * */
    public function addSetting($setting)
    {
        echo '<input name="' . $setting['name'] . '"
            value="' . $setting['value'] . '" type="text" />';
    }

    /**
     * Custom search
     *
     * @param array  $posts    Posts returned by search
     * @param object $wp_query Query
     *
     * @return array Posts
     * */
    public function search($posts, WP_Query $wp_query)
    {
        $searchquery = $wp_query->get('s');
        if (!empty($searchquery) && $wp_query->is_main_query()) {
            global $wpdb;
            $curwpdb = $wpdb;
            $curprefix = $wpdb->prefix;
            foreach ($this->otherDBs as $db) {
                $wpdb = new wpdb($db->getDbUser(), $db->getDbPassword(true), $db->getDbName(), DB_HOST);
                $wpdb->set_prefix($db->getDbPrefix());
                $query = new WP_query($wp_query->query);
                $otherposts = $query->get_posts();
                foreach ($otherposts as &$post) {
                    $post->otherdb_thumbnail = $this->getOtherDbPostThumbnailUrl($post->ID, $wpdb, $db);
                    $post->post_type = 'otherdb_post_' . $db->getId();
                }
                $posts = array_merge($posts, $otherposts);
            }
            //We restore the old wpdb
            $wpdb = $curwpdb;
        }
        return $posts;
    }
    
    /**
     * Returns OtherDB Thumbnail URL from post
     *
     * @param int $post_id
     * @param Wpdb $wpdb
     * @param OtherDB $otherDb
     * @return string
     */
    public function getOtherDbPostThumbnailUrl($post_id, $wpdb, OtherDB $otherDb)
    {
        $query = new WP_Query(
            array(
                'p' => $post_id
            )
        );
        $posts = $query->get_posts();
        if (!$posts) {
            return null;
        }
		$thumbnail_id = $this->getOtherDbMeta($post_id, $wpdb, '_thumbnail_id');
		if (!empty($thumbnail_id)){
			$thumbnail_src = $this->getOtherDbMeta($thumbnail_id, $wpdb, '_wp_attached_file');
			return $otherDb->getUrl() . '/wp-content/uploads/' . $thumbnail_src ;
		} else {
			return null;
		}
    }

    /**
     * Returns OtherDB Meta value from post and metakey
     *
     * @param int $post_id
     * @param Wpdb $wpdb
     * @param string $meta_key
     * @return void
     */
    public function getOtherDbMeta($post_id, $wpdb, $meta_key) {
		if (!empty($post_id) && !empty($meta_key)) {
			$sql = "SELECT `meta_value` FROM `". $wpdb->prefix."postmeta` WHERE `post_id` = $post_id AND `meta_key` LIKE '". $meta_key ."';";
			return $wpdb->get_var($sql, 0, 0);
		}
		return null;
    }

    /**
     * Hooks into post_type_link function and replaces permalink with link to
     * other DB website
     *
     * @param string $url = post_link
     * @param WP_Post $post = current post
     * @return string
     */
    public function modifyPermalink($url, $post)
    {
        $matches = [];
        if (preg_match('/otherdb_post_([0-9]+)/', $url, $matches) === 1) {
            $id = (int) $matches[1];
            $db = $this->getOtherDBFromId($id);
            if ($db) {
                if (get_option('mdb_use_redirect_permalinks', false)) {

                    $url = $db->getUrl() . '/?p=' . $post->ID;
                } else {
                    $url = str_replace(get_option('home'), $db->getUrl(), get_permalink($post->ID));
                }
            }
        }
        return $url;
    }

    public function modifyHasPostThumbnail($has_thumbnail, $post, $thumbnail_id) {
        global $post;
        if (preg_match('/otherdb_post_([0-9]+)/', $post->post_type) === 1) {
            if (!empty($post->otherdb_thumbnail)) {
                return true;
            }
        }
        return (bool) $has_thumbnail;
    }

    public function modifyThumbnailSrc($html) {
        global $post;
        if (preg_match('/otherdb_post_([0-9]+)/', $post->post_type) === 1) {
            if (!empty($post->otherdb_thumbnail)) {
                if (!empty($html)) {
                    return preg_replace('/src=[\'"](.+)[\'"]/', 'src="'.$post->otherdb_thumbnail .'"', $html);
                } else {
                    return '<img src="'.$post->otherdb_thumbnail.'" />';
                }
            }
        }
        return $html;
    }


    /**
     * Return the OtherDB from a given ID
     *
     * @param integer $id
     * @return OtherDB or null
     */
    public function getOtherDBFromId(int $id)
    {
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
    public function removeEditLink($link, $id, $text)
    {
        return false;
    }
}