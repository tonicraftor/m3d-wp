
const LOOPMODE = [
    THREE.LoopRepeat, THREE.LoopOnce, THREE.LoopPingPong];

/*******************************
 * Key frame tracks
 ******************************/

const Translation = {
    track: 'position',
    info: {
        'duration': [
            {
                type: 'float',
                label: 'Duration(sec)',
                default: 1,
                desc: 'The duration from start to end of the keyframe',
            },
        ],
        'translate': [
            {
                type: 'float',
                label: 'x',
                default: 1,
            },
            {
                type: 'float',
                label: 'y',
                default: 0,
            },
            {
                type: 'float',
                label: 'z',
                default: 0,
            },
        ],
    },
    create:(params) => {
        const values = [];
        let time = 0;
        const times = params.map(p => {
            values.push(...p['translate']);
            let dur = p['duration'][0];
            if(dur < 0) dur = 0;
            time += dur;
            return time;
        });
        if(times[0] !== 0){
            times.splice(0, 0, 0);
            values.splice(0, 0, 0, 0, 0);
        }
        return new THREE.VectorKeyframeTrack( '.position', times, values);
    },
};

const Rotation = {
    track: 'rotation',
    info: {
        'duration': [
            {
                type: 'float',
                label: 'Duration(sec)',
                default: 1,
            },
        ],
        'axis&angle': [
            {
                type: 'float',
                label: 'Axis-x',
                default: 0,
            },
            {
                type: 'float',
                label: 'Axis-y',
                default: 1,
            },
            {
                type: 'float',
                label: 'Axis-z',
                default: 0,
            },
            {
                type: 'float',
                label: 'Angle(radian)',
                default: Math.PI,
            },
        ],
    },
    create:(params) => {
        const values = [];
        let time = 0;
        const axis = new THREE.Vector3();
        const qn = new THREE.Quaternion();
        const times = params.map(p => {
            const aaa = p['axis&angle'];
            axis.set(aaa[0], aaa[1], aaa[2]);
            qn.setFromAxisAngle(axis, aaa[3]);
            values.push(qn.x, qn.y, qn.z, qn.w);
            let dur = p['duration'][0];
            if(dur < 0) dur = 0;
            time += dur;
            return time;
        });
        if(times[0] !== 0){
            times.splice(0, 0, 0);
            values.splice(0, 0, 0, 0, 0, 1);
        }
        return new THREE.QuaternionKeyframeTrack( '.quaternion', times, values);
    },
};

const Scale = {
    track: 'scale',
    info: {
        'duration': [
            {
                type: 'float',
                label: 'Duration(sec)',
                default: 1,
            },
        ],
        'scale': [
            {
                type: 'float',
                label: 'x',
                default: 1,
            },
            {
                type: 'float',
                label: 'y',
                default: 1,
            },
            {
                type: 'float',
                label: 'z',
                default: 1,
            },
        ],
    },
    create:(params) => {
        const values = [];
        let time = 0;
        const times = params.map(p => {
            values.push(...p['scale']);
            let dur = p['duration'][0];
            if(dur < 0) dur = 0;
            time += dur;
            return time;
        });
        if(times[0] !== 0){
            times.splice(0, 0, 0);
            values.splice(0, 0, 1, 1, 1);
        }
        return new THREE.VectorKeyframeTrack( '.scale', times, values);
    },
};

const Acceleration = {
    track: 'speed',
    info: {
        'duration': [
            {
                type: 'float',
                label: 'Duration(sec)',
                default: 1,
            },
        ],
        'speed': [
            {
                type: 'float',
                label: 'x (1/sec)',
                default: 0,
            },
            {
                type: 'float',
                label: 'y (1/sec)',
                default: 1,
            },
            {
                type: 'float',
                label: 'z (1/sec)',
                default: 0,
            },
        ],
    },
    create:(params) => {
        const values = [];
        let time = 0;
        const times = params.map(p => {
            values.push(...p['speed']);
            let dur = p['duration'][0];
            if(dur < 0) dur = 0;
            time += dur;
            return time;
        });
        if(times[0] !== 0){
            times.splice(0, 0, 0);
            values.splice(0, 0, 0, 0, 0);
        }
        return new THREE.VectorKeyframeTrack( '.speed', times, values);
    },
};

const Custom = {
    track: '',
    info: {
        'duration': [
            {
                type: 'float',
                label: 'Duration(sec)',
                default: 1,
            },
        ],
        'property': [
            {
                type: 'string',
                label: 'name',
                default: '.position',
                desc: 'A property name of the object, like ".position", ".material.color". This value must be set in the first keyframe.'
            },
            {
                type: 'select',
                label: 'data type',
                options: ['Vector', 'Number', 'Color', 'Quaternion', 'Boolean', 'String'],
                default: 0,
                desc: 'The data type of the property. This value must be set in the first keyframe.'
            },
            {
                type: 'select',
                label: 'interpolation mode',
                options: ['Default', 'Linear', 'Smooth', 'Discreter'],
                default: 0,
                desc: 'The interpolation mode of the property. This value must be set in the first keyframe.'
            },
        ],
        'value': [
            {
                type: 'string',
                label: 'value',
                default: '[0, 0, 0]',
                desc: 'A json string represents the value. Examples: Vector: [10, 2, 5], Color: [1, 0.5, 0.8], Quaternion: [0, 0, 1, 1], Number: 0.8, String: "xxx"'
            },
        ],
    },
    create:(params) => {
        const track = params[0]['property'][0];
        const dt = params[0]['property'][1];
        const imode = [
            undefined, 
            THREE.InterpolateLinear,
            THREE.InterpolateSmooth,
            THREE.InterpolateDiscrete,
        ][params[0]['property'][2]];
        const values = [];
        let time = 0;
        const times = params.map(p => {
            let valArr;
            try{
                valArr = JSON.parse(p['value'][0]);
            }
            catch(e){
                alert('Animation parameters ' + e.name + ': ' + e.message);
                valArr = [];
            }
            let v = [valArr].flat();
            values.push(...v);
            let dur = p['duration'][0];
            if(dur < 0) dur = 0;
            time += dur;
            return time;
        });
        if(times[0] !== 0){
            times.splice(0, 0, 0);
            if(dt === 0 || dt === 2)values.splice(0, 0, 0, 0, 0);
            else if(dt === 1) values.splice(0, 0, 0);
            else if(dt === 3) values.splice(0, 0, 0, 0, 0, 1);
            else if(dt === 4) values.splice(0, 0, false);
            else values.splice(0, 0, '');
        }
        const func = [
            'VectorKeyframeTrack',
            'NumberKeyframeTrack',
            'ColorKeyframeTrack',
            'QuaternionKeyframeTrack',
            'BooleanKeyframeTrack',
            'StringKeyframeTrack',
        ][dt];
        return new THREE[func]( track, times, values, imode);
    },
};

/*******************************
 * Animation actions
 ******************************/

const Normal_Action = {
    subtype: 'normal',
    info: {
        'options': [
            {
                type: 'radio',
                label: 'Looping mode',
                options: ['Repeat', 'Once', 'Pingpong'],
                default: 0,
            },
            {
                type: 'integer',
                label: 'Repetitions',
                default: 0,
            },
            {
                type: 'range',
                label: 'Weight',
                options: [0, 1, 0.01],
                default: 1,
            },
            {
                type: 'float',
                label: 'Play speed',
                default: 1,
            },
        ],
        'Fade': [
            {
                type: 'float',
                label: 'Fade In (sec)',
                default: 0,
            },
            {
                type: 'float',
                label: 'Fade Out (sec)',
                default: 0,
            },
        ],
        'Warpping': [
            {
                type: 'float',
                label: 'Start play speed',
                default: 0,
            },
            {
                type: 'float',
                label: 'End play speed',
                default: 0,
            },
            {
                type: 'float',
                label: 'Duration',
                default: 0,
            },
        ],
    },
    update: (action, params) => {
        const options = params['options'];
        const fade = params['Fade'];
        const warp = params['Warpping'];
        if(options){
            action.loop = LOOPMODE[options[0]];
            const repeats = options[1];
            action.repetitions = repeats <= 0 ? Infinity : repeats;
            action.weight = options[2];
            action.timeScale = options[3];
        }
        if(fade) {
            if(fade[0] > 0) action.fadeIn(fade[0]);
            if(fade[1] > 0) action.fadeOut(fade[1]);
        }
        if(warp){
            if(warp[2] > 0) action.warp(...warp);
        }
    }
}

const AnimationLib = {
    trackTypes: {
        Translation,
        Rotation,
        Scale,
        Acceleration,
        Custom,
    },
    animTypes: {
        Normal: Normal_Action,
    }
};

export default AnimationLib;