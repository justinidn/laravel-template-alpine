<nav class="navbar navbar-expand-lg shadow-sm py-2">
    <div class="container-fluid px-1">
        <!-- Logo / Brand -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('dashboard') }}">
            {{ config('app.name', 'Laravel') }}
        </a>

        <!-- Toggle Mobile -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Nav Left -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                        Dashboard
                    </a>
                </li>

                <!-- Menu Dropdown Data Master -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('departments.*') ? 'active fw-semibold' : '' }}"
                        href="#"
                        id="masterDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Master
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0 mt-2" aria-labelledby="masterDropdown">
                        @can('departments.view')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('departments.*') ? 'active' : '' }}"
                                href="{{ route('departments.index') }}">
                                <i class="bi bi-building me-2"></i> Department
                            </a>
                        </li>
                        @endcan
                        @can('users.view')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                href="{{ route('users.index') }}">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>

                <!-- Menu Dropdown Site Management -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('master-menus.*') ? 'active fw-semibold' : '' }}"
                        href="#"
                        id="siteManagementDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Site Management
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0 mt-2" aria-labelledby="siteManagementDropdown">
                        @can('master-menus.view')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('master-menus.*') ? 'active' : '' }}"
                                href="{{ route('master-menus.index') }}">
                                <i class="bi bi-list me-2"></i> Master Menu
                            </a>
                        </li>
                        @endcan
                        @can('roles.view')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                                href="{{ route('roles.index') }}">
                                <i class="bi bi-diagram-2 me-2"></i> Role Permission
                            </a>
                        </li>
                        @endcan
                        @can('roles.view')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('user-permissions.*') ? 'active' : '' }}"
                                href="{{ route('user-permissions.index') }}">
                                <i class="bi bi-people me-2"></i> User Permissions
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
            </ul>

            <!-- Nav Right / Profile -->
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span class="fw-medium">{{ Auth::user()->name }}</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-0 mt-2" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>