<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$filename = filter_input(INPUT_GET, 'filename');
?>
<h1 class="wp-heading-inline">Material3d Editor</h1>
<?php
if(current_user_can( 'edit_m3d_scene' )):
    $config = $this->config;
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
                    <div>Settings <a class="m3d-help-btn" href="<?=menu_page_url('m3d-help.php', false)?>">?</a></div>
                    <div onclick="toggleSettings()">✕</div>
                </div>
                <div class="section">
                    <div class="title">Libraries</div>
                    <div class="fields">
                        <div>Root URL:</div>
                        <div>
                            <div>
                                <input type="radio" id="lib-root-0" name="lib-root" value="0">
                                <label for="lib-root-0">Local</label>
                            </div>
                            <div>
                                <input type="radio" id="lib-root-1" name="lib-root" value="1">
                                <label for="lib-root-1">Material3d.net</label>
                            </div>
                            <div class="line-input">
                                <input type="radio" id="lib-root-2" name="lib-root" value="2">
                                <label for="lib-root-2">Other: </label>
                                <input type="text" id="lib-root-txt" name="lib-root-txt">
                            </div>
                        </div>
                        <div>Object3d Libraries:</div>
                        <div>
                            <textarea name="lib-obj3d" id="lib-obj3d" cols="30" rows="10"></textarea>
                        </div>
                        <div>Texture Library:</div>
                        <div>
                            <input type="text" id="lib-tex" name="lib-tex">
                        </div>
                        <div>Material Library:</div>
                        <div>
                            <input type="text" id="lib-mat" name="lib-mat">
                        </div>
                        <div>Forcefield Library:</div>
                        <div>
                            <input type="text" id="lib-ff" name="lib-ff">
                        </div>
                        <div>Animation Library:</div>
                        <div>
                            <input type="text" id="lib-anim" name="lib-anim">
                        </div>
                    </div>
                </div>
                <div class="section">
                    <div class="title">Scene Files</div>
                    <div class="fields">
                        <div>Upload URL:</div>
                        <div>
                            <div>
                                <input type="radio" id="files-up-0" name="files-up" value="0">
                                <label for="files-up-0">Local</label>
                            </div>
                            <div class="line-input">
                                <input type="radio" id="files-up-1" name="files-up" value="1">
                                <label for="files-up-1">Other: </label>
                                <input type="text" id="files-up-txt" name="files-up-txt">
                            </div>
                        </div>
                        <div>Download URL:</div>
                        <div>
                            <div>
                                <input type="radio" id="files-down-0" name="files-down" value="0">
                                <label for="files-down-0">Local</label>
                            </div>
                            <div>
                                <input type="radio" id="files-down-1" name="files-down" value="1">
                                <label for="files-down-1">Gallery</label>
                            </div>
                            <div class="line-input">
                                <input type="radio" id="files-down-2" name="files-down" value="2">
                                <label for="files-down-2">Other: </label>
                                <input type="text" id="files-down-txt" name="files-down-txt">
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
    </div>
</div>