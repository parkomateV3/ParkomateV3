<!DOCTYPE html>
<html>

<head>
    <title>3D Parking Visualization</title>
    <style>
        body {
            margin: 0;
        }

        canvas {
            display: block;
        }
    </style>
</head>

<body>
    <div id="scene-container"></div>
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

            // Camera setup
            camera = new THREE.PerspectiveCamera(130, window.innerWidth / window.innerHeight, 1, 1000);
            renderer = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });
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
                var y = 0;
                if (item.status == 1) {
                    loadVehicle("car", position, Math.PI * item.a / 180, item.label); // show label on hover only
                }
                // loadVehicleWithLabel("car", position, Math.PI * item.a / 180, item.label);

                // if (item.status == 1) {
                //     loadVehicle("car", position, Math.PI * item.a / 180);
                //     y = 35;
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
            controls.minDistance = 5;
            controls.maxDistance = 1000;
            camera.position.set(0, 300, 0);
            controls.update();
        }

        // function loadVehicle(modelName, position) {
        //     const mtlLoader = new THREE.MTLLoader();
        //     const objLoader = new THREE.OBJLoader();

        //     mtlLoader.setPath("{{ asset('vehicles/') }}/");
        //     mtlLoader.load(`${modelName}.mtl`, (materials) => {
        //         materials.preload();
        //         objLoader.setMaterials(materials);
        //         objLoader.setPath("{{ asset('vehicles/') }}/");
        //         objLoader.load(`${modelName}.obj`, (object) => {
        //             object.position.set(position.x, position.y, position.z);
        //             object.scale.set(0.1, 0.1, 0.1);
        //             scene.add(object);
        //         });
        //     });
        // }

        let carObjects = [];
        let carLabels = [];
        let raycaster = new THREE.Raycaster();
        let mouse = new THREE.Vector2();
        let hoveredLabel = null;

        function loadVehicle(modelName, position, rotation = 0, labelText = "") {
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

                    // Store label in userData
                    object.userData.label = labelText;

                    scene.add(object);
                    carObjects.push(object); // Add to raycasting list
                });
            });
        }

        function createLabelMesh(text) {
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");
            canvas.width = 300;
            canvas.height = 150;

            context.font = "Bold 50px Arial";
            context.fillStyle = "white";
            context.textAlign = "center";
            context.fillText(text, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.MeshBasicMaterial({
                map: texture,
                transparent: true
            });

            return new THREE.Mesh(new THREE.PlaneGeometry(100, 50), material);
        }


        function createLabel(text, position, rotation) {
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");

            canvas.width = 300;
            canvas.height = 150;

            context.font = "Bold 50px Arial";
            context.fillStyle = "white";
            context.textAlign = "center";
            context.fillText(text, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.MeshBasicMaterial({
                map: texture,
                transparent: true
            });

            const plane = new THREE.Mesh(new THREE.PlaneGeometry(100, 50), material);
            plane.position.set(position.x, position.y, position.z);
            plane.rotation.set(rotation.x, rotation.y, rotation.z);
            scene.add(plane);
            return plane;
        }


        window.addEventListener('mousemove', onMouseMove, false);

        function onMouseMove(event) {
            // Convert mouse position to normalized device coordinates (-1 to +1)
            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            const intersects = raycaster.intersectObjects(carObjects, true);

            if (intersects.length > 0) {
                const object = intersects[0].object;

                // Remove old label if exists
                if (hoveredLabel) {
                    scene.remove(hoveredLabel);
                    hoveredLabel = null;
                }

                // Get top-level car object (in case object is part of group)
                let top = object;
                while (top.parent && !top.userData.label && top.parent !== scene) {
                    top = top.parent;
                }

                if (top.userData.label) {
                    hoveredLabel = createLabelMesh(top.userData.label);
                    hoveredLabel.position.set(
                        top.position.x,
                        top.position.y + 40,
                        top.position.z
                    );
                    scene.add(hoveredLabel);
                }

            } else {
                // Remove label if nothing is hovered
                if (hoveredLabel) {
                    scene.remove(hoveredLabel);
                    hoveredLabel = null;
                }
            }
        }


        // function loadVehicle(modelName, position, rotation = 0) {
        //     const mtlLoader = new THREE.MTLLoader();
        //     const objLoader = new THREE.OBJLoader();

        //     mtlLoader.setPath("{{ asset('vehicles/') }}/");
        //     mtlLoader.load(`${modelName}.mtl`, (materials) => {
        //         materials.preload();
        //         objLoader.setMaterials(materials);
        //         objLoader.setPath("{{ asset('vehicles/') }}/");
        //         objLoader.load(`${modelName}.obj`, (object) => {
        //             object.position.set(position.x, position.y, position.z);
        //             object.scale.set(car_scale[0], car_scale[1], car_scale[2]);
        //             object.rotation.y = rotation; // Rotate the car
        //             scene.add(object);
        //         });
        //     });
        // }

        // Load vehicles (adjust coordinates as needed)
        // console.log(Math.floor(500 - width / 2));

        function addLabel(text, position, rotation) {
            // const canvas = document.createElement("canvas");
            // const context = canvas.getContext("2d");
            // context.font = "Bold 40px Arial";
            // context.fillStyle = "white"; // Text color
            // context.fillText(text, 10, 50);

            // const texture = new THREE.CanvasTexture(canvas);
            // const material = new THREE.SpriteMaterial({
            //     map: texture
            // });
            // const sprite = new THREE.Sprite(material);

            // sprite.scale.set(100, 50, 1); // Adjust size of text
            // sprite.position.set(position.x, position.y, position.z);
            // // sprite.rotation.y = rotation;
            // scene.add(sprite);

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

        // coordinate.map((item, index) => {
        //     let position = {
        //         x: Math.floor(item.x - dimension[0] / 2),
        //         y: item.z,
        //         z: Math.floor(item.y - dimension[1] / 2)
        //     };

        //     if (item.status == 1) {
        //         loadVehicle('car', position, Math.PI * item.a / 180);
        //         position.y += 50; // Place label above car
        //     } else {
        //         position.y = 10; // Place label slightly above the floor
        //     }

        //     addLabel(item.label, position);
        // });

        // loadVehicle('car', {
        //     x: 10,
        //     y: 0,
        //     z: -15
        // }, Math.PI); // Rotate 180°
        // // loadVehicle('car', {
        // //     x: 10,
        // //     y: 0,
        // //     z: 10
        // // }, -Math.PI / 2); // Rotate -90°
        // loadVehicle('car', {
        //     x: 10,
        //     y: 0,
        //     z: 15
        // }, 0); // No rotation
        // // loadVehicle('car', {
        // //     x: 0,
        // //     y: 0,
        // //     z: 10
        // // }, Math.PI / 4); // Rotate 45°
        // loadVehicle('car', {
        //     x: 36,
        //     y: 0,
        //     z: -12
        // }, -Math.PI / 4); // Rotate -45°

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
</body>

</html>