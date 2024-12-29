<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        {{-- <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
            CMS
        </div> --}}

        <div class="sidebar-brand-icon">
            {{-- <i class="fas fa-laugh-wink"></i> --}}
            PETROLINK
        </div>
        {{-- <div class="sidebar-brand-text mx-3">CMS</div> --}}
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    @if(auth()->check())
        @role('HRD|Staff|Manager|Supervisor')
        @can('view_dashboard')
            <li class="nav-item {{ Route::currentRouteName() === 'dashboard' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard')}}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
        @endcan
        @endrole
    @endif

    {{-- <li class="nav-item active">
        <a class="nav-link" href="index.html">
            <i class="fas fa-fw fa-calendar"></i>
            <span>Hari Libur</span></a>
    </li> --}}

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    {{-- <div class="sidebar-heading">
        Interface
    </div> --}}

    <!-- Nav Item - Pages Collapse Menu -->
    {{-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Components</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Custom Components:</h6>
                <a class="collapse-item" href="buttons.html">Buttons</a>
                <a class="collapse-item" href="cards.html">Cards</a>
            </div>
        </div>
    </li> --}}

    <!-- Nav Item - Utilities Collapse Menu -->
    {{-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Utilities</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Custom Utilities:</h6>
                <a class="collapse-item" href="utilities-color.html">Colors</a>
                <a class="collapse-item" href="utilities-border.html">Borders</a>
                <a class="collapse-item" href="utilities-animation.html">Animations</a>
                <a class="collapse-item" href="utilities-other.html">Other</a>
            </div>
        </div>
    </li> --}}

    <!-- Divider -->
    {{-- <hr class="sidebar-divider"> --}}

    <!-- Heading -->
    {{-- <div class="sidebar-heading">
        Addons
    </div> --}}

    <!-- Nav Item - Pages Collapse Menu -->
    {{-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Login Screens:</h6>
                <a class="collapse-item" href="login.html">Login</a>
                <a class="collapse-item" href="register.html">Register</a>
                <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Other Pages:</h6>
                <a class="collapse-item" href="404.html">404 Page</a>
                <a class="collapse-item" href="blank.html">Blank Page</a>
            </div>
        </div>
    </li> --}}

    <!-- Nav Item - Tables -->
    {{-- <li class="nav-item">
        <a class="nav-link" href="tables.html">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span></a>
    </li> --}}

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-check-square"></i>
            <span>Pengajuan</span>
        </a>
        <div id="collapseTwo" class="collapse {{ Route::currentRouteName() === 'pengajuan.cuti.index' ? 'show' : (Route::currentRouteName() === 'pengajuan.izin.index' ? 'show' : '') }}
" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ Route::currentRouteName() === 'pengajuan.cuti.index' ? 'active' : '' }}" href="{{ route('pengajuan.cuti.index')}}">Cuti</a>
                <a class="collapse-item {{ Route::currentRouteName() === 'pengajuan.izin.index' ? 'active' : '' }}" href="{{ route('pengajuan.izin.index')}}">Izin</a>
            </div>
        </div>
    </li>

    @role('HRD')
        <li class="nav-item {{ Route::currentRouteName() === 'cutitahunan.index' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('cutitahunan.index')}}">
                <i class="fas fa-fw fa-clone"></i>
                <span>Setup Program</span></a>
        </li>
    @endrole

    @role('HRD')
        <li class="nav-item {{ Route::currentRouteName() === 'users.index' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('users.index')}}">
                <i class="fas fa-fw fa-users"></i>
                <span>Users</span></a>
        </li>
    @endrole

    @role('HRD')
        <li class="nav-item {{ Route::currentRouteName() === 'divisi.index' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('divisi.index')}}">
                <i class="fas fa-fw fa-laptop"></i>
                <span>Divisi</span></a>
        </li>
    @endrole

    @role('HRD')
        <li class="nav-item {{ Route::currentRouteName() === 'laporan.index' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('laporan.index')}}">
                <i class="fas fa-fw fa-file"></i>
                <span>Laporan</span></a>
        </li>
    @endrole

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>