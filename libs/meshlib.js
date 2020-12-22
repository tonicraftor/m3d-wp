
const CreateDefaultMesh = () => {    
    const mesh = new THREE.Mesh();
    mesh.material = new THREE.MeshStandardMaterial({ wireframe: true, side: THREE.DoubleSide});
    return mesh;
};

const Sphere = {
    subtype: '3d',
    info: {
        'sphere': [
            {
                type: 'float',
                label: 'radius',
                default: 0.5,
            },
            {
                type: 'integer',
                label: 'Width Segments',
                default: 16,
                desc: 'number of horizontal segments. Minimum value is 3.',
            },
            {
                type: 'integer',
                label: 'Height Segments',
                default: 8,
                desc: 'number of vertical segments. Minimum value is 2.',
            },
            {
                type: 'float',
                label: 'phi start',
                default: 0,
                desc: 'horizontal starting angle',
            },
            {
                type: 'float',
                label: 'phi length',
                default: Math.PI * 2,
                desc: 'horizontal sweep angle size',
            },
            {
                type: 'float',
                label: 'theta start',
                default: 0,
                desc: 'vertical starting angle',
            },
            {
                type: 'float',
                label: 'theta length',
                default: Math.PI,
                desc: 'vertical sweep angle size',
            },
        ],
    },
    icon: 'icons/sphere.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const newParams = params['sphere'];
        mesh.geometry = new THREE.SphereBufferGeometry( ...newParams);
    },
};

const Box = {
    subtype: '3d',
    info: {
        'box': [
            {
                type: 'float',
                label: 'Width',
                default: 1,
            },
            {
                type: 'float',
                label: 'Height',
                default: 1,
            },
            {
                type: 'float',
                label: 'Depth',
                default: 1,
            },
            {
                type: 'integer',
                label: 'Width Segments',
                default: 1,
                desc: 'Number of segmented rectangular faces along the width of the sides',
            },
            {
                type: 'integer',
                label: 'Height Segments',
                default: 1,
                desc: 'Number of segmented rectangular faces along the height of the sides',
            },
            {
                type: 'integer',
                label: 'Depth Segments',
                default: 1,
                desc: 'Number of segmented rectangular faces along the depth of the sides',
            },
        ],
    },
    icon: 'icons/box.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const newParams = params['box'];
        mesh.geometry = new THREE.BoxBufferGeometry( ...newParams);
    },
};

const Cylinder = {
    subtype: '3d',
    info: {
        'cylinder': [
            {
                type: 'float',
                label: 'top radius',
                default: 0.5,
                desc: 'Radius of the cylinder at the top',
            },
            {
                type: 'float',
                label: 'bottom radius',
                default: 0.5,
                desc: 'Radius of the cylinder at the bottom',
            },
            {
                type: 'float',
                label: 'Height',
                default: 1,
                desc: 'Height of the cylinder',
            },
            {
                type: 'integer',
                label: 'radial segments',
                default: 8,
                desc: 'Number of segmented faces around the circumference of the cylinder',
            },
            {
                type: 'integer',
                label: 'height segments',
                default: 1,
                desc: 'Number of rows of faces along the height of the cylinder',
            },
            {
                type: 'checkbox',
                label: 'open caps',
                default: false,
                desc: 'A Boolean indicating whether the ends of the cylinder are open or capped',
            },
            {
                type: 'float',
                label: 'theta start',
                default: 0,
                desc: 'Start angle for first segment',
            },
            {
                type: 'float',
                label: 'theta length',
                default: 2 * Math.PI,
                desc: 'The central angle, often called theta, of the circular sector',
            },
        ],
    },
    icon: 'icons/cylinder.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['cylinder'];
        mesh.geometry = new THREE.CylinderBufferGeometry(...p);
    },
};

const Cone = {
    subtype: '3d',
    info: {
        'cone': [
            {
                type: 'float',
                label: 'radius',
                default: 0.5,
                desc: 'Radius of the cone base',
            },
            {
                type: 'float',
                label: 'height',
                default: 1,
                desc: 'Height of the cone',
            },
            {
                type: 'integer',
                label: 'radial segments',
                default: 8,
                desc: 'Number of segmented faces around the circumference of the cone',
            },
            {
                type: 'integer',
                label: 'height segments',
                default: 1,
                desc: 'Number of rows of faces along the height of the cone',
            },
            {
                type: 'checkbox',
                label: 'open base',
                default: false,
                desc: 'A Boolean indicating whether the base of the cone is open or capped',
            },
            {
                type: 'float',
                label: 'theta start',
                default: 0,
                desc: 'Start angle for first segment',
            },
            {
                type: 'float',
                label: 'theta length',
                default: 2 * Math.PI,
                desc: 'The central angle, often called theta, of the circular sector',
            },
        ],
    },
    icon: 'icons/cone.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['cone'];
        mesh.geometry = new THREE.ConeBufferGeometry(...p);
    },
};

const Polyhedron = {
    subtype: '3d',
    info: {
        'polyhedron': [
            {
                type: 'radio',
                label: 'polyhedron type',
                options: ['Tetrahedron', 'Octahedron', 'Dodecahedron', 'Icosahedron'],
                default: 2,
                desc: ' Type of the polyhedron',
            },
            {
                type: 'float',
                label: 'radius',
                default: 0.5,
                desc: ' Radius of the dodecahedron',
            },
            {
                type: 'integer',
                label: 'detail',
                default: 0,
                desc: 'Setting this to a value greater than 0 adds vertices making it no longer a dodecahedron',
            },
        ],
    },
    icon: 'icons/polyhedron.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const [type, radius, detail] = params['polyhedron'];
        const geoclass = [
            'TetrahedronBufferGeometry',
            'OctahedronBufferGeometry',
            'DodecahedronBufferGeometry',
            'IcosahedronBufferGeometry'
        ][type];
        mesh.geometry = new THREE[geoclass](radius, detail);
    },
};

const Text = {
    subtype: '3d',
    info: {
        'text': [
            {
                type: 'string',
                label: 'text',
                default: 'Text',
                desc: '3d text',
            },
            {
                type: 'select',
                label: 'font',
                options: ['helvetiker normal', 'helvetiker bold', 'optimer normal', 'optimer bold', 'gentilis normal', 'gentilis bold',
                'droid sans normal', 'droid sans bold', 'droid serif normal', 'droid serif bold'],
                default: 0,
                desc: 'The font of the text',
            },
            {
                type: 'float',
                label: 'size',
                default: 1,
                desc: 'Size of the text',
            },
            {
                type: 'float',
                label: 'height',
                default: 0.5,
                desc: 'Thickness to extrude text',
            },
            {
                type: 'integer',
                label: 'curve segments',
                default: 3,
                desc: 'Number of points on the curves',
            },
            {
                type: 'checkbox',
                label: 'enable bevel',
                default: false,
                desc: 'Turn on bevel',
            },
            {
                type: 'float',
                label: 'bevel thickness',
                default: 1,
                desc: 'How deep into text bevel goes',
            },
            {
                type: 'float',
                label: 'bevel size',
                default: 0.8,
                desc: 'How far from text outline is bevel',
            },
            {
                type: 'float',
                label: 'bevel offset',
                default: 0,
                desc: 'How far from text outline bevel starts',
            },
            {
                type: 'integer',
                label: 'bevel segments',
                default: 3,
                desc: 'Number of bevel segments',
            },
        ],
    },
    icon: 'icons/text.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const [text, fontIdx, size, height, curveSegments, bevelEnabled, bevelThickness, bevelSize, bevelOffset, bevelSegments] = params['text'];
        const loader = new THREE.FontLoader();
        loader.load('https://material3d.net/fonts/' + ['helvetiker', 'optimer', 'gentilis', 'droid/droid_sans', 'droid/droid_serif'][Math.floor(fontIdx/2)]
        + ['_regular', '_bold'][fontIdx%2] + '.typeface.json', (font) => {
            mesh.geometry = new THREE.TextBufferGeometry(text, {
                font, size, height, curveSegments, bevelEnabled, bevelThickness, bevelSize, bevelOffset, bevelSegments
            });
        });
    },
};

const Torus = {
    subtype: '3d',
    info: {
        'torus': [
            {
                type: 'float',
                label: 'radius',
                default: 1,
                desc: 'Radius of the torus, from the center of the torus to the center of the tube',
            },
            {
                type: 'float',
                label: 'tube radius',
                default: 0.4,
                desc: 'Radius of the tube',
            },
            {
                type: 'integer',
                label: 'radial segments',
                default: 8,
                desc: 'Number of segments along the tube',
            },
            {
                type: 'integer',
                label: 'tubular segments',
                default: 12,
                desc: 'Number of segments along the torus',
            },
            {
                type: 'float',
                label: 'Central angle',
                default: Math.PI * 2,
                desc: 'The central angle of the torus',
            },
        ],
    },
    icon: 'icons/torus.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['torus'];
        mesh.geometry = new THREE.TorusBufferGeometry(...p);
    },
};

const TorusKnot = {
    subtype: '3d',
    info: {
        'torus knot': [
            {
                type: 'float',
                label: 'radius',
                default: 1,
                desc: 'Radius of the torus',
            },
            {
                type: 'float',
                label: 'tube radius',
                default: 0.4,
                desc: 'Radius of the tube',
            },
            {
                type: 'integer',
                label: 'tubular segments',
                default: 64,
                desc: 'Number of segments along the torus',
            },
            {
                type: 'integer',
                label: 'radial segments',
                default: 8,
                desc: 'The number of segments that make up the cross-section of the torus',
            },
            {
                type: 'integer',
                label: 'p value',
                default: 2,
                desc: 'This value determines, how many times the geometry winds around its axis of rotational symmetry',
            },
            {
                type: 'integer',
                label: 'q value',
                default: 3,
                desc: 'This value determines, how many times the geometry winds around a circle in the interior of the torus',
            },
        ],
    },
    icon: 'icons/torusknot.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['torus knot'];
        mesh.geometry = new THREE.TorusKnotBufferGeometry(...p);
    },
};

const Tube = {
    subtype: '3d',
    info: {
        'tube': [
            {
                type: 'dataset',
                label: 'path',
                options: ['points3d'],
                default: '-1,0.8,0.5, -0.6,1,0.2, 0.6,-1,-0.2, 1,-0.8,-0.5',
                desc: 'An array of 3d points',
            },
            {
                type: 'radio',
                label: 'curve style',
                options: ['Catmull–Rom', 'Line segments'],
                default: 0,
                desc: 'The curve style of the tube path',
            },
            {
                type: 'integer',
                label: 'tubular segments',
                default: 64,
                desc: 'The number of segments that make up the tube',
            },
            {
                type: 'float',
                label: 'tube radius',
                default: 0.2,
                desc: 'Radius of the tube',
            },
            {
                type: 'integer',
                label: 'radial segments',
                default: 8,
                desc: 'The number of segments that make up the cross-section',
            },
            {
                type: 'checkbox',
                label: 'closed',
                default: false,
                desc: 'Whether the ends of the tube are closed',
            },
        ],
    },
    icon: 'icons/tube.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const [path, cstyle, ...pars] = params['tube'];
        const vecs = [];
        for(let i = 2; i < path.length; i += 3){
            vecs.push(new THREE.Vector3(path[i-2], path[i-1], path[i]));
        }
        if(vecs.length < 2){
            console.error('The length of path array is less than 2.');
            return;
        }
        let curve;
        if(cstyle === 0){
            curve = new THREE.CatmullRomCurve3(vecs);
        }
        else {
            curve = new THREE.CurvePath();
            for(let i = 0; i < vecs.length - 1; i++){
                curve.add(new THREE.LineCurve3(vecs[i], vecs[i + 1]));
            }
        }
        mesh.geometry = new THREE.TubeGeometry(curve, ...pars);
    },
};

const Lathe = {
    subtype: '3d',
    info: {
        'lathe': [
            {
                type: 'dataset',
                label: 'path',
                options: ['points2d'],
                default: '0.6,1.9, 1,1, 0.6,0.4, 0,0',
                desc: 'An array of 2d points',
            },
            {
                type: 'radio',
                label: 'curve style',
                options: ['Catmull–Rom', 'Line segments'],
                default: 0,
                desc: 'The curve style of the lathe path',
            },
            {
                type: 'integer',
                label: 'path segments',
                default: 8,
                desc: 'The number of segments along the path',
            },
            {
                type: 'integer',
                label: 'radial segments',
                default: 12,
                desc: 'The number of segments on the circumference',
            },
            {
                type: 'float',
                label: 'phi start',
                default: 0,
                desc: 'The starting angle in radians',
            },
            {
                type: 'float',
                label: 'phi length',
                default: Math.PI * 2,
                desc: 'the radian (0 to 2PI) range of the lathed section',
            },
        ],
    },
    icon: 'icons/lathe.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const [path, cstyle, pSeg, ...pars] = params['lathe'];
        const vecs = [];
        for(let i = 1; i < path.length; i += 2){
            vecs.push(new THREE.Vector2(path[i-1], path[i]));
        }
        if(vecs.length < 2){
            console.error('The length of path array is less than 2.');
            return;
        }
        let curve;
        if(cstyle === 0){
            curve = new THREE.SplineCurve(vecs);
        }
        else {
            curve = new THREE.CurvePath();
            for(let i = 0; i < vecs.length - 1; i++){
                curve.add(new THREE.LineCurve(vecs[i], vecs[i + 1]));
            }
        }
        const ptVecs = curve.getPoints(pSeg);
        mesh.geometry = new THREE.LatheBufferGeometry(ptVecs, ...pars);
    },
};

const Extrude = {
    subtype: '3d',
    info: {
        'extrude': [
            {
                type: 'dataset',
                label: 'shape',
                options: ['points2d'],
                default: '-1,-1, -1,1, 1,1, 1,-1',
                desc: 'An array of 2d points',
            },
            {
                type: 'integer',
                label: 'shape segments',
                default: 12,
                desc: 'Number of points on the shape',
            },
            {
                type: 'integer',
                label: 'steps',
                default: 1,
                desc: 'Number of points used for subdividing segments along the depth of the extruded spline',
            },
            {
                type: 'float',
                label: 'depth',
                default: 1,
                desc: 'Depth to extrude the shape',
            },
            {
                type: 'checkbox',
                label: 'enable bevel',
                default: true,
                desc: 'Apply beveling to the shape',
            },
            {
                type: 'float',
                label: 'bevel thickness',
                default: 1,
                desc: 'How deep into the original shape the bevel goes',
            },
            {
                type: 'float',
                label: 'bevel size',
                default: 1,
                desc: 'Distance from the shape outline that the bevel extends',
            },
            {
                type: 'float',
                label: 'bevel offset',
                default: 0,
                desc: 'Distance from the shape outline that the bevel starts',
            },
            {
                type: 'integer',
                label: 'bevel segments',
                default: 3,
                desc: 'Number of bevel layers',
            },
        ],
    },
    icon: 'icons/extrude.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const [path, curveSegments, steps, depth, bevelEnabled, bevelThickness, bevelSize, bevelOffset, bevelSegments] = params['extrude'];
        const vecs = [];
        for(let i = 1; i < path.length; i += 2){
            vecs.push(new THREE.Vector2(path[i-1], path[i]));
        }
        const shape = new THREE.Shape(vecs);
        mesh.geometry = new THREE.ExtrudeBufferGeometry(shape, {
            curveSegments, steps, depth, bevelEnabled, bevelThickness, bevelSize, bevelOffset, bevelSegments
        });
    },
};

const Circle = {
    subtype: '2d',
    info: {
        'circle': [
            {
                type: 'float',
                label: 'radius',
                default: 0.5,
                desc: 'Radius of the circle',
            },
            {
                type: 'integer',
                label: 'segments',
                default: 8,
                desc: 'Number of segments (triangles)',
            },
            {
                type: 'float',
                label: 'theta start',
                default: 0,
                desc: 'Start angle for first segment',
            },
            {
                type: 'float',
                label: 'theta length',
                default: 2 * Math.PI,
                desc: 'The central angle, often called theta, of the circular sector',
            },
        ],
    },
    icon: 'icons/circle.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['circle'];
        mesh.geometry = new THREE.CircleBufferGeometry(...p);
    },
};

const Rectangle = {
    subtype: '2d',
    info: {
        'rectangle': [
            {
                type: 'float',
                label: 'width',
                default: 1,
                desc: 'Width along the X axis',
            },
            {
                type: 'float',
                label: 'height',
                default: 1,
                desc: 'Height along the Y axis',
            },
            {
                type: 'integer',
                label: 'width segments',
                default: 1,
                desc: 'Number of segments along x axis',
            },
            {
                type: 'integer',
                label: 'height segments',
                default: 1,
                desc: 'Number of segments along y axis',
            },
        ],
    },
    icon: 'icons/rectangle.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['rectangle'];
        mesh.geometry = new THREE.PlaneBufferGeometry(...p);
    },
};

const Ring = {
    subtype: '2d',
    info: {
        'ring': [
            {
                type: 'float',
                label: 'inner radius',
                default: 0.5,
                desc: 'Radius of the inner circle',
            },
            {
                type: 'float',
                label: 'outer radius',
                default: 0.3,
                desc: 'Radius of the outer circle',
            },
            {
                type: 'integer',
                label: 'theta segments',
                default: 8,
                desc: 'Number of segments on the ring circle',
            },
            {
                type: 'integer',
                label: 'phi segments',
                default: 1,
                desc: 'Number of segments cross the ring circle',
            },
            {
                type: 'float',
                label: 'theta start',
                default: 0,
                desc: 'Start angle for first segment',
            },
            {
                type: 'float',
                label: 'theta length',
                default: 2 * Math.PI,
                desc: 'The central angle, often called theta, of the circular sector',
            },
        ],
    },
    icon: 'icons/ring.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const p = params['ring'];
        mesh.geometry = new THREE.RingBufferGeometry(...p);
    },
};

const Shape = {
    subtype: '2d',
    info: {
        'shape': [
            {
                type: 'dataset',
                label: 'shape',
                options: ['points2d'],
                default: '0,-1, -1,0.4, -0.4,1, 0,0.5, 0.4,1, 1,0.4, 0,-1',
                desc: 'An array of 2d points',
            },
            {
                type: 'integer',
                label: 'shape segments',
                default: 12,
                desc: 'Number of segments on the shape',
            },
        ],
    },
    icon: 'icons/shape.svg',
    create: CreateDefaultMesh,
    update: (mesh, params) => {
        const [path, segments] = params['shape'];
        const vecs = [];
        for(let i = 1; i < path.length; i += 2){
            vecs.push(new THREE.Vector2(path[i-1], path[i]));
        }
        const shape = new THREE.Shape(vecs);
        mesh.geometry = new THREE.ShapeBufferGeometry(shape, segments);
    },
};


const MeshLib =  {
    title: 'Geometry',
    types: {
        Sphere,
        Box,
        Cylinder,
        Cone,
        Polyhedron,
        Text,
        Torus,
        TorusKnot,
        Tube,
        Lathe,
        Extrude,
        Circle,
        Rectangle,
        Ring,
        Shape
    }
};

export default MeshLib;