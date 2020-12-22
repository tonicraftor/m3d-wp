<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://material3d.net
 * @since      1.0.0
 *
 * @package    Material3dWP
 * @subpackage Material3dWP/includes
 * @author     Tony Luo <tonicraftor@gmail.com>
 */

class Material3dWP {

    public static $plugin_name = 'material3dwp';
    public static $script_h_three = 'm3dwp-three';
    public static $script_h_player = 'm3dwp-player';
    public static $script_h_loader = 'm3dwp-loader';
    public static $style_h_player = 'm3dwp-playercss';
    public static $version = M3DWP_VERSION;
    public static $role = 'm3d_editor';
    public static $capability = 'edit_m3d_scene';
    public static $table_name = 'm3d_scenes';
    public static $meta_table_name = 'm3d_meta';
    public function __construct() {
        //set locale
        add_action( 'plugins_loaded', array($this, 'set_locale'));
        //add shortcode
        add_action( 'init', array($this, 'add_shortcode'));
        //add role
        add_action( 'init', array($this, 'add_role_cap'));
        //register scripts
        add_action( 'init', array($this, 'register_scripts'));
        //enqueue scripts
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        //admin hooks
        if(is_admin()){
            $this->define_admin_hooks();
        }
    }

    public function activate_hook() {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = self::$table_name;
        $meta_table_name = self::$meta_table_name;
    
        $sql = ["CREATE TABLE $table_name (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            filename VARCHAR(255) NOT NULL,
            data MEDIUMTEXT NOT NULL,
            author VARCHAR(255) NOT NULL,
            createTime DATETIME DEFAULT '2020-12-01 00:00:00' NOT NULL,
            updateTime DATETIME DEFAULT '2020-12-01 00:00:00' NOT NULL,
            status TINYINT DEFAULT 1 NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (filename)
        ) $charset_collate;",
        "CREATE TABLE $meta_table_name (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            meta_key VARCHAR(255) NOT NULL,
            meta_txt VARCHAR(65535) DEFAULT '' NOT NULL,
            meta_value INTEGER DEFAULT 0 NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (meta_key)
        ) $charset_collate;"
        ];
    
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        $res = $wpdb ->get_row("SELECT * FROM $meta_table_name WHERE meta_key='scene_count'");
        if(!$res){
            $wpdb ->insert($meta_table_name, array('meta_key' => 'scene_count'));
        }
        $res = $wpdb ->get_row("SELECT * FROM $meta_table_name WHERE meta_key='trash_count'");
        if(!$res){
            $wpdb ->insert($meta_table_name, array('meta_key' => 'trash_count'));
        }
        self::replace_meta('config_lib_root', 0, '/');
        self::replace_meta('config_lib_object3d', 0, '{
            "mesh": "libs/meshlib.js",
            "points": "libs/pointslib.js",
            "light": "libs/lightlib.js",
            "camera": "libs/cameralib.js",
            "extra": "libs/extralib.js",
            "custom": "libs/customlib.js"
        }');
        self::replace_meta('config_lib_texture', 0, 'libs/texturelib.js');
        self::replace_meta('config_lib_material', 0, 'libs/materiallib.js');
        self::replace_meta('config_lib_forceField', 0, 'libs/forcefieldlib.js');
        self::replace_meta('config_lib_animation', 0, 'libs/animationlib.js');
        self::replace_meta('config_files_upload', 0, '');
        self::replace_meta('config_files_download', 0, '');
    }

    public function deactivate_hook(){
        //
    }

    public function uninstall_hook() {
        //
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS ".self::$table_name );
        $wpdb->query( "DROP TABLE IF EXISTS ".self::$meta_table_name );
    }

    public function set_locale() {
        load_plugin_textdomain(
			'm3dwp',
			false,
			M3DWP_URL. 'languages/'
		);
    }

    /**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
    public function register_scripts(){
        wp_register_script( self::$script_h_three, M3DWP_URL.'three/build/three.min.js', array(), self::$version );
        wp_register_script( self::$script_h_player, M3DWP_URL.'player/material3dplayer.min.js', array(self::$script_h_three), self::$version);
        wp_register_script( self::$script_h_loader, M3DWP_URL.'player/m3dsceneloader.js', array(self::$script_h_player), self::$version);
        wp_localize_script( self::$script_h_loader, 'ajaxObj', array('url' => admin_url('admin-ajax.php')) );
        wp_register_style( self::$style_h_player, M3DWP_URL.'player/style.css', array(), self::$version );
    }

    public function enqueue_scripts(){
        wp_enqueue_script( self::$script_h_loader);
        wp_enqueue_style( self::$style_h_player );
	}

    public function add_shortcode(){
        add_shortcode( 'm3dscene', array($this, 'm3dscene_shortcode') );
    }

    public function m3dscene_shortcode($atts = []){
        // normalize attribute keys, lowercase
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );
        $o = '<m3dscene ';
        foreach($atts as $key => $val){
            $o .= $key."=\"$val\"";
        }
        $o .= '></m3dscene>';
        return $o;
    }

    public function add_role_cap(){
        $role = add_role(
            self::$role,
            'M3d Editor',
            array(
                self::$capability => true,
            ),
        );
        //add capability to specific roles
        //check setting to get who have the capability 'edit_m3d_scene'
        get_role('administrator')->add_cap(self::$capability);
        get_role('editor')->add_cap(self::$capability);
    }

    private function define_admin_hooks() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-material3dwp-admin.php';
        $plugin_admin = new Material3dWP_Admin();
        add_action( 'admin_menu', array($plugin_admin, 'admin_menu') );
        add_action( 'admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles' ));
        add_action( 'admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts' ));
        add_action( 'wp_ajax_m3d_save_scene', array($plugin_admin, 'save_scene' ));
        add_action( 'wp_ajax_m3d_load_scene', array($plugin_admin, 'load_scene' ));
        add_action( 'wp_ajax_m3d_trash_scene', array($plugin_admin, 'trash_scene' ));
        add_action( 'wp_ajax_m3d_del_scene', array($plugin_admin, 'del_scene' ));
        add_action( 'wp_ajax_m3d_restore_scene', array($plugin_admin, 'restore_scene' ));
        add_filter( 'plugin_action_links', array($plugin_admin, 'plugin_action_links' ), 10, 2 );

    }

    public static function get_meta($metakey){
        $meta_table_name = self::$meta_table_name;
        global $wpdb;
        return $wpdb->get_row("SELECT meta_value, meta_txt FROM $meta_table_name WHERE meta_key='$metakey'");
    }
    public static function replace_meta($metakey, $metavalue, $metatxt){
        global $wpdb;
        return $wpdb->replace(self::$meta_table_name, array('meta_key' => $metakey, 'meta_value' => $metavalue, 'meta_txt' => $metatxt), array('%s', '%d', '%s'));
    }
    public static function update_meta($metakey, $metavalue, $metatxt){
        global $wpdb;
        return $wpdb->update(self::$meta_table_name, array('meta_value' => $metavalue, 'meta_txt' => $metatxt), array('meta_key' => $metakey), array('%d', '%s'));
    }
}