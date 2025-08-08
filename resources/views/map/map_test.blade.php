<!doctype html>
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

    .imgcss {
        border: 2px solid beige;
        border-radius: 10px;
        height: 160px;
        width: 160px;
    }

    .imgcss2 {
        border: 2px solid red;
        border-radius: 10px;
        height: 160px;
        width: 160px;
    }
</style>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('dashboard/assets/images/logo/logo-white-2.png') }}" alt="" width="80" height="60">
            </a>
        </div>
    </nav>
    <div class="container mt-3 mb-5 pb-5" style="width: 90%;">

        <h4 class="text-center fw-bolder">You are in {{getSitename($site_id)}},<br> on {{getFloorname($floor_id)}}, near {{$location}}</h4>

        <div class="row" style="text-align:right;">
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/current_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Current Location</span>
            </div>
            @if($flag == 0)
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/destination_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Destination Location</span>
            </div>
            @endif
        </div>

        <div id="scene-container"></div>

        <hr style="border: 2px solid black; width: 80%; opacity: 1; display: block; margin: 20px auto;">


        @if($flag)
        <h4 class="text-center fw-bolder">Select the destination where you want to go from your current location</h4>
        <form action="{{route('get_map')}}" method="post" class="text-center mt-3" id="pillerForm">
            @csrf
            <input type="hidden" name="site_id" id="site_id" value="{{$site_id}}">
            <input type="hidden" name="floor_id" id="floor_id" value="{{$floor_id}}">
            <input type="hidden" name="location" id="location" value="{{$location}}">
            <select name="categories" id="categories" class="form-select rounded-pill fw-bold bg-warning d-block mx-auto" style="width: 60%;" aria-label="Default select example" required>
                <option value="">Select Category</option>
                @php $site_type = getSiteType($site_id); @endphp
                @if($site_type == 'findmycar')
                <option value="findmycar">Find My Car</option>
                @endif
                @foreach ($categories as $category)
                @if($category == $destinationLocation)
                <option value="{{$category}}" selected>{{$category}}</option>
                @else
                <option value="{{$category}}">{{$category}}</option>
                @endif
                @endforeach
            </select>
            <br>
            <select name="piller_name" id="piller_name" onchange="submitForm()" class="piller_name form-select rounded-pill fw-bold bg-warning mx-auto" style="width: 60%;" aria-label="Default select example" required>
                <option value="">Select Destination Location</option>
            </select>

            <input type="text" id="searchcar" class="form-control rounded-pill fw-bold bg-warning mx-auto" placeholder="Search with car number..." style="width: 60%;">
            <br>
            <div id="showresults" style="display: flex;justify-content: center;flex-wrap:wrap;">

            </div>
            <div id="showresults2" style="display: flex;justify-content: center;flex-wrap:wrap;">

            </div>

            <br>
            <!-- <button type="submit" class="btn btn-success rounded-pill mt-3">Find</button> -->
        </form>
        @endif
    </div>

    <!-- Footer -->
    @php $ad_image = getSiteAdImage($site_id); @endphp
    @if($ad_image != null || $ad_image != '')
    <footer class="bg-dark text-white text-center mt-auto w-100" style="bottom: 0;">
        <img src="{{ asset('logos/' . $ad_image) }}" class="" alt="footer-image" height="60" width="100%">
    </footer>
    @endif

    <div id="data-container"
        data-coordinate='@json($carcoordinates)'
        data-dimension='@json($dimension)'
        data-car_scale='@json($car_scale)'
        data-floor_image='@json($processedImg)'
        data-floor_id='@json($floor_id)'
        data-locationdata='@json($locationData)'
        data-flag='@json($flag)'
        data-labelsize='@json($label_size)'>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        submitForm = function() {
            document.getElementById('pillerForm').submit();
            // alert(document.getElementById('piller_name').value);
        }

        $(document).ready(function() {
            $('#searchcar').hide();
            $('#categories').on('change', function() {
                var selectedValue = $(this).val();

                if (selectedValue == "findmycar") {
                    $('#piller_name').hide();
                    $('#searchcar').show();
                    $('#showresults').show();
                } else {
                    $('#piller_name').show();
                    $('#searchcar').hide();
                    $('#showresults').hide();
                }

                var site_id = $('#site_id').val();
                const baseUrl = window.location.origin + "/get_category_data/" + site_id + "/" + selectedValue;
                // alert(baseUrl);
                if (selectedValue) {
                    $.ajax({
                        url: baseUrl,
                        type: 'GET',
                        success: function(response) {
                            // console.log(response);
                            $('#piller_name').empty();
                            $('#piller_name').append('<option value="" selected>Select Destination Location</option>');
                            $.each(response, function(index, location) {
                                // console.log(floor);    

                                $('#piller_name').append('<option value="' + location + '">' + location + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                } else {
                    $('#piller_name').empty();
                    $('#piller_name').append('<option value="" selected>Select Destination Location</option>');
                    alert("Please select a site.");
                }

            });

            // Debounce function to limit the rate of function execution
            function debounce(func, delay) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), delay);
                };
            }

            // Function to handle the AJAX request
            function handleSearch() {
                $('#showresults').empty();
                $('#showresults2').empty();
                const input = $('#searchcar').val();
                var site_id = $('#site_id').val();
                var floor_id = $('#floor_id').val();
                var clocation = $('#location').val();

                if (input.length > 2) {
                    const mainurl = window.location.origin;
                    const baseUrl2 = `${mainurl}/dashboard/findmycarsearch/${site_id}/${input}`;
                    const baseUrl3 = `${mainurl}/dashboard/findmycarsearch/${site_id}/NA`;
                    $.ajax({
                        url: baseUrl2,
                        type: 'GET',
                        success: function(response) {
                            if (response.flag == 3) {
                                $('#showresults').append(
                                    `<a href="#" class="btn btn-sm btn-primary mx-1">${response.message}</a>`
                                );
                            } else {
                                $('#showresults').empty();
                                $.each(response.sensorData, function(index, location) {
                                    $('#showresults').append(
                                        // `<a href="${mainurl}/get_map_result/${site_id}/${floor_id}/${clocation}/${location.near_piller}" class="btn btn-sm btn-primary mx-1">${location.number}</a>`
                                        `<a href="${mainurl}/get_map_result/${site_id}/${floor_id}/${clocation}/${location.sensor}" class="mx-1" style="display:grid;text-decoration:none;color:black;"><img class="imgcss" src="${mainurl}/uploads/${location.car_image}" width="100" height="80">${location.number}</a>`
                                    );
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });

                    $.ajax({
                        url: baseUrl3,
                        type: 'GET',
                        success: function(response) {

                            if (response.flag != 3) {
                                $('#showresults2').empty();
                                $('#showresults2').append(`<hr class="mb-3" style="border: 1px solid black;width:90%;">`);
                                $.each(response.sensorData, function(index, location) {
                                    $('#showresults2').append(
                                        // `<a href="${mainurl}/get_map_result/${site_id}/${floor_id}/${clocation}/${location.near_piller}" class="btn btn-sm btn-primary mx-1">${location.number}</a>`
                                        `<a href="${mainurl}/get_map_result/${site_id}/${floor_id}/${clocation}/${location.sensor}" class="mx-1" style="display:grid;text-decoration:none;color:black;"><img class="imgcss2" src="${mainurl}/uploads/${location.car_image}" width="100" height="80">Not Detected</a>`
                                    );
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                }
            }

            // Attach the debounced function to the input event
            $('#searchcar').on('input', debounce(handleSearch, 300));
        });
    </script>
    <style>
        .fixed-bottom-img {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            /* Ensures it stays above other elements */
        }
    </style>


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
        var locationdata = JSON.parse(container.dataset.locationdata);
        var flag = JSON.parse(container.dataset.flag);

        var floor_image = JSON.parse(container.dataset.floor_image);
        // var floorImagePath = "{{ asset('floors') }}/" + floor_image;
        if (flag == 0) {
            var floorImagePath = floor_image;
        } else {
            var floorImagePath = "{{ asset('floors') }}/" + floor_image;
        }
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
            let locPosition = {
                x: Math.floor(locationdata[0] + 15 - dimension[0] / 2),
                y: 15,
                z: Math.floor(locationdata[1] + 15 - dimension[1] / 2),
            };
            loadLocation('blue_loc', locPosition, Math.PI * 180 / 180, "current");
            if (flag == 0) {
                locPosition = {
                    x: Math.floor(locationdata[2] + 15 - dimension[0] / 2),
                    y: 15,
                    z: Math.floor(locationdata[3] + 15 - dimension[1] / 2),
                };
                loadLocation('red_loc', locPosition, Math.PI * 180 / 180, "destination");
            }

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
            controls.enableRotate = true;
            // controls.enableZoom = false; // Prevent zooming
            // controls.enablePan = true; // Prevent panning
            controls.enableDamping = true;
            controls.dampingFactor = 0.1;
            controls.minDistance = 50;
            controls.maxDistance = 900;
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
            const baseUrl = window.location.origin + "/dashboard/floormap/getfloordata/" + floor_id;

            $.get(baseUrl, function(data, status) {
                if (data.data) {
                    updateCars(data.coordinates);
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
</body>

</html>