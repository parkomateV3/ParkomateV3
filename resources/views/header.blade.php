<!doctype html>
<html lang="en">

<head>
  <title>Parkomate Admin Panel</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="{{ asset('dashboard/assets/images/logo/favicon.ico') }}">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <!-- <link rel="stylesheet" href="{{ asset('assets/css/font.css') }}"> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <meta name="robots" content="noindex, follow">
</head>
<style>
  div.dataTables_wrapper div.dataTables_length select {
    height: 25px !important;
  }

  div.dataTables_wrapper div.dataTables_filter input {
    height: 34px !important;
  }

  .textarea_height {
    height: 70px !important;
  }
</style>

<body>
  <div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
      <div class="custom-menu">
        <button type="button" id="sidebarCollapse" class="btn btn-primary">
          <i class="fa fa-bars"></i>
          <span class="sr-only">Toggle Menu</span>
        </button>
      </div>
      <div class="p-4 pt-5">
        <h2 class="text-white"><a href="index.html" class="logo text-white">Parkomate</a></h2>
        <ul class="list-unstyled components mb-5">
          <!-- <li class="active">
            <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Home</a>
            <ul class="collapse list-unstyled" id="homeSubmenu">
              <li>
                <a href="#">Home 1</a>
              </li>
              <li>
                <a href="#">Home 2</a>
              </li>
              <li>
                <a href="#">Home 3</a>
              </li>
            </ul>
          </li> -->
          <li>
            <a href="{{ route('site.index') }}">Sites</a>
          </li>
          <!-- <li>
            <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Pages</a>
            <ul class="collapse list-unstyled" id="pageSubmenu">
              <li>
                <a href="#">Page 1</a>
              </li>
              <li>
                <a href="#">Page 2</a>
              </li>
              <li>
                <a href="#">Page 3</a>
              </li>
            </ul>
          </li> -->
          <li>
            <a href="{{ route('floor.index') }}">Floors</a>
          </li>
          <li>
            <a href="{{ route('interconnect.index') }}">Floor Interconnections</a>
          </li>
          <li>
            <a href="{{ route('zonal.index') }}">Zonals</a>
          </li>
          <li>
            <a href="{{ route('sensor.index') }}">Senors</a>
          </li>
          <li>
            <a href="{{ route('eecsdevice.index') }}">EECS Devices</a>
          </li>
          <li>
            <a href="{{ route('eecssensor.index') }}">EECS Senors</a>
          </li>
          <li>
            <a href="{{ route('eecs.index') }}">EECS</a>
          </li>
          <li>
            <a href="{{ route('display.index') }}">Displays</a>
          </li>
          <li>
            <a href="{{ route('displaydata.index') }}">Display Data</a>
          </li>
          <li>
            <a href="{{ route('symbol.index') }}">Symbol</a>
          </li>
          <li>
            <a href="{{ route('displaysymbol.index') }}">Display Symbol</a>
          </li>
          <li>
            <a href="{{ route('reservation.index') }}">Sensor Reservation</a>
          </li>
          <li>
            <a href="{{ route('reservation_info.index') }}">Slot Reservation</a>
          </li>
          <li>
            <a href="{{ route('table.index') }}">Table View</a>
          </li>
          <li>
            <a href="{{ route('entries.index') }}">Table Entries</a>
          </li>
          @if(Auth::user()->role_id == 1)
          <li>
            <a href="{{ route('admins') }}">Users</a>
          </li>
          @endif
          <li>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-danger text-white mt-2">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    </nav>

    <div id="content" class="p-4 p-md-5 pt-5">
      @yield('content')
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#userTable').DataTable();
    })
  </script>
  <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
  <script src="{{ asset('assets/js/popper.js') }}"></script>
  <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
    integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
    data-cf-beacon='{"rayId":"8ba5709eec2d3a42","serverTiming":{"name":{"cfL4":true}},"version":"2024.8.0","token":"cd0b4b3a733644fc843ef0b185f98241"}'
    crossorigin="anonymous"></script>
</body>

</html>