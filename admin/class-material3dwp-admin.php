<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://material3d.net
 * @since      1.0.0
 *
 * @package    Material3dWP
 * @subpackage Material3dWP/admin
 * @author     Tony Luo <tonicraftor@gmail.com>
 */

class Material3dWP_Admin {


    public $config;
	public function __construct() {

    }
    
    /**
	 * create the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {

        $slug = 'admin-m3d-scenes.php';
        $cap = 'edit_posts';
		add_menu_page(
            __('M3d Scenes', 'm3dwp'),
            __('M3d Scenes', 'm3dwp'),
            $cap,
            $slug,
            array($this, 'admin_menu_scenes'),
            'data:image/svg+xml;base64,PHN2ZyB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgaWQ9InN2ZyI+CiAgPHBhdGggc3R5bGU9ImZpbGw6IzYxNjE2MTtmaWxsLW9wYWNpdHk6MC4zOTYwNzg0NDtzdHJva2U6bm9uZSIgZD0ibSAxMi41LDEuNSBjIDYuMDc1MTMyLDAgMTEsNC45MjQ4NyAxMSwxMSAwLDYuMDc1MTMgLTQuOTI0ODY4LDExIC0xMSwxMSAtNi4wNzUxMzIyLDAgLTExLC00LjkyNDg3IC0xMSwtMTEgMCwtNi4wNzUxMyA0LjkyNDg2NzcsLTExIDExLC0xMSBsIC00LDMgYyAtMS42NTY4NTQyLDAgLTMsMS4zNDMxNSAtMywzIDAsMS42NTY4NSAxLjM0MzE0NTgsMyAzLDMgMS42NTY4NTQsMCAzLC0xLjM0MzE1IDMsLTMgbCAzLjgsMC40IGMgLTAuNjIwNzM3LDAuOTM5MzcgMC40LDIgMC40LDIgMCwwIC0xLjM4MTMzLC0wLjQ0MTUyIC0xLjksMC40IC0wLjQ4MTc0MiwwLjc4MTYgMC4xLDEuNSAwLjEsMS41IDAsMCAtMS4zOTcwMTMsMC42OTU1IC0xLjg1MzcsMS4yMzE1OSBDIDExLjMwMTUwNywxMy45MDU4NyAxMSwxNS41IDExLDE1LjUgbCAtMy41LDMgNC40LC0yIGMgMCwwIDIuMDg0Njg5LC0wLjU3Njk2IDIuNiwtMS4zIDAuNjU3ODExLC0wLjkyMjk4IDAuMywtMi43IDAuMywtMi43IDAsMCAxLjAwNDYzMSwwLjEzMjg5IDEuNSwtMC44IDAuNDk2MzYzLC0wLjkzNDc2IC0wLjYsLTEuOCAtMC42LC0xLjggMCwwIDEuNjM3NTU4LDAuMTY5NjggMi4yLC0xLjEgbCAzLjEsMS40IGMgLTAuNTQ5NjQ4LDEuMzEzMDUgMC43OTU5NzUsMi45Mjg1NCAwLjEsNC4xIC0wLjU2NjcwNCwwLjk1Mzg3IC0yLjQyODM0MiwwLjY0OTA5IC0zLDEuNiAtMC41MzQwODksMC44ODg0MiAwLjQ1MzcxMywyLjE2Nzk3IDAsMy4xIC0wLjc1ODc3LDEuNTU4NjkgLTQuMSwzLjIgLTQuMSwzLjIgMCwwIDQuNDkxNTQ5LC0wLjc5MTE4IDUuOCwtMi40IDAuNjkwMzY5LC0wLjg0ODg1IDAuMDc1MzIsLTIuMzAxNyAwLjcsLTMuMiAwLjUwNTI3NywtMC43MjY2IDEuNTkyMzc2LC0wLjYxNDQ1IDIsLTEuNCAwLjkyNDU4MywtMS43ODE4MSAwLC02LjMgMCwtNi4zIDAsMCAtMS4zNzY1NDEsMC4yMTAzNSAtMS41LDEuMyBMIDE3LjksOC44IGMgMCwwIDAuMTEzNDc0LC0xLjA4NzAxIC0wLjYsLTEuNSAtMC43NTA1MzMsLTAuNDM0NDUgLTEuMzgwNzM3LC0wLjE3OTkxIC0yLDAuNiBMIDExLjUsNy41IGMgMCwtMS42NTY4NSAtMS4zNDMxNDYsLTMgLTMsLTMiLz4KICA8cGF0aCBzdHlsZT0iZmlsbDojNjE2MTYxO2ZpbGwtb3BhY2l0eToxO3N0cm9rZTpub25lIiBkPSJNIDEyLDEgQyAxOC4wNzUxMzIsMSAyMyw1LjkyNDg3IDIzLDEyIDIzLDE4LjA3NTEzIDE4LjA3NTEzMiwyMyAxMiwyMyA1LjkyNDg2NzgsMjMgMSwxOC4wNzUxMyAxLDEyIDEsNS45MjQ4NyA1LjkyNDg2NzcsMSAxMiwxIEwgOCw0IEMgNi4zNDMxNDU4LDQgNSw1LjM0MzE1IDUsNyBjIDAsMS42NTY4NSAxLjM0MzE0NTgsMyAzLDMgMS42NTY4NTQzLDAgMywtMS4zNDMxNSAzLC0zIGwgMy44LDAuNCBjIC0wLjYyMDczNywwLjkzOTM3IDAuNCwyIDAuNCwyIDAsMCAtMS4zODEzMywtMC40NDE1MiAtMS45LDAuNCAtMC40ODE3NDIsMC43ODE2IDAuMSwxLjUgMC4xLDEuNSAwLDAgLTEuMzk3MDEzLDAuNjk1NSAtMS44NTM3LDEuMjMxNTkgQyAxMC44MDE1MDcsMTMuNDA1ODcgMTAuNSwxNSAxMC41LDE1IEwgNywxOCAxMS40LDE2IGMgMCwwIDIuMDg0Njg5LC0wLjU3Njk2IDIuNiwtMS4zIDAuNjU3ODExLC0wLjkyMjk4IDAuMywtMi43IDAuMywtMi43IDAsMCAxLjAwNDYzMSwwLjEzMjg5IDEuNSwtMC44IDAuNDk2MzYzLC0wLjkzNDc2IC0wLjYsLTEuOCAtMC42LC0xLjggMCwwIDEuNjM3NTU4LDAuMTY5NjggMi4yLC0xLjEgbCAzLjEsMS40IGMgLTAuNTQ5NjQ4LDEuMzEzMDUgMC43OTU5NzUsMi45Mjg1NCAwLjEsNC4xIC0wLjU2NjcwNCwwLjk1Mzg3IC0yLjQyODM0MiwwLjY0OTA5IC0zLDEuNiAtMC41MzQwODksMC44ODg0MiAwLjQ1MzcxMywyLjE2Nzk3IDAsMy4xIC0wLjc1ODc3LDEuNTU4NjkgLTQuMSwzLjIgLTQuMSwzLjIgMCwwIDQuNDkxNTQ5LC0wLjc5MTE4IDUuOCwtMi40IDAuNjkwMzY5LC0wLjg0ODg1IDAuMDc1MzIsLTIuMzAxNyAwLjcsLTMuMiAwLjUwNTI3NywtMC43MjY2IDEuNTkyMzc2LC0wLjYxNDQ1IDIsLTEuNCAwLjkyNDU4MywtMS43ODE4MSAwLC02LjMgMCwtNi4zIDAsMCAtMS4zNzY1NDEsMC4yMTAzNSAtMS41LDEuMyBMIDE3LjQsOC4zIGMgMCwwIDAuMTEzNDc0LC0xLjA4NzAxIC0wLjYsLTEuNSAtMC43NTA1MzMsLTAuNDM0NDUgLTEuMzgwNzM3LC0wLjE3OTkxIC0yLDAuNiBMIDExLDcgQyAxMSw1LjM0MzE1IDkuNjU2ODU0Myw0IDgsNCIvPgo8L3N2Zz4=',
            20
        );
        add_submenu_page( $slug, __('M3d Scenes', 'm3dwp'), __('M3d Scenes', 'm3dwp'), $cap, $slug, array($this, 'admin_menu_scenes') );
        add_submenu_page( $slug, __('M3d Editor','m3dwp'), __('M3d Editor','m3dwp'), 'edit_m3d_scene', 'admin-m3d-editor.php', array($this, 'admin_menu_editor') );
        add_submenu_page( $slug, __('Help/Support','m3dwp'), __('Help/Support','m3dwp'), $cap, 'admin-m3d-help.php', array($this, 'admin_menu_help') );
    }

    public function admin_menu_scenes(){
        require_once(plugin_dir_path( __FILE__ ) . 'views/scenes.php');
    }
    
    public function admin_menu_editor(){
        require_once(plugin_dir_path( __FILE__ ) . 'views/editor.php');
    }

    public function admin_menu_settings(){
        require_once(plugin_dir_path( __FILE__ ) . 'views/settings.php');
    }

    public function admin_menu_help(){
        require_once(plugin_dir_path( __FILE__ ) . 'views/help.php');
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
        wp_enqueue_style( Material3dWP::$style_h_player );
        if($hook === 'm3d-scenes_page_admin-m3d-editor'){
            wp_enqueue_style( Material3dWP::$plugin_name.'-editor', plugin_dir_url( __FILE__ ) . 'css/editor.css', array(), Material3dWP::$version);
            wp_enqueue_style( Material3dWP::$plugin_name.'-editor-ad', plugin_dir_url( __FILE__ ) . 'css/editor-ad.css', array(), Material3dWP::$version);
        }
        elseif($hook === 'toplevel_page_admin-m3d-scenes'){
            wp_enqueue_style( Material3dWP::$plugin_name.'-scenes', plugin_dir_url( __FILE__ ) . 'css/scenes.css', array(), Material3dWP::$version);
        }
        wp_enqueue_style( Material3dWP::$plugin_name.'-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), Material3dWP::$version);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
        if($hook === 'toplevel_page_admin-m3d-scenes'){
            wp_enqueue_script( Material3dWP::$script_h_player);
            wp_enqueue_script( Material3dWP::$plugin_name.'-scenes', plugin_dir_url( __FILE__ ) . 'views/js/scenes.js', array(), Material3dWP::$version, true);
            wp_add_inline_script( Material3dWP::$plugin_name.'-scenes', ' m3d_scenelist.init();m3d_player.reset();' );
        }
        else if($hook === 'm3d-scenes_page_admin-m3d-editor'){
            wp_enqueue_script( Material3dWP::$script_h_player);
            wp_enqueue_script( Material3dWP::$plugin_name.'-config', plugin_dir_url( __FILE__ ) . 'views/js/config.js', array(), Material3dWP::$version, true);
            wp_enqueue_script( Material3dWP::$plugin_name.'-editor', plugin_dir_url( __FILE__ ) . 'views/js/material3deditor.js', array(), Material3dWP::$version, true);
            wp_add_inline_script( Material3dWP::$plugin_name.'-editor', $this->inline_script_editor() );
        }
    }

    private function inline_script_editor(){
        $post_arr = filter_input_array(INPUT_POST);
        if($post_arr && isset($post_arr['_wpnonce']) && wp_verify_nonce($post_arr['_wpnonce'], 'm3d_settings_form')){
            if(isset($post_arr['lib-root'])){
                Material3dWP::update_meta('config_lib_root', $post_arr['lib-root'], $post_arr['lib-root-txt'] ?? '');
            }
            if(isset($post_arr['lib-obj3d'])){
                Material3dWP::update_meta('config_lib_object3d', 0, $post_arr['lib-obj3d']);
            }
            if(isset($post_arr['lib-tex'])){
                Material3dWP::update_meta('config_lib_texture', 0, $post_arr['lib-tex']);
            }
            if(isset($post_arr['lib-mat'])){
                Material3dWP::update_meta('config_lib_material', 0, $post_arr['lib-mat']);
            }
            if(isset($post_arr['lib-ff'])){
                Material3dWP::update_meta('config_lib_forceField', 0, $post_arr['lib-ff']);
            }
            if(isset($post_arr['lib-anim'])){
                Material3dWP::update_meta('config_lib_animation', 0, $post_arr['lib-anim']);
            }
            if(isset($post_arr['files-up'])){
                Material3dWP::update_meta('config_files_upload', $post_arr['files-up'], $post_arr['files-up-txt'] ?? '');
            }
            if(isset($post_arr['files-down'])){
                Material3dWP::update_meta('config_files_download', $post_arr['files-down'], $post_arr['files-down-txt'] ?? '');
            }
        }
        $config = $this->config = (object)[
            'lib_root' => Material3dWP::get_meta('config_lib_root'),
            'lib_obj3d' => Material3dWP::get_meta('config_lib_object3d') ->meta_txt,
            'lib_tex' => Material3dWP::get_meta('config_lib_texture') ->meta_txt,
            'lib_mat' => Material3dWP::get_meta('config_lib_material') ->meta_txt,
            'lib_ff' => Material3dWP::get_meta('config_lib_forceField') ->meta_txt,
            'lib_anim' => Material3dWP::get_meta('config_lib_animation') ->meta_txt,
            'files_up' => Material3dWP::get_meta('config_files_upload'),
            'files_down' => Material3dWP::get_meta('config_files_download')
        ];
        if( $config->lib_root->meta_value == 0){
            $lib_root = M3DWP_URL;
        }
        else if($config->lib_root->meta_value == 1){
            $lib_root = M3D_NET_HOME;
        }
        else{
            $lib_root = $config->lib_root->meta_txt;
        }

        $files_up = $config->files_up->meta_value == 0 ?
        admin_url('admin-ajax.php').'?action=m3d_save_scene&wpnonce='.esc_attr(wp_create_nonce('m3d_save_scene'))
        : $config->files_up->meta_txt;

        if($config->files_down->meta_value == 0){//local
            $files_down = admin_url('admin-ajax.php').'?action=m3d_load_scene';
        }
        else if($config->files_down->meta_value == 1){//gallery
            $files_down = M3D_NET_HOME.'ajax.php?action=load';
        }
        else{//other
            $files_down = $config->files_down->meta_txt;
        }

        $filename = filter_input(INPUT_GET, 'filename');
        $where = filter_input(INPUT_GET, 'where');
        if(!$filename && $filename !== '0') $filename = '';
        if(!$where) $where = '';
        
        $script = <<<EOD
        MATERIAL3DEDITOR_CONFIG.libraries = {
            root: '$lib_root',
            object3d: $config->lib_obj3d,
            texture: '$config->lib_tex',
            material: '$config->lib_mat',
            forceField: '$config->lib_ff',
            animation: '$config->lib_anim'
        };
        MATERIAL3DEDITOR_CONFIG.editor = {
            files: {
                'upload_url': '$files_up',
                'download_url': '$files_down'
            }
        };
        var container = document.getElementById('m3d-editor');
        Material3dEditor.run(container);
        loadScene('$filename', '$where');
        function loadScene(fname, where){
            if(fname){
                var old_download_url = Material3dEditor.files.download_url;
                Material3dEditor.files.download_url = where === 'gallery' ? (M3DHOST.m3dnet + 'ajax.php?action=load')
                    : (M3DHOST.ajax + '?action=m3d_load_scene');
                Material3dEditor.loadScene(fname, 1, true);
                Material3dEditor.files.download_url = old_download_url;
            }
        }
        function onSaveScene(){
            var filenameEle = document.getElementById('m3d-filename');
            var filename = '';
            if(filenameEle){
                var filename = filenameEle.value;
            }
            if(!filename){
                alert('Invalid or empty file name!');
                return;
            }
            Material3dEditor.saveScene(filename, 1);
        }
        function toggleSettings(){
            var title = document.getElementById('m3d-editor-title');
            title.classList.toggle('hide');
        }
EOD;
        return $script;
    }

    
    public function plugin_action_links( $links, $file ) {
        if ( $file != M3DWP_PLUGIN_BASENAME ) {
            return $links;
        }

        $help_link = '<a href="'.menu_page_url('admin-m3d-help.php', false).'">Help</a>';

        array_unshift( $links, $help_link );

        return $links;
    }
    
    public function save_scene(){
        if(!current_user_can( 'edit_m3d_scene' )){
            http_response_code(404);
            exit('Current user can not edit m3d scenes.');
        }
        $action = filter_input(INPUT_GET, 'action');
        $nonce = filter_input(INPUT_GET, 'wpnonce');
        if($action !== 'm3d_save_scene' || wp_verify_nonce($nonce, 'm3d_save_scene') === FALSE){
            http_response_code(404);
            exit('Invalid or expired request for saving a scene.');
        }
        $posts = filter_input_array(INPUT_POST);
        foreach($posts as $key => $value){
            $fname = trim($key);
            $fname = substr($fname, -5) === '.json' ? substr($fname, 0, -5) : $fname;
            //write to database
            $table_name = Material3dWP::$table_name;
            $meta_table_name = Material3dWP::$meta_table_name;
            global $wpdb;
            $result = $wpdb->get_row(
                $wpdb->prepare("SELECT id FROM $table_name WHERE filename=%s", $fname)
            );
            $now = date('Y-m-d H:i:s');
            if(!$result){
                //add new record
                $curuser = wp_get_current_user();
                $author = $curuser ? $curuser ->data ->display_name : 'unkonwn';
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'filename' => $fname,
                        'data' => $value,
                        'author' => $author,
                        'createTime' => $now,
                        'updateTime' => $now
                    )
                );
                //update meta
                $wpdb->query("UPDATE $meta_table_name SET meta_value = meta_value + 1 WHERE meta_key='scene_count'");
            }
            else{
                //update record
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'data' => $value,
                        'updateTime' => $now
                    ),
                    array(
                        'id' => $result->id
                    )
                );
            }
            if(!$result){
                http_response_code(404);
                exit('file upload failed!');
            }
        }
        echo 'file upload suceeded!';
        http_response_code(200);
        exit;
    }

    public function load_scene(){
        //request handlers should exit() when they complete their task
        $action = filter_input(INPUT_GET, 'action');
        if($action !== 'm3d_load_scene'){
            http_response_code(404);
            exit('Invalid request for loading a scene.');
        }
        $fname = filter_input(INPUT_GET, 'filename');
        if($fname){
            $fname = trim($fname);
            $fname = substr($fname, -5) === '.json' ? substr($fname, 0, -5) : $fname;
            //read database
            $table_name = Material3dWP::$table_name;
            global $wpdb;
            $data = $wpdb->get_var(
                $wpdb->prepare("SELECT data FROM $table_name WHERE filename=%s", $fname)
            );
            if($data !== NULL){
                http_response_code(200);
                echo $data;
                exit;
            }
        }
        http_response_code(404);
        exit;
    }

    public function doAjax($action){
        if(!current_user_can( 'edit_m3d_scene' )){
            http_response_code(404);
            exit('Current user can not edit m3d scenes.');
        }
        $action_get = filter_input(INPUT_GET, 'action');
        $nonce = filter_input(INPUT_GET, 'wpnonce');
        if($action !== $action_get || wp_verify_nonce($nonce, $action) === FALSE){
            http_response_code(404);
            exit('Invalid or expired request for editing a scene.');
        }
        $ids = filter_input(INPUT_GET, 'ids');
        if($ids){
            $table_name = Material3dWP::$table_name;
            $meta_table_name = Material3dWP::$meta_table_name;
            global $wpdb;
            $ids = explode('_', $ids);
            $len = count($ids);
            $pre = implode(',', array_fill(0, $len, '%d'));
            if($action == 'm3d_trash_scene'){
                $pre = "UPDATE $table_name SET status = 0 WHERE id IN ($pre)";
                $response = 'trashing files suceeded!';
                $meta_query0 = "+ $len";
                $meta_query1 = "- $len";
            }
            elseif($action == 'm3d_del_scene'){
                $pre = "DELETE FROM $table_name WHERE id IN ($pre)";
                $response = 'deleting files suceeded!';
                $meta_query0 = "- $len";
                $meta_query1 = "";
            }
            elseif($action == 'm3d_restore_scene'){
                $pre = "UPDATE $table_name SET status = 1 WHERE id IN ($pre)";
                $response = 'restoring files suceeded!';
                $meta_query0 = "- $len";
                $meta_query1 = "+ $len";
            }
            $result = $wpdb->query($wpdb->prepare($pre, $ids));
            if($result){
                $wpdb->query("UPDATE $meta_table_name SET meta_value = meta_value $meta_query0 WHERE meta_key='trash_count'");
                if($meta_query1)$wpdb->query("UPDATE $meta_table_name SET meta_value = meta_value $meta_query1 WHERE meta_key='scene_count'");
                echo $response;
                http_response_code(200);
                exit;
            }
        }
        http_response_code(404);
        exit;
    }

    public function trash_scene(){
        $this->doAjax('m3d_trash_scene');
    }

    public function del_scene(){
        $this->doAjax('m3d_del_scene');
    }

    public function restore_scene(){
        $this->doAjax('m3d_restore_scene');
    }
}