@extends('header')
@section('content')
<style>
  label {
    margin-bottom: 0 !important;
  }
</style>
<div class="container">
  <div class="row">
    <div class="col-md-6 m-auto">
      <h4 class="text-center">Zonal Update</h4>
      <form action="" method="post">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="site_id" class="form-label">Sites</label>
            <select class="form-select" name="site_id" id="site_id">
              <option value="" selected>Select Site</option>
              @foreach($siteData as $site)
              <option value="{{$site->site_id}}">{{$site->site_name}} ({{ $site->site_username }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="floor_id" class="form-label">Floors</label>
            <select class="form-select" name="floor_id" id="floor_id">
              <option value="" selected>Select Floor</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="zonal_no" class="form-label">Zonal Number</label>
            <input type="text" name="zonal_no" class="form-control" id="zonal_no" value="">
          </div>
          <div class="mb-3">
            <label for="zonal_name" class="form-label">Zonal Name</label>
            <input type="text" name="zonal_name" class="form-control" id="zonal_name" value="">
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

    $('#floor_id').on('change', function() {
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