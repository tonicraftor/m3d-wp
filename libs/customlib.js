
const ParametricGeometries = {

	klein: function ( v, u, target ) {

		u *= Math.PI;
		v *= 2 * Math.PI;

		u = u * 2;
		var x, y, z;
		if ( u < Math.PI ) {

			x = 3 * Math.cos( u ) * ( 1 + Math.sin( u ) ) + ( 2 * ( 1 - Math.cos( u ) / 2 ) ) * Math.cos( u ) * Math.cos( v );
			z = - 8 * Math.sin( u ) - 2 * ( 1 - Math.cos( u ) / 2 ) * Math.sin( u ) * Math.cos( v );

		} else {

			x = 3 * Math.cos( u ) * ( 1 + Math.sin( u ) ) + ( 2 * ( 1 - Math.cos( u ) / 2 ) ) * Math.cos( v + Math.PI );
			z = - 8 * Math.sin( u );

		}

		y = - 2 * ( 1 - Math.cos( u ) / 2 ) * Math.sin( v );

		target.set( x, y, z );

	},

	plane: function ( width, height ) {

		return function ( u, v, target ) {

			var x = u * width;
			var y = 0;
			var z = v * height;

			target.set( x, y, z );

		};

	},

	mobius: function ( u, t, target ) {

		// flat mobius strip
		// http://www.wolframalpha.com/input/?i=M%C3%B6bius+strip+parametric+equations&lk=1&a=ClashPrefs_*Surface.MoebiusStrip.SurfaceProperty.ParametricEquations-
		u = u - 0.5;
		var v = 2 * Math.PI * t;

		var x, y, z;

		var a = 2;

		x = Math.cos( v ) * ( a + u * Math.cos( v / 2 ) );
		y = Math.sin( v ) * ( a + u * Math.cos( v / 2 ) );
		z = u * Math.sin( v / 2 );

		target.set( x, y, z );

	},

	mobius3d: function ( u, t, target ) {

		// volumetric mobius strip

		u *= Math.PI;
		t *= 2 * Math.PI;

		u = u * 2;
		var phi = u / 2;
		var major = 2.25, a = 0.125, b = 0.65;

		var x, y, z;

		x = a * Math.cos( t ) * Math.cos( phi ) - b * Math.sin( t ) * Math.sin( phi );
		z = a * Math.cos( t ) * Math.sin( phi ) + b * Math.sin( t ) * Math.cos( phi );
		y = ( major + x ) * Math.sin( u );
		x = ( major + x ) * Math.cos( u );

		target.set( x, y, z );

	}

};

const Klein = {
    subtype: '3d',
    info: {
        'klein': [
            {
                type: 'integer',
                label: 'Slices',
                default: 25,
                desc: 'The count of slices to use for the klein',
            },
            {
                type: 'integer',
                label: 'Stacks',
                default: 25,
                desc: 'The count of stacks to use for the klein',
            },
        ],
    },
    icon: 'icons/custom.svg',
    create: () => {
        const mesh = new THREE.Mesh();
        mesh.material = new THREE.MeshStandardMaterial({ wireframe: true, side: THREE.DoubleSide});
        return mesh;
    },
    update: (mesh, params) => {
        const par = params['klein'];
        mesh.geometry = new THREE.ParametricBufferGeometry(ParametricGeometries.klein, ...par);
    }
};

const CustomLib =  {
    title: 'MyLibrary',
    icon: 'icons/customIcon.svg',
    types: {
        Klein,
    }
};

export default CustomLib;