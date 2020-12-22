
const CoordIndex = ['x', 'y', 'z'];

class NewtonForceClass {
    constructor(force, axis, resistance, attenuation, box) {
        this.enabled = true;
        const attenFunc = attenuation !== 0
        ? (x, z, force) => 
            force * (1 - Math.sqrt(x * x + z * z) / attenuation)
        : undefined;
        const resistFunc = resistance !== 0 ? force < 0
            ? (speedY, force) => force + speedY * speedY * resistance
            : (speedY, force) => force - speedY * speedY * resistance
        : undefined;
        const pAxis = CoordIndex.filter(a => a !== axis);
        this.updateTime = (position, speed, deltaTime) => {
            if(position.x < box[0] || position.x > box[1]
                || position.y < box[2] || position.y > box[3]
                || position.z < box[4] || position.z > box[5]) return;
            let f = force;
            if(attenFunc) f = attenFunc(position[pAxis[0]], position[pAxis[1]], f);
            if(resistFunc) f = resistFunc(speed[axis], f);
            speed[axis] += f * deltaTime;
        }
    }
}

const NewtonForce = {
    subtype: 'force',
    info: {
        'parameters': [
            {
                type: 'float',
                label: 'Force',
                default: 1,
            },
            {
                type: 'radio',
                label: 'Direction',
                options: ['x-axis', 'y-axis', 'z-axis'],
                default: 1,
            },
            {
                type: 'float',
                label: 'Resistance',
                default: 0,
            },
            {
                type: 'float',
                label: 'Attenuation range',
                default: 0,
            },
        ],
        'limits': [
            {
                type: 'float',
                label: 'Left (-X)',
                default: -1000,
            },
            {
                type: 'float',
                label: 'Right (+X)',
                default: 1000,
            },
            {
                type: 'float',
                label: 'Bottom (-Y)',
                default: -1000,
            },
            {
                type: 'float',
                label: 'Top (+Y)',
                default: 1000,
            },
            {
                type: 'float',
                label: 'Back (-Z)',
                default: -1000,
            },
            {
                type: 'float',
                label: 'Front (+Z)',
                default: 1000,
            },
        ],
    },
    create:(obj3d, params) => {
        const par = params.parameters;
        const limits = params.limits;
        return new NewtonForceClass(par[0], CoordIndex[par[1]],par[2],par[3], limits);
    }
};

class CollisionClass {
    constructor(object, bOutside, tObjects, bSpheres, cor, cof, mr) {
        this.enabled = true;
        this.updateTime = (position, speed, deltaTime) => {};
        const tObj = tObjects[0];
        if(!object || !tObj || object === tObj) return;
        if(mr < 0) return;
        const objects = [object, tObj];
        const comFuncs = objects.map((o, i) => o.geometry ? o.geometry
            [bSpheres[i] ? 'computeBoundingSphere' : 'computeBoundingBox'] : undefined);
        //console.log('comFuncs', comFuncs);
        const shapeObjs = comFuncs.map((f, i) => {
            if(!f){
                bSpheres[i] = true;
                return new THREE.Sphere(new THREE.Vector3(0,0,0), 0);
            }
            else {
                const {geometry} = objects[i];
                const comShape = bSpheres[i] ? 'boundingSphere' : 'boundingBox';
                f.call(geometry);
                return geometry[comShape];
            }
        });
        let collCheck;
        const dirVec = new THREE.Vector3();
        const speedVec = new THREE.Vector3();
        const posVec = new THREE.Vector3();
        const posDiff = new THREE.Vector3();
        const factor0f = (mr + 1 - cof)/(mr + 1);
        const factor0 = (mr - cor)/(mr + 1) - factor0f;
        const factor1f = mr * cof/(mr + 1);
        const factor1 = mr * (1 + cor)/(mr + 1) - factor1f;
        let dirL = 1;
        if(bSpheres[0] && bSpheres[1]) {
            const comShapes = shapeObjs;
            collCheck = bOutside ? () => {
                dirVec.addVectors(comShapes[0].center, posDiff).sub(comShapes[1].center);
                dirL = dirVec.length();
                return dirL <= comShapes[0].radius + comShapes[1].radius;
            }
            : () => {
                dirVec.subVectors(comShapes[1].center, posDiff).sub(comShapes[0].center);
                dirL = dirVec.length();
                return dirL >= comShapes[0].radius - comShapes[1].radius;
            };
        }
        else if(!bSpheres[0] && !bSpheres[1]){
            const comShapes = shapeObjs;
            collCheck = bOutside ? () => {
                dirL = 1;
                let minDist = Infinity;
                for(let i = 0; i < 2; i++){
                    posVec.subVectors(comShapes[1 - i].max, comShapes[i].min)
                    [i === 0 ? 'sub' : 'add'](posDiff);
                    //console.log('posVec[', i, ']:', posVec);
                    const mdir = posVec.x < posVec.y
                    ? (posVec.x < posVec.z ? 'x' : 'z')
                    : (posVec.y < posVec.z ? 'y' : 'z');
                    if(posVec[mdir] < 0) return false;
                    if(posVec[mdir] < minDist){
                        minDist = posVec[mdir];
                        dirVec.set(0, 0, 0);
                        dirVec[mdir] = 1 - 2 * i;
                    }
                }
                return true;
            }
            : () => {
                dirL = 1;
                for(let i = 0; i < 2; i++){
                    i === 0
                    ? posVec.subVectors(comShapes[0].max, comShapes[1].max).add(posDiff)
                    : posVec.subVectors(comShapes[1].min, comShapes[0].min).sub(posDiff);
                    const mdir = posVec.x < posVec.y
                    ? (posVec.x < posVec.z ? 'x' : 'z')
                    : (posVec.y < posVec.z ? 'y' : 'z');
                    if(posVec[mdir] <= 0){
                        dirVec.set(0, 0, 0);
                        dirVec[mdir] = 1 - 2 * i;
                        return true;
                    }
                }
                return false;
            }
        }
        else {
            const sIdx = bSpheres[0] ? 0 : 1;
            const comSphere = shapeObjs[sIdx];
            const comBox = shapeObjs[1 - sIdx];
            const vecFunc = sIdx === 0 ? 'addVectors' : 'subVectors';
            collCheck = bOutside ? () => {
                posVec[vecFunc](comSphere.center, posDiff);
                dirVec.subVectors(comBox.max, posVec);
                CoordIndex.forEach(d => {
                    if(dirVec[d] > 0){
                        dirVec[d] = comBox.min[d] - posVec[d];
                        if(dirVec[d] < 0)dirVec[d] = 0;
                    }
                });
                if(sIdx === 0) dirVec.negate();
                dirL = dirVec.length();
                return dirL <= comSphere.radius;
            }
            : sIdx === 0 ? () => {
                posVec.addVectors(comBox.max, comBox.min).multiplyScalar(0.5);
                dirVec.subVectors(posVec, comSphere.center).sub(posDiff);
                posVec.sub(comBox.min);
                CoordIndex.forEach(d => {
                    dirVec[d] = dirVec[d] + (dirVec[d] > 0 ? posVec.x : -posVec.x);
                });
                dirL = dirVec.length();
                return dirL >= comSphere.radius;
            }
            : () => {
                dirL = 1;
                let minDist = Infinity;
                for(let i = 0; i < 2; i++){
                    i === 0
                    ? posVec.subVectors(comBox.max, comSphere.center).add(posDiff)
                    : posVec.subVectors(comSphere.center, comBox.min).sub(posDiff);
                    //console.log('posVec[', i, ']:', posVec);
                    const mdir = posVec.x < posVec.y
                    ? (posVec.x < posVec.z ? 'x' : 'z')
                    : (posVec.y < posVec.z ? 'y' : 'z');
                    if(posVec[mdir] < minDist){
                        minDist = posVec[mdir];
                        dirVec.set(0, 0, 0);
                        dirVec[mdir] = 1 - 2 * i;
                    }
                }
                return minDist <= comSphere.radius;
            }
        }
        this.updateTime = (position, speed, deltaTime) => {
            tObjects.forEach(target => {
                posDiff.subVectors(position, target.position);
                const speed1 = target.speed;
                if(collCheck()){
                    if(dirL === 0) return;
                    //speed: object speed
                    //speed1: target speed
                    //speedVec: relative speed of target to object
                    //dirVec: collision direction from target to object
                    //projs: projection scalar of speedVec on direction vector
                    //speedProj: dirVec * projs: speedVec projection on dirVec
                    //speedPerp: speedVec - speedProj: speedVec perpendicular on dirVec
                    //(mr - cor)/(mr + 1): scalar of speedProj after collision
                    //(mr + 1 - cof)/(mr + 1): scalar of speedPerp after collision
                    //mr * (1 + cor)/(mr + 1): scalar of speedProj after collision on target
                    //mr * cof/(mr + 1): scalar of speedPerp after collision on target
                    speedVec.subVectors(speed1, speed);
                    const projs = dirVec.dot(speedVec) /(dirL * dirL);
                    if(projs > 0){
                        speed1.copy(speed).addScaledVector(dirVec, factor0 * projs)
                        .addScaledVector(speedVec, factor0f);
                        speed.addScaledVector(dirVec, factor1 * projs)
                        .addScaledVector(speedVec, factor1f);
                    }
                }
            })
        }
        //console.log('CollisionClass.func', this.func);
    }
}

const Collision = {
    subtype: 'collision',
    info: {
        'object': [
            {
                type: 'radio',
                label: 'Bounding Shape',
                options: ['Sphere', 'Box'],
                default: 0,
            },
        ],
        'targets': [
            {
                type: 'object',
                label: 'Targets',
                options: ['all', 'exclusive', 'multiple'],
                default: '',
            },
            {
                type: 'radio',
                label: 'Bounding Shape',
                options: ['Sphere', 'Box'],
                default: 0,
            },
            {
                type: 'radio',
                label: 'Relation to Object',
                options: ['Outside', 'Inside'],
                default: 0,
            },
        ],
        'physics': [
            {
                type: 'float',
                label: 'Mass Ratio (T/O)',
                default: 1,
            },
            {
                type: 'range',
                label: 'Restitution coefficient',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'range',
                label: 'Fraction coefficient',
                options: [0, 1, 0.01],
                default: 0,
            },
        ]
    },
    create:(obj3d, params) => {
        const {object, targets, physics} = params;
        const tObjs = targets[0];
        const bSpheres = [object[0] === 0, targets[1] === 0];
        const bOutSide = targets[2] === 0;
        const [mr, cor, cof] = physics;
        return new CollisionClass(obj3d, bOutSide, tObjs, bSpheres, cor, cof, mr);
    }
};

const ForceFieldLib = {
    types: {
        NewtonForce,
        Collision,
    }
};

export default ForceFieldLib;