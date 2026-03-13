{{-- SIDEBAR NAVIGATION --}}
<style>
html, body { height: 100%; }
</style>
<div x-data="{
    collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
    openGroup: localStorage.getItem('sidebar_open_group') || '',
    toggle() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar_collapsed', this.collapsed);
        if (this.collapsed) { this.openGroup = ''; localStorage.setItem('sidebar_open_group', ''); }
    },
    toggleGroup(name) {
        if (this.collapsed) {
            this.collapsed = false;
            localStorage.setItem('sidebar_collapsed', 'false');
            this.$nextTick(() => {
                this.openGroup = name;
                localStorage.setItem('sidebar_open_group', name);
            });
        } else {
            this.openGroup = this.openGroup === name ? '' : name;
            localStorage.setItem('sidebar_open_group', this.openGroup);
        }
    }
}"
    :style="collapsed ? 'width:72px' : 'width:260px'"
    style="position:fixed;top:0;left:0;z-index:40;height:100vh;display:flex;flex-direction:column;transition:width 0.3s ease;background:#f5f0e8;border-right:2px solid #e2d9c8;font-family:'DM Sans',sans-serif;overflow:visible;box-shadow:4px 0 24px rgba(0,0,0,0.08);">

    {{-- ── LOGO ROW ── --}}
    <div style="height:68px;display:flex;align-items:center;padding:0 16px;border-bottom:1px solid #e2d9c8;flex-shrink:0;position:relative;z-index:2;">

        <a href="{{ route('dashboard') }}"
           :title="collapsed ? 'COG-TOR System' : ''"
           style="display:flex;align-items:center;gap:11px;text-decoration:none;min-width:0;overflow:hidden;flex:1;">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(145deg,#e8c96e,#c9a84c,#9e7428);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 10px rgba(201,168,76,0.35);">
                <i class="fa-solid fa-graduation-cap" style="color:#fff;font-size:1.05rem;"></i>
            </div>
            <div x-show="!collapsed"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 style="min-width:0;">
                <div style="font-weight:700;font-size:0.95rem;color:#1a1a2e;letter-spacing:0.01em;line-height:1.1;">COG-TOR</div>
                <div style="font-size:0.6rem;letter-spacing:0.14em;text-transform:uppercase;color:#c9a84c;font-weight:600;">System</div>
            </div>
        </a>

        {{-- Toggle button — overlaps sidebar edge --}}
        <button @click="toggle()"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                style="position:absolute;right:-14px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;background:#f5f0e8;border:2px solid #e2d9c8;color:#c9a84c;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:2px 0 8px rgba(0,0,0,0.12);transition:background 0.15s,color 0.15s,border-color 0.15s;z-index:50;flex-shrink:0;"
                onmouseover="this.style.background='#c9a84c';this.style.color='#fff';this.style.borderColor='#c9a84c';"
                onmouseout="this.style.background='#f5f0e8';this.style.color='#c9a84c';this.style.borderColor='#e2d9c8';">
            <i class="fa-solid" :class="collapsed ? 'fa-angles-right' : 'fa-angles-left'" style="font-size:0.65rem;"></i>
        </button>
    </div>

    {{-- ── NAV ITEMS — flex:1 forces this to fill all remaining space ── --}}
    <nav style="flex:1;overflow-y:auto;overflow-x:hidden;padding:12px 10px;">

        @if(auth()->user()->hasRole('admin'))

            <a href="{{ route('admin.dashboard') }}"
               title="Dashboard"
               class="sdb-link {{ request()->routeIs('admin.dashboard') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               title="Users"
               class="sdb-link {{ request()->routeIs('admin.users.*') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-users sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Users</span>
            </a>

            <div x-show="!collapsed" class="sdb-section-label">Academic</div>
            <div x-show="collapsed" class="sdb-divider"></div>

            <div>
                <button @click="toggleGroup('academic')"
                        title="Academic Setup"
                        class="sdb-link sdb-group {{ request()->routeIs('admin.departments.*','admin.courses.*','admin.subjects.*') ? 'sdb-active' : '' }}"
                        :class="collapsed ? 'sdb-collapsed' : ''">
                    <i class="fa-solid fa-building-columns sdb-icon"></i>
                    <span x-show="!collapsed" class="sdb-label" style="flex:1;text-align:left;">Academic Setup</span>
                    <i x-show="!collapsed" class="fa-solid sdb-chevron" :class="openGroup==='academic' ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <div x-show="openGroup==='academic' && !collapsed"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="sdb-children">
                    <a href="{{ route('admin.departments.index') }}" title="Departments" class="sdb-child {{ request()->routeIs('admin.departments.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-sitemap sdb-child-icon"></i>Departments
                    </a>
                    <a href="{{ route('admin.courses.index') }}" title="Courses" class="sdb-child {{ request()->routeIs('admin.courses.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-book-open sdb-child-icon"></i>Courses
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" title="Subjects" class="sdb-child {{ request()->routeIs('admin.subjects.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-chalkboard sdb-child-icon"></i>Subjects
                    </a>
                </div>
            </div>

            <div>
                <button @click="toggleGroup('calendar')"
                        title="Academic Calendar"
                        class="sdb-link sdb-group {{ request()->routeIs('admin.school-years.*','admin.semesters.*') ? 'sdb-active' : '' }}"
                        :class="collapsed ? 'sdb-collapsed' : ''">
                    <i class="fa-solid fa-calendar-days sdb-icon"></i>
                    <span x-show="!collapsed" class="sdb-label" style="flex:1;text-align:left;">Academic Calendar</span>
                    <i x-show="!collapsed" class="fa-solid sdb-chevron" :class="openGroup==='calendar' ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <div x-show="openGroup==='calendar' && !collapsed"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="sdb-children">
                    <a href="{{ route('admin.school-years.index') }}" title="School Years" class="sdb-child {{ request()->routeIs('admin.school-years.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-calendar sdb-child-icon"></i>School Years
                    </a>
                    <a href="{{ route('admin.semesters.index') }}" title="Semesters" class="sdb-child {{ request()->routeIs('admin.semesters.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-layer-group sdb-child-icon"></i>Semesters
                    </a>
                </div>
            </div>

        @endif

        @if(auth()->user()->hasRole('head_of_department'))
            <a href="{{ route('head_of_department.dashboard') }}" title="Dashboard"
               class="sdb-link {{ request()->routeIs('head_of_department.dashboard') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
            <a href="{{ route('head_of_department.students.index') }}" title="Students"
               class="sdb-link {{ request()->routeIs('head_of_department.students.*') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-user-graduate sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Students</span>
            </a>
        @endif
            <a href="{{ route('head_of_department.dashboard') }}" title="Dashboard"
               class="sdb-link {{ request()->routeIs('head_of_department.dashboard') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
            <a href="{{ route('head_of_department.students.index') }}" title="Students"
               class="sdb-link {{ request()->routeIs('head_of_department.students.*') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-user-graduate sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Students</span>
            </a>
        @endif

        @if(auth()->user()->hasRole('faculty'))
            <a href="{{ route('faculty.dashboard') }}" title="Dashboard"
               class="sdb-link {{ request()->routeIs('faculty.dashboard') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
            <a href="{{ route('faculty.subjects') }}" title="My Subjects"
               class="sdb-link {{ request()->routeIs('faculty.subjects*') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-chalkboard-user sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">My Subjects</span>
            </a>
        @endif

        @if(auth()->user()->hasRole('registrar'))
            <a href="{{ route('registrar.dashboard') }}" title="Dashboard"
               class="sdb-link {{ request()->routeIs('registrar.dashboard') ? 'sdb-active' : '' }}"
               :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
        @endif

    </nav>

    {{-- ── FOOTER — flex-shrink:0 pins it to the bottom always ── --}}
    <div style="flex-shrink:0;border-top:2px solid #e2d9c8;background:#ede8de;">
        <div style="display:flex;align-items:center;padding:12px 14px;gap:10px;"
             :style="collapsed ? 'justify-content:center;flex-direction:column;padding:12px 0;gap:8px;' : ''">

            {{-- Avatar + name as profile link --}}
            <a href="{{ route('profile.edit') }}"
               title="Edit Profile"
               style="display:flex;align-items:center;gap:10px;text-decoration:none;flex:1;min-width:0;border-radius:8px;padding:5px 6px;transition:background 0.15s;"
               :style="collapsed ? 'flex:none;padding:5px;' : ''"
               onmouseover="this.style.background='rgba(201,168,76,0.12)'"
               onmouseout="this.style.background='transparent'">
                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#e8c96e,#c9a84c);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(201,168,76,0.3);">
                    <span style="color:#fff;font-size:0.85rem;font-weight:700;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <div x-show="!collapsed"
                     x-transition:enter="transition-opacity duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     style="flex:1;min-width:0;">
                    <div style="font-size:0.85rem;font-weight:600;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name }}</div>
                    <div style="font-size:0.68rem;color:#8a7a60;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->email }}</div>
                </div>
            </a>

            {{-- Logout button --}}
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0;">
                @csrf
                <button type="submit"
                        title="Log Out"
                        class="sdb-logout-btn"
                        onmouseover="this.style.background='rgba(239,68,68,0.1)';this.style.borderColor='rgba(239,68,68,0.4)';this.style.color='#dc2626';"
                        onmouseout="this.style.background='transparent';this.style.borderColor='#d4c9b4';this.style.color='#8a7a60';">
                    <i class="fa-solid fa-right-from-bracket" style="font-size:0.85rem;"></i>
                    <span x-show="!collapsed"
                          x-transition:enter="transition-opacity duration-150"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          style="font-size:0.8rem;font-weight:500;white-space:nowrap;">Log Out</span>
                </button>
            </form>

        </div>
    </div>

</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.sdb-section-label {
    padding:16px 8px 5px;font-size:0.62rem;letter-spacing:0.14em;
    text-transform:uppercase;color:#b8a88a;font-weight:700;
}
.sdb-divider { margin:10px 8px;border-top:1px solid #e2d9c8; }

.sdb-link {
    display:flex;align-items:center;gap:12px;
    width:100%;padding:10px 12px;margin-bottom:2px;
    border-radius:10px;text-decoration:none;
    color:#4a4535;font-size:0.875rem;font-weight:500;
    transition:background 0.15s,color 0.15s;
    background:none;border:none;cursor:pointer;
    white-space:nowrap;overflow:hidden;
}
.sdb-link:hover { background:rgba(201,168,76,0.12);color:#1a1a2e; }
.sdb-link:hover .sdb-icon { color:#c9a84c; }
.sdb-active { background:rgba(201,168,76,0.18) !important;color:#7a5c1e !important; }
.sdb-active .sdb-icon { color:#c9a84c !important; }
.sdb-collapsed { justify-content:center !important;padding-left:0 !important;padding-right:0 !important; }

.sdb-icon { font-size:1rem;width:20px;text-align:center;flex-shrink:0;color:#8a7a60;transition:color 0.15s; }
.sdb-label { white-space:nowrap;overflow:hidden; }
.sdb-chevron { font-size:0.65rem;color:#b8a88a;transition:transform 0.2s;flex-shrink:0; }

.sdb-children {
    margin:3px 0 4px 20px;padding-left:14px;
    border-left:2px solid #e2d9c8;
}
.sdb-child {
    display:flex;align-items:center;gap:9px;
    padding:8px 10px;border-radius:8px;text-decoration:none;
    color:#6b5f4a;font-size:0.825rem;font-weight:500;
    transition:background 0.15s,color 0.15s;margin-bottom:2px;
}
.sdb-child:hover { background:rgba(201,168,76,0.1);color:#7a5c1e; }
.sdb-child-active { color:#c9a84c !important;font-weight:600; }
.sdb-child-icon { font-size:0.75rem;width:14px;text-align:center;flex-shrink:0; }

.sdb-logout-btn {
    display:flex;align-items:center;justify-content:center;gap:7px;
    padding:7px 11px;border-radius:8px;
    background:transparent;border:1px solid #d4c9b4;
    color:#8a7a60;cursor:pointer;
    transition:background 0.15s,color 0.15s,border-color 0.15s;
    font-family:'DM Sans',sans-serif;white-space:nowrap;
}

nav::-webkit-scrollbar { width:3px; }
nav::-webkit-scrollbar-track { background:transparent; }
nav::-webkit-scrollbar-thumb { background:#d4c9b4;border-radius:3px; }
</style>
