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
            <h4 class="text-center">Zonal Update</h4>
            <form action="{{ route('zonal.update', $editZonal->zonal_id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($editZonal->site_id) }}">
                        <input type="hidden" name="site_id" value="{{ $editZonal->site_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="floor_name" class="form-label">Floor Name</label>
                        <input type="text" name="floor_name" class="form-control" id="floor_name" value="{{ getFloorname($editZonal->floor_id) }}">
                        <input type="hidden" name="floor_id" value="{{ $editZonal->floor_id }}">
                    </div>
                    <div class="mb-3">
                        <label for="zonal_name" class="form-label">Zonal Name</label>
                        <input type="text" name="zonal_name" class="form-control" id="zonal_name" value="{{ $editZonal->zonal_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="zonal_no" class="form-label">Zonal Number</label>
                        <input type="text" name="zonal_no" class="form-control" id="zonal_no" value="{{ $editZonal->zonal_unique_no }}">
                        <input type="hidden" name="zonal_no_old" class="form-control" id="zonal_no_old" value="{{ $editZonal->zonal_unique_no }}" required>
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