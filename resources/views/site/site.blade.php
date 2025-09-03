@extends('header')
@section('content')
<style>
  .sitelogo {
    display: block;
    width: 130px;
    border: 1px solid black;
    border-radius: 10px;
    margin-top: 5px;
  }

  label {
    margin-bottom: 0 !important;
  }
</style>
<div class="container">
  <h2 class="text-center">Site Data</h2>
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
  <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#siteModal">Add Data</button>
  <br><br>
  <div class="row m-auto">
    <div class="col-md-12 m-auto">

      <table id="userTable" class="table table-hover table-bordered table-responsive">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Site Name</th>
            <th scope="col">Username</th>
            <th scope="col">City</th>
            <th scope="col">State</th>
            <th scope="col">Country</th>
            <th scope="col">Location</th>
            <th scope="col">Status</th>
            <th scope="col">Products</th>
            <th scope="col">Floors</th>
            <th scope="col">Zonals</th>
            <th scope="col">Sensors</th>
            <th scope="col">Displays</th>
            <th scope="col">Email</th>
            <th scope="col">Report Frequency</th>
            <th scope="col">Logo</th>
            <th scope="col">Ad Image</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($siteData as $site)
          <tr>
            <th scope="row">{{ $site->site_id }}</th>
            <td>{{ $site->site_name }}</td>
            <td>{{ $site->site_username }}</td>
            <td>{{ $site->site_city }}</td>
            <td>{{ $site->site_state }}</td>
            <td>{{ $site->site_country }}</td>
            <td>{{ $site->site_location }}</td>
            <td>{{ $site->site_status }}</td>
            <td>{{ $site->site_type_of_product }}</td>
            <td>{{ $site->number_of_floors }}</td>
            <td>{{ $site->number_of_zonals }}</td>
            <td>{{ $site->number_of_sensors }}</td>
            <td>{{ $site->number_of_displays }}</td>
            <td>{{ $site->email }}</td>
            <td>{{ $site->report_frequency }}</td>
            <td>
              <img src="{{ asset('logos/'.$site->site_logo) }}" class="border border-success rounded-3" width="80px" alt="">
            </td>
            <td>
              <img src="{{ asset('logos/'.$site->ad_image) }}" class="border border-success rounded-3" width="80px" alt="">
            </td>
            <td>
              @if($can_edit == 1)
              <form action="{{route('site.destroy', $site->site_id)}}" id="deleteForm-{{ $site->site_id }}" onsubmit="return confirmDelete(event, '{{ $site->site_id }}');" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
              </form>

              <a href="{{ route('site.edit', $site->site_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="siteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Site Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('site.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="site_name" class="form-label">Site Name</label>
            <input type="text" name="site_name" class="form-control" id="site_name" value="{{ old('site_name') }}" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" id="username" value="{{ old('username') }}" required>
          </div>
          <!-- <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="text" name="password" class="form-control" id="password" value="" required>
          </div> -->
          <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" name="city" class="form-control" id="city" value="{{ old('city') }}" required>
          </div>
          <div class="mb-3">
            <label for="state" class="form-label">State</label>
            <input type="text" name="state" class="form-control" id="state" value="{{ old('state') }}" required>
          </div>
          <div class="mb-3">
            <label for="country" class="form-label">Country</label>
            <input type="text" name="country" class="form-control" id="country" value="{{ old('country') }}" required>
          </div>
          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" class="form-control" id="location" value="{{ old('location') }}" required>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="status" required>
              <option value="">Select Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="typeofproduct" class="form-label">Type of Product</label>
            <select class="form-select" name="typeofproduct" id="typeofproduct" required>
              <option value="">Select Product Type</option>
              <option value="sspi">SSPI</option>
              <option value="eecs">EECS</option>
              <option value="findmycar">Find My Car</option>
              <option value="cpgs">CPGS</option>
              <option value="slot_reservation">Slot Reservation</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="floors" class="form-label">Number of Floors</label>
            <input type="text" name="floors" class="form-control" id="floors" value="{{ old('floors') }}" required>
          </div>
          <div class="mb-3">
            <label for="zonals" class="form-label">Number of Zonals</label>
            <input type="text" name="zonals" class="form-control" id="zonals" value="{{ old('zonals') }}" required>
          </div>
          <div class="mb-3">
            <label for="sensors" class="form-label">Number of Sensors</label>
            <input type="text" name="sensors" class="form-control" id="sensors" value="{{ old('sensors') }}" required>
          </div>
          <div class="mb-3">
            <label for="displays" class="form-label">Number of Displays</label>
            <input type="text" name="displays" class="form-control" id="displays" value="{{ old('displays') }}" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email (add multiple emails with comma seperate without space)</label>
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" required>
          </div>
          <div class="mb-3">
            <label for="report" class="form-label">Report Frequency</label>
            <select class="form-select" name="report" id="report" required>
              <option value="">Select Report Frequency</option>
              <option value="none">None</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="financial_model" class="form-label">Financial Model</label>
            <textarea name="financial_model" class="form-control textarea_height" placeholder='{"Week name":{"minutes":amount}}' id="financial_model">{{ old('financial_model') }}</textarea>
          </div>
          <div class="mb-3">
            <label for="overtime_hours" class="form-label">Overtime Hours</label>
            <input type="text" name="overtime_hours" class="form-control" placeholder='Hourse' value="24" id="overtime_hours">
          </div>
          <div class="mb-3">
            <label for="logo" class="form-label">Logo ( Max 2MB )</label>
            <input type="file" name="logo" class="form-control" id="logo" accept="image/*">
            <img id="uploadedImage" class="sitelogo" src="#" alt="Uploaded Image" accept="image/png, image/jpeg" style="display:none;">
          </div>
          <div class="mb-3">
            <label for="ad_image" class="form-label">Advertisement Image ( Max 2MB )</label>
            <input type="file" name="ad_image" class="form-control" id="ad_image" accept="image/*">
            <img id="uploadedAdImage" class="sitelogo" src="#" alt="Uploaded Image" accept="image/png, image/jpeg" style="display:none;">
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
  function confirmDelete(event, siteId) {
    event.preventDefault(); // Prevent the default form submission

    const form = document.getElementById(`deleteForm-${siteId}`);

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

  document.getElementById('logo').addEventListener('change', function() {
    if (this.files[0]) {
      var picture = new FileReader();
      picture.readAsDataURL(this.files[0]);
      picture.addEventListener('load', function(event) {
        document.getElementById('uploadedImage').setAttribute('src', event.target.result);
        document.getElementById('uploadedImage').style.display = 'block';
      });
    } else {
      document.getElementById('uploadedImage').style.display = 'none';
    }
  });

  document.getElementById('ad_image').addEventListener('change', function() {
    if (this.files[0]) {
      var picture = new FileReader();
      picture.readAsDataURL(this.files[0]);
      picture.addEventListener('load', function(event) {
        document.getElementById('uploadedAdImage').setAttribute('src', event.target.result);
        document.getElementById('uploadedAdImage').style.display = 'block';
      });
    } else {
      document.getElementById('uploadedAdImage').style.display = 'none';
    }
  });
</script>
@endsection