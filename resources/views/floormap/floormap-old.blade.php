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
    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse" id="floorname">
        {{getFloorName($floor_id)}}
    </h4>
    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
        <span class="text-success-500" id="available">Available: 0</span> &nbsp; <span class="text-danger-500" id="occupied">Occupied: 0</span> &nbsp; <span id="total">Total: 0</span>
    </h4>


    <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse">

        <!-- <h4
            class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
            Floor Map</h4> -->
        <h5>
            <a href="{{ url('dashboard/overnightfloormap/'. $floor_id) }}" class="btn btn-sm btn-primary">View Overnight</a>
        </h5>
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
            if (item.status == 1) {
                loadVehicle(item.v_color, position, Math.PI * item.a / 180, item.label); // show label on hover only
            } else if (item.status == 2) {
                var y = 5;
                createLabel(item.label, {
                    x: Math.floor(item.x - dimension[0] / 2),
                    y: y,
                    z: Math.floor(item.y - dimension[1] / 2),
                }, {
                    x: Math.PI * 270 / 180,
                    y: Math.PI * 0 / 180,
                    z: Math.PI * item.a / 180
                }, 'rgba(255, 0, 0, 0.7)', '#ffffff');
            } else if (item.status == 4) {
                var y = 5;
                createLabel(item.label, {
                    x: Math.floor(item.x - dimension[0] / 2),
                    y: y,
                    z: Math.floor(item.y - dimension[1] / 2),
                }, {
                    x: Math.PI * 270 / 180,
                    y: Math.PI * 0 / 180,
                    z: Math.PI * item.a / 180
                }, 'rgba(255, 255, 0, 0.7)', '#000000');
            } else {
                var y = 5;
                createLabel(item.label, {
                    x: Math.floor(item.x - dimension[0] / 2),
                    y: y,
                    z: Math.floor(item.y - dimension[1] / 2),
                }, {
                    x: Math.PI * 270 / 180,
                    y: Math.PI * 0 / 180,
                    z: Math.PI * item.a / 180
                }, 'rgba(0, 0, 0, 0.7)', '#ffffff');
            }

        });

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        // controls.zoomToCursor = true;
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


    let carObjects = {};
    let carLabels = {};
    let carStates = {}; // 🔥 Add it here!
    let raycaster = new THREE.Raycaster();
    let mouse = new THREE.Vector2();

    function loadVehicle(modelName, position, rotation = 0, labelText = "") {
        // Remove any existing label (it might be static from an "available" state)
        if (carLabels[labelText]) {
            scene.remove(carLabels[labelText]);
            delete carLabels[labelText];
        }
        const mtlLoader = new THREE.MTLLoader();
        const objLoader = new THREE.OBJLoader();

        mtlLoader.setPath("{{ asset('vehicles/') }}/");
        mtlLoader.load(`${modelName}.mtl`, (materials) => {
            materials.preload();
            objLoader.setMaterials(materials);
            objLoader.setPath("{{ asset('vehicles/') }}/");
            objLoader.load(`${modelName}.obj`, (object) => {
                const group = new THREE.Group();

                object.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;
                    }
                });

                object.position.set(position.x, position.y, position.z);
                object.scale.set(car_scale[0], car_scale[1], car_scale[2]);
                object.rotation.y = rotation;

                group.add(object);
                scene.add(group);
                carObjects[labelText] = group;

                // Create dynamic (hover-based) label for vehicle; start hidden.
                const label = createLabel(labelText, {
                        x: position.x,
                        y: position.y + 35,
                        z: position.z
                    }, {
                        x: Math.PI * 270 / 180,
                        y: 0,
                        z: 0
                    }, 'rgba(0, 0, 0, 0.7)', '#ffffff',
                    false // dynamic: not static, so hide by default
                );
                label.visible = false;
                carLabels[labelText] = label;
            });
        });
    }


    // function createLabel(text, position, rotation) {
    //     const canvas = document.createElement("canvas");
    //     const context = canvas.getContext("2d");

    //     canvas.width = 300;
    //     canvas.height = 150;

    //     context.font = "Bold 70px Arial";
    //     context.fillStyle = "white";
    //     context.textAlign = "center";
    //     context.fillText(text, canvas.width / 2, canvas.height / 2);

    //     const texture = new THREE.CanvasTexture(canvas);
    //     const material = new THREE.MeshBasicMaterial({
    //         map: texture,
    //         transparent: true
    //     });

    //     const plane = new THREE.Mesh(new THREE.PlaneGeometry(100, 50), material);
    //     plane.position.set(position.x, position.y, position.z);
    //     plane.rotation.set(rotation.x, rotation.y, rotation.z);
    //     scene.add(plane);
    //     return plane;
    // }

    function createLabel(text, position, rotation, labelColor, textColor, isStatic = false) {
        // 1. Calculate text dimensions first
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
        texture.minFilter = THREE.LinearFilter;
        texture.generateMipmaps = false;
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



    window.addEventListener('mousemove', onMouseMove, false);
    window.addEventListener('touchstart', onTouchMove, false);
    window.addEventListener('touchmove', onTouchMove, false);

    function onTouchMove(event) {
        if (event.touches.length > 0) {
            const touch = event.touches[0];
            const canvasBounds = renderer.domElement.getBoundingClientRect();

            mouse.x = ((touch.clientX - canvasBounds.left) / canvasBounds.width) * 2 - 1;
            mouse.y = -((touch.clientY - canvasBounds.top) / canvasBounds.height) * 2 + 1;
            raycaster.setFromCamera(mouse, camera);

            // Hide all dynamic labels
            Object.values(carLabels).forEach(labelMesh => {
                if (!labelMesh.userData.isStatic) {
                    labelMesh.visible = false;
                }
            });

            // Gather meshes for all cars
            const meshes = [];
            for (const group of Object.values(carObjects)) {
                group.traverse(child => {
                    if (child.isMesh) meshes.push(child);
                });
            }

            const intersects = raycaster.intersectObjects(meshes, true);
            if (intersects.length > 0) {
                const intersected = intersects[0].object;
                // Find the parent group of the intersected mesh
                let parentGroup = null;
                for (const group of Object.values(carObjects)) {
                    if (group === intersected.parent || group.children.includes(intersected.parent) || group.children.includes(intersected)) {
                        parentGroup = group;
                        break;
                    }
                }
                if (parentGroup) {
                    // Show the dynamic label for the vehicle
                    for (const [label, group] of Object.entries(carObjects)) {
                        if (group === parentGroup && carLabels[label]) {
                            carLabels[label].visible = true;
                            break;
                        }
                    }
                }
            }
        }
    }


    // function onMouseMove(event) {
    //     const canvasBounds = renderer.domElement.getBoundingClientRect();

    //     mouse.x = ((event.clientX - canvasBounds.left) / canvasBounds.width) * 2 - 1;
    //     mouse.y = -((event.clientY - canvasBounds.top) / canvasBounds.height) * 2 + 1;

    //     raycaster.setFromCamera(mouse, camera);

    //     // Get all meshes inside car groups
    //     const meshes = carObjects.flatMap(group => group.children);
    //     const intersects = raycaster.intersectObjects(meshes, true);

    //     // Hide all labels first
    //     carLabels.forEach(item => item.label.visible = false);

    //     if (intersects.length > 0) {
    //         const intersected = intersects[0].object;

    //         // Find the group this mesh belongs to
    //         let parentGroup = null;
    //         for (const group of carObjects) {
    //             if (group.children.includes(intersected.parent) || group.children.includes(intersected)) {
    //                 parentGroup = group;
    //                 break;
    //             }
    //         }

    //         if (parentGroup) {
    //             const labelObj = carLabels.find(item => item.car === parentGroup);
    //             if (labelObj) {
    //                 labelObj.label.visible = true;
    //             }
    //         }
    //     }
    // }

    function onMouseMove(event) {
        const canvasBounds = renderer.domElement.getBoundingClientRect();

        mouse.x = ((event.clientX - canvasBounds.left) / canvasBounds.width) * 2 - 1;
        mouse.y = -((event.clientY - canvasBounds.top) / canvasBounds.height) * 2 + 1;
        raycaster.setFromCamera(mouse, camera);

        // Only hide hover-only labels (those with isStatic false)
        Object.values(carLabels).forEach(labelMesh => {
            if (!labelMesh.userData.isStatic) {
                labelMesh.visible = false;
            }
        });

        // Gather meshes for all cars
        const meshes = [];
        for (const group of Object.values(carObjects)) {
            group.traverse(child => {
                if (child.isMesh) meshes.push(child);
            });
        }

        const intersects = raycaster.intersectObjects(meshes, true);
        if (intersects.length > 0) {
            const intersected = intersects[0].object;
            // Find the parent group of the intersected mesh
            let parentGroup = null;
            for (const group of Object.values(carObjects)) {
                if (group === intersected.parent || group.children.includes(intersected.parent) || group.children.includes(intersected)) {
                    parentGroup = group;
                    break;
                }
            }
            if (parentGroup) {
                // Only show the dynamic (hover-based) label for the vehicle
                for (const [label, group] of Object.entries(carObjects)) {
                    if (group === parentGroup && carLabels[label]) {
                        carLabels[label].visible = true;
                        break;
                    }
                }
            }
        }
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
    $(document).ready(function() {
        refreshDashboard();
        // getLast4HourGraphInitialData();


        setInterval(function() {
            refreshDashboard();
        }, 5000);
    });

    function refreshDashboard() {
        var floor_id = JSON.parse(container.dataset.floor_id);
        const baseUrl = window.location.origin + "/dashboard/floormap/getfloordata/" + floor_id;

        $.get(baseUrl, function(data, status) {
            if (data.data) {
                updateCars(data.coordinates);
                $('#available').text('Available: ' + data.available);
                $('#occupied').text('Occupied: ' + data.occupied);
                $('#total').text('Total: ' + data.total);
                $('#floorname').text(data.floor_name);
            } else {
                console.log('Data not found!');
            }


        });
    }

    function updateCars(newCoordinates) {
        const seenLabels = new Set();

        newCoordinates.forEach(item => {
            const label = item.label;
            const status = item.status;
            seenLabels.add(label);

            let position = {
                x: Math.floor(item.x - dimension[0] / 2),
                y: item.z,
                z: Math.floor(item.y - dimension[1] / 2),
            };

            const rotation = Math.PI * item.a / 180;
            const prevStatus = carStates[label];
            // Set new status immediately
            carStates[label] = status;

            // If status has changed, remove any previously rendered car or label
            if (prevStatus !== undefined && prevStatus !== status) {
                if (carObjects[label]) {
                    scene.remove(carObjects[label]);
                    delete carObjects[label];
                }
                if (carLabels[label]) {
                    scene.remove(carLabels[label]);
                    delete carLabels[label];
                }
            }

            if (status == 1) { // Occupied - show car (hover-based label)
                if (!carObjects[label]) {
                    loadVehicle(item.v_color, position, rotation, label);
                } else {
                    // Update existing car position
                    const car = carObjects[label];
                    car.children[0].position.set(position.x, position.y, position.z);
                    car.children[0].rotation.y = rotation;
                }
            } else { // Available - show static label
                if (!carLabels[label]) {
                    // Create a static label: isStatic true makes it always visible
                    if (status == 2) {
                        var labelMesh = createLabel(label, {
                                x: position.x,
                                y: 5,
                                z: position.z
                            }, {
                                x: Math.PI * 270 / 180,
                                y: Math.PI * 0 / 180,
                                z: Math.PI * item.a / 180
                            }, 'rgba(255, 0, 0, 0.7)', '#ffffff',
                            true // static label
                        );
                    } else if (status == 4) {
                        var labelMesh = createLabel(label, {
                                x: position.x,
                                y: 5,
                                z: position.z
                            }, {
                                x: Math.PI * 270 / 180,
                                y: Math.PI * 0 / 180,
                                z: Math.PI * item.a / 180
                            }, 'rgba(255, 255, 0, 0.7)', '#000000',
                            true // static label
                        );
                    } else {
                        var labelMesh = createLabel(label, {
                                x: position.x,
                                y: 5,
                                z: position.z
                            }, {
                                x: Math.PI * 270 / 180,
                                y: Math.PI * 0 / 180,
                                z: Math.PI * item.a / 180
                            }, 'rgba(0, 0, 0, 0.7)', '#ffffff',
                            true // static label
                        );
                    }
                    carLabels[label] = labelMesh;
                } else {
                    carLabels[label].position.set(position.x, 5, position.z);
                }
            }
        });

        // Remove any cars/labels for which we no longer have data
        for (const label in carStates) {
            if (!seenLabels.has(label)) {
                if (carObjects[label]) {
                    scene.remove(carObjects[label]);
                    delete carObjects[label];
                }
                if (carLabels[label]) {
                    scene.remove(carLabels[label]);
                    delete carLabels[label];
                }
                delete carStates[label];
            }
        }
    }
</script>

@endsection