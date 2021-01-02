

document.addEventListener("DOMContentLoaded", function(event) { 
    const scenes = document.querySelectorAll('m3dscene');
    scenes.forEach(item => {
        const container = document.createElement('div');
        container.classList.add('m3d-scene');
        item.appendChild(container);
        const name = item.getAttribute('name');
        if(!name)return;
        const options = {};
        if(item.hasAttribute('width') || item.hasAttribute('height')){
            const size = {width: item.getAttribute('width'), height: item.getAttribute('height')};
            if(!size.width)size.width = 200;
            if(!size.height)size.height = 100;
            options.size = size;
        }
        if(item.hasAttribute('background')){
            const bg = item.getAttribute('background');
            if(bg === ''){
                options.background = undefined;
            }
            else{
                const color = parseInt(bg);
                options.background = isNaN(color) ? bg : color;
            }
        }
        if(item.hasAttribute('environment')){
            const env = item.getAttribute('environment');
            options.environment = env === '' ? undefined : env;
        }
        if(item.hasAttribute('camera')){
            const camera = item.getAttribute('camera');
            options.camera = camera === '' ? undefined : camera;
        }
        if(item.hasAttribute('fog')){
            const fog = item.getAttribute('fog');
            if(fog === ''){
                options.fog = undefined;
            }
            else {
                const pars = fog.split(',').map((p, i) => i === 0 ? parseInt(p) : parseFloat(p));
                if(pars.length === 3){
                    options.fog = {
                        color: isNaN(pars[0]) ? 0 : pars[0],
                        near: isNaN(pars[1]) ? 1.0 : pars[1],
                        far: isNaN(pars[2]) ? 1000.0 : pars[2]
                    };
                }
            }
        }
        if(item.hasAttribute('vrsupport')){
            options.VRSupport = item.getAttribute('vrsupport') === 'true';
        }
        const sceneObj = Material3dPlayer.play(container, M3DWP_HOST.ajax + '?action=m3d_load_scene&filename=' + name, options);
        if(item.hasAttribute('onplay')){
            const func = item.getAttribute('onplay');
            if(window[func]){
                window[func](sceneObj);
            }
        }
    });
});