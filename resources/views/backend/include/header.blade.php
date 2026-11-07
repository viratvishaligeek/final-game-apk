 <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-info"
     id="sidenavAccordion">
     <button class="btn btn-icon btn-transparent-dark me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
         <i data-feather="menu">
         </i>
     </button>
     <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="{{ route('admin.dashboard') }}">{{ setting('title', config('sites.current.name', config('app.name', 'Play Online Khaiwal'))) }} Admin</a>

     <ul class="navbar-nav align-items-center ms-auto">
         <li class="nav-item me-2">
             <button type="button" class="btn btn-sm btn-outline-dark text-nowrap" id="admin-enable-alert-sounds" title="Enable notification sounds">
                 <i data-feather="volume-2"></i><span class="d-inline ms-1 small" id="admin-sound-status">Enable sound</span>
             </button>
         </li>
         <li class="nav-item dropdown no-caret me-2 dropdown-notifications">
             <a class="btn btn-icon btn-transparent-dark dropdown-toggle position-relative" id="navbarDropdownMessages"
                 href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                 aria-expanded="false" aria-label="Unread support messages">
                 <i data-feather="mail"></i>
                 <span id="admin-chat-unread-badge" class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle d-none">0</span>
             </a>
             <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                 aria-labelledby="navbarDropdownMessages" style="min-width:320px">
                 <h6 class="dropdown-header dropdown-notifications-header">Unread Support Messages</h6>
                 <div id="admin-chat-notification-items">
                     <div class="dropdown-item small text-muted">Loading…</div>
                 </div>
                 <a class="dropdown-item dropdown-notifications-footer" href="{{ route('admin.support-chat.index') }}">Open Live Chat</a>
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
