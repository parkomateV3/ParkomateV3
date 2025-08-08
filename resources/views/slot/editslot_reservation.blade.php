@extends('header')
@section('content')
<style>
    label {
        margin-bottom: 0 !important;
    }
</style>
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
    </div>
    @endif
    <div class="row">
        <div class="col-md-6 m-auto">
            <h4 class="text-center">Reservation Update</h4>
            <form action="{{ route('reservation_info.update', $editReservation->id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($editReservation->site_id) }}">
                        <input type="hidden" name="site_id" value="{{ $editReservation->site_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="floor_name" class="form-label">Floor Name</label>
                        <input type="text" name="floor_name" class="form-control" id="floor_name" value="{{ getFloorname($editReservation->floor_id) }}">
                        <input type="hidden" name="floor_id" value="{{ $editReservation->floor_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="zonal_name" class="form-label">Zonal Name</label>
                        <input type="text" name="zonal_name" class="form-control" id="zonal_name" value="{{ getZonalname($editReservation->zonal_id) }}">
                        <input type="hidden" name="zonal_id" value="{{ $editReservation->zonal_id }}">
                    </div>
                    <div class="mb-3">
                        <label for="reservation_name" class="form-label">Reservation Name</label>
                        <input type="text" name="reservation_name" class="form-control" id="reservation_name" value="{{ $editReservation->reservation_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="reservation_number" class="form-label">Reservation Number</label>
                        <input type="text" name="reservation_number" class="form-control" id="reservation_number" value="{{ $editReservation->reservation_number }}" required>
                        <input type="hidden" name="reservation_number_old" value="{{ $editReservation->reservation_number }}">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Is Blocked</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="">Select</option>
                            <option value="1" @if($editReservation->status == 1) selected @endif>Yes</option>
                            <option value="0" @if($editReservation->status == 0) selected @endif>No</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="goBack()">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function goBack() {
        window.history.back(); // or use window.history.go(-1);
    }
</script>

@endsection