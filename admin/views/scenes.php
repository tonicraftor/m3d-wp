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
            <button onclick="m3d_scenelist.goTab(<?=$i?>)"<?=$tabidx == $i ? ' class="active"' : ''?>><?=$TabNames[$i]?></button>
            <?php endfor ?>
        </div>
        <div class="btns">
            <?php if($can_edit):
            if($tabidx == 0): ?>
            <a href="<?=$editor_url?>" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/add.svg'?>" alt="add" title="Add new">
            </a>
            <a onclick="m3d_scenelist.trashAll()" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash all" title="Trash all selected">
            </a>
            <?php elseif($tabidx == 1): ?>
            <a onclick="m3d_scenelist.restoreAll()" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore all" title="Restore all selected">
            </a>
            <a onclick="m3d_scenelist.delAll()" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete all" title="Delete all selected">
            </a>
            <?php endif;
            endif;
            ?>
            <input type="text" id="m3d_search_input" value="<?=$search?>">
            <a onclick="m3d_scenelist.search()" class="action-btn large">
                <img src="<?=M3DWP_URL.'imgs/search.svg'?>" alt="search" title="Search">
            </a>
        </div>
        <table class="scene-table wp-list-table widefat striped table-view-list posts">
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
                            <img src="<?=M3DWP_URL.'imgs/edit.svg'?>" alt="edit" title="Edit">
                        </a>
                        <a onclick="m3d_scenelist.trash('<?=$val->id?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/trash.svg'?>" alt="trash" title="Trash">
                        </a>
                        <?php endif ?>
                        <a href="#player-wrap" onclick="m3d_scenelist.play('<?=$val->filename?>', true)" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/play.svg'?>" alt="play" title="Play">
                        </a>
                        <?php elseif($tabidx == 1):
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.restore('<?=$val->id?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/restore.svg'?>" alt="restore" title="Restore">
                        </a>
                        <a onclick="m3d_scenelist.del('<?=$val->id?>')" class="action-btn">
                            <img src="<?=M3DWP_URL.'imgs/delete.svg'?>" alt="delete" title="Delete forever">
                        </a>
                        <?php endif ?>
                        <?php else:
                        if($can_edit): ?>
                        <a onclick="m3d_scenelist.edit('<?=$val->filename?>', 'gallery')" class="action-btn">
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
        <?php if($pgcount): ?>
        <div class="pagination">
            <button <?=$pgidx >= 5 ? 'onclick="m3d_scenelist.goPage('.($pgidx - 5).')"' : 'disabled'?>>«</button>
            <button <?=$pgidx > 0 ? 'onclick="m3d_scenelist.goPage('.($pgidx - 1).')"' : 'disabled'?>>&lt;</button>
            <input type="number" id="m3_page_index" value="<?=$pgidx + 1?>"> of <?=$pgcount?>
            <button <?=($pgidx + 1) < $pgcount ? 'onclick="m3d_scenelist.goPage('.($pgidx + 1).')"' : 'disabled'?>>&gt;</button>
            <button <?=($pgidx + 5) < $pgcount ? 'onclick="m3d_scenelist.goPage('.($pgidx + 5).')"' : 'disabled'?>>»</button>
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
                    <div><div>Color: <span class="color-bar"></span></div><input type="text" id="opt-bg-c" value="0x000000" onchange="m3d_player.setOption('background', 1, this.value);m3d_player.setColor(this);"></div>
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
                    <div><div>Color: <span class="color-bar"></span></div><input type="text" id="opt-fog-c" value="0x000000" onchange="m3d_player.setOption('fog', 1, this.value);m3d_player.setColor(this);"></div>
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
            const searchinput = document.getElementById('m3d_search_input');
            searchinput.addEventListener("keyup", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                    this.search();
                }
            });
        },
        play: function(filename, bLocal){
            const wrap = document.getElementById('player-wrap');
            wrap.classList.add('show');
            m3d_player.filename = filename;
            m3d_player.bLocal = bLocal;
            m3d_player.reset();
            m3d_player.play();
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
                window.location.reload();
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
            const searchinput = document.getElementById('m3d_search_input');
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
    var m3d_player = {
        filename: '',
        bLocal: true,
        reset: function(){
            this.options = {
                width: [true, '600'],
                height: [true, '300'],
                background: [false, '0x000000', ''],
                environment: [false, ''],
                camera: [false, ''],
                fog: [false, '0x000000', '1.0', '1000.0'],
                vrsupport: [false, true]
            };
            const wrap = document.getElementById('player-wrap');
            const sc = wrap.querySelector('#m3d-play-sc');
            if(sc){
                sc.innerHTML =  '[m3dscene name="' + this.filename + '" width="600" height="300"]';
            }
            const content = wrap.querySelector('.content');
            if(content){
                if(this.content){
                    content.innerHTML = this.content;
                }
                else {
                    this.content = content.innerHTML;
                }
            }
        },
        setOption: function(key, index, value){
            const options = this.options;
            options[key][index] = value;
            let str = '[m3dscene name="' + this.filename + '"';
            if(options.width[0]){
                str += ' width="' + options.width[1] + '"';
            }
            if(options.height[0]){
                str += ' height="' + options.height[1] + '"';
            }
            if(options.background[0]){
                if(options.background[2] !== ''){
                    str += ' background="' + options.background[2] + '"';
                }
                else if(options.background[1] !== ''){
                    str += ' background="' + options.background[1] + '"';
                }
                else {
                    str += ' background=""';
                }
            }
            if(options.environment[0]){
                str += ' environment="' + options.environment[1] + '"';
            }
            if(options.camera[0]){
                str += ' camera="' + options.camera[1] + '"';
            }
            if(options.fog[0]){
                if(options.fog[1] === '' && options.fog[2] === '' && options.fog[3] === ''){
                    str += ' fog=""';
                }
                else {
                    str += ' fog="' + options.fog[1] + ',' + options.fog[2] + ',' + options.fog[3] + '"';
                }
            }
            if(options.vrsupport[0]){
                str += ' vrsupport="' + options.vrsupport[1] + '"';
            }
            const sc = document.getElementById('m3d-play-sc');
            sc.innerHTML =  str + ']';
        },
        setColor: function(ele){
            let col = ele.value ? parseInt(ele.value) : 0xffffff;
            if(isNaN(col) || col > 0xffffff) col = 0xffffff;
            if(col < 0 ) col = 0;
            const prev = ele.previousElementSibling;
            if(prev){
                const bar = prev.querySelector('.color-bar');
                if(bar){
                    col = col.toString(16);
                    col = '#' + '0'.repeat(6 - col.length) + col;
                    bar.style.backgroundColor = col;
                }
            }
        },
        onCopySc: function(){
            const sc = document.getElementById('m3d-play-sc');
            navigator.clipboard.writeText(sc.innerHTML).then(function() {}, function() {alert('Text copy failed!');});
        },
        play: function(){
            if(this.sceneObj){
                this.sceneObj.animationController.stop();
            }
            const url = this.bLocal ? '<?=admin_url('admin-ajax.php')?>?action=m3d_load_scene&filename=' : '<?=M3D_NET_HOME?>ajax.php?action=load&filename=';
            const opt = this.options;
            const options = {};
            if(opt.width[0] || opt.height[0]){
                const size = {
                    width: opt.width[0] ? parseInt(opt.width[1]) : 200,
                    height: opt.height[0] ? parseInt(opt.height[1]) : 100
                };
                if(!isNaN(size.width) && !isNaN(size.height)) options.size = size;
            }
            if(opt.background[0]){
                let bg = opt.background[2];
                if(bg === ''){
                    bg = opt.background[1];
                    if(bg !== ''){
                        bg = parseInt(bg);
                        if(isNaN(bg)) bg = 0;
                    }
                    else {
                        bg = undefined;
                    }
                }
                options.background = bg;
            }
            if(opt.environment[0]){
                let env = opt.environment[1];
                if(env === '') env = undefined;
                options.environment = env;
            }
            if(opt.camera[0]){
                let cam = opt.camera[1];
                if(cam === '') cam = undefined;
                options.camera = cam;
            }
            if(opt.fog[0]){
                if(opt.fog[1] === '' && opt.fog[2] === '' && opt.fog[3] === ''){
                    options.fog = undefined;
                }
                else {
                    const fog = {
                        color: parseInt(opt.fog[1]),
                        near: parseFloat(opt.fog[2]),
                        far: parseFloat(opt.fog[3])
                    };
                    if(isNaN(fog.color)) fog.color = 0;
                    if(isNaN(fog.near)) fog.near = 1.0;
                    if(isNaN(fog.far)) fog.far = 1000.0;
                    options.fog = fog;
                }
                
            }
            if(opt.vrsupport[0]){
                options.VRSupport = opt.vrsupport[1];
            }
            const container = document.querySelector('#player-wrap > .main-part > .container');
            if(container){
                container.innerHTML = '';
                this.sceneObj = Material3dPlayer.play(container, url + this.filename, options);
            }
        }
    };
    m3d_scenelist.init();
    m3d_player.reset();
</script>