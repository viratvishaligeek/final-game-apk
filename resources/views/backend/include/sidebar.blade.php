<div id="layoutSidenav_nav">
    <nav class="sidenav shadow-right sidenav-light">
        <div class="sidenav-menu">
            <div class="nav accordion" id="accordionSidenav">
                <div class="sidenav-menu-heading d-sm-none">Account</div>
                <a class="nav-link d-sm-none" href="">
                    <div class="nav-link-icon"><i data-feather="bell"></i></div>
                    Alerts
                    <span class="badge bg-warning-soft text-warning ms-auto">4 New!</span>
                </a>
                <a class="nav-link d-sm-none" href="">
                    <div class="nav-link-icon"><i data-feather="mail"></i></div>
                    Messages
                    <span class="badge bg-success-soft text-success ms-auto">2 New!</span>
                </a>
                @php
                    $menuItems = config('menu', []);

                    $isRouteActive = function ($routeName) {
                        return !empty($routeName) && request()->routeIs($routeName);
                    };

                    $isRouteGroupActive = function ($routeName) {
                        if (empty($routeName)) {
                            return false;
                        }

                        $parts = explode('.', $routeName);

                        if (count($parts) < 2) {
                            return request()->routeIs($routeName);
                        }

                        $pattern = implode('.', array_slice($parts, 0, -1)) . '.*';

                        return request()->routeIs($pattern);
                    };

                    $hasPermission = function ($permission) {
                        if (empty($permission)) {
                            return true;
                        }
                        $user = auth()->user();
                        if (!$user) {
                            return false;
                        }

                        $permissions = is_array($permission) ? $permission : [$permission];

                        if (method_exists($user, 'hasAnyPermission')) {
                            return $user->hasAnyPermission($permissions);
                        }

                        if (method_exists($user, 'canAny')) {
                            return $user->canAny($permissions);
                        }

                        foreach ($permissions as $perm) {
                            if ($user->can($perm)) {
                                return true;
                            }
                        }

                        return false;
                    };
                @endphp

                @foreach ($menuItems as $item)
                    @if (($item['type'] ?? 'link') === 'heading')
                        <div class="sidenav-menu-heading">
                            {{ $item['text'] }}
                        </div>
                    @elseif (($item['type'] ?? 'link') === 'dropdown')
                        @php
                            $allowedChildren = array_filter($item['items'] ?? [], function ($child) use (
                                $hasPermission,
                            ) {
                                return $hasPermission($child['permission'] ?? null);
                            });

                            $parentPermission = $item['permission'] ?? null;
                            if (!empty($parentPermission) && !$hasPermission($parentPermission)) {
                                continue;
                            }

                            if (empty($allowedChildren)) {
                                continue;
                            }
                            $parentActive = false;

                            foreach ($allowedChildren as $child) {
                                if (!empty($child['route']) && $isRouteGroupActive($child['route'])) {
                                    $parentActive = true;
                                    break;
                                }
                            }
                            $childActive = !empty($child['route']) && $isRouteActive($child['route']);
                            $collapseId = $item['id'] ?? 'collapse-' . \Illuminate\Support\Str::slug($item['text']);
                        @endphp

                        <a class="nav-link {{ $parentActive ? 'active' : 'collapsed' }}" href="javascript:void(0);"
                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                            aria-expanded="{{ $parentActive ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                            @if (!empty($item['icon']))
                                <div class="nav-link-icon">
                                    <i data-feather="{{ $item['icon'] }}"></i>
                                </div>
                            @endif
                            {{ $item['text'] }}
                            <div class="sidenav-collapse-arrow">
                                <i class="fas fa-angle-down"></i>
                            </div>
                        </a>

                        <div class="collapse {{ $parentActive ? 'show' : '' }}" id="{{ $collapseId }}"
                            data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav">
                                @foreach ($allowedChildren as $child)
                                    @php
                                        $childActive = !empty($child['route']) && $isRouteActive($child['route']);
                                    @endphp
                                    <a class="nav-link {{ $childActive ? 'active' : '' }}"
                                        href="{{ !empty($child['route']) ? route($child['route']) : $child['url'] ?? '#' }}">
                                        @if (!empty($child['icon']))
                                            <div class="nav-link-icon">
                                                <i data-feather="{{ $child['icon'] }}"></i>
                                            </div>
                                        @endif
                                        {{ $child['text'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @else
                        @if (!$hasPermission($item['permission'] ?? null))
                            @continue
                        @endif
                        @php
                            $linkActive = !empty($item['route']) && $isRouteActive($item['route']);
                        @endphp
                        <a class="nav-link {{ $linkActive ? 'active' : '' }}"
                            href="{{ !empty($item['route']) ? route($item['route']) : $item['url'] ?? '#' }}">
                            @if (!empty($item['icon']))
                                <div class="nav-link-icon">
                                    <i data-feather="{{ $item['icon'] }}"></i>
                                </div>
                            @endif
                            {{ $item['text'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Sidenav Footer-->
        <div class="sidenav-footer">
            <div class="sidenav-footer-content">
                <div class="sidenav-footer-subtitle">Logged in as:</div>
                <div class="sidenav-footer-title">{{ Auth::user()->name }}</div>
            </div>
        </div>
    </nav>
</div>
