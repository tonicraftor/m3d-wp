<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$filename = filter_input(INPUT_GET, 'filename');
$where = filter_input(INPUT_GET, 'where');
$config_libs = array(
    'root' => Material3dWP::get_meta('config_lib_root'),
    'object3d' => Material3dWP::get_meta('config_lib_object3d'),
    'texture' => Material3dWP::get_meta('config_lib_texture'),
    'material' => Material3dWP::get_meta('config_lib_material'),
    'forceField' => Material3dWP::get_meta('config_lib_forceField'),
    'animation' => Material3dWP::get_meta('config_lib_animation')
);
$config_files = array(
    'upload_url' => Material3dWP::get_meta('config_files_upload'),
    'download_url' => Material3dWP::get_meta('config_files_download')
);
?>
<h1 class="wp-heading-inline">Material3d Editor</h1>
<?php
if(current_user_can( 'edit_m3d_scene' )):
?>
<div>
<input type="text" id="m3d-filename" value="<?=($filename ? $filename : '')?>">
<button type="button" onclick="onSaveScene()">Save</button>
</div>

<?php else: ?>
<div class="m3d-page-info">
    Current user can not edit m3d scenes. Use the role "m3d_editor" or the capability "edit_m3d_scene".
</div>
<?php endif ?>
<div>
    <div id="m3d-editor"></div>
    <script src="<?=plugin_dir_url( __FILE__ )?>js/config.js"></script>
    <script src="<?=plugin_dir_url( __FILE__ )?>js/material3deditor.js"></script>
    <script>
        MATERIAL3DEDITOR_CONFIG.libraries = {
            root: '<?php
                $libs_root = $config_libs['root'];
                if($libs_root ->meta_value == 0){
                    echo M3DWP_URL;
                }
                else if($libs_root ->meta_value == 1){
                    echo M3D_NET_HOME;
                }
                else{
                    echo $libs_root ->meta_txt;
                }
            ?>',
            object3d: <?=$config_libs['object3d']->meta_txt?>,
            texture: '<?=$config_libs['texture']->meta_txt?>',
            material: '<?=$config_libs['material']->meta_txt?>',
            forceField: '<?=$config_libs['forceField']->meta_txt?>',
            animation: '<?=$config_libs['animation']->meta_txt?>'
        };
        MATERIAL3DEDITOR_CONFIG.editor = {
            files: {
                "upload_url": "<?php
                    $editor_upload = $config_files['upload_url'];
                    if($editor_upload ->meta_value == 0){//local
                        echo admin_url('admin-ajax.php').'?action=m3d_save_scene&wpnonce='.esc_attr(wp_create_nonce('m3d_save_scene'));
                    }
                    else{//other
                        echo $editor_upload->meta_txt;
                    }                   
                ?>",
                "download_url": "<?php
                    $editor_download = $config_files['download_url'];
                    if($editor_download ->meta_value == 0){//local
                        echo admin_url('admin-ajax.php').'?action=m3d_load_scene';
                    }
                    else if($editor_download ->meta_value == 1){//gallery
                        echo M3D_NET_HOME.'ajax.php?action=load';
                    }
                    else{//other
                        echo $editor_download->meta_txt;
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
    </script>
</div>