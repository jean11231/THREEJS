<!DOCTYPE html>
<html lang="en">
	<head>
		<title>three.js webgl - morphtargets - MD2</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				color: #fff;
				font-family:Monospace;
				font-size:13px;
				text-align:center;
				font-weight: bold;
				background-color: #000;
				margin: 0px;
				overflow: hidden;
			}
			#info {
				position: absolute;
				padding: 10px;
				width: 100%;
				text-align: center;
				color: #fff;
			}
			a { color: skyblue; }
		</style>
	</head>

	<body>
		<div id="info">
			<a href="http://threejs.org" target="_blank">three.js</a> - MD2 Loader -
			Character by <a href="http://planetquake.gamespy.com/View.php?view=Quake2.Detail&id=368">Brian Collins</a>
		</div>

		<script src="https://threejs.org/build/three.js"></script>

		<script src="https://threejs.org/examples/js/controls/OrbitControls.js"></script>

		<script src='https://threejs.org/examples/js/loaders/MD2Loader.js'></script>
		<script src='https://threejs.org/examples/js/MD2Character.js'></script>

		<script src='https://threejs.org/examples/js/Detector.js'></script>
		<script src='https://threejs.org/examples/js/libs/stats.min.js'></script>
		<script src='https://threejs.org/examples/js/libs/dat.gui.min.js'></script>

		<script>
			if ( ! Detector.webgl ) Detector.addGetWebGLMessage();
			var SCREEN_WIDTH = window.innerWidth;
			var SCREEN_HEIGHT = window.innerHeight;
			var container, camera, scene, renderer;
			var character;
			var gui, playbackConfig = {
				speed: 1.0,
				wireframe: false
			};
			var controls;
			var clock = new THREE.Clock();
			init();
			animate();
			function init() {
				container = document.createElement( 'div' );
				document.body.appendChild( container );
				// CAMERA
				camera = new THREE.PerspectiveCamera( 40, window.innerWidth / window.innerHeight, 1, 1000 );
				camera.position.set( 0, 150, 400 );
				// SCENE
				scene = new THREE.Scene();
				scene.fog = new THREE.Fog( 0x050505, 400, 1000 );
				// LIGHTS
				scene.add( new THREE.AmbientLight( 0x222222 ) );
				var light = new THREE.SpotLight( 0xffffff, 5, 1000 );
				light.position.set( 200, 250, 500 );
				light.angle = 0.5;
				light.penumbra = 0.5;
				light.castShadow = true;
				light.shadow.mapSize.width = 1024;
				light.shadow.mapSize.height = 1024;
				// scene.add( new THREE.CameraHelper( light.shadow.camera ) );
				scene.add( light );
				var light = new THREE.SpotLight( 0xffffff, 5, 1000 );
				light.position.set( -100, 350, 350 );
				light.angle = 0.5;
				light.penumbra = 0.5;
				light.castShadow = true;
				light.shadow.mapSize.width = 1024;
				light.shadow.mapSize.height = 1024;
				// scene.add( new THREE.CameraHelper( light.shadow.camera ) );
				scene.add( light );
				//  GROUND
				var gt = new THREE.TextureLoader().load( "textures/terrain/grasslight-big.jpg" );
				var gg = new THREE.PlaneBufferGeometry( 2000, 2000 );
				var gm = new THREE.MeshPhongMaterial( { color: 0xffffff, map: gt } );
				var ground = new THREE.Mesh( gg, gm );
				ground.rotation.x = - Math.PI / 2;
				ground.material.map.repeat.set( 8, 8 );
				ground.material.map.wrapS = ground.material.map.wrapT = THREE.RepeatWrapping;
				ground.receiveShadow = true;
				scene.add( ground );
				// RENDERER
				renderer = new THREE.WebGLRenderer( { antialias: true } );
				renderer.setClearColor( scene.fog.color );
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );
				container.appendChild( renderer.domElement );
				//
				renderer.gammaInput = true;
				renderer.gammaOutput = true;
				renderer.shadowMap.enabled = true;
				// STATS
				stats = new Stats();
				container.appendChild( stats.dom );
				// EVENTS
				window.addEventListener( 'resize', onWindowResize, false );
				// CONTROLS
				controls = new THREE.OrbitControls( camera, renderer.domElement );
				controls.target.set( 0, 50, 0 );
				// GUI
				gui = new dat.GUI();
				gui.add( playbackConfig, 'speed', 0, 2 ).onChange( function() {
					character.setPlaybackRate( playbackConfig.speed );
				} );
				gui.add( playbackConfig, 'wireframe', false ).onChange( function() {
					character.setWireframe( playbackConfig.wireframe );
				} );
				// CHARACTER
				var config = {
					baseUrl: "laalaa",
					body: "laalaa.md2",
					skins: [ "laalaa.png", "ctf_b.png", "ctf_r.png" ],
					weapons:  [  [ "weapon.md2", "weapon.png" ]	]
				};
				character = new THREE.MD2Character();
				character.scale = 3;
				character.onLoadComplete = function() {
					setupSkinsGUI( character );
					setupWeaponsGUI( character );
					setupGUIAnimations( character );
					character.setAnimation( character.meshBody.geometry.animations[0].name )
				};
				character.loadParts( config );
				scene.add( character.root );
			}
			// EVENT HANDLERS
			function onWindowResize( event ) {
				SCREEN_WIDTH = window.innerWidth;
				SCREEN_HEIGHT = window.innerHeight;
				renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );
				camera.aspect = SCREEN_WIDTH/ SCREEN_HEIGHT;
				camera.updateProjectionMatrix();
			}
			// GUI
			function labelize( text ) {
				var parts = text.split( "." );
				if ( parts.length > 1 ) {
					parts.length -= 1;
					return parts.join( "." );
				}
				return text;
			}
			//
			function setupWeaponsGUI( character ) {
				var folder = gui.addFolder( "Weapons" );
				var generateCallback = function( index ) {
					return function () { character.setWeapon( index ); };
				};
				var guiItems = [];
				for ( var i = 0; i < character.weapons.length; i ++ ) {
					var name = character.weapons[ i ].name;
					playbackConfig[ name ] = generateCallback( i );
					guiItems[ i ] = folder.add( playbackConfig, name ).name( labelize( name ) );
				}
			}
			//
			function setupSkinsGUI( character ) {
				var folder = gui.addFolder( "Skins" );
				var generateCallback = function( index ) {
					return function () { character.setSkin( index ); };
				};
				var guiItems = [];
				for ( var i = 0; i < character.skinsBody.length; i ++ ) {
					var name = character.skinsBody[ i ].name;
					playbackConfig[ name ] = generateCallback( i );
					guiItems[ i ] = folder.add( playbackConfig, name ).name( labelize( name ) );
				}
			}
			//
			function setupGUIAnimations( character ) {
				var folder = gui.addFolder( "Animations" );
				var generateCallback = function( animationClip ) {
					return function () { character.setAnimation( animationClip.name ); };
				};
				var i = 0, guiItems = [];
				var animations = character.meshBody.geometry.animations;
				for ( var i = 0; i < animations.length; i ++ ) {
					var clip = animations[i];
					playbackConfig[ clip.name ] = generateCallback( clip );
					guiItems[ i ] = folder.add( playbackConfig, clip.name, clip.name );
					i ++;
				}
			}
			//
			function animate() {
				requestAnimationFrame( animate );
				render();
				stats.update();
			}
			function render() {
				var delta = clock.getDelta();
				controls.update();
				character.update( delta );
				renderer.render( scene, camera );
			}
		</script>

	</body>
</html>