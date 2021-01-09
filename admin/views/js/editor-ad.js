
function loadEditor(fname, where){
    let lib_root;
    if( M3DE_CONFIG.lib_root.meta_value == 0){
        lib_root = M3DHOST.plugin;
    }
    else if(M3DE_CONFIG.lib_root.meta_value == 1){
        lib_root = M3DHOST.m3dnet;
    }
    else{
        lib_root = M3DE_CONFIG.lib_root.meta_txt;
    }

    const files_up = M3DE_CONFIG.files_up.meta_value == 0 ?
    (M3DHOST.ajax + '?action=m3d_save_scene&wpnonce=' + M3DE_SAVE_NONCE)
    : M3DE_CONFIG.files_up.meta_txt;

    let files_down;
    if(M3DE_CONFIG.files_down.meta_value == 0){//local
        files_down = M3DHOST.ajax + '?action=m3d_load_scene';
    }
    else if(M3DE_CONFIG.files_down.meta_value == 1){//gallery
        files_down = M3DHOST.m3dnet + 'ajax.php?action=load';
    }
    else{//other
        files_down = M3DE_CONFIG.files_down.meta_txt;
    }
    MATERIAL3DEDITOR_CONFIG.libraries = {
        root: lib_root,
        object3d: M3DE_CONFIG.lib_obj3d,
        texture: M3DE_CONFIG.lib_tex,
        material: M3DE_CONFIG.lib_mat,
        forceField: M3DE_CONFIG.lib_ff,
        animation: M3DE_CONFIG.lib_anim
    };
    MATERIAL3DEDITOR_CONFIG.editor = {
        files: {
            'upload_url': files_up,
            'download_url': files_down
        }
    };
    const container = document.getElementById('m3d-editor');
    Material3dEditor.run(container);
    if(fname){
        const old_download_url = Material3dEditor.files.download_url;
        Material3dEditor.files.download_url = where === 'gallery' ? (M3DHOST.m3dnet + 'ajax.php?action=load')
            : (M3DHOST.ajax + '?action=m3d_load_scene');
        Material3dEditor.loadScene(fname, 1, true);
        Material3dEditor.files.download_url = old_download_url;
    }
}
function onSaveScene(){
    const filenameEle = document.getElementById('m3d-filename');
    let filename = '';
    if(filenameEle){
        filename = filenameEle.value;
    }
    if(!filename){
        alert('Invalid or empty file name!');
        return;
    }
    Material3dEditor.saveScene(filename, 1);
}
function toggleSettings(){
    const title = document.getElementById('m3d-editor-title');
    title.classList.toggle('hide');
}
function initSettings(){
    const title = document.getElementById('m3d-editor-title');
    let ele = title.querySelector('#lib-root-' + M3DE_CONFIG.lib_root.meta_value);
    if(ele){ele.checked = true;}
    ele = title.querySelector('#lib-root-txt');
    if(ele){ele.value = M3DE_CONFIG.lib_root.meta_txt;}
    ele = title.querySelector('#lib-obj3d');
    if(ele){
        let str = JSON.stringify(M3DE_CONFIG.lib_obj3d);
        str = str.replaceAll(',', ',\n');
        ele.innerHTML = str;
    }
    ele = title.querySelector('#lib-tex');
    if(ele){ele.value = M3DE_CONFIG.lib_tex;}
    ele = title.querySelector('#lib-mat');
    if(ele){ele.value = M3DE_CONFIG.lib_mat;}
    ele = title.querySelector('#lib-ff');
    if(ele){ele.value = M3DE_CONFIG.lib_ff;}
    ele = title.querySelector('#lib-anim');
    if(ele){ele.value = M3DE_CONFIG.lib_anim;}
    ele = title.querySelector('#files-up-' + M3DE_CONFIG.files_up.meta_value);
    if(ele){ele.checked = true;}
    ele = title.querySelector('#files-up-txt');
    if(ele){ele.value = M3DE_CONFIG.files_up.meta_txt;}
    ele = title.querySelector('#files-down-' + M3DE_CONFIG.files_down.meta_value);
    if(ele){ele.checked = true;}
    ele = title.querySelector('#files-down-txt');
    if(ele){ele.value = M3DE_CONFIG.files_down.meta_txt;}
}