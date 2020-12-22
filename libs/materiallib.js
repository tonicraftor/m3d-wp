
const SIDEOPTIONS = [THREE.FrontSide, THREE.BackSide, THREE.DoubleSide];

const Standard = {
    subtype: 'mesh',
    info: {
        'options': [
            {
                type: 'range',
                label: 'Metalness',
                options: [0, 1, 0.01],
                default: 0,
                desc: 'How much the material is like a metal. Non-metallic materials such as wood or stone use 0.0, metallic use 1.0',
            },
            {
                type: 'range',
                label: 'Roughness',
                options: [0, 1, 0.01],
                default: 0,
                desc: 'How rough the material appears. 0.0 means a smooth mirror reflection, 1.0 means fully diffuse',
            },
        ],
        'appearance': [
            {
                type: 'color',
                label: 'Color',
                default: 0xffffff,
            },
            {
                type: 'texture',
                label: 'Texture',
                default: '',
            },
            {
                type: 'texture',
                label: 'Environment Map',
                default: '',
                desc: 'Environment Map of the material',
            },
            {
                type: 'color',
                label: 'Emissive color',
                default: 0,
                desc: 'Emissive (light) color of the material, essentially a solid color unaffected by other lighting',
            },
            {
                type: 'range',
                label: 'Emissive intensity',
                options: [0, 1, 0.01],
                default: 1,
                desc: 'Intensity of the emissive light',
            },
            {
                type: 'range',
                label: 'Refraction ratio',
                options: [0, 1, 0.01],
                default: 0.98,
                desc: 'The ratio of the index of refraction (IOR) of the material to air, used with refraction environment map',
            },
        ],
        'common': [
            {
                type: 'radio',
                label: 'Surface',
                options: ['Smooth', 'Wireframe', 'Flat'],
                default: 0,
                desc: 'Surface render type',
            },
            {
                type: 'radio',
                label: 'Side',
                options: ['Front', 'Back', 'Both'],
                default: 2,
                desc: 'Which sides should be rendered',
            },
            {
                type: 'range',
                label: 'Opacity',
                options: [0, 1, 0.01],
                default: 1,
                desc: 'Opacity of the material',
            },
        ],
    },
    create: () => new THREE.MeshStandardMaterial(),
    update: (material, params) => {
        const {options, appearance, common} = params;
        if(options){
            [material.metalness, material.roughness] = options;
        }
        if(appearance){
            material.color.set(appearance[0]);
            material.map = appearance[1];
            material.envMap = appearance[2];
            material.emissive.set(appearance[3]);
            material.emissiveIntensity = appearance[4];
            material.refractionRatio = appearance[5];
        }
        if(common){
            material.wireframe  = common[0] === 1;
            material.flatShading = common[0] === 2;
            material.side = SIDEOPTIONS[common[1]];
            material.opacity = common[2];
            material.transparent = common[2] < 1;
        }
        material.needsUpdate = true;
    }
};

const Physical = {
    subtype: 'mesh',
    info: {
        'options': Standard.info.options,
        'advanced': [
            {
                type: 'range',
                label: 'Clearcoat',
                options: [0, 1, 0.01],
                default: 0,
                desc: 'Use clear coat for materials that have a thin translucent layer over the base layer',
            },
            {
                type: 'range',
                label: 'Clearcoat roughness',
                options: [0, 1, 0.01],
                default: 0,
                desc: 'Use clear coat for materials that have a thin translucent layer over the base layer',
            },
            {
                type: 'range',
                label: 'Reflectivity',
                options: [0, 1, 0.01],
                default: 0.5,
                desc: 'Degree of reflectivity',
            },
            {
                type: 'range',
                label: 'Transmission',
                options: [0, 1, 0.01],
                default: 0,
                desc: 'Degree of transmission (or optical transparency), used to model transparent or semitransparent plastic/glass materials',
            },
        ],
        'appearance': Standard.info.appearance,
        'common': Standard.info.common
    },
    create: () => new THREE.MeshPhysicalMaterial(),
    update: (material, params) => {
        const {advanced} = params;
        Standard.update(material, params);
        if(advanced){
            [material.clearcoat, material.clearcoatRoughness, material.reflectivity, material.transmission] = advanced;
            if(material.transmission > 0)material.transparent = true;
        }
    },
};

const Phong = {
    subtype: 'mesh',
    info: {
        'options': [
            {
                type: 'color',
                label: 'Specular color',
                default: 0x111111,
                desc: 'This defines the color of its shine'
            },
            {
                type: 'texture',
                label: 'Specular map',
                default: '',
                desc: 'The specular map value affects both how much the specular surface highlight contributes and how much of the environment map affects the surface'
            },
            {
                type: 'float',
                label: 'Shininess',
                default: 30,
                desc: 'How shiny the specular highlight is; a higher value gives a sharper highlight'
            },
            {
                type: 'range',
                label: 'Reflectivity',
                options: [0, 1, 0.01],
                default: 1,
                desc: 'How much the environment map affects the surface'
            },
        ],
        'appearance': Standard.info.appearance,
        'common': Standard.info.common
    },
    create: () => new THREE.MeshPhongMaterial(),
    update: (material, params) => {
        const {options} = params;
        Standard.update(material, {...params, options: undefined});
        if(options){
            material.specular.set(options[0]);
            material.specularMap = options[1];
            material.shininess = options[2];
            material.reflectivity = options[3];
        }
    },
};

const Lambert = {
    subtype: 'mesh',
    info: {
        'options': [
            {
                type: 'range',
                label: 'Reflectivity',
                options: [0, 1, 0.01],
                default: 1,
                desc: 'How much the environment map affects the surface'
            },
            {
                type: 'texture',
                label: 'Specular map',
                default: '',
            },
        ],
        'appearance': Standard.info.appearance,
        'common': Standard.info.common
    },
    create: () => new THREE.MeshLambertMaterial(),
    update: (material, params) => {
        const {options} = params;
        Standard.update(material, {...params, options: undefined});
        if(options){
            material.reflectivity = options[0];
            material.specularMap = options[1];
        }
    },
};

const MaterialLib = {
    types: {
        Standard,
        Physical,
        Phong,
        Lambert,
    }
};

export default MaterialLib;