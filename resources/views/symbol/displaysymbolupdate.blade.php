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
            <h4 class="text-center">Display Symbol Update</h4>
            <form action="{{ route('displaysymbol.update', $editDisplaySymbol->ondisplay_id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="display_id" class="form-label">Display</label>
                        <input type="text" name="coordinates" class="form-control" id="coordinates" placeholder="x, y" value="{{ getDisplayLocation($editDisplaySymbol->display_id) }}">
                    </div>

                    <div class="mb-3">
                        <label for="symbol_id" class="form-label">Symbol</label>
                        <select class="form-select" name="symbol_id" id="symbol_id" required>
                            <option value="" selected>Select Symbol</option>
                            @foreach($symbolData as $symbol)
                            @if($editDisplaySymbol->symbol_to_show == $symbol->symbol_id)
                            <option value="{{$symbol->symbol_id}}" selected>{{$symbol->symbol_name}}</option>
                            @else
                            <option value="{{$symbol->symbol_id}}">{{$symbol->symbol_name}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="coordinates" class="form-label">Coordinates</label>
                        <input type="text" name="coordinates" class="form-control" id="coordinates" placeholder="x, y" value="{{ $editDisplaySymbol->coordinates }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" placeholder="(R,G,B)" value="{{$editDisplaySymbol->color}}" id="color">
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