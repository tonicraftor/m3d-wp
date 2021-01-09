
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
    },
    play: function(filename, bLocal){
        const wrap = document.getElementById('player-wrap');
        wrap.classList.add('show');
        m3d_player.filename = filename;
        m3d_player.bLocal = bLocal;
        m3d_player.reset();
        m3d_player.play();
    },
    edit: function(filename, where){
        window.location.href = filename + (where ? ('&where=' + where) : '');
    },
    getSelIds: function(){
        if(this.selection.length === 0) return '';
        const rows = this.table_rows;
        return this.selection.map(idx => rows[idx].getAttribute('rowid')).join('_');
    },
    doAjax: function(action, nonce, ids){
        if(!ids)return;
        var xhr = new XMLHttpRequest();
        var url = M3DHOST.ajax + '?action='+ action + '&wpnonce=' + nonce + '&ids=' + ids;
        xhr.open('GET', url, true);
        xhr.onload = () => {
            window.location.reload();
        };
        xhr.send();
    },
    trash: function(id, nonce){
        this.doAjax('m3d_trash_scene', nonce, id);
    },
    trashAll: function(nonce){
        this.doAjax('m3d_trash_scene', nonce, this.getSelIds());
    },
    del: function(id, nonce){
        this.doAjax('m3d_del_scene', nonce, id);
    },
    delAll: function(nonce){
        this.doAjax('m3d_del_scene', nonce, this.getSelIds());
    },
    restore: function(id, nonce){
        this.doAjax('m3d_restore_scene', nonce, id);
    },
    restoreAll: function(nonce){
        this.doAjax('m3d_restore_scene', nonce, this.getSelIds());
    },
    search: function(pgquery){
        const searchinput = document.getElementById('m3d_search_input');
        window.location.href = pgquery + '&search=' + encodeURIComponent(searchinput.value);
    },
    goPage: function(pgquery){
        console.log(pgquery);
        window.location.href = pgquery;
    }
};
var m3d_player = {
    filename: '',
    bLocal: true,
    reset: function(){
        this.options = {
            width: [true, '600'],
            height: [true, '300'],
            background: [false, '0xffffff', ''],
            environment: [false, ''],
            camera: [false, ''],
            fog: [false, '0xffffff', '1.0', '1000.0'],
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
        const url = this.bLocal ? (M3DHOST.ajax + '?action=m3d_load_scene&filename=') : (M3DHOST.m3dnet + 'ajax.php?action=load&filename=');
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