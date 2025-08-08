<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    .text-center {
        text-align: center;
    }

    img {
        display: block;
        margin: auto;
    }
</style>

<body>
    <p class="text-center"> You are in {{getSitename($site_id)}}, on {{getFloorname($floor_id)}} near {{$location}}</p>
    <br>
    <img class="img" src="{{ $processedImg }}" alt="Processed Image">
    <hr>
    <form action="{{route('get_map')}}" method="post" class="text-center" id="pillerForm">
        @csrf
        <input type="hidden" name="site_id" value="{{$site_id}}">
        <input type="hidden" name="floor_id" value="{{$floor_id}}">
        <input type="hidden" name="location" value="{{$location}}">
        
        <select name="piller_name" id="piller_name" onchange="submitForm()">

            <option value="">Select Destination Location</option>
            @foreach ($piller_names as $piller)
            @if($piller == $destinationLocation)
            <option value="{{$piller}}" selected>{{$piller}}</option>
            @else
            <option value="{{$piller}}">{{$piller}}</option>
            @endif
            @endforeach
        </select>
    </form>
</body>

<script>
    submitForm = function() {
        document.getElementById('pillerForm').submit();
        // alert(document.getElementById('piller_name').value);
    }
</script>

</html>