<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    if ( ! is_admin() ) {
        exit;
    }

    $PGLIMIT = 20;
    $TabNames = ['Scene List', 'Trashcan', 'Gallery'];
    $OrderFields = ['filename', 'author', 'updateTime'];
    
    $get_arr = filter_input_array(INPUT_GET);
    if(!$get_arr) $get_arr = [];
    $tabidx = $get_arr['tab'] ?? 0;
    $search = $get_arr['search'] ?? '';
    $pgidx = intval($get_arr['pgidx'] ?? 0);
    
    $orderby = $get_arr['orderby'] ?? 0;
    $orderfield = $OrderFields[$orderby];
    $asc = ($get_arr['asc'] ?? '') !== 'false';

    $editor_url = menu_page_url('m3d-editor.php', false);
    $scenes_url = menu_page_url('m3d-scenes.php', false);
    $tab_url = "$scenes_url&tab=$tabidx";

    $orderhead = ['Name', 'Author', 'Date'];
    $pgquery = "onclick=\"m3d_scenelist.goPage('$tab_url&search=$search&orderby=";
    for($i = 0; $i < 3; $i++){
        $tstr = $orderby == $i ? ($asc ? 'asc' : 'desc') : '';
        $tstr1 = $pgquery.$i.($orderby == $i && $asc ? '&asc=false' : '');
        $orderhead[$i] = "<a class=\"order $tstr\" $tstr1')\">$orderhead[$i]</a>";
    }

    $can_edit = current_user_can( 'edit_m3d_scene' );

    if($tabidx != 2){
        $table_name = Material3dWP::$table_name;
        $meta_table_name = Material3dWP::$meta_table_name;
        global $wpdb;
        $staus =  $tabidx == 0 ? 1 : 0;
        if(!$search){
            $metakey = $tabidx == 0 ? 'scene_count' : 'trash_count';
            $totalcount = $wpdb->get_var("SELECT meta_value FROM $meta_table_name WHERE meta_key='$metakey'");
            if(!$totalcount) $totalcount = 0;
            $pgcount = $totalcount ? (floor(($totalcount - 1)/$PGLIMIT) + 1) : 0;
            if($pgidx >= $pgcount) $pgidx = $pgcount - 1;
            $conditions = ' ORDER BY '.$orderfield.($asc ? '' : ' DESC').' LIMIT '.$PGLIMIT.' OFFSET '.($PGLIMIT * $pgidx);
            $results = $wpdb->get_results("SELECT * FROM $table_name WHERE status = $staus".$conditions);
            $rcount = count($results);
        }
        else {
            $conditions = ' AND (filename LIKE %s OR author LIKE %s) ORDER BY '.$orderfield.($asc ? '' : ' DESC');
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE status = $staus".$conditions, array("%$search%", "%$search%")));
            $totalcount = count($results);
            $pgcount = $totalcount ? (floor(($totalcount - 1)/$PGLIMIT) + 1) : 0;
            if($pgidx >= $pgcount) $pgidx = $pgcount - 1;
            $offset = $PGLIMIT * $pgidx;
            $rcount = $totalcount - $offset;
            if( $rcount > $PGLIMIT) $rcount = $PGLIMIT;
            $results = array_slice($results, $offset, $rcount);
        }
        
        if($tabidx == 0){
            $nonce_trash = esc_attr(wp_create_nonce('m3d_trash_scene'));
        }
        else{
            $nonce_del = esc_attr(wp_create_nonce('m3d_del_scene'));
            $nonce_restore = esc_attr(wp_create_nonce('m3d_restore_scene'));
        }
    }
    else {
        $args = [
            'action' => 'info',
            'limit' => $PGLIMIT,
            'pgidx' => $pgidx,
            'orderby' => $orderfield,
            'search'=> $search
        ];
        if(!$asc) $args['asc'] = 'false';

        $data = wp_remote_get(M3D_NET_HOME."ajax.php?action=info&limit=$PGLIMIT&pgidx=$pgidx&orderby=$orderfield".($asc ? '' : '&asc=false')."&search=$search");

        if(wp_remote_retrieve_response_code($data) === 200){
            $data = wp_remote_retrieve_body($data);
            try{
                $data = json_decode($data);
            }
            catch(Exception $ex){
                $data = '';
            }
        }
        else {
            $data = '';
        }
        if($data){
            $totalcount = $data->total;
            $pgcount = $totalcount ? (floor(($totalcount - 1)/$PGLIMIT) + 1) : 0;
            if($pgidx >= $pgcount) $pgidx = $pgcount - 1;
            $results = $data->results;
            $rcount = count($results);
        }
        else {
            $totalcount = 0;
            $pgcount = 0;
            $rcount = 0;
            $results = [];
        }
    }

?>
<div class="m3d-admin-wrap">
    <h1 class="wp-heading-inline">Material3d Scenes</h1>
    <div class="m3d-page-info">
    <?php if($search): ?>
    Search results for "<?=$search?>": <span id="m3d-search-res"><?=$totalcount?></span> items.
    <?php endif ?>
    </div>
    
    <div class="m3d-scenelist <?=$tabidx == 1 ? 'trashcan' : ($tabidx == 2 ? 'gallery' : '')?>">
        <div class="tabs">
            <?php for($i = 0; $i < 3; $i++): ?>
            <button onclick="m3d_scenelist.goPage('<?=$scenes_url?>&tab=<?=$i?>')"<?=$tabidx == $i ? ' class="active"' : ''?>><?=$TabNames[$i]?></button>
            <?php endfor ?>
        </div>
        <div class="btns">
            <?php if($can_edit):
            if($tabidx == 0): ?>
            <a href="<?=$editor_url?>" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/add.svg'?>" alt="add" title="Add new">
            </a>
            <a onclick="m3d_scenelist.trashAll('<?=$nonce_trash?>')" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash all" title="Trash all selected">
            </a>
            <?php elseif($tabidx == 1): ?>
            <a onclick="m3d_scenelist.restoreAll('<?=$nonce_restore?>')" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore all" title="Restore all selected">
            </a>
            <a onclick="m3d_scenelist.delAll('<?=$nonce_del?>')" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete all" title="Delete all selected">
            </a>
            <?php endif;
            endif;
            ?>
            <input type="text" id="m3d_search_input" value="<?=$search?>" onchange="m3d_scenelist.search('<?=$tab_url?>')">
            <a onclick="m3d_scenelist.search('<?=$tab_url?>')" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/search.svg'?>" alt="search" title="Search">
            </a>
        </div>
        <table class="scene-table wp-list-table widefat striped table-view-list posts">
            <thead>
                <tr>
                    <th><?=$orderhead[0]?></th>
                    <th>Action</th>
                    <?php if($tabidx == 0): ?>
                    <th>Shortcode <a class="m3d-help-btn" href="<?=menu_page_url('m3d-help.php', false)?>">?</a></th>
                    <?php endif ?>
                    <th><?=$orderhead[1]?></th>
                    <th><?=$orderhead[2]?></th>
                </tr>
            </thead>
            <tbody>
            <?php for($i = 0; $i < $rcount; $i++):
                $val = $results[$i];
            ?>
                <tr rowid="<?=$val->id?>">
                    <td class="title">
                        <?php if($tabidx == 0): ?>
                        <a href="<?=$editor_url?>&filename=<?=$val->filename?>"><?=$val->filename?></a>
                        <?php else: ?>
                        <?=$val->filename?>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if($tabidx == 0):
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.edit('<?=$editor_url?>&filename=<?=$val->filename?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit" title="Edit">
                        </a>
                        <a onclick="m3d_scenelist.trash('<?=$val->id?>', '<?=$nonce_trash?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash" title="Trash">
                        </a>
                        <?php endif ?>
                        <a href="#player-wrap" onclick="m3d_scenelist.play('<?=$val->filename?>', true)" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play" title="Play">
                        </a>
                        <?php elseif($tabidx == 1):
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.restore('<?=$val->id?>', '<?=$nonce_restore?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore" title="Restore">
                        </a>
                        <a onclick="m3d_scenelist.del('<?=$val->id?>', '<?=$nonce_del?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete" title="Delete forever">
                        </a>
                        <?php endif ?>
                        <?php else:
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.edit('<?=$editor_url?>&filename=<?=$val->filename?>', 'gallery')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit" title="Edit">
                        </a>
                        <?php endif ?>
                        <a onclick="m3d_scenelist.play('<?=$val->filename?>', false)" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play" title="Play">
                        </a>
                        <?php endif ?>
                    </td>
                    <?php if($tabidx == 0): ?>
                    <td>[m3dscene name="<?=$val->filename?>"]</td>
                    <?php endif ?>
                    <td><?=$val->author?></td>
                    <td><?=$val->updateTime?></td>
                </tr>
            <?php endfor ?>
            </tbody>
            <tfoot>
                <tr><td colspan="5" style="font-size: 20px;">
                    <span><?=$totalcount?> items.</span>
                    <?php if($tabidx == 0):?>
                        <?php if($can_edit):?>
                            <?php if($totalcount === 0):?>
                                <span>Press <div class="action-btn static"><img src="<?=M3DWP_URL.'imgs/add.svg'?>" alt="add"></div>button to add a new scene.</span>
                            <?php else:?>
                                <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit"></span><span>Edit </span>
                                <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash"></span><span>Trash </span>
                            <?php endif;?>
                        <?php endif;?>
                        <?php if($totalcount !== 0):?>
                            <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play"></span><span>Play </span>
                        <?php endif;?>
                    <?php elseif($tabidx == 1):?>
                        <?php if($can_edit):?>
                        <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore"></span><span>Restore </span>
                        <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete"></span><span>Delete forever </span>
                        <?php endif;?>
                    <?php else:?>
                        <?php if($can_edit):?>
                        <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit"></span><span>Edit </span>
                        <?php endif;?>
                        <span class="action-btn static"><img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play"></span><span>Play </span>
                    <?php endif;?>
                </td></tr>
            </tfoot>
        </table>
        <?php if($pgcount):
            $pg_url = "$tab_url&orderby=$orderby&search=$search".($asc ? '' : '&asc=false').'&pgidx=';
            $pgquery = "onclick=\"m3d_scenelist.goPage('$pg_url";
        ?>
        <div class="pagination">
            <button <?=$pgidx >= 5 ? $pgquery.($pgidx - 5).'\')"' : 'disabled'?>>«</button>
            <button <?=$pgidx > 0 ? $pgquery.($pgidx - 1).'\')"' : 'disabled'?>>&lt;</button>
            <input type="number" id="m3_page_index" value="<?=$pgidx + 1?>" onchange="let pgidx=parseInt(this.value);if(!isNaN(pgidx)){if(pgidx<1)pgidx=1;m3d_scenelist.goPage('<?=$pg_url?>'+(pgidx-1));}"> of <?=$pgcount?>
            <button <?=($pgidx + 1) < $pgcount ? $pgquery.($pgidx + 1).'\')"' : 'disabled'?>>&gt;</button>
            <button <?=($pgidx + 5) < $pgcount ? $pgquery.($pgidx + 5).'\')"' : 'disabled'?>>»</button>
        </div>
        <?php endif ?>
    </div>
    <div id="player-wrap">
        <div class="shortcode"><span>Shortcode:</span><span id="m3d-play-sc"></span><span onclick="m3d_player.onCopySc()"><img src="<?=M3DWP_URL.'imgs/copy.svg'?>" alt="copy" title="Copy"></span></div>
        <div class="options">
            <div class="title">Options</div>
            <div class="content">
                <div>
                    <input type="checkbox" id="opt-w" checked onchange="m3d_player.setOption('width', 0, this.checked)"><label for="opt-w">Width</label>
                </div>
                <div><input type="number" id="opt-w-v" value="600" onchange="m3d_player.setOption('width', 1, this.value)"></div>
                <div>
                    <input type="checkbox" id="opt-h" checked onchange="m3d_player.setOption('height', 0, this.checked)"><label for="opt-h">Height</label>
                </div>
                <div><input type="number" id="opt-h-v" value="300" onchange="m3d_player.setOption('height', 1, this.value)"></div>
                <div>
                    <div><input type="checkbox" id="opt-bg" onchange="m3d_player.setOption('background', 0, this.checked)"><label for="opt-bg">Background</label></div>
                </div>
                <div>
                    <div><div>Color: <span class="color-bar"></span></div><input type="text" id="opt-bg-c" value="0xffffff" onchange="m3d_player.setOption('background', 1, this.value);m3d_player.setColor(this);"></div>
                    <div><div>Texture: </div><input type="text" id="opt-bg-t" value="" onchange="m3d_player.setOption('background', 2, this.value)"></div>
                </div>
                <div>
                    <input type="checkbox" id="opt-env" onchange="m3d_player.setOption('environment', 0, this.checked)"><label for="opt-env">Environment</label>
                </div>
                <div>
                    <input type="text" id="opt-env-v" value="" onchange="m3d_player.setOption('environment', 1, this.value)">
                </div>
                <div>
                    <input type="checkbox" id="opt-cam" onchange="m3d_player.setOption('camera', 0, this.checked)"><label for="opt-cam">Camera</label>
                </div>
                <div>
                    <input type="text" id="opt-cam-v" value="" onchange="m3d_player.setOption('camera', 1, this.value)">
                </div>
                <div>
                    <div><input type="checkbox" id="opt-fog" onchange="m3d_player.setOption('fog', 0, this.checked)"><label for="opt-fog">Fog</label></div>
                </div>
                <div>
                    <div><div>Color: <span class="color-bar"></span></div><input type="text" id="opt-fog-c" value="0xffffff" onchange="m3d_player.setOption('fog', 1, this.value);m3d_player.setColor(this);"></div>
                    <div><div>Near: </div><input type="number" id="opt-fog-n" value="1.0" onchange="m3d_player.setOption('fog', 2, this.value)"></div>
                    <div><div>Far: </div><input type="number" id="opt-fog-f" value="1000.0" onchange="m3d_player.setOption('fog', 3, this.value)"></div>
                </div>
                <div>
                    <input type="checkbox" id="opt-vr" onchange="m3d_player.setOption('vrsupport', 0, this.checked)"><label for="opt-vr">VRSupport:</label>
                </div>
                <div>
                    <input type="radio" id="opt-vr-t" name="opt-vr" checked onclick="m3d_player.setOption('vrsupport', 1, true)"><label for="opt-vr-t">true</label>
                    <input type="radio" id="opt-vr-f" name="opt-vr" onclick="m3d_player.setOption('vrsupport', 1, false)"><label for="opt-vr-f">false</label>
                </div>
            </div>
            <div class="btns">
                <button onclick="m3d_player.play()" class="button-primary">Apply</button>
                <button onclick="m3d_player.reset();m3d_player.play()" class="button-primary">Reset</button>
            </div>
        </div>
        <div class="main-part">
            <div class="container"></div>
        </div>
    </div>
</div>