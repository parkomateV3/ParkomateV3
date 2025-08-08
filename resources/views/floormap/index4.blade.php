@extends('dashboard.header')
@section('content')
<title>3D Parking Visualization</title>
<style>
    canvas {
        display: block;
    }
</style>

<div class="flex justify-between flex-wrap items-center mb-6" style="margin-bottom: 0px !important;">
    <h4
        class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
        Floor Map</h4>
</div>

<div>
    <div class="grid grid-cols-12">
        <div id="scene-container"></div>
    </div>
</div>



<div id="data-container"
    data-coordinate='@json($coordinates)'
    data-dimension='@json($dimension)'
    data-car_scale='@json($car_scale)'
    data-floor_image='@json($floor_image)'>
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
    var floor_image = JSON.parse(container.dataset.floor_image);
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
        // scene.background = new THREE.Color(0x0000ffff); // <-- Set background to white
        // Camera setup
        camera = new THREE.PerspectiveCamera(130, window.innerWidth / window.innerHeight, 1, 1000);
        renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true
        });
        renderer.setSize(window.innerWidth - 130, window.innerHeight);
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
            var y = 0;
            // if (item.status == 1) {
            loadVehicleWithLabel("car", position, Math.PI * item.a / 180, item.label);
            // }
            // if (item.status == 1) {
            //     loadVehicle("car", position, Math.PI * item.a / 180);
            //     y = 35;
            //     addLabel(item.label, {
            //         x: Math.floor(item.x - dimension[0] / 2),
            //         y: y,
            //         z: Math.floor(item.y - dimension[1] / 2),
            //     }, {
            //         x: Math.PI * 270 / 180,
            //         y: Math.PI * 0 / 180,
            //         z: Math.PI * 0 / 180
            //     });
            // } else {
            //     y = 10; // Slightly above floor
            //     addLabel(item.label, {
            //         x: Math.floor(item.x - dimension[0] / 2),
            //         y: y,
            //         z: Math.floor(item.y - dimension[1] / 2),
            //     }, {
            //         x: Math.PI * 270 / 180,
            //         y: Math.PI * 0 / 180,
            //         z: Math.PI * 0 / 180
            //     });
            // }
            // position.y += 20; // Raise label above the car

        });

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.1;
        controls.minDistance = 10;
        controls.maxDistance = 1000;
        camera.position.set(0, 300, 0);
        controls.update();
    }

    let carObjects = [];
    let carLabels = [];
    let raycaster = new THREE.Raycaster();
    let mouse = new THREE.Vector2();

    function loadVehicleWithLabel(modelName, position, rotation, labelText) {
        const mtlLoader = new THREE.MTLLoader();
        const objLoader = new THREE.OBJLoader();

        mtlLoader.setPath("{{ asset('vehicles/') }}/");
        mtlLoader.load(`${modelName}.mtl`, (materials) => {
            materials.preload();
            objLoader.setMaterials(materials);
            objLoader.setPath("{{ asset('vehicles/') }}/");
            objLoader.load(`${modelName}.obj`, (object) => {
                object.position.set(position.x, position.y, position.z);
                object.scale.set(car_scale[0], car_scale[1], car_scale[2]);
                object.rotation.y = rotation;

                scene.add(object);
                carObjects.push(object); // Add to interactive list

                // Add hidden label slightly above the car
                let labelPos = {
                    ...position,
                    y: position.y + 35
                };
                let labelMesh = createLabel(labelText, labelPos);
                labelMesh.visible = true; // hidden initially
                carLabels.push({
                    car: object,
                    label: labelMesh
                });
            });
        });
    }

    function createLabel(text, position) {
        const canvas = document.createElement("canvas");
        const context = canvas.getContext("2d");

        canvas.width = 300;
        canvas.height = 150;

        context.font = "Bold 50px Arial";
        context.fillStyle = "white";
        context.textAlign = "center";
        context.fillText(text, canvas.width / 2, canvas.height / 2);

        const texture = new THREE.CanvasTexture(canvas);
        texture.needsUpdate = true;

        const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: false
        });

        const plane = new THREE.Mesh(new THREE.PlaneGeometry(100, 50), material);
        plane.position.set(position.x, position.y, position.z);
        scene.add(plane);
        return plane;
    }

    window.addEventListener('mousemove', onMouseMove, false);

    function onMouseMove(event) {
        console.log('hover');

        // Normalize mouse position
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

        // Raycast from camera to mouse
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(carObjects);

        // Hide all labels first
        carLabels.forEach(item => item.label.visible = false);

        if (intersects.length > 0) {
            const hoveredCar = intersects[0].object;
            const labelItem = carLabels.find(item => item.car === hoveredCar);
            if (labelItem) labelItem.label.visible = true;
        }
    }


    function loadVehicle(modelName, position, rotation = 0) {
        const mtlLoader = new THREE.MTLLoader();
        const objLoader = new THREE.OBJLoader();

        mtlLoader.setPath("{{ asset('vehicles/') }}/");
        mtlLoader.load(`${modelName}.mtl`, (materials) => {
            materials.preload();
            objLoader.setMaterials(materials);
            objLoader.setPath("{{ asset('vehicles/') }}/");
            objLoader.load(`${modelName}.obj`, (object) => {
                object.position.set(position.x, position.y, position.z);
                object.scale.set(car_scale[0], car_scale[1], car_scale[2]);
                object.rotation.y = rotation; // Rotate the car
                scene.add(object);
            });
        });
    }


    function addLabel(text, position, rotation) {

        const canvas = document.createElement("canvas");
        const context = canvas.getContext("2d");

        canvas.width = 300;
        canvas.height = 150;

        context.font = "Bold 50px Arial";
        context.fillStyle = "white"; // Text color
        context.textAlign = "center";
        context.fillText(text, canvas.width / 2, canvas.height / 2);

        const texture = new THREE.CanvasTexture(canvas);
        texture.needsUpdate = true;

        const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true
        });

        const plane = new THREE.Mesh(new THREE.PlaneGeometry(100, 50), material);

        plane.position.set(position.x, position.y, position.z);

        // Rotate the text
        plane.rotation.set(rotation.x, rotation.y, rotation.z);

        scene.add(plane);

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
</script>

@endsection