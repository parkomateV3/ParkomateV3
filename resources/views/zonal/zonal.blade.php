@extends('header')
@section('content')
<style>
  label {
    margin-bottom: 0 !important;
  }
</style>
<div class="container">
  <h2 class="text-center">Zonal Data</h2>

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
  <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#zonalModal">Add Data</button>
  <br><br>
  <div class="row m-auto">
    <div class="col-md-12 m-auto">

      <table id="userTable" class="table table-hover table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Site Name</th>
            <th scope="col">Floor Name</th>
            <th scope="col">Zonal Name</th>
            <th scope="col">Zonal Numbers</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($zonalData as $zonal)
          <tr>
            <th scope="row">{{ $zonal->zonal_id }}</th>
            <td>{{ getSitename($zonal->site_id) }}</td>
            <td>{{ getFloorname($zonal->floor_id) }}</td>
            <td>{{ $zonal->zonal_name }}</td>
            <td>{{ $zonal->zonal_unique_no }}</td>
            <td>
              @if($can_edit == 1)
              <form action="{{route('zonal.destroy', $zonal->zonal_id)}}" id="deleteForm-{{ $zonal->zonal_id }}" onsubmit="return confirmDelete(event, '{{ $zonal->zonal_id }}');" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
              </form>
              <a href="{{ route('zonal.edit', $zonal->zonal_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="zonalModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Zonal Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('zonal.store') }}" method="post">
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
            <label for="floor_id" class="form-label">Floors</label>
            <select class="form-select" name="floor_id" id="floor_id" required>
              <option value="" selected>Select Floor</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="zonal_no" class="form-label">Zonal Number</label>
            <input type="text" name="zonal_no" class="form-control" id="zonal_no" value="{{ old('zonal_no') }}" required>
          </div>
          <div class="mb-3">
            <label for="zonal_name" class="form-label">Zonal Name</label>
            <input type="text" name="zonal_name" class="form-control" id="zonal_name" value="{{ old('zonal_name') }}" required>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // Function to handle delete confirmation
  function confirmDelete(event, zonalId) {
    event.preventDefault(); // Prevent the default form submission

    const form = document.getElementById(`deleteForm-${zonalId}`);

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

  $(document).ready(function() {

    $('#site_id').on('change', function() {
      var selectedValue = $(this).val();

      if (selectedValue) {
        $.ajax({
          url: '{{ url("floor") }}/' + selectedValue,
          type: 'GET',
          success: function(response) {
            $('#floor_id').empty();
            $('#floor_id').append('<option value="" selected>Select Floor</option>');
            $.each(response.data, function(index, floor) {
              // console.log(floor);    

              $('#floor_id').append('<option value="' + floor.floor_id + '">' + floor.floor_name + ' (' + floor.site_username + ')</option>');
            });
          },
          error: function(xhr, status, error) {
            console.log(xhr.responseText);
          }
        });
      } else {
        alert("Please select a site.");
      }

    });
  });
</script>

@endsection