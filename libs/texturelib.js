
const TexWrappings = [
    THREE.RepeatWrapping,
    THREE.ClampToEdgeWrapping,
    THREE.MirroredRepeatWrapping,
];
const TexMagFilter = [
    THREE.NearestFilter,
    THREE.LinearFilter,
];
const TexMinFilter = [
    THREE.NearestFilter,
    THREE.LinearFilter,
    THREE.NearestMipmapNearestFilter,
    THREE.NearestMipmapLinearFilter,
    THREE.LinearMipmapNearestFilter,
    THREE.LinearMipmapLinearFilter,
];

const ImageTexture = {
    subtype: 'Image Texture',
    createInfo: {
        'Image texture': [
            {
                type: 'image',
                label: 'Image',
                default: '',
            },
        ],
    },
    updateInfo: {
        'options': [
            {
                type: 'select',
                label: 'U Wrap',
                options: ['Repeat', 'Clamp to edge', 'Mirror repeat'],
                default: 1,
            },
            {
                type: 'select',
                label: 'V Wrap',
                options: ['Repeat', 'Clamp to edge', 'Mirror repeat'],
                default: 1,
            },
            {
                type: 'select',
                label: 'Mag filter',
                options: ['Nearest', 'Linear'],
                default: 1,
            },
            {
                type: 'select',
                label: 'Min filter',
                options: ['Nearest', 'Linear', 'Nearest(mipmap nearest)',
                'Nearest(mipmap linear)', 'Linear(mipmap nearest)',
                'Linear(mipmap linear)',],
                default: 1,
            },
        ],
        'advanced': [
            {
                type: 'range',
                label: 'U offset',
                options: [0, 1, 0.01],
                default: 0,
            },
            {
                type: 'range',
                label: 'V offset',
                options: [0, 1, 0.01],
                default: 0,
            },
            {
                type: 'integer',
                label: 'U repeat',
                default: 1,
            },
            {
                type: 'integer',
                label: 'V repeat',
                default: 1,
            },
            {
                type: 'float',
                label: 'Rotation',
                default: 0,
            },
            {
                type: 'range',
                label: 'Center U',
                options: [0, 1, 0.01],
                default: 0,
            },
            {
                type: 'range',
                label: 'Center V',
                options: [0, 1, 0.01],
                default: 0,
            },
        ],
    },
    create: (params) => {
        const img = params['Image texture'][0];
        return (img && img.imgType === 'image')
        ? new THREE.TextureLoader().load(img.origin, (texture) => {texture.needsUpdate = true;}) : new THREE.Texture();
    },
    update: (texture, params) => {
        const {options, advanced} = params;
        if(options){
            texture.wrapS = TexWrappings[options[0]];
            texture.wrapT = TexWrappings[options[1]];
            texture.magFilter = TexMagFilter[options[2]];
            texture.minFilter = TexMinFilter[options[3]];
        }
        if(advanced){
            const [offsetX, offsetY, repeatX, repeatY, rotation, centerX, centerY] = advanced;
            texture.offset.set(offsetX, offsetY);
            texture.repeat.set(repeatX, repeatY);
            texture.rotation = rotation;
            texture.center.set(centerX, centerY);
        }
        if(texture.image)texture.needsUpdate =true;
    }
};

const CubeTexture = {
    subtype: 'Cube Texture',
    createInfo: {
        'Cube texture': [
            {
                type: 'image',
                label: 'Left',
                default: '',
            },
            {
                type: 'image',
                label: 'Right',
                default: '',
            },
            {
                type: 'image',
                label: 'Front',
                default: '',
            },
            {
                type: 'image',
                label: 'Back',
                default: '',
            },
            {
                type: 'image',
                label: 'Top',
                default: '',
            },
            {
                type: 'image',
                label: 'Bottom',
                default: '',
            },
        ],
    },
    updateInfo: (() => {
        const uinfo = {...ImageTexture.updateInfo};
        const optArr = [
            ...uinfo['options'],
            {
                type: 'radio',
                label: 'mapping',
                options: ['Reflection', 'Refraction'],
                default: 0,
            },
        ];
        optArr[3] = {
            ...optArr[3],
            options: ['Nearest', 'Linear'],
            default: 1
        };
        uinfo['options'] = optArr;
        return uinfo;
    })(),
    create: function(params) {
        const imgInfos = params['Cube texture'].map(img => img ? img.origin : '');
        const tex = new THREE.CubeTextureLoader().load(imgInfos, (texture) => {texture.needsUpdate = true;});
        return tex;
    },
    update: (texture, params) => {
        const {options} = params;
        if(options) {
            texture.mapping = [THREE.CubeReflectionMapping, THREE.CubeRefractionMapping][options[4]];
        }
        ImageTexture.update(texture, params);
    },
};

const VideoTexture = {
    subtype: 'Video Texture',
    createInfo: {
        'Video texture': [
            {
                type: 'string',
                label: 'Video id',
                default: '',
                desc: 'HTML video element id on the same page'
            },
        ],
    },
    updateInfo: {...ImageTexture.updateInfo},
    create: function(params) {
        const video = document.getElementById(params['Video texture'][0]);
        if(!video){
            console.error('Wrong video element id.');
            return new THREE.Texture();
        }
        return new THREE.VideoTexture( video );
    },
    update: (texture, params) => {
        ImageTexture.update(texture, params);
    },
};

const CanvasTexture = {
    subtype: 'Canvas Texture',
    createInfo: {
        'Canvas texture': [
            {
                type: 'string',
                label: 'Canvas id',
                default: '',
                desc: 'HTML canvas element id on the same page'
            },
        ],
    },
    updateInfo: {...ImageTexture.updateInfo},
    create: function(params) {
        const canvas = document.getElementById(params['Canvas texture'][0]);
        if(!canvas){
            console.error('Wrong canvas element id.');
            return new THREE.Texture();
        }
        return new THREE.CanvasTexture( canvas );
    },
    update: (texture, params) => {
        ImageTexture.update(texture, params);
    },
};

const TextureLib = {
    types: {
        [ImageTexture.subtype]: ImageTexture,
        [CubeTexture.subtype]: CubeTexture,
        [VideoTexture.subtype]: VideoTexture,
        [CanvasTexture.subtype]: CanvasTexture,
    }
};

export default TextureLib;