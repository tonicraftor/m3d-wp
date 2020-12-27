<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$filename = filter_input(INPUT_GET, 'filename');
$where = filter_input(INPUT_GET, 'where');
?>
<h1 class="wp-heading-inline">Material3d Editor</h1>
<?php
if(current_user_can( 'edit_m3d_scene' )):
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
    $lib_root = Material3dWP::get_meta('config_lib_root');
    $lib_obj3d = Material3dWP::get_meta('config_lib_object3d') ->meta_txt;
    $lib_tex = Material3dWP::get_meta('config_lib_texture') ->meta_txt;
    $lib_mat = Material3dWP::get_meta('config_lib_material') ->meta_txt;
    $lib_ff = Material3dWP::get_meta('config_lib_forceField') ->meta_txt;
    $lib_anim = Material3dWP::get_meta('config_lib_animation') ->meta_txt;
    $files_up = Material3dWP::get_meta('config_files_upload');
    $files_down = Material3dWP::get_meta('config_files_download');
?>
<div class="m3d-admin-wrap">
    <div class="editor-title hide" id="m3d-editor-title">
        <div class="btns">
            <a onclick="toggleSettings()" class="btn-settings"><img src="<?=M3DWP_URL.'imgs/settings.svg'?>" alt="settings"></a>
            <input type="text" id="m3d-filename" class="title" size="30" spellcheck="true" autocomplete="off" placeholder="Scene name" value="<?=($filename ? $filename : '')?>">
            <button type="button" class="button-primary" onclick="onSaveScene()">Save</button>
        </div>
        <div class="wrap-settings">
            <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
                <?php wp_nonce_field( 'm3d_settings_form'); ?>
                <div class="top-bar">
                    <div>Settings <a class="m3d-help-btn" href="<?=menu_page_url('admin-m3d-help.php', false)?>">?</a></div>
                    <div onclick="toggleSettings()">✕</div>
                </div>
                <div class="section">
                    <div class="title">Libraries</div>
                    <div class="fields">
                        <div>Root URL:</div>
                        <?php
                            $checked = ['', '', ''];
                            $checked[$lib_root ->meta_value] = ' checked';
                        ?>
                        <div>
                            <div>
                                <input type="radio" id="lib-root-0" name="lib-root" value="0"<?=$checked[0]?>>
                                <label for="lib-root-0">Local</label>
                            </div>
                            <div>
                                <input type="radio" id="lib-root-1" name="lib-root" value="1"<?=$checked[1]?>>
                                <label for="lib-root-1">Material3d.net</label>
                            </div>
                            <div class="line-input">
                                <input type="radio" id="lib-root-2" name="lib-root" value="2"<?=$checked[2]?>>
                                <label for="lib-root-2">Other: </label>
                                <input type="text" name="lib-root-txt" value="<?=$lib_root ->meta_txt?>">
                            </div>
                        </div>
                        <div>Object3d Libraries:</div>
                        <div>
                            <textarea name="lib-obj3d" cols="30" rows="10"><?=$lib_obj3d?></textarea>
                        </div>
                        <div>Texture Library:</div>
                        <div>
                            <input type="text" name="lib-tex" value="<?=$lib_tex?>">
                        </div>
                        <div>Material Library:</div>
                        <div>
                            <input type="text" name="lib-mat" value="<?=$lib_mat?>">
                        </div>
                        <div>Forcefield Library:</div>
                        <div>
                            <input type="text" name="lib-ff" value="<?=$lib_ff?>">
                        </div>
                        <div>Animation Library:</div>
                        <div>
                            <input type="text" name="lib-anim" value="<?=$lib_anim?>">
                        </div>
                    </div>
                </div>
                <div class="section">
                    <div class="title">Scene Files</div>
                    <div class="fields">
                        <div>Upload URL:</div>
                        <?php
                            $checked = ['', ''];
                            $checked[$files_up ->meta_value] = ' checked';
                        ?>
                        <div>
                            <div>
                                <input type="radio" id="files-up-0" name="files-up" value="0"<?=$checked[0]?>>
                                <label for="files-up-0">Local</label>
                            </div>
                            <div class="line-input">
                                <input type="radio" id="files-up-1" name="files-up" value="1"<?=$checked[1]?>>
                                <label for="files-up-1">Other: </label>
                                <input type="text" name="files-up-txt" value="<?=$files_up ->meta_txt?>">
                            </div>
                        </div>
                        <div>Download URL:</div>
                        <?php
                            $checked = ['', '', ''];
                            $checked[$files_down ->meta_value] = ' checked';
                        ?>
                        <div>
                            <div>
                                <input type="radio" id="files-down-0" name="files-down" value="0"<?=$checked[0]?>>
                                <label for="files-down-0">Local</label>
                            </div>
                            <div>
                                <input type="radio" id="files-down-1" name="files-down" value="1"<?=$checked[1]?>>
                                <label for="files-down-1">Gallery</label>
                            </div>
                            <div class="line-input">
                                <input type="radio" id="files-down-2" name="files-down" value="2"<?=$checked[2]?>>
                                <label for="files-down-2">Other: </label>
                                <input type="text" name="files-down-txt" value="<?=$files_down ->meta_txt?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section" style="text-align: center; margin-bottom: 10px;">
                    <button type="submit" class="button-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="page-info">
        Current user can not edit m3d scenes. Use the role "m3d_editor" or the capability "edit_m3d_scene".
    </div>
    <?php endif ?>
    <div class="editor-wrap">
        <div id="m3d-editor"></div>
        <script src="<?=plugin_dir_url( __FILE__ )?>js/config.js"></script>
        <script src="<?=plugin_dir_url( __FILE__ )?>js/material3deditor.js"></script>
        <script>
            MATERIAL3DEDITOR_CONFIG.libraries = {
                root: '<?php
                    if($lib_root ->meta_value == 0){
                        echo M3DWP_URL;
                    }
                    else if($lib_root ->meta_value == 1){
                        echo M3D_NET_HOME;
                    }
                    else{
                        echo $lib_root ->meta_txt;
                    }
                ?>',
                object3d: <?=$lib_obj3d?>,
                texture: '<?=$lib_tex?>',
                material: '<?=$lib_mat?>',
                forceField: '<?=$lib_ff?>',
                animation: '<?=$lib_anim?>'
            };
            MATERIAL3DEDITOR_CONFIG.editor = {
                files: {
                    "upload_url": "<?php
                        if($files_up ->meta_value == 0){//local
                            echo admin_url('admin-ajax.php').'?action=m3d_save_scene&wpnonce='.esc_attr(wp_create_nonce('m3d_save_scene'));
                        }
                        else{//other
                            echo $files_up->meta_txt;
                        }                   
                    ?>",
                    "download_url": "<?php
                        if($files_down ->meta_value == 0){//local
                            echo admin_url('admin-ajax.php').'?action=m3d_load_scene';
                        }
                        else if($files_down ->meta_value == 1){//gallery
                            echo M3D_NET_HOME.'ajax.php?action=load';
                        }
                        else{//other
                            echo $files_down->meta_txt;
                        }                   
                    ?>"
                }
            };
            var container = document.getElementById('m3d-editor');
            Material3dEditor.run(container);
            <?php
            if($filename):
            ?>
            var old_download_url = Material3dEditor.files.download_url;
            Material3dEditor.files.download_url = '<?=$where === 'gallery' ? (M3D_NET_HOME."ajax.php?action=load")
                    : (admin_url('admin-ajax.php')."?action=m3d_load_scene") ?>';
            Material3dEditor.loadScene('<?=$filename?>', 1, true);
            Material3dEditor.files.download_url = old_download_url;
            <?php endif ?>
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
        </script>
    </div>
</div>