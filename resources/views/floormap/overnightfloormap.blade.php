@extends('dashboard.header')
@section('content')
<title>3D Parking Visualization</title>
<style>
    #scene-container {
        width: 100%;
        height: 600px;
        /* adjust based on space needed */
        position: relative;
        z-index: 1;
        overflow: hidden;
        background-color: transparent;
        /* or white */
    }

    canvas {
        display: block;
        max-width: 100%;
        max-height: 100%;
    }
</style>

<div class="flex justify-between flex-wrap items-center mb-6" style="margin-bottom: 0px !important;">
    <h4
        class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse" id="floorname">
        {{getFloorName($floor_id)}}
    </h4>
    <h4
        class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
        <span class="text-danger-500" id="available">Overnight Stay: {{count($overnightSensors)}}</span> &nbsp; <span class="text-primary-500" id="occupied">Overtime Stay: {{count($overDaysSensors)}}</span>
    </h4>


    <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse">

        <!-- <h4
            class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
            Overnight Stay: {{count($overnightSensors)}}</h4> -->
        <span>
            <a href="{{ url('dashboard/floormap/'. $floor_id) }}" class="btn btn-sm btn-primary">View Floormap</a>
        </span>
    </div>
</div>

<div>
    <div id="scene-container"></div>
</div>



<div id="data-container"
    data-coordinate='@json($coordinates)'
    data-dimension='@json($dimension)'
    data-car_scale='@json($car_scale)'
    data-floor_image='@json($floor_image)'
    data-floor_id='@json($floor_id)'
    data-overnight_sensors='@json($overnightSensors)'
    data-overdayssensors='@json($overDaysSensors)'
    data-labelsize='@json($label_size)'>
</div>


<!-- Three.js and Required Loaders -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/MTLLoader.js"></script>
<script src="https://unpkg.com/three-spritetext@latest"></script>

<script>
    var container = document.getElementById('data-container');
    var coordinate = JSON.parse(container.dataset.coordinate);
    var car_scale = JSON.parse(container.dataset.car_scale);
    var dimension = JSON.parse(container.dataset.dimension);
    var labelsize = JSON.parse(container.dataset.labelsize);
    var floor_image = JSON.parse(container.dataset.floor_image);
    var overnightSensors = container.dataset.overnight_sensors ? JSON.parse(container.dataset.overnight_sensors) : [];
    var overDaysSensors = container.dataset.overdayssensors ? JSON.parse(container.dataset.overdayssensors) : [];
    // console.log(overnightSensors);

    var floorImagePath = "{{ asset('floors') }}/" + floor_image;
    // coordinate.map((item, index) => {
    //     console.log(item);
    // });

    let scene, camera, renderer, controls;
    var width = dimension[0];
    var height = dimension[1];

    function init() {
        // Scene setup
        scene = new THREE.Scene();

        // Camera setup
        camera = new THREE.PerspectiveCamera(130, window.innerWidth / window.innerHeight, 1, 1000);
        camera.layers.enable(1);
        renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true
        });
        renderer.setPixelRatio(window.devicePixelRatio); // <-- ADD HERE
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById("scene-container").appendChild(renderer.domElement);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(10, 10, 10);
        scene.add(directionalLight);

        // Load floor texture
        const textureLoader = new THREE.TextureLoader();
        textureLoader.load(
            floorImagePath,
            (texture) => {
                texture.wrapS = THREE.RepeatWrapping;
                texture.wrapT = THREE.RepeatWrapping;

                const floorGeometry = new THREE.PlaneGeometry(width, height);
                const floorMaterial = new THREE.MeshStandardMaterial({
                    map: texture,
                    roughness: 0.8,
                    metalness: 0.2,
                });

                const floor = new THREE.Mesh(floorGeometry, floorMaterial);
                floor.rotation.x = -Math.PI / 2;
                floor.receiveShadow = true;
                scene.add(floor);
            },
            undefined,
            (error) => console.error("Error loading floor texture:", error)
        );

        // Ensure `scene` is defined before calling addLabel
        coordinate.forEach((item, index) => {
            let position = {
                x: Math.floor(item.x - dimension[0] / 2),
                y: item.z,
                z: Math.floor(item.y - dimension[1] / 2),
            };

            if (overnightSensors.some(sensor => sensor == item.sensor_id)) {
                // console.log(overnightSensors);
                // console.log(item.sensor_id);
                var y = 5;
                createLabel(item.label, {
                    x: Math.floor(item.x - dimension[0] / 2),
                    y: y,
                    z: Math.floor(item.y - dimension[1] / 2),
                }, {
                    x: Math.PI * 270 / 180,
                    y: Math.PI * 0 / 180,
                    z: Math.PI * 0 / 180
                }, 'rgba(255, 0, 0, 0.7)', '#ffffff', true);
            }
            if (overDaysSensors.some(sensor => sensor == item.sensor_id)) {
                // console.log(overnightSensors);
                // console.log(item.sensor_id);
                var y = 5;
                createLabel(item.label, {
                    x: Math.floor(item.x - dimension[0] / 2),
                    y: y,
                    z: Math.floor(item.y - dimension[1] / 2),
                }, {
                    x: Math.PI * 270 / 180,
                    y: Math.PI * 0 / 180,
                    z: Math.PI * 0 / 180
                }, 'rgba(0, 50, 255, 0.7)', '#ffffff', true);
            }


        });

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.1;
        controls.minDistance = 5;
        controls.maxDistance = 1000;
        camera.position.set(0, 300, 0);
        camera.lookAt(0, 0, 0);
        controls.update();

        // Restore saved camera view if available
        const savedView = localStorage.getItem('cameraView');
        if (savedView) {
            const view = JSON.parse(savedView);
            camera.position.set(view.position.x, view.position.y, view.position.z);
            controls.target.set(view.target.x, view.target.y, view.target.z);
            controls.update(); // Required after setting position & target
        } else {
            camera.position.set(0, 300, 0); // Default view
            controls.update();
        }
    }


    function createLabel(text, position, rotation, labelColor, textColor, isStatic = false) {
        const fontsize = parseInt(labelsize);
        const padding = 10;
        const ctx = document.createElement('canvas').getContext('2d');
        ctx.font = `bold ${fontsize}px Arial`;
        const textWidth = ctx.measureText(text).width;

        // 2. Create properly sized canvas
        const canvas = document.createElement("canvas");
        const context = canvas.getContext("2d");
        canvas.width = textWidth + padding * 2;
        canvas.height = fontsize + padding * 1;

        // 3. Draw background
        context.fillStyle = labelColor;
        context.beginPath();
        context.roundRect(0, 0, canvas.width, canvas.height, 15);
        context.fill();

        // 4. Draw text centered
        context.fillStyle = textColor;
        context.font = `bold ${fontsize}px Arial`;
        context.textAlign = "center";
        context.textBaseline = "middle";
        context.fillText(text, canvas.width / 2, canvas.height / 2);

        // 5. Create texture
        const texture = new THREE.CanvasTexture(canvas);
        texture.needsUpdate = true;

        // 6. Create sprite material
        const material = new THREE.SpriteMaterial({
            map: texture,
            transparent: true,
            depthTest: false
        });

        // 7. Create sprite instead of plane
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(canvas.width * 0.2, canvas.height * 0.3, 1);
        sprite.position.set(position.x, position.y, position.z); // Offset Y position

        // 8. Billboard behavior (always face camera)
        sprite.material.rotation = Math.PI * 2; // Adjust based on your camera angle
        sprite.renderOrder = 999;
        // Store whether this label is static (for available spots)
        sprite.userData.isStatic = isStatic;
        // For static labels always show them, for dynamic labels start as hidden.
        sprite.visible = isStatic ? true : false;
        scene.add(sprite);
        return sprite;
    }

    // Handle window resize
    window.addEventListener('resize', onWindowResize, false);

    function onWindowResize() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    }

    // Animation loop
    function animate() {
        requestAnimationFrame(animate);

        controls.update(); // Required for damping
        renderer.render(scene, camera);
    }

    // Start the app
    init();
    animate();

    // Save camera view before page unload
    window.addEventListener('beforeunload', function() {
        const camPos = camera.position;
        const target = controls.target;

        const cameraView = {
            position: {
                x: camPos.x,
                y: camPos.y,
                z: camPos.z
            },
            target: {
                x: target.x,
                y: target.y,
                z: target.z
            }
        };

        localStorage.setItem('cameraView', JSON.stringify(cameraView));
    });
</script>

<script>
    // $(document).ready(function() {
        // refreshDashboard();
        // // getLast4HourGraphInitialData();


        // setInterval(function() {
        //     refreshDashboard();
        // }, 5000);
    // });

    // function refreshDashboard() {
    //     var floor_id = JSON.parse(container.dataset.floor_id);
    //     const baseUrl = window.location.origin + "/dashboard/floormap/getfloordata/" + floor_id;

    //     $.get(baseUrl, function(data, status) {
    //         if (data.data) {
    //             $('#available').text('Available: ' + data.available);
    //             $('#occupied').text('Occupied: ' + data.occupied);
    //             $('#total').text('Total: ' + data.total);
    //             $('#floorname').text(data.floor_name);
    //         } else {
    //             console.log('Data not found!');
    //         }


    //     });
    // }
</script>

@endsection