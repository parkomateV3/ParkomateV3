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
            <h4 class="text-center">Display Update</h4>
            <form action="{{ route('display.update', $editDisplay->display_id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($editDisplay->site_id) }}">
                        <input type="hidden" name="site_id" value="{{ $editDisplay->site_id }}">
                    </div>

                    <div class="mb-3">
                        <label for="display_no" class="form-label">Display Number</label>
                        <input type="text" name="display_no" class="form-control" id="display_no" value="{{ $editDisplay->display_unique_no }}">
                        <input type="hidden" name="display_no_old" value="{{ $editDisplay->display_unique_no }}">
                    </div>

                    <div class="mb-3">
                        <label for="display_location" class="form-label">Location of the Display On Site</label>
                        <input type="text" name="display_location" class="form-control" id="display_location" value="{{ $editDisplay->location_of_the_display_on_site }}">
                    </div>
                    <div class="mb-3">
                        <label for="intensity" class="form-label">Intensity</label>
                        <select class="form-select" name="intensity" id="intensity">
                            <option value="">Select Intensity</option>
                            <option value="1" {{$editDisplay->intensity == '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{$editDisplay->intensity == '2' ? 'selected' : '' }}>2</option>
                            <option value="3" {{$editDisplay->intensity == '3' ? 'selected' : '' }}>3</option>
                            <option value="4" {{$editDisplay->intensity == '4' ? 'selected' : '' }}>4</option>
                            <option value="5" {{$editDisplay->intensity == '5' ? 'selected' : '' }}>5</option>
                            <option value="6" {{$editDisplay->intensity == '6' ? 'selected' : '' }}>6</option>
                            <option value="7" {{$editDisplay->intensity == '7' ? 'selected' : '' }}>7</option>
                            <option value="8" {{$editDisplay->intensity == '8' ? 'selected' : '' }}>8</option>
                            <option value="9" {{$editDisplay->intensity == '9' ? 'selected' : '' }}>9</option>
                            <option value="10" {{$editDisplay->intensity == '10' ? 'selected' : '' }}>10</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="panels" class="form-label">No of Panels</label>
                        <input type="number" name="panels" class="form-control" id="panels" value="{{ $editDisplay->panels }}" required>
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