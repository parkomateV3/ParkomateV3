@extends('header')
@section('content')
<style>
    label {
        margin: 0 !important;
    }

    .sitelogo {
        display: block;
        width: 130px;
        border: 1px solid black;
        border-radius: 10px;
        margin-top: 5px;
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
            <h4 class="text-center">Symbol Update</h4>
            <form action="{{ route('symbol.update', $editSymbol->symbol_id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="symbol_name" class="form-label">Symbol Name</label>
                        <input type="text" name="symbol_name" class="form-control" id="symbol_name" value="{{ $editSymbol->symbol_name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="binary_data" class="form-label">Binary Data</label>
                        <input type="text" name="binary_data" class="form-control" id="binary_data" value="{{ $editSymbol->binary_data }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="symbol_size" class="form-label">Symbol Size</label>
                        <input type="text" name="symbol_size" class="form-control" id="symbol_size" placeholder="width, height" value="{{ $editSymbol->symbol_size }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="symbol_img" class="form-label">Symbol Image ( Max 2MB )</label>
                        <input type="file" name="symbol_img" class="form-control" id="symbol_img" accept="image/*">
                        <input type="hidden" name="old_symbol_img" class="form-control" id="old_symbol_img" value="{{ $editSymbol->symbol_img }}">
                        <img id="uploadedImage" class="sitelogo" src="{{ asset('symbols/'.$editSymbol->symbol_img) }}" alt="Uploaded Image" accept="image/png, image/jpeg">
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

    document.getElementById('symbol_img').addEventListener('change', function() {
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
</script>
@endsection