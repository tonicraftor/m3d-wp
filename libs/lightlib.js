
const DirectionalLight = {
    info: {
        'directional light': [
            {
                type: 'color',
                label: 'Color',
                default: 0xffffff,
            },
            {
                type: 'range',
                label: 'Intensity',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'checkbox',
                label: 'Cast shadow',
                default: false,
            },
        ],
    },
    icon: 'icons/directionallight.svg',
    create: () => new THREE.DirectionalLight(),
    update: (light, params) => {
        const lightp = params['directional light'];
        if(lightp){
            light.color.set(lightp[0]);
            light.intensity = lightp[1];
            light.castShadow = lightp[2];
        }
    }
};

const PointLight = {
    info: {
        'point light': [
            {
                type: 'color',
                label: 'Color',
                default: 0xffffff,
            },
            {
                type: 'range',
                label: 'Intensity',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'float',
                label: 'Distance',
                default: 0,
                desc: 'Maximum range of the light'
            },
            {
                type: 'float',
                label: 'Decay',
                default: 1,
                desc: 'The amount the light dims along the distance of the light'
            },
            {
                type: 'checkbox',
                label: 'Cast shadow',
                default: false,
            },
        ],
    },
    icon: 'icons/pointlight.svg',
    create: () => new THREE.PointLight(),
    update: (light, params) => {
        const p = params['point light'];
        if(p){
            light.color.set(p[0]);
            light.intensity = p[1];
            light.distance = p[2];
            light.decay = p[3];
            light.castShadow = p[4];
        }
    }
};

const SpotLight = {
    info: {
        'spot light': [
            {
                type: 'color',
                label: 'Color',
                default: 0xffffff,
            },
            {
                type: 'range',
                label: 'Intensity',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'float',
                label: 'Distance',
                default: 0,
                desc: 'Maximum range of the light'
            },
            {
                type: 'float',
                label: 'Angle',
                default: Math.PI/2,
                desc: 'Maximum angle of light dispersion from its direction'
            },
            {
                type: 'range',
                label: 'Penumbra',
                options: [0, 1, 0.01],
                default: 0,
                desc: 'The factor of the spotlight cone that is attenuated due to penumbra'
            },
            {
                type: 'float',
                label: 'Decay',
                default: 1,
                desc: 'The amount the light dims along the distance of the light'
            },
            {
                type: 'checkbox',
                label: 'Cast shadow',
                default: false,
            },
        ],
    },
    icon: 'icons/spotlight.svg',
    create: () => new THREE.SpotLight(),
    update: (light, params) => {
        const p = params['spot light'];
        if(p){
            light.color.set(p[0]);
            light.intensity = p[1];
            light.distance = p[2];
            light.angle = p[3];
            light.penumbra = p[4];
            light.decay = p[5];
            light.castShadow = p[6];
        }
    }
};

const LightLib =  {
    title: 'Light',
    types: {
        DirectionalLight,
        PointLight,
        SpotLight,
    }
};

export default LightLib;