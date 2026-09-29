 <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
     id="sidenavAccordion">
     <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
         <i data-feather="menu">
         </i>
     </button>
     <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="{{ route('admin.dashboard') }}">SB Admin Pro</a>

     <ul class="navbar-nav align-items-center ms-auto">
         <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
             <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownMessages"
                 href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                 aria-expanded="false">
                 <i data-feather="mail">
                 </i>
             </a>
             <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                 aria-labelledby="navbarDropdownMessages">
                 <h6 class="dropdown-header dropdown-notifications-header">
                     <i class="me-2" data-feather="mail">
                     </i>
                     Message Center
                 </h6>
                 <a class="dropdown-item dropdown-notifications-item" href="dashboard-1.html#!">
                     <img class="dropdown-notifications-item-img"
                         src="{{ URL::asset('backend') }}/img/illustrations/profiles/profile-5.png" />
                     <div class="dropdown-notifications-item-content">
                         <div class="dropdown-notifications-item-content-text">Lorem ipsum dolor sit amet,
                             consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                             aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
                             aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
                             velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                             proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</div>
                         <div class="dropdown-notifications-item-content-details">Colby Newton · 3d</div>
                     </div>
                 </a>
                 <a class="dropdown-item dropdown-notifications-footer" href="dashboard-1.html#!">Read All
                     Messages</a>
             </div>
         </li>
         <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
             <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage"
                 href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                 aria-expanded="false">
                 <img class="img-fluid" src="{{ URL::asset('backend') }}/img/illustrations/profiles/profile-1.png" />
             </a>
             <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                 aria-labelledby="navbarDropdownUserImage">
                 <h6 class="dropdown-header d-flex align-items-center">
                     <img class="dropdown-user-img"
                         src="{{ URL::asset('backend') }}/img/illustrations/profiles/profile-1.png" />
                     <div class="dropdown-user-details">
                         <div class="dropdown-user-details-name">{{ Auth::user()->name }}</div>
                         <div class="dropdown-user-details-email">
                             {{ Auth::user()->email }}
                         </div>
                     </div>
                 </h6>
                 <div class="dropdown-divider">
                 </div>
                 <a class="dropdown-item" href="{{ route('admin.profile') }}">
                     <div class="dropdown-item-icon">
                         <i data-feather="settings">
                         </i>
                     </div>
                     Account
                 </a>
                 <a class="dropdown-item" href="{{ route('admin.logout') }}">
                     <div class="dropdown-item-icon">
                         <i data-feather="log-out">
                         </i>
                     </div>
                     Logout
                 </a>
             </div>
         </li>
     </ul>
 </nav>
