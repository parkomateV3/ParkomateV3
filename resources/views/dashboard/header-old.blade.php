<!DOCTYPE html>
<!-- Template Name: DashCode - HTML, React, Vue, Tailwind Admin Dashboard Template Author: Codeshaper Website: https://codeshaper.net Contact: support@codeshaperbd.net Like: https://www.facebook.com/Codeshaperbd Purchase: https://themeforest.net/item/dashcode-admin-dashboard-template/42600453 License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project. -->
<html lang="zxx" dir="ltr" class="light">
<style>
  @media (max-width: 767.98px) {
    .dropdown-menu.show {
      inset: auto auto -65px 60px !important;
    }
  }
</style>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <title>Dashcode - HTML Template</title>
  <link rel="icon" type="image/png" href="{{ asset('dashboard/assets/images/logo/favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <!-- BEGIN: Theme CSS-->
  <link rel="stylesheet" href="{{ asset('dashboard/assets/css/rt-plugins.css') }}">
  <link rel="stylesheet" href="{{ asset('dashboard/assets/css/app.css') }}">
  <!-- End : Theme CSS-->

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>

  <script src="{{ asset('dashboard/assets/js/settings.js') }}" sync></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <!-- Resources -->
  <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/themes/Responsive.js"></script>

  <!-- Icons Css -->
  <link href="{{ asset('dashboard/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
  <!-- App Css-->
  <!-- <link href="{{ asset('dashboard/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" /> -->

  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

</head>

<body class=" font-inter dashcode-app" id="body_class">
  <!-- [if IE]> <p class="browserupgrade"> You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security. </p> <![endif] -->
  <main class="app-wrapper">
    <!-- BEGIN: Sidebar -->
    <!-- BEGIN: Sidebar -->
    <div class="sidebar-wrapper group">
      <div id="bodyOverlay"
        class="w-screen h-screen fixed top-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm z-10 hidden"></div>
      <div class="logo-segment">
        <a class="flex items-center" href="{{ route('dashboard/home') }}">
          <!-- <img src="{{ asset('dashboard/assets/images/logo/logo-c.svg') }}" class="black_logo" alt="logo">
          <img src="{{ asset('dashboard/assets/images/logo/logo-c-white.svg') }}" class="white_logo" alt="logo"> -->
          <img src="{{ asset('dashboard/assets/images/logo/logo-black-2.png') }}" class="black_logo" alt="logo">
          <img src="{{ asset('dashboard/assets/images/logo/logo-white-2.png') }}" class="white_logo" alt="logo">
          <!-- <span class="ltr:ml-3 rtl:mr-3 text-xl font-Inter font-bold text-slate-900 dark:text-white">Parkomate</span> -->
        </a>
        <!-- Sidebar Type Button -->
        <div id="sidebar_type" class="cursor-pointer text-slate-900 dark:text-white text-lg">
          <span class="sidebarDotIcon extend-icon cursor-pointer text-slate-900 dark:text-white text-2xl">
            <div
              class="h-4 w-4 border-[1.5px] border-slate-900 dark:border-slate-700 rounded-full transition-all duration-150 ring-2 ring-inset ring-offset-4 ring-black-900 dark:ring-slate-400 bg-slate-900 dark:bg-slate-400 dark:ring-offset-slate-700">
            </div>
          </span>
          <span class="sidebarDotIcon collapsed-icon cursor-pointer text-slate-900 dark:text-white text-2xl">
            <div
              class="h-4 w-4 border-[1.5px] border-slate-900 dark:border-slate-700 rounded-full transition-all duration-150">
            </div>
          </span>
        </div>
        <button class="sidebarCloseIcon text-2xl">
          <iconify-icon class="text-slate-900 dark:text-slate-200" icon="clarity:window-close-line"></iconify-icon>
        </button>
      </div>
      <div id="nav_shadow" class="nav_shadow h-[60px] absolute top-[80px] nav-shadow z-[1] w-full transition-all duration-200 pointer-events-none
      opacity-0"></div>
      <div class="sidebar-menus bg-white dark:bg-slate-800 py-2 px-4 h-[calc(100%-80px)] overflow-y-auto z-50"
        id="sidebar_menus">
        <ul class="sidebar-menu">
          <!-- Apps Area -->
          <li>
            <a href="{{ route('dashboard/home') }}" class="navItem {{$active == 'home' ? 'active' : ''}}">
              <span class="flex items-center">
                <iconify-icon class="nav-icon" icon="heroicons-outline:home"></iconify-icon>
                <span>Dashboard</span>
              </span>
            </a>
          </li>
          <li>
            @php
            $access = Auth::user()->access;
            $accessArray = explode(',', $access);
            $checkAccess = "table-view";
            $flag = 0;
            if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
            }
            @endphp
            @if($flag == 0)
            <a href="#" class="toolTip onTop onClickTooltip navItem {{$active == 'table-view' ? 'active' : ''}}" data-tippy-content="No Access" data-tippy-theme="danger">
              @else
              <a href="{{ route('dashboard/table-view') }}" class="navItem {{$active == 'table-view' ? 'active' : ''}}">
                @endif
                <span class="flex items-center">
                  <iconify-icon class=" nav-icon" icon="heroicons-outline:document-text"></iconify-icon>
                  <span>Table View</span>
                </span>
              </a>
          </li>
          <li>
            @php
            $checkAccess = "detailed-view";
            $flag1 = 0;
            if (in_array($checkAccess, $accessArray)) {
            $flag1 = 1;
            }
            @endphp
            @if($flag1 == 0)
            <a href="#" class="toolTip onTop onClickTooltip navItem {{$active == 'detailed-view' ? 'active' : ''}}" data-tippy-content="No Access" data-tippy-theme="danger">
              @else
              <a href="{{ route('dashboard/detailed-view') }}" class="navItem {{$active == 'detailed-view' ? 'active' : ''}}">
                @endif
                <span class="flex items-center">
                  <iconify-icon class=" nav-icon" icon="heroicons-outline:document-text"></iconify-icon>
                  <span>Detailed View</span>
                </span>
              </a>
          </li>
          <li>
            @php
            $checkAccess = "summary-report";
            $flag2 = 0;
            if (in_array($checkAccess, $accessArray)) {
            $flag2 = 1;
            }
            @endphp
            @if($flag2 == 0)
            <a href="#" class="toolTip onTop onClickTooltip navItem {{$active == 'summary-report' ? 'active' : ''}}" data-tippy-content="No Access" data-tippy-theme="danger">
              @else
              <a href="{{ route('dashboard/summary-report') }}" class="navItem {{$active == 'summary-report' ? 'active' : ''}}">
                @endif
                <span class="flex items-center">
                  <iconify-icon class="nav-icon" icon="heroicons-outline:clipboard-list"></iconify-icon>
                  <span>Summary Report</span>
                </span>
              </a>
          </li>
          <li>
            @php
            $checkAccess = "historical-data";
            $flag3 = 0;
            if (in_array($checkAccess, $accessArray)) {
            $flag3 = 1;
            }
            @endphp
            @if($flag3 == 0)
            <a href="#" class="toolTip onTop onClickTooltip navItem {{$active == 'history' ? 'active' : ''}}" data-tippy-content="No Access" data-tippy-theme="danger">
              @else
              <a href="{{ route('dashboard/history') }}" class="navItem {{$active == 'history' ? 'active' : ''}}">
                @endif
                <span class="flex items-center">
                  <iconify-icon class="nav-icon" icon="heroicons-outline:clipboard-list"></iconify-icon>
                  <span>Historical Data</span>
                </span>
              </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- End: Sidebar -->
    <!-- End: Sidebar -->

    <div class="flex flex-col justify-between min-h-screen">
      <div>
        <!-- BEGIN: Header -->
        <!-- BEGIN: Header -->
        <div class="z-[9]" id="app_header">
          <div
            class="app-header z-[999] ltr:ml-[248px] rtl:mr-[248px] bg-white dark:bg-slate-800 shadow-sm dark:shadow-slate-700">
            <div class="flex justify-between items-center h-full">
              <div class="flex items-center md:space-x-4 space-x-2 xl:space-x-0 rtl:space-x-reverse vertical-box">
                <a href="{{ route('dashboard/home') }}" class="mobile-logo xl:hidden inline-block">
                  <img src="{{ asset('dashboard/assets/images/logo/logo-black-2.png') }}" class="black_logo w-10" alt="logo">
                  <img src="{{ asset('dashboard/assets/images/logo/logo-white-2.png') }}" class="white_logo w-10" alt="logo">
                </a>

              </div>
              <!-- end vertcial -->


              <div class="nav-tools flex items-center lg:space-x-5 space-x-3 rtl:space-x-reverse leading-0">

                <!-- BEGIN: Toggle Theme -->
                <div>
                  <button id="themeMood"
                    class="h-[28px] w-[28px] lg:h-[32px] lg:w-[32px] lg:bg-gray-500-f7 bg-slate-50 dark:bg-slate-900 lg:dark:bg-slate-900 dark:text-white text-slate-900 cursor-pointer rounded-full text-[20px] flex flex-col items-center justify-center">
                    <iconify-icon class="text-slate-800 dark:text-white text-xl dark:block hidden" id="moonIcon"
                      icon="line-md:sunny-outline-to-moon-alt-loop-transition"></iconify-icon>
                    <iconify-icon class="text-slate-800 dark:text-white text-xl dark:hidden block" id="sunIcon"
                      icon="line-md:moon-filled-to-sunny-filled-loop-transition"></iconify-icon>
                  </button>
                </div>
                <!-- END: TOggle Theme -->

                <!-- BEGIN: gray-scale Dropdown -->
                <div>
                  <button id="grayScale" class="lg:h-[32px] lg:w-[32px] lg:bg-slate-100 lg:dark:bg-slate-900 dark:text-white text-slate-900 cursor-pointer
            rounded-full text-[20px] flex flex-col items-center justify-center">
                    <iconify-icon class="text-slate-800 dark:text-white text-xl"
                      icon="mdi:paint-outline"></iconify-icon>
                  </button>
                </div>
                <!-- END: gray-scale Dropdown -->




                <!-- BEGIN: Profile Dropdown -->
                <!-- Profile DropDown Area -->
                <div class="md:block hidden w-full">
                  <button class="text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-medium rounded-lg text-sm text-center
      inline-flex items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="lg:h-8 lg:w-8 h-7 w-7 rounded-full flex-1 ltr:mr-[10px] rtl:ml-[10px]">
                      <img src="{{ asset('dashboard/assets/images/all-img/user.png') }}" alt="user"
                        class="block w-full h-full object-cover rounded-full">
                    </div>
                    <span
                      class="flex-none text-slate-600 dark:text-white text-sm font-normal items-center lg:flex hidden overflow-hidden text-ellipsis whitespace-nowrap">
                      @if(Auth::user())
                      {{Auth::user()->name}}
                      @else
                      Login
                      @endif
                    </span>
                    <svg
                      class="w-[16px] h-[16px] dark:text-white hidden lg:inline-block text-base inline-block ml-[10px] rtl:mr-[10px]"
                      aria-hidden="true" fill="none" stroke="currentColor" viewbox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <!-- Dropdown menu -->
                  <div class="dropdown-menu z-10 hidden bg-white divide-y divide-slate-100 shadow w-44 dark:bg-slate-800 border dark:border-slate-700 !top-[23px] rounded-md
      overflow-hidden">
                    <ul class="py-1 text-sm text-slate-800 dark:text-slate-200">
                      <li>
                        <a href="{{ route('dashboard/home') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600 dark:hover:text-white font-inter text-sm text-slate-600
            dark:text-white font-normal">
                          <iconify-icon icon="heroicons-outline:user"
                            class="relative top-[2px] text-lg ltr:mr-1 rtl:ml-1"></iconify-icon>
                          <span class="font-Inter">Dashboard</span>
                        </a>
                      </li>
                      <li>
                        <a href="{{ route('dashboard/logout') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600 dark:hover:text-white font-inter text-sm text-slate-600
            dark:text-white font-normal">
                          <iconify-icon icon="heroicons-outline:login"
                            class="relative top-[2px] text-lg ltr:mr-1 rtl:ml-1"></iconify-icon>
                          <span class="font-Inter">Logout</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- END: Header -->
                <button class="smallDeviceMenuController md:hidden block leading-0">
                  <iconify-icon class="cursor-pointer text-slate-900 dark:text-white text-2xl"
                    icon="heroicons-outline:menu-alt-3"></iconify-icon>
                </button>
                <!-- end mobile menu -->
              </div>
              <!-- end nav tools -->
            </div>
          </div>
        </div>

        <!-- BEGIN: Search Modal -->
        <div class="modal fade fixed top-0 left-0 hidden w-full h-full outline-none overflow-x-hidden overflow-y-auto"
          id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
          <div class="modal-dialog relative w-auto pointer-events-none top-1/4">
            <div
              class="modal-content border-none shadow-lg relative flex flex-col w-full pointer-events-auto bg-white dark:bg-slate-900 bg-clip-padding rounded-md outline-none text-current">
              <form>
                <div class="relative">
                  <input type="text" class="form-control !py-3 !pr-12" placeholder="Search">
                  <button
                    class="absolute right-0 top-1/2 -translate-y-1/2 w-9 h-full border-l text-xl border-l-slate-200 dark:border-l-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <iconify-icon icon="heroicons-solid:search"></iconify-icon>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- END: Search Modal -->
        <!-- END: Header -->
        <!-- END: Header -->


      </div>

      <div class="content-wrapper transition-all duration-150 ltr:ml-[248px] rtl:mr-[248px]" id="content_wrapper">
        <div class="page-content">
          <div class="transition-all duration-150 container-fluid" id="page_layout">
            <div id="content_layout">

              @yield('content')

            </div>
          </div>
        </div>
      </div>

      <!-- BEGIN: Footer For Desktop and tab -->
      <footer class="md:block hidden" id="footer">
        <div
          class="site-footer px-6 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-300 py-4 ltr:ml-[248px] rtl:mr-[248px]">
          <div class="grid md:grid-cols-2 grid-cols-1 md:gap-5">
            <div class="text-center ltr:md:text-start rtl:md:text-right text-sm">
              COPYRIGHT ©
              <span id="thisYear"></span>
              Parkomate Solutions LLP.
            </div>
          </div>
        </div>
      </footer>
      <!-- END: Footer For Desktop and tab -->

      <div class="bg-white bg-no-repeat custom-dropshadow footer-bg dark:bg-slate-700 flex justify-around items-center
    backdrop-filter backdrop-blur-[40px] fixed left-0 bottom-0 w-full z-[9999] bothrefm-0 py-[12px] px-4 md:hidden">
        <button class="relative bg-white bg-no-repeat backdrop-filter backdrop-blur-[40px] rounded-full footer-bg dark:bg-slate-700
      h-[65px] w-[65px] z-[-1] -mt-[40px] flex justify-center items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="h-[50px] w-[50px] rounded-full relative left-[0px] hrefp-[0px] custom-dropshadow">
            <img src="{{ asset('dashboard/assets/images/all-img/user.png') }}" alt=""
              class="w-full h-full rounded-full border-2 border-slate-100">
          </div>
        </button>
        <div class="dropdown-menu hidden bg-white divide-y divide-slate-100 shadow w-44 dark:bg-slate-800 border dark:border-slate-700 !top-[23px] rounded-md overflow-hidden">
          <ul class="py-1 text-sm text-slate-800 dark:text-slate-200">
            <li>
              <a href="{{ route('dashboard/home') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600 dark:hover:text-white font-inter text-sm text-slate-600
            dark:text-white font-normal">
                <iconify-icon icon="heroicons-outline:user"
                  class="relative top-[2px] text-lg ltr:mr-1 rtl:ml-1"></iconify-icon>
                <span class="font-Inter">Dashboard</span>
              </a>
            </li>
            <li>
              <a href="{{ route('dashboard/logout') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600 dark:hover:text-white font-inter text-sm text-slate-600
            dark:text-white font-normal">
                <iconify-icon icon="heroicons-outline:login"
                  class="relative top-[2px] text-lg ltr:mr-1 rtl:ml-1"></iconify-icon>
                <span class="font-Inter">Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </main>
  <!-- scripts -->
  <script src="{{ asset('dashboard/assets/libs/node-waves/waves.min.js') }}"></script>
  <script src="{{ asset('dashboard/assets/libs/feather-icons/feather.min.js') }}"></script>
  <script src="{{ asset('dashboard/assets/js/jquery-3.6.0.min.js') }}"></script>
  <script src="{{ asset('dashboard/assets/js/rt-plugins.js') }}"></script>
  <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>
</body>

</html>