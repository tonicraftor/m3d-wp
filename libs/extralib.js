
const Lines = {
    info: {
        'Lines': [
            {
                type: 'dataset',
                label: 'path',
                options: ['points3d'],
                default: '-1,0.8,0.5, -0.6,1,0.2, 0.6,-1,-0.2, 1,-0.8,-0.5',
                desc: 'An array of 3d points',
            },
            {
                type: 'color',
                label: 'Color',
                default: 0,
                desc: 'Color of the lines'
            },
            {
                type: 'checkbox',
                label: 'Dashed',
                default: false,
                desc: 'If true, the lines are dashed'
            },
            {
                type: 'float',
                label: 'Dash size',
                default: 0.3,
                desc: 'The size of the dash. This is both the gap with the stroke. If not dashed, this will be ignored.'
            },
            {
                type: 'float',
                label: 'Gap size',
                default: 0.1,
                desc: 'The size of the gap. If not dashed, this will be ignored.'
            },
            {
                type: 'float',
                label: 'Scale',
                default: 1,
                desc: 'The scale of the dashed part of a line. If not dashed, this will be ignored.'
            },
        ],
    },
    icon: 'icons/lines.svg',
    create: () => new THREE.Line(),
    update: (object, params) => {
        const {Lines: lines} = params;
        if(!lines) return;
        const [path, color, dashed, dashSize, gapSize, scale] = lines;
        const vecs = [];
        for(let i = 2; i < path.length; i += 3){
            vecs.push(new THREE.Vector3(path[i-2], path[i-1], path[i]));
        }
        const geometry = new THREE.BufferGeometry().setFromPoints( vecs );
        const material = dashed ? new THREE.LineDashedMaterial({color, dashSize, gapSize, scale})
            : new THREE.LineBasicMaterial({color});
        object.geometry = geometry;
        object.material = material;
        if(dashed) object.computeLineDistances();
    }
};

const Sprite = {
    info: {
        'Sprite': [
            {
                type: 'color',
                label: 'Color',
                default: 0,
                desc: 'The color of the sprite'
            },
            {
                type: 'texture',
                label: 'Texture',
                default: '',
                desc: 'The texture of the sprite'
            },
            {
                type: 'float',
                label: 'Rotation',
                default: 0,
                desc: 'The rotation of the sprite in radians'
            },
            {
                type: 'checkbox',
                label: 'Size attenuation',
                default: true,
                desc: 'Whether the size of the sprite is attenuated by the camera depth. (Perspective camera only.)'
            },
        ],
    },
    icon: 'icons/sprite.svg',
    create: () => new THREE.Sprite(),
    update: (object, params) => {
        const {Sprite: sprite} = params;
        if(!sprite)return;
        const matpars = {
            color: new THREE.Color(sprite[0]),
            rotation: sprite[2],
            sizeAttenuation: sprite[3]
        };
        const map = sprite[1];
        if(map) matpars.map = map;
        object.material = new THREE.SpriteMaterial(matpars);
    }
};

const ExtraLib = {
    title: 'Extra',
    types: {
        Lines,
        Sprite,
    }
};

export default ExtraLib;