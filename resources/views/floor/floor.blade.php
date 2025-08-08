@extends('header')
@section('content')
<style>
  .imagecss {
    display: block;
    width: 130px;
    border: 1px solid black;
    border-radius: 10px;
    margin-top: 5px;
  }

  label {
    margin: 0 !important;
  }
</style>
<div class="container">
  <h2 class="text-center">Floor Data</h2>
  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
  </div>
  @endif
  <!-- Display Success Message -->
  @if (session('message'))
  <div class="alert alert-success">
    {{ session('message') }}
  </div>
  @endif
  <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#floorModal">Add Data</button>
  <br><br>
  <div class="row m-auto">
    <div class="col-md-12 m-auto">

      <table id="userTable" class="table table-hover table-bordered table-responsive">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Site Name</th>
            <th scope="col">Floor Name</th>
            <th scope="col">Floor Image</th>
            <th scope="col">Piller Names</th>
            <th scope="col">Piller Coordinates</th>
            <th scope="col">Actions</th>
            <th scope="col">Download</th>
          </tr>
        </thead>
        <tbody>
          @foreach($floorData as $floor)
          <tr>
            <th scope="row">{{ $floor->floor_id }}</th>
            <td>{{getSitename($floor->site_id)}}</td>
            <td>{{ $floor->floor_name }}</td>
            <td>
              <img src="{{ asset('floors/'.$floor->floor_image) }}" class="border border-success rounded-3" width="80px" alt="">
            </td>
            <td>
              {{ Str::limit($floor->piller_name, 20) }}
            </td>
            <td>{{ Str::limit($floor->piller_coordinates, 14) }}</td>
            <td>
              @if($can_edit == 1)
              <form action="{{route('floor.destroy', $floor->floor_id)}}" id="deleteForm-{{ $floor->floor_id }}" onsubmit="return confirmDelete(event, '{{ $floor->floor_id }}');" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
              </form>
              <a href="{{ route('floor.edit', $floor->floor_id) }}" class="btn btn-sm btn-outline-success ">Edit</a><br>

              @endif
            </td>
            <td>
              <a href="{{ url('download-qr/' . $floor->site_id . '/' . $floor->floor_id) }}" class="btn btn-sm btn-warning ">Download QR</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="floorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Floor Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('floor.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

          <div class="mb-3">
            <label for="site_id" class="form-label">Sites</label>
            <select class="form-select" name="site_id" id="site_id" required>
              <option value="" selected>Select Site</option>
              @foreach($siteData as $site)
              <option value="{{$site->site_id}}">{{$site->site_name}} ({{ $site->site_username }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="floor_name" class="form-label">Floor Name</label>
            <input type="text" name="floor_name" class="form-control" id="floor_name" value="{{ old('floor_name') }}" required>
          </div>
          <div class="mb-3">
            <label for="floor_image" class="form-label">Floor 3D Map Image ( Max 2MB )</label>
            <input type="file" name="floor_image" class="form-control" id="floor_image" accept="image/*">
            <img id="uploadedFloorImage" class="imagecss" src="#" alt="Uploaded Image" accept="image/png, image/jpeg" style="display:none;">
          </div>
          <div class="mb-3">
            <label for="piller_name" class="form-label">Piller Name</label>
            <textarea name="piller_name" class="form-control textarea_height" id="piller_name">{{ old('piller_name') }}</textarea>
          </div>
          <div class="mb-3">
            <label for="piller_coordinates" class="form-label">Piller Coordinates</label>
            <textarea name="piller_coordinates" class="form-control textarea_height" id="piller_coordinates">{{ old('piller_coordinates') }}</textarea>
          </div>
          <div class="mb-3">
            <label for="current_location_symbol" class="form-label">Current Location Symbol ( Max 2MB )</label>
            <input type="file" name="current_location_symbol" class="form-control" id="current_location_symbol" accept="image/*">
            <img id="uploadedCurrentImage" class="imagecss" src="#" alt="Uploaded Image" accept="image/png, image/jpeg" style="display:none;">
          </div>
          <div class="mb-3">
            <label for="destination_location_symbol" class="form-label">Destination Location Symbol ( Max 2MB )</label>
            <input type="file" name="destination_location_symbol" class="form-control" id="destination_location_symbol" accept="image/*">
            <img id="uploadedDestinationImage" class="imagecss" src="#" alt="Uploaded Image" accept="image/png, image/jpeg" style="display:none;">
          </div>
          <div class="mb-3">
            <label for="interconnect_location_symbol" class="form-label">Interconnect Location Symbol ( Max 2MB )</label>
            <input type="file" name="interconnect_location_symbol" class="form-control" id="interconnect_location_symbol" accept="image/*">
            <img id="uploadedInterconnectImage" class="imagecss" src="#" alt="Uploaded Image" accept="image/png, image/jpeg" style="display:none;">
          </div>
          <div class="mb-3">
            <label for="symbol_size" class="form-label">Symbol Size</label>
            <input type="text" name="symbol_size" class="form-control" id="symbol_size" placeholder="height,width,radius,spacing,max_distance" value="{{ old('symbol_size') }}">
          </div>
          <div class="mb-3">
            <label for="floor_image_sensor_mapping_dimenssion" class="form-label">Floor Mapping Dimensions</label>
            <input type="text" name="floor_image_sensor_mapping_dimenssion" class="form-control" id="floor_image_sensor_mapping_dimenssion" placeholder="width, height" value="{{ old('floor_image_sensor_mapping_dimenssion') }}">
          </div>
          <div class="mb-3">
            <label for="car_scale" class="form-label">Floor Car Scale</label>
            <input type="text" name="car_scale" class="form-control" id="car_scale" placeholder="x, y, z" value="{{ old('car_scale') }}">
          </div>
          <div class="mb-3">
            <label for="label_properties" class="form-label">Label Size</label>
            <input type="text" name="label_properties" class="form-control" id="label_properties" placeholder="label size" value="60">
          </div>
          <div class="mb-3">
            <label for="floor_map_coordinate" class="form-label">Floor Map Coordinates</label>
            <textarea name="floor_map_coordinate" class="form-control textarea_height" id="floor_map_coordinate" placeholder='[{"x":"250","y":"85","z":"0","a":"180","label":"S1-TEST","i_color":"r","v_color":"r","sensor_id":"1"}]'>{{ old('floor_map_coordinate') }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Function to handle delete confirmation
  function confirmDelete(event, floorId) {
    event.preventDefault(); // Prevent the default form submission

    const form = document.getElementById(`deleteForm-${floorId}`);

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit(); // Submit the correct form
      }
    });

    return false; // Return false to ensure the form does not submit immediately
  }

  document.getElementById('floor_image').addEventListener('change', function() {
    if (this.files[0]) {
      var picture = new FileReader();
      picture.readAsDataURL(this.files[0]);
      picture.addEventListener('load', function(event) {
        document.getElementById('uploadedFloorImage').setAttribute('src', event.target.result);
        document.getElementById('uploadedFloorImage').style.display = 'block';
      });
    } else {
      document.getElementById('uploadedFloorImage').style.display = 'none';
    }
  });

  document.getElementById('current_location_symbol').addEventListener('change', function() {
    if (this.files[0]) {
      var picture = new FileReader();
      picture.readAsDataURL(this.files[0]);
      picture.addEventListener('load', function(event) {
        document.getElementById('uploadedCurrentImage').setAttribute('src', event.target.result);
        document.getElementById('uploadedCurrentImage').style.display = 'block';
      });
    } else {
      document.getElementById('uploadedCurrentImage').style.display = 'none';
    }
  });

  document.getElementById('destination_location_symbol').addEventListener('change', function() {
    if (this.files[0]) {
      var picture = new FileReader();
      picture.readAsDataURL(this.files[0]);
      picture.addEventListener('load', function(event) {
        document.getElementById('uploadedDestinationImage').setAttribute('src', event.target.result);
        document.getElementById('uploadedDestinationImage').style.display = 'block';
      });
    } else {
      document.getElementById('uploadedDestinationImage').style.display = 'none';
    }
  });
</script>
@endsection