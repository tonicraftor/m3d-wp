
const POINT_STATE = {
    INPOOL: 0,
    RUNNING: 1,
    DISAPPEARED: 2
};

const Dots = {
    subtype: 'points',
    info: {
        'appearance': [
            {
                type: 'float',
                label: 'Size',
                default: 0.1,
            },
            {
                type: 'color',
                label: 'Color',
                default: 0x000000,
            },
            {
                type: 'texture',
                label: 'Texture',
                default: '',
            },
            {
                type: 'range',
                label: 'Opacity',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'range',
                label: 'Alpha Test',
                options: [0, 1, 0.01],
                default: 0.5,
            },
        ],
        'dimension': [
            {
                type: 'float',
                label: 'Density',
                default: 100,
                desc: 'The number of points in a cubic unit of 1*1*1',
            },
            {
                type: 'float',
                label: 'Width',
                default: 2,
            },
            {
                type: 'float',
                label: 'Length',
                default: 2,
            },
            {
                type: 'float',
                label: 'Height',
                default: 2,
            },
        ],
    },
    icon: 'icons/dots.svg',
    create: () => new THREE.Points(new THREE.BufferGeometry(),
        new THREE.PointsMaterial()),
    update: (points, params) => {
        const {appearance, dimension} = params;
        const geometry = points.geometry;
        const material = points.material;
        if(dimension){
            const [d, w, l, h] = dimension;
            const vertices = [];
            const number = Math.round(d * w * l * h);
            const w2 = 0.5 * w, l2 = 0.5 * l, h2 = 0.5 * h;

            for ( let i = 0; i < number; i ++ ) {
                const x = Math.random() * w - w2;
                const y = Math.random() * h - h2;
                const z = Math.random() * l - l2;
                vertices.push( x, y, z );
            }
            geometry.setAttribute( 'position', new THREE.Float32BufferAttribute( vertices, 3 ) );
        }
        if(appearance){
            material.size = appearance[0];
            material.color = new THREE.Color(appearance[1]);
            material.map = appearance[2];
            material.opacity = appearance[3];
            material.transparent = material.opacity < 1;
            material.alphaTest = appearance[4];
            material.needsUpdate = true;
        }
    }
};

//particle system
//container: a box
//pool: a square or circle at the bottom of the box
//constraint object: provide a shape to contrain the movement of particles
//--use voxel to represent the constraint object
//--at the emitting plane, outside the constraint object, particles will not be created
//--during the movement of particles, the constraint object make particles rebounce
//--all voxel at the border has a rebouncing plane, which is the average plane of all triangles in that voxel
//--Each particle has a initial speed, random in x and z directiom
//--A force and a resistance can be put on particles
//--the resistance is proportional to the square of the speed in any direction
//--for water, the force is the gravity. for smoke, the force is the buoyancy
//collision
//decay over time
//speed change
const Particles = {
    subtype: 'points',
    info: {
        'appearance': [
            {
                type: 'float',
                label: 'Size',
                default: 0.1,
            },
            {
                type: 'color',
                label: 'Color',
                default: 0x000000,
            },
            {
                type: 'texture',
                label: 'Texture',
                default: '',
            },
            {
                type: 'range',
                label: 'Opacity',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'range',
                label: 'Alpha Test',
                options: [0, 1, 0.01],
                default: 0.5,
            },
        ],
        'pool': [
            {
                type: 'radio',
                label: 'Shape',
                options: ['Circle', 'Square'],
                default: 0,
            },
            {
                type: 'float',
                label: 'Diameter or Length',
                default: 1,
            },
            {
                type: 'integer',
                label: 'Total Number',
                default: 1000,
            },
            {
                type: 'float',
                label: 'Number of Each Spawn',
                default: 10,
            },
            {
                type: 'float',
                label: 'Interval (sec)',
                default: 0.1,
            },
        ],
        'particles': [
            {
                type: 'float',
                label: 'duration (sec)',
                default: 1,
            },
            {
                type: 'float',
                label: 'Velocity',
                default: 1,
            },
            {
                type: 'range',
                label: 'Randomize',
                options: [0, 1, 0.01],
                default: 0,
            },
        ],
    },
    icon: 'icons/particles.svg',
    create: () => new THREE.Points(new THREE.BufferGeometry(),
        new THREE.PointsMaterial()),
    update: (points, params) => {
        const {appearance, pool, particles} = params;
        const geometry = points.geometry;
        const material = points.material;
        points.userData.pool = pool;
        points.userData.particles = particles;
        if(pool){
            const count = pool[2];
            const vertices = Array(3 * count).fill(0);
            geometry.setAttribute( 'position', new THREE.Float32BufferAttribute( vertices, 3 ) );
            geometry.setAttribute( 'speed', new THREE.Float32BufferAttribute( vertices, 3 ) );
            geometry.setAttribute( 'state', new THREE.Float32BufferAttribute( Array(count).fill(POINT_STATE.INPOOL), 1 ) );
            geometry.setAttribute( 'age', new THREE.Float32BufferAttribute( Array(count).fill(0), 1 ) );
        }
        if(appearance){
            material.size = appearance[0];
            material.color = new THREE.Color(appearance[1]);
            material.map = appearance[2];
            material.opacity = appearance[3];
            material.transparent = material.opacity < 1;
            material.alphaTest = appearance[4];
            material.needsUpdate = true;
        }
    },
    updateTime: (obj3d, forceFields, deltaTime) => {
        const geometry = obj3d.geometry;
        const {pool, particles} = obj3d.userData;
        const [poolshape, poolsize, count, spawnCount, interval] = pool;
        const [duration, iSpeed, randomizor] = particles;
        const {position: positions, speed: speeds, state: states, age: ages}
            = geometry.attributes;
        const speedVec = new THREE.Vector3();
        const posVec = new THREE.Vector3();
        for(let i = 0; i < count; i++){
            if(states.getX(i) !== POINT_STATE.RUNNING)continue;
            const age = ages.getX(i) + deltaTime;
            ages.setX(i, age);
            if(age >= duration){
                states.setX(i, POINT_STATE.INPOOL);
                positions.setXYZ(i, 0, 0, 0);
                continue;
            }
            //update positions and states
            speedVec.set(speeds.getX(i), speeds.getY(i), speeds.getZ(i));
            posVec.set(positions.getX(i), positions.getY(i), positions.getZ(i))
            .addScaledVector(speedVec, deltaTime);
            positions.setXYZ(i, posVec.x, posVec.y, posVec.z);
            forceFields.forEach(ff => ff.updateTime(posVec, speedVec, deltaTime));
            //update speeds
            speeds.setXYZ(i, speedVec.x, speedVec.y, speedVec.z);
        }
        positions.needsUpdate = true;
        ages.needsUpdate = true;
        states.needsUpdate = true;
        //spawn new points from the pool
        if(interval <= 0) return;
        let newSpawnNum = Math.round(deltaTime / interval * spawnCount);
        const pr = poolsize/2;
        const yf0 = Math.sqrt(1 - randomizor * randomizor) * iSpeed;
        const yf1 = iSpeed - yf0;
        for(let i = 0; i< count; i++){
            if(newSpawnNum < 1)break;
            if(states.getX(i) !== POINT_STATE.INPOOL)continue;
            const x = 1 - Math.random() * 2;
            const z = 1 - Math.random() * 2;
            const zf = poolshape === 0 ? Math.sqrt(1 - x * x) : 1;
            positions.setXYZ(i, x * pr, 0, z * zf * pr);
            ages.setX(i, 0);
            states.setX(i, POINT_STATE.RUNNING);
            //randomize speeds
            const sy = yf0 + Math.random() * yf1;
            const sxz = Math.sqrt(iSpeed * iSpeed - sy * sy);
            let rxz = Math.random() * 4 - 2;
            let zsign = 1;
            if(rxz < 0) {
                zsign = -1;
                rxz = - rxz;
            }
            const sx = sxz * (1 - rxz);
            const sz = zsign * Math.sqrt(sxz * sxz - sx * sx);
            speeds.setXYZ(i, sx, sy, sz);
            //speeds[i] = [particles[1], particles[2], particles[3]] as number[];
            newSpawnNum--;
        }
    }
};

const PointsLib =  {
    title: 'Points',
    types: {
        Dots,
        Particles,
    }
};

export default PointsLib;