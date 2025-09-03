@extends('header')
@section('content')
<title>Mark Parking Spots</title>

<style>
    #dotContainer div {
        position: absolute;
        width: 15px;
        height: 15px;
        background-color: red;
        border: 1px solid #fff;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
</style>

<div id="imageClickView" style="display: block;">
    <h5>Click on the image to select coordinates</h5>
    <a href="{{ route('floorMapDetails', ['floor_id' => $floor_id]) }}" class="btn btn-primary mb-3">Back</a>

    <div style="position: relative; display: inline-block;">
        <img
            id="floorImage"
            src="/floors/{{ $floor_image }}"
            class="img-fluid"
            style="cursor: crosshair; border: 2px dashed #999; display: block;"
            onclick="handleImageClick(event)"
        />

        <div id="dotContainer"
             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
        </div>
    </div>

    <div class="mt-3" id="coordDisplay" style="display: none;">
        <h6>Selected Coordinates:</h6>
        <code>X: <span id="coordX">0</span>, Y: <span id="coordY">0</span></code>
    </div>
</div>

{{-- MODAL FOR COORDINATE INPUT --}}
<div class="modal fade" id="addCarModal" tabindex="-1" aria-labelledby="addCarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('floormap.storeCar') }}" method="POST">
            @csrf
            <input type="hidden" name="floor_id" value="{{ $floor_id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Car Spot</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>x</label>
                        <input type="text" name="x" id="inputX" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>y</label>
                        <input type="text" name="y" id="inputY" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>z</label>
                        <input type="text" name="z" id="inputZ" class="form-control" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>a</label>
                        <input type="text" name="a" id="inputA" class="form-control" value="180" required>
                    </div>
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="label" id="inputLabel" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Sensor ID</label>
                        <input type="number" name="sensor_id" class="form-control" required>
                    </div>

                    {{-- Object selection dropdown --}}
                    <div class="form-group">
                        <label>Select Object</label>
                        <select name="object_id" id="objectDropdown" class="form-control" required>
                            <option value="">-- Select 3D Object --</option>
                            @foreach($objects as $object)
                                <option value="{{ $object->id }}" data-object-name="{{ $object->name }}">
                                    {{ $object->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>i_color</label>
                        <input type="text" name="i_color" id="iColorInput" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>v_color</label>
                        <input type="text" name="v_color" id="vColorInput" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="1">Occupied</option>
                            <option value="0">Free</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger mr-2" data-dismiss="modal"
                        onclick="closeCoordinateModal()">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Save Spot
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<!-- ... keep your existing EDIT MODAL here as needed ... -->

{{-- DELETE MODAL --}}
<!-- ... keep your existing DELETE MODAL here as needed ... -->

@if (isset($coordinates))
<script>
const spots = {!! json_encode($coordinates) !!};

document.addEventListener("DOMContentLoaded", function () {
    const img = document.getElementById("floorImage");
    const dotContainer = document.getElementById("dotContainer");

    function renderDots() {
        const scaleX = img.clientWidth / img.naturalWidth;
        const scaleY = img.clientHeight / img.naturalHeight;
        dotContainer.innerHTML = "";

        spots.forEach(spot => {
            if (spot.status == 1) {
                const dot = document.createElement("div");
                dot.style.left = (spot.x * scaleX) + "px";
                dot.style.top = (spot.y * scaleY) + "px";
                dot.title = spot.label || "";
                dotContainer.appendChild(dot);
            }
        });
    }

    if (img.complete) renderDots();
    else img.onload = renderDots;

    window.addEventListener("resize", renderDots);

    // Object dropdown auto-fill i_color and v_color
    const objectDropdown = document.getElementById("objectDropdown");
    const iColorInput = document.getElementById("iColorInput");
    const vColorInput = document.getElementById("vColorInput");
    if (objectDropdown) {
        objectDropdown.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            const objName = selectedOption.getAttribute('data-object-name') || '';
            iColorInput.value = objName;
            vColorInput.value = objName;
        });
    }
});

function handleImageClick(event) {
    const img = document.getElementById("floorImage");
    const rect = img.getBoundingClientRect();
    const scaleX = img.naturalWidth / img.clientWidth;
    const scaleY = img.naturalHeight / img.clientHeight;
    const offsetX = event.clientX - rect.left;
    const offsetY = event.clientY - rect.top;

    const clickedX = Math.round(offsetX * scaleX);
    const clickedY = Math.round(offsetY * scaleY);

    let foundNearby = false;

    spots.forEach((spot, index) => {
        const dist = Math.sqrt(Math.pow(clickedX - spot.x, 2) + Math.pow(clickedY - spot.y, 2));
        if (dist <= 20) {
            foundNearby = true;

            // Open edit modal
            document.getElementById("editIndex").value = index;
            document.getElementById("editX").value = spot.x;
            document.getElementById("editY").value = spot.y;
            document.getElementById("editZ").value = spot.z || 0;
            document.getElementById("editA").value = spot.a || 180;
            document.getElementById("editLabel").value = spot.label;
            document.getElementById("editSensor").value = spot.sensor_id;
            document.getElementById("editIColor").value = spot.i_color;
            document.getElementById("editVColor").value = spot.v_color;
            document.getElementById("editStatus").value = spot.status;

            $('#editCarModal').modal('show');
            document.getElementById("deleteIndex").value = index;
        }
    });

    if (!foundNearby) {
        const dotContainer = document.getElementById("dotContainer");
        const dot = document.createElement("div");
        dot.style.left = offsetX + "px";
        dot.style.top = offsetY + "px";
        dotContainer.appendChild(dot);

        document.getElementById("inputX").value = clickedX;
        document.getElementById("inputY").value = clickedY;
        document.getElementById("coordX").innerText = clickedX;
        document.getElementById("coordY").innerText = clickedY;
        document.getElementById("coordDisplay").style.display = "block";
        $('#addCarModal').modal('show');
    }
}
</script>
@endif
@endsection
