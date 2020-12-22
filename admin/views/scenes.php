<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    if ( ! is_admin() ) {
        exit;
    }

    $PGLIMIT = 2;
    $TabNames = ['Scene List', 'Trashed', 'Gallery'];
    $OrderFields = ['filename', 'author', 'updateTime'];
    
    $get_arr = filter_input_array(INPUT_GET);
    if(!$get_arr) $get_arr = [];
    $tabidx = $get_arr['tab'] ?? 0;
    $search = $get_arr['search'] ?? '';
    $pgidx = intval($get_arr['pgidx'] ?? 0);
    
    $orderby = $get_arr['orderby'] ?? 0;
    $orderfield = $OrderFields[$orderby];
    $asc = ($get_arr['asc'] ?? '') !== 'false';

    $orderhead = ['Name', 'Author', 'Date'];
    for($i = 0; $i < 3; $i++){
        $tstr = $orderby == $i ? ($asc ? 'asc' : 'desc') : '';
        $orderhead[$i] = "<a class=\"order $tstr\" onclick=\"m3d_scenelist.goOrder($i, '$tstr')\">$orderhead[$i]</a>";
    }

    $editor_url = menu_page_url('admin-m3d-editor.php', false);
    $scenes_url = menu_page_url('admin-m3d-scenes.php', false);

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
        //fetch data with curl
        $ch = curl_init(M3D_NET_HOME."ajax.php?action=info&limit=$PGLIMIT&pgidx=$pgidx&orderby=$orderfield".($asc ? '' : '&asc=false')."&search=$search");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        if(curl_errno($ch)){
            $data = '';
        }
        curl_close($ch);

        if($data){
            $data = json_decode($data);
            $totalcount = $data->total;
            $pgcount = $totalcount ? (floor(($totalcount - 1)/$PGLIMIT) + 1) : 0;
            if($pgidx >= $pgcount) $pgidx = $pgcount - 1;
            $results = $data->results;
            $rcount = count($results);
        }
        else{
            $totalcount = 0;
            $pgcount = 0;
            $rcount = 0;
            $results = [];
        }
    }
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Material3d Scenes</h1>
    <div class="m3d-page-info">
    <?php if($search): ?>
    Search results for "<?=$search?>": <span id="m3d-search-res"><?=$totalcount?></span> items.
    <?php endif ?>
    </div>
    
    <div class="m3d-scenelist">
        <div class="tabs">
            <?php for($i = 0; $i < 3; $i++): ?>
            <button onclick="m3d_scenelist.goTab(<?=$i?>)"<?=$tabidx == $i ? ' class="active"' : ''?>><?=$TabNames[$i]?></button>
            <?php endfor ?>
        </div>
        <div class="btns">
            <?php if($can_edit):
            if($tabidx == 0): ?>
            <a href="<?=$editor_url?>" class="action-btn">
                <img src="<?=M3DWP_URL.'imgs/add.svg'?>" alt="add">
            </a>
            <a onclick="m3d_scenelist.trashAll()" class="action-btn">
                <img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash all">
            </a>
            <?php elseif($tabidx == 1): ?>
            <a onclick="m3d_scenelist.restoreAll()" class="action-btn">
                <img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore all">
            </a>
            <a onclick="m3d_scenelist.delAll()" class="action-btn">
                <img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete all">
            </a>
            <?php endif;
            endif;
            ?>
            <input type="text" id="m3_search_input" value="<?=$search?>">
            <a onclick="m3d_scenelist.search()" class="action-btn">
                <img src="<?=M3DWP_URL.'imgs/search.svg'?>" alt="search">
            </a>
        </div>
        <table class="scene-table wp-list-table widefat fixed striped table-view-list posts">
            <thead>
                <tr>
                    <th><?=$orderhead[0]?></th>
                    <th>Action</th>
                    <?php if($tabidx == 0): ?>
                    <th>Shortcode <a class="m3d-help-btn" href="<?=menu_page_url('admin-m3d-help.php', false)?>">?</a></th>
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
                        <a onclick="m3d_scenelist.edit('<?=$val->filename?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit">
                        </a>
                        <a onclick="m3d_scenelist.trash('<?=$val->id?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash">
                        </a>
                        <?php endif ?>
                        <a onclick="m3d_scenelist.play('<?=$val->filename?>', true)" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play">
                        </a>
                        <?php elseif($tabidx == 1):
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.restore('<?=$val->id?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore">
                        </a>
                        <a onclick="m3d_scenelist.del('<?=$val->id?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete">
                        </a>
                        <?php endif ?>
                        <?php else:
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.edit('<?=$val->filename?>', 'gallery')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit">
                        </a>
                        <?php endif ?>
                        <a onclick="m3d_scenelist.play('<?=$val->filename?>', false)" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play">
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
        </table>
        <?php if($pgcount): ?>
        <div class="pagination">
            <div><?=$totalcount?> items</div>
            <button <?=$pgidx >= 5 ? 'onclick="m3d_scenelist.goPage('.($pgidx - 5).')"' : 'disabled'?>>«</button>
            <button <?=$pgidx > 0 ? 'onclick="m3d_scenelist.goPage('.($pgidx - 1).')"' : 'disabled'?>>&lt;</button>
            <input type="number" id="m3_page_index" value="<?=$pgidx + 1?>"> of <?=$pgcount?>
            <button <?=($pgidx + 1) < $pgcount ? 'onclick="m3d_scenelist.goPage('.($pgidx + 1).')"' : 'disabled'?>>&gt;</button>
            <button <?=($pgidx + 5) < $pgcount ? 'onclick="m3d_scenelist.goPage('.($pgidx + 5).')"' : 'disabled'?>>»</button>
        </div>
        <?php endif ?>
    </div>
    <div id="player"></div>
</div>
<script>
    var m3d_scenelist = {
        selection: [],
        table_rows: [],
        init: function(){
            const list = document.querySelector('.m3d-scenelist > table > tbody');
            this.table_rows = list.children;
            const rows = this.table_rows;
            for(let i = 0; i < rows.length; i++){
                rows[i].onclick = (e) => {
                    let selection = this.selection;
                    selection.forEach(idx => rows[idx].classList.remove('active'));
                    if(e.shiftKey && selection.length > 0){
                        let start = selection[0];
                        const cb = start < i ? (() => start++) : (() => start--);
                        selection = Array(Math.abs(i - start) + 1).fill(0).map(cb);
                    }
                    else if(e.ctrlKey){
                        const j = selection.indexOf(i);
                        j >= 0 ? selection.splice(j, 1) : selection.push(i);
                    }
                    else selection = [i];
                    selection.forEach(idx => rows[idx].classList.add('active'));
                    this.selection = selection;
                }
                rows[i].onmousedown = (e) => {
                    if(e.shiftKey && this.selection.length > 0){
                            const winsel = window.getSelection();
                            winsel.empty();
                    }
                }
            }
            
            const pgidx_input = document.getElementById('m3_page_index');
            if(pgidx_input){
                pgidx_input.addEventListener("keyup", (event) => {
                    if (event.key === "Enter") {
                        event.preventDefault();
                        let pgidx = parseInt(event.target.value);
                        if(!isNaN(pgidx)){
                            if(pgidx > <?=$pgcount?>) pgidx = <?=$pgcount?>;
                            if(pgidx < 1) pgidx = 1;
                            this.goPage(pgidx - 1);
                        }
                    }
                });
            }
            const searchinput = document.getElementById('m3_search_input');
            searchinput.addEventListener("keyup", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                    this.search();
                }
            });
        },
        play: function(filename, bLocal){
            const container = document.getElementById('player');
            const url = bLocal ? '<?=admin_url('admin-ajax.php')?>?action=m3d_load_scene&filename=' : '<?=M3D_NET_HOME?>ajax.php?action=load&filename=';
            Material3dPlayer.play(container, url + filename);
        },
        <?php if($can_edit): ?>
        edit: function(filename, where){
            window.location.href = '<?=$editor_url?>' + (where ? ('&where=' + where) : '') + '&filename=' + filename;
        },
        getSelIds: function(){
            if(this.selection.length === 0) return '';
            const rows = this.table_rows;
            return this.selection.map(idx => rows[idx].getAttribute('rowid')).join('_');
        },
        doAjax: function(action, nonce, ids){
            if(!ids)return;
            var xhr = new XMLHttpRequest();
            var url = '<?=admin_url('admin-ajax.php')?>?action='+ action + '&wpnonce=' + nonce + '&ids=' + ids;
            xhr.open('GET', url, true);
            xhr.onload = () => {
                //window.location.reload();
                console.log(xhr.response);
            };
            xhr.send();
        },
        <?php if($tabidx == 0): ?>
        trash: function(id){
            this.doAjax('m3d_trash_scene', '<?=$nonce_trash?>', id);
        },
        trashAll: function(){
            this.doAjax('m3d_trash_scene', '<?=$nonce_trash?>', this.getSelIds());
        },
        <?php elseif($tabidx == 1): ?>
        del: function(id){
            this.doAjax('m3d_del_scene', '<?=$nonce_del?>', id);
        },
        delAll: function(){
            this.doAjax('m3d_del_scene', '<?=$nonce_del?>', this.getSelIds());
        },
        restore: function(id){
            this.doAjax('m3d_restore_scene', '<?=$nonce_restore?>', id);
        },
        restoreAll: function(){
            this.doAjax('m3d_restore_scene', '<?=$nonce_restore?>', this.getSelIds());
        },
        <?php endif ?>
        <?php endif ?>
        search: function(){
            const searchinput = document.getElementById('m3_search_input');
            window.location.href = '<?=$scenes_url?>&tab=<?=$tabidx?>&search=' + encodeURIComponent(searchinput.value);
        },
        goTab: function(index){
            window.location.href = '<?=$scenes_url?>&tab=' + index;
        },
        goPage: function(index){
            window.location.href = '<?=$scenes_url?>&tab=<?=$tabidx?>&pgidx=' + index + '&orderby=<?=$orderby?><?=$asc ? '' : '&asc=false'?>&search=<?=$search?>';
        },
        goOrder: function(orderby, asc){
            window.location.href = '<?=$scenes_url?>&tab=<?=$tabidx?>&orderby=' + orderby + '&asc=' + (asc !== 'asc') + '&search=<?=$search?>';
        }
    };
    m3d_scenelist.init();    
</script>