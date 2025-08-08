@extends('header')
@section('content')
<style>

    label {
        margin: 0 !important;
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
            <h4 class="text-center">Interconnection Data Update</h4>
            <form action="{{ route('interconnect.update', $editInterconnect->floor_interconnection_id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Sites</label>
                        <select class="form-select" name="site_id" id="site_id" style="pointer-events: none;">
                            <option value="">Select Site</option>
                            @foreach($siteData as $site)
                            <option value="{{$site->site_id}}" {{ $site->site_id == $editInterconnect->site_id ? 'selected' : '' }}>{{$site->site_username}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="floor_info" class="form-label">Floor Info</label>
                        <textarea name="floor_info" class="form-control textarea_height" id="floor_info" required>{{ $editInterconnect->floor_info }}</textarea>
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