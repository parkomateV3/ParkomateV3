<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Map</title>
</head>
<style>

</style>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('dashboard/assets/images/logo/logo-white-2.png') }}" alt="" width="80" height="60">
            </a>
        </div>
    </nav>
    <div class="container mt-5 mb-5 pb-5">

        <h4 class="text-center fw-bolder">You are in {{getSitename($site_id)}},<br> on {{getFloorname($floor_id)}}, near {{$location}},<br> your destination is at {{getFloorname($interFloorId)}},<br> please move to {{$interCoordinate}} for {{getFloorname($interFloorId)}}</h4>

        <div class="row" style="text-align: right;">
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/current_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Current Location</span>
            </div>
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/destination_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Destination Location</span>
            </div>
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/interconnect_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Interconnect Location</span>
            </div>
        </div>

        <div id="scene-container"></div>

        <hr style="border: 1px solid black; width: 80%; opacity: 1; display: block; margin: 20px auto;">

        <div id="scene-container-2"></div>

        <hr style="border: 2px solid black; opacity: 1; display: block; margin: 20px auto;">

    </div>

    @php $ad_image = getSiteAdImage($site_id); @endphp
    @if($ad_image != null || $ad_image != '')
    <footer class="bg-dark text-white text-center mt-auto w-100" style="bottom: 0;">
        <img src="{{ asset('logos/' . $ad_image) }}" class="" alt="footer-image" height="60" width="100%">
    </footer>
    @endif


    <div id="data-container"
        data-coordinate='@json($carcoordinates)'
        data-coordinate2='@json($carcoordinates2)'
        data-dimension='@json($dimension)'
        data-dimension2='@json($dimension2)'
        data-car_scale='@json($car_scale)'
        data-car_scale2='@json($car_scale2)'
        data-floor_image='@json($processedImg)'
        data-floor_image2='@json($processedImg1)'
        data-floor_id='@json($floor_id)'
        data-floor_id2='@json($interFloorId)'
        data-labelsize='@json($label_size)'
        data-locationdata='@json($locationData)'
        data-labelsize2='@json($label_size2)'>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        var locationdata = JSON.parse(container.dataset.locationdata);
        var floorImagePath = JSON.parse(container.dataset.floor_image);

        // inter floor
        var coordinate2 = JSON.parse(container.dataset.coordinate2);
        var car_scale2 = JSON.parse(container.dataset.car_scale2);
        var dimension2 = JSON.parse(container.dataset.dimension2);
        var labelsize2 = JSON.parse(container.dataset.labelsize2);
        var floorImagePath2 = JSON.parse(container.dataset.floor_image2);
        // var floorImagePath = "{{ asset('floors') }}/" + floor_image;
        // coordinate.map((item, index) => {
        //     console.log(item);
        // });

        let scene, camera, renderer, controls;
        let scene2, camera2, renderer2, controls2;
        var width = dimension[0];
        var height = dimension[1];
        var width2 = dimension2[0];
        var height2 = dimension2[1];

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

            // Generate random coordinates
            const generateRandomPosition = (xc, zc) => ({
                x: xc, // -20 to 20 range
                y: 5,
                z: zc
            });

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

            let locPosition = {
                x: Math.floor(locationdata[0] + 15 - dimension[0] / 2),
                y: 15,
                z: Math.floor(locationdata[1] + 15 - dimension[1] / 2),
            };
            loadLocation('blue_loc', locPosition, Math.PI * 180 / 180, "current");
            locPosition = {
                x: Math.floor(locationdata[2] + 15 - dimension[0] / 2),
                y: 15,
                z: Math.floor(locationdata[3] + 15 - dimension[1] / 2),
            };
            loadLocation('yellow_loc', locPosition, Math.PI * 180 / 180, "interconnection");
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
            // const savedView = localStorage.getItem('cameraView');
            // if (savedView) {
            //     const view = JSON.parse(savedView);
            //     camera.position.set(view.position.x, view.position.y, view.position.z);
            //     controls.target.set(view.target.x, view.target.y, view.target.z);
            //     controls.update(); // Required after setting position & target
            // } else {
            //     camera.position.set(0, 300, 0); // Default view
            //     controls.update();
            // }
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

        function loadLocation(modelName, position, rotation = 0, labelText = "") {
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
                    object.scale.set(car_scale[0] * 20, car_scale[1] * 20, car_scale[2] * 20);
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

        let carObjects2 = {};
        let carLabels2 = {};
        let carStates2 = {}; // 🔥 Add it here!
        let raycaster2 = new THREE.Raycaster();
        let mouse2 = new THREE.Vector2();

        function loadVehicle2(modelName, position, rotation = 0, labelText = "") {
            // Remove any existing label (it might be static from an "available" state)
            if (carLabels2[labelText]) {
                scene2.remove(carLabels2[labelText]);
                delete carLabels2[labelText];
            }
            const mtlLoader2 = new THREE.MTLLoader();
            const objLoader2 = new THREE.OBJLoader();

            mtlLoader2.setPath("{{ asset('vehicles/') }}/");
            mtlLoader2.load(`${modelName}.mtl`, (materials) => {
                materials.preload();
                objLoader2.setMaterials(materials);
                objLoader2.setPath("{{ asset('vehicles/') }}/");
                objLoader2.load(`${modelName}.obj`, (object) => {
                    const group2 = new THREE.Group();

                    object.traverse((child) => {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                        }
                    });

                    object.position.set(position.x, position.y, position.z);
                    object.scale.set(car_scale[0], car_scale[1], car_scale[2]);
                    object.rotation.y = rotation;

                    group2.add(object);
                    scene2.add(group2);
                    carObjects2[labelText] = group2;

                    // Create dynamic (hover-based) label for vehicle; start hidden.
                    const label = createLabel2(labelText, {
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
                    carLabels2[labelText] = label;
                });
            });
        }

        function loadLocation2(modelName, position, rotation = 0, labelText = "") {
            // Remove any existing label (it might be static from an "available" state)
            if (carLabels2[labelText]) {
                scene2.remove(carLabels2[labelText]);
                delete carLabels2[labelText];
            }
            const mtlLoader2 = new THREE.MTLLoader();
            const objLoader2 = new THREE.OBJLoader();

            mtlLoader2.setPath("{{ asset('vehicles/') }}/");
            mtlLoader2.load(`${modelName}.mtl`, (materials) => {
                materials.preload();
                objLoader2.setMaterials(materials);
                objLoader2.setPath("{{ asset('vehicles/') }}/");
                objLoader2.load(`${modelName}.obj`, (object) => {
                    const group2 = new THREE.Group();

                    object.traverse((child) => {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                        }
                    });

                    object.position.set(position.x, position.y, position.z);
                    object.scale.set(car_scale2[0] * 20, car_scale2[1] * 20, car_scale2[2] * 20);
                    object.rotation.y = rotation;

                    group2.add(object);
                    scene2.add(group2);
                    carObjects2[labelText] = group2;

                    // Create dynamic (hover-based) label for vehicle; start hidden.
                    const label2 = createLabel2(labelText, {
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
                    label2.visible = false;
                    carLabels2[labelText] = label2;
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

        function createLabel2(text, position, rotation, labelColor, textColor, isStatic = false) {
            // 1. Calculate text dimensions first
            const fontsize2 = parseInt(labelsize);
            const padding2 = 10;
            const ctx2 = document.createElement('canvas').getContext('2d');
            ctx2.font = `bold ${fontsize2}px Arial`;
            const textWidth2 = ctx2.measureText(text).width;

            // 2. Create properly sized canvas
            const canvas2 = document.createElement("canvas");
            const context2 = canvas2.getContext("2d");
            canvas2.width = textWidth2 + padding2 * 2;
            canvas2.height = fontsize2 + padding2 * 1;

            // 3. Draw background
            context2.fillStyle = labelColor;
            context2.beginPath();
            context2.roundRect(0, 0, canvas2.width, canvas2.height, 15);
            context2.fill();

            // 4. Draw text centered
            context2.fillStyle = textColor;
            context2.font = `bold ${fontsize2}px Arial`;
            context2.textAlign = "center";
            context2.textBaseline = "middle";
            context2.fillText(text, canvas2.width / 2, canvas2.height / 2);

            // 5. Create texture
            const texture2 = new THREE.CanvasTexture(canvas2);
            texture2.minFilter = THREE.LinearFilter;
            texture2.generateMipmaps = false;
            texture2.needsUpdate = true;

            // 6. Create sprite material
            const material2 = new THREE.SpriteMaterial({
                map: texture2,
                transparent: true,
                depthTest: false
            });

            // 7. Create sprite instead of plane
            const sprite2 = new THREE.Sprite(material2);
            sprite2.scale.set(canvas2.width * 0.2, canvas2.height * 0.3, 1);
            sprite2.position.set(position.x, position.y, position.z); // Offset Y position

            // 8. Billboard behavior (always face camera)
            sprite2.material.rotation = Math.PI * 2; // Adjust based on your camera angle
            sprite2.renderOrder = 999;
            // Store whether this label is static (for available spots)
            sprite2.userData.isStatic = isStatic;
            // For static labels always show them, for dynamic labels start as hidden.
            sprite2.visible = isStatic ? true : false;
            scene2.add(sprite2);
            return sprite2;
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


                // inter floor 
                const touch2 = event.touches[0];
                const canvasBounds2 = renderer2.domElement.getBoundingClientRect();

                mouse2.x = ((touch2.clientX - canvasBounds2.left) / canvasBounds2.width) * 2 - 1;
                mouse2.y = -((touch2.clientY - canvasBounds2.top) / canvasBounds2.height) * 2 + 1;
                raycaster2.setFromCamera(mouse2, camera2);

                // Hide all dynamic labels
                Object.values(carLabels2).forEach(labelMesh2 => {
                    if (!labelMesh2.userData.isStatic) {
                        labelMesh2.visible = false;
                    }
                });

                // Gather meshes for all cars
                const meshes2 = [];
                for (const group2 of Object.values(carObjects2)) {
                    group2.traverse(child => {
                        if (child.isMesh) meshes2.push(child);
                    });
                }

                const intersects2 = raycaster2.intersectObjects(meshes2, true);
                if (intersects2.length > 0) {
                    const intersected2 = intersects2[0].object;
                    // Find the parent group of the intersected mesh
                    let parentGroup2 = null;
                    for (const group2 of Object.values(carObjects2)) {
                        if (group2 === intersected2.parent || group2.children.includes(intersected2.parent) || group2.children.includes(intersected2)) {
                            parentGroup2 = group2;
                            break;
                        }
                    }
                    if (parentGroup2) {
                        // Show the dynamic label for the vehicle
                        for (const [label2, group2] of Object.entries(carObjects2)) {
                            if (group2 === parentGroup2 && carLabels2[label2]) {
                                carLabels2[label2].visible = true;
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


            // inter floor
            const canvasBounds2 = renderer2.domElement.getBoundingClientRect();

            mouse2.x = ((event.clientX - canvasBounds2.left) / canvasBounds2.width) * 2 - 1;
            mouse2.y = -((event.clientY - canvasBounds2.top) / canvasBounds2.height) * 2 + 1;
            raycaster2.setFromCamera(mouse2, camera2);

            // Only hide hover-only labels (those with isStatic false)
            Object.values(carLabels2).forEach(labelMesh2 => {
                if (!labelMesh2.userData.isStatic) {
                    labelMesh2.visible = false;
                }
            });

            // Gather meshes for all cars
            const meshes2 = [];
            for (const group2 of Object.values(carObjects2)) {
                group2.traverse(child2 => {
                    if (child2.isMesh) meshes2.push(child2);
                });
            }

            const intersects2 = raycaster2.intersectObjects(meshes2, true);
            if (intersects2.length > 0) {
                const intersected2 = intersects2[0].object;
                // Find the parent group of the intersected mesh
                let parentGroup2 = null;
                for (const group2 of Object.values(carObjects2)) {
                    if (group2 === intersected2.parent || group2.children.includes(intersected2.parent) || group2.children.includes(intersected2)) {
                        parentGroup2 = group2;
                        break;
                    }
                }
                if (parentGroup2) {
                    // Only show the dynamic (hover-based) label for the vehicle
                    for (const [label2, group2] of Object.entries(carObjects2)) {
                        if (group2 === parentGroup2 && carLabels2[label2]) {
                            carLabels2[label2].visible = true;
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

        function initSecondFloor() {
            scene2 = new THREE.Scene();
            camera2 = new THREE.PerspectiveCamera(130, window.innerWidth / window.innerHeight, 1, 1000);
            renderer2 = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });

            renderer2.setPixelRatio(window.devicePixelRatio);
            renderer2.setSize(window.innerWidth, window.innerHeight);
            document.getElementById("scene-container-2").appendChild(renderer2.domElement);

            // Lighting
            const ambientLight2 = new THREE.AmbientLight(0xffffff, 0.5);
            const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight2.position.set(10, 10, 10);
            scene2.add(ambientLight2);
            scene2.add(directionalLight2);

            // Floor Image
            const textureLoader2 = new THREE.TextureLoader();
            textureLoader2.load(
                floorImagePath2,
                (texture) => {
                    texture.wrapS = THREE.RepeatWrapping;
                    texture.wrapT = THREE.RepeatWrapping;

                    const floorGeometry2 = new THREE.PlaneGeometry(width2, height2);
                    const floorMaterial2 = new THREE.MeshStandardMaterial({
                        map: texture,
                        roughness: 0.8,
                        metalness: 0.2,
                    });

                    const floor2 = new THREE.Mesh(floorGeometry2, floorMaterial2);
                    floor2.rotation.x = -Math.PI / 2;
                    floor2.receiveShadow = true;
                    scene2.add(floor2);
                },
                undefined,
                (error) => console.error("Error loading floor texture for second map:", error)
            );

            let locPosition1 = {
                x: Math.floor(locationdata[4] + 15 - dimension2[0] / 2),
                y: 15,
                z: Math.floor(locationdata[5] + 15 - dimension2[1] / 2),
            };
            loadLocation2('red_loc', locPosition1, Math.PI * 180 / 180, "destination");
            locPosition1 = {
                x: Math.floor(locationdata[6] + 15 - dimension2[0] / 2),
                y: 15,
                z: Math.floor(locationdata[7] + 15 - dimension2[1] / 2),
            };
            loadLocation2('yellow_loc', locPosition1, Math.PI * 180 / 180, "interconnect");
            coordinate2.forEach((item, index) => {
                let position2 = {
                    x: Math.floor(item.x - dimension2[0] / 2),
                    y: item.z,
                    z: Math.floor(item.y - dimension2[1] / 2),
                };
                if (item.status == 1) {
                    loadVehicle2(item.v_color, position2, Math.PI * item.a / 180, item.label); // show label on hover only
                } else if (item.status == 2) {
                    var y = 5;
                    createLabel2(item.label, {
                        x: Math.floor(item.x - dimension2[0] / 2),
                        y: y,
                        z: Math.floor(item.y - dimension2[1] / 2),
                    }, {
                        x: Math.PI * 270 / 180,
                        y: Math.PI * 0 / 180,
                        z: Math.PI * item.a / 180
                    }, 'rgba(255, 0, 0, 0.7)', '#ffffff');
                } else if (item.status == 4) {
                    var y = 5;
                    createLabel2(item.label, {
                        x: Math.floor(item.x - dimension2[0] / 2),
                        y: y,
                        z: Math.floor(item.y - dimension2[1] / 2),
                    }, {
                        x: Math.PI * 270 / 180,
                        y: Math.PI * 0 / 180,
                        z: Math.PI * item.a / 180
                    }, 'rgba(255, 255, 0, 0.7)', '#000000');
                } else {
                    var y = 5;
                    createLabel2(item.label, {
                        x: Math.floor(item.x - dimension2[0] / 2),
                        y: y,
                        z: Math.floor(item.y - dimension2[1] / 2),
                    }, {
                        x: Math.PI * 270 / 180,
                        y: Math.PI * 0 / 180,
                        z: Math.PI * item.a / 180
                    }, 'rgba(0, 0, 0, 0.7)', '#ffffff');
                }

            });

            controls2 = new THREE.OrbitControls(camera2, renderer2.domElement);
            controls2.enableDamping = true;
            controls2.dampingFactor = 0.1;
            controls2.minDistance = 5;
            controls2.maxDistance = 1000;

            camera2.position.set(0, 300, 0);
            controls2.update();

            function animateSecond() {
                requestAnimationFrame(animateSecond);
                controls2.update();
                renderer2.render(scene2, camera2);
            }

            animateSecond();

            window.addEventListener('resize', () => {
                camera2.aspect = window.innerWidth / window.innerHeight;
                camera2.updateProjectionMatrix();
                renderer2.setSize(window.innerWidth, window.innerHeight);
            });
        }

        // Start the app
        init();
        animate();
        initSecondFloor();

        // Save camera view before page unload
        // window.addEventListener('beforeunload', function() {
        //     const camPos = camera.position;
        //     const target = controls.target;

        //     const cameraView = {
        //         position: {
        //             x: camPos.x,
        //             y: camPos.y,
        //             z: camPos.z
        //         },
        //         target: {
        //             x: target.x,
        //             y: target.y,
        //             z: target.z
        //         }
        //     };

        //     localStorage.setItem('cameraView', JSON.stringify(cameraView));
        // });
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
            var floor_id2 = JSON.parse(container.dataset.floor_id2);
            const baseUrl = window.location.origin + "/dashboard/floormap/getfloordata/" + floor_id;
            const baseUrl2 = window.location.origin + "/dashboard/floormap/getfloordata/" + floor_id2;

            $.get(baseUrl, function(data, status) {
                if (data.data) {
                    updateCars(data.coordinates);
                } else {
                    console.log('Data not found!');
                }
            });
            $.get(baseUrl2, function(data, status) {
                if (data.data) {
                    updateCars2(data.coordinates);
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

        function updateCars2(newCoordinates) {
            const seenLabels2 = new Set();

            newCoordinates.forEach(item => {
                const label2 = item.label;
                const status2 = item.status;
                seenLabels2.add(label2);

                let position2 = {
                    x: Math.floor(item.x - dimension2[0] / 2),
                    y: item.z,
                    z: Math.floor(item.y - dimension2[1] / 2),
                };

                const rotation2 = Math.PI * item.a / 180;
                const prevStatus2 = carStates2[label2];
                // Set new status immediately
                carStates2[label2] = status2;

                // If status has changed, remove any previously rendered car or label
                if (prevStatus2 !== undefined && prevStatus2 !== status2) {
                    if (carObjects2[label2]) {
                        scene2.remove(carObjects2[label2]);
                        delete carObjects2[label2];
                    }
                    if (carLabels2[label2]) {
                        scene2.remove(carLabels2[label2]);
                        delete carLabels2[label2];
                    }
                }

                if (status2 == 1) { // Occupied - show car (hover-based label2)
                    if (!carObjects2[label2]) {
                        loadVehicle2(item.v_color, position2, rotation2, label2);
                    } else {
                        // Update existing car position
                        const car2 = carObjects2[label2];
                        car2.children[0].position.set(position2.x, position2.y, position2.z);
                        car2.children[0].rotation.y = rotation2;
                    }
                } else { // Available - show static label2
                    if (!carLabels2[label2]) {
                        // Create a static label2: isStatic true makes it always visible
                        if (status2 == 2) {
                            var labelMesh2 = createLabel2(label2, {
                                    x: position2.x,
                                    y: 5,
                                    z: position2.z
                                }, {
                                    x: Math.PI * 270 / 180,
                                    y: Math.PI * 0 / 180,
                                    z: Math.PI * item.a / 180
                                }, 'rgba(255, 0, 0, 0.7)', '#ffffff',
                                true // static label2
                            );
                        } else if (status2 == 4) {
                            var labelMesh2 = createLabel2(label2, {
                                    x: position2.x,
                                    y: 5,
                                    z: position2.z
                                }, {
                                    x: Math.PI * 270 / 180,
                                    y: Math.PI * 0 / 180,
                                    z: Math.PI * item.a / 180
                                }, 'rgba(255, 255, 0, 0.7)', '#000000',
                                true // static label2
                            );
                        } else {
                            var labelMesh2 = createLabel2(label2, {
                                    x: position2.x,
                                    y: 5,
                                    z: position2.z
                                }, {
                                    x: Math.PI * 270 / 180,
                                    y: Math.PI * 0 / 180,
                                    z: Math.PI * item.a / 180
                                }, 'rgba(0, 0, 0, 0.7)', '#ffffff',
                                true // static label2
                            );
                        }
                        carLabels2[label2] = labelMesh2;
                    } else {
                        carLabels2[label2].position.set(position2.x, 5, position2.z);
                    }
                }
            });

            // Remove any cars/labels for which we no longer have data
            for (const label2 in carStates2) {
                if (!seenLabels2.has(label2)) {
                    if (carObjects2[label2]) {
                        scene2.remove(carObjects2[label2]);
                        delete carObjects2[label2];
                    }
                    if (carLabels2[label2]) {
                        scene2.remove(carLabels2[label2]);
                        delete carLabels2[label2];
                    }
                    delete carStates2[label2];
                }
            }
        }
    </script>
</body>

<script>
</script>

</html>