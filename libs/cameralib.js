
const PerspectiveCamera = {
    info: {
        'Perspective Camera': [
            {
                type: 'float',
                label: 'Field of view',
                default: 50,
                desc: 'Camera frustum vertical field of view, from bottom to top of view, in degrees'
            },
            {
                type: 'float',
                label: 'Aspect',
                default: 2,
                desc: 'Camera frustum aspect ratio, usually the canvas width / canvas height'
            },
            {
                type: 'float',
                label: 'Near plane',
                default: 0.1,
                desc: 'Camera frustum near plane'
            },
            {
                type: 'float',
                label: 'Far plane',
                default: 2000,
                desc: 'Camera frustum far plane'
            },
            {
                type: 'float',
                label: 'Zoom',
                default: 1,
                desc: 'Camera zoom factor'
            },
        ],
    },
    icon: 'icons/perscamera.svg',
    create: () => new THREE.PerspectiveCamera(),
    update: (camera, params) => {
        const p = params['Perspective Camera'];
        if(!p)return;
        const c = camera;
        c.fov = p[0];
        c.aspect = p[1];
        c.near = p[2];
        c.far = p[3];
        c.zoom = p[4];
        c.updateProjectionMatrix();
    }
};

const OrthographicCamera = {
    info: {
        'Orthographic Camera': [
            {
                type: 'float',
                label: 'Left plane',
                default: -12.7,
                desc: 'Camera frustum left plane'
            },
            {
                type: 'float',
                label: 'Right plane',
                default: 12.7,
                desc: 'Camera frustum right plane'
            },
            {
                type: 'float',
                label: 'Top plane',
                default: 6.14,
                desc: 'Camera frustum top plane'
            },
            {
                type: 'float',
                label: 'Bottom plane',
                default: -6.14,
                desc: 'Camera frustum bottom plane'
            },
            {
                type: 'float',
                label: 'Near plane',
                default: 0.1,
                desc: 'Camera frustum near plane'
            },
            {
                type: 'float',
                label: 'Far plane',
                default: 2000,
                desc: 'Camera frustum far plane'
            },
            {
                type: 'float',
                label: 'Zoom',
                default: 1,
                desc: 'Camera zoom factor'
            },
        ],
    },
    icon: 'icons/orthocamera.svg',
    create: () => new THREE.OrthographicCamera(-12.7, 12.7, 6.14, -6.14),
    update: (camera, params) => {
        const p = params['Orthographic Camera'];
        if(!p)return;
        const c = camera;
        c.left = p[0];
        c.right = p[1];
        c.top = p[2];
        c.bottom = p[3];
        c.near = p[4];
        c.far = p[5];
        c.zoom = p[6];
        c.updateProjectionMatrix();
    }
};

const CameraLib = {
    title: 'Camera',
    types: {
        PerspectiveCamera,
        OrthographicCamera,
    }
};

export default CameraLib;