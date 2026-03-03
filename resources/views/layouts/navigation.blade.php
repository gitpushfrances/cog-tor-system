{{-- SIDEBAR NAVIGATION --}}
<div x-data="{
    collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
    openGroup: localStorage.getItem('sidebar_open_group') || '',
    toggle() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar_collapsed', this.collapsed);
        if (this.collapsed) { this.openGroup = ''; }
    },
    toggleGroup(name) {
        if (this.collapsed) {
            this.collapsed = false;
            localStorage.setItem('sidebar_collapsed', 'false');
            this.$nextTick(() => { this.openGroup = name; localStorage.setItem('sidebar_open_group', name); });
        } else {
            this.openGroup = this.openGroup === name ? '' : name;
            localStorage.setItem('sidebar_open_group', this.openGroup);
        }
    }
}"
     :style="collapsed ? 'width:72px' : 'width:260px'"
     style="position:fixed;top:0;left:0;z-index:40;height:100vh;display:flex;flex-direction:column;transition:width 0.3s ease;background:#0c1b36;border-right:1px solid rgba(201,168,76,0.13);font-family:'DM Sans',sans-serif;">

    {{-- AMBIENT GLOW --}}
    <div style="position:absolute;top:0;left:0;right:0;height:220px;background:radial-gradient(ellipse 160% 70% at 50% 0%,rgba(201,168,76,0.07) 0%,transparent 70%);pointer-events:none;"></div>

    {{-- ── LOGO ROW ── --}}
    <div style="height:68px;display:flex;align-items:center;justify-content:space-between;padding:0 16px;border-bottom:1px solid rgba(201,168,76,0.1);flex-shrink:0;position:relative;z-index:1;">

        {{-- Logo (expanded) --}}
        <a x-show="!collapsed"
           href="{{ route('dashboard') }}"
           style="display:flex;align-items:center;gap:11px;text-decoration:none;min-width:0;overflow:hidden;">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(145deg,#e8c96e,#c9a84c,#9e7428);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 0 3px rgba(201,168,76,0.15),0 4px 16px rgba(201,168,76,0.28);">
                <i class="fa-solid fa-graduation-cap" style="color:#0c1b36;font-size:1.05rem;"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:0.95rem;color:#fff;letter-spacing:0.01em;line-height:1.1;">COG-TOR</div>
                <div style="font-size:0.6rem;letter-spacing:0.14em;text-transform:uppercase;color:#c9a84c;font-weight:600;">System</div>
            </div>
        </a>

        {{-- Logo icon only (collapsed) --}}
        <div x-show="collapsed" style="display:flex;justify-content:center;width:100%;">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(145deg,#e8c96e,#c9a84c,#9e7428);display:flex;align-items:center;justify-content:center;box-shadow:0 0 0 3px rgba(201,168,76,0.15),0 4px 16px rgba(201,168,76,0.28);">
                <i class="fa-solid fa-graduation-cap" style="color:#0c1b36;font-size:1.05rem;"></i>
            </div>
        </div>

        {{-- Toggle button (expanded) --}}
        <button x-show="!collapsed" @click="toggle()" class="sdb-toggle-btn">
            <i class="fa-solid fa-angles-left" style="font-size:0.7rem;"></i>
        </button>
    </div>

    {{-- Expand button (collapsed) --}}
    <div x-show="collapsed" style="display:flex;justify-content:center;padding:10px 0;flex-shrink:0;border-bottom:1px solid rgba(201,168,76,0.08);">
        <button @click="toggle()" class="sdb-toggle-btn">
            <i class="fa-solid fa-angles-right" style="font-size:0.7rem;"></i>
        </button>
    </div>

    {{-- ── ROLE BADGE ── --}}
    <div style="padding:10px 16px;border-bottom:1px solid rgba(201,168,76,0.08);flex-shrink:0;position:relative;z-index:1;">
        @php $role = auth()->user()->roles->first()?->name ?? 'user'; @endphp
        <div x-show="!collapsed" style="display:flex;align-items:center;gap:8px;">
            <div style="width:7px;height:7px;border-radius:50%;background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,0.6);flex-shrink:0;"></div>
            <span style="font-size:0.65rem;letter-spacing:0.13em;text-transform:uppercase;color:#c9a84c;font-weight:700;">{{ ucfirst($role) }}</span>
        </div>
        <div x-show="collapsed" style="display:flex;justify-content:center;">
            <div style="width:7px;height:7px;border-radius:50%;background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,0.6);"></div>
        </div>
    </div>

    {{-- ── NAV ITEMS (scrollable) ── --}}
    <nav style="flex:1;overflow-y:auto;overflow-x:hidden;padding:12px 10px;position:relative;z-index:1;">

        {{-- ════ ADMIN ════ --}}
        @if(auth()->user()->hasRole('admin'))

            <a href="{{ route('admin.dashboard') }}" class="sdb-link {{ request()->routeIs('admin.dashboard') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="sdb-link {{ request()->routeIs('admin.users.*') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-users sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Users</span>
            </a>

            <div x-show="!collapsed" class="sdb-section-label">Academic</div>
            <div x-show="collapsed" class="sdb-divider"></div>

            {{-- Academic Setup --}}
            <div>
                <button @click="toggleGroup('academic')"
                        class="sdb-link sdb-group {{ request()->routeIs('admin.departments.*','admin.courses.*','admin.subjects.*') ? 'sdb-active' : '' }}"
                        :class="collapsed ? 'sdb-collapsed' : ''">
                    <i class="fa-solid fa-building-columns sdb-icon"></i>
                    <span x-show="!collapsed" class="sdb-label" style="flex:1;text-align:left;">Academic Setup</span>
                    <i x-show="!collapsed" class="fa-solid sdb-chevron" :class="openGroup==='academic'?'fa-chevron-down':'fa-chevron-right'"></i>
                </button>
                <div x-show="openGroup==='academic' && !collapsed"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="sdb-children">
                    <a href="{{ route('admin.departments.index') }}" class="sdb-child {{ request()->routeIs('admin.departments.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-sitemap sdb-child-icon"></i>Departments
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="sdb-child {{ request()->routeIs('admin.courses.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-book-open sdb-child-icon"></i>Courses
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" class="sdb-child {{ request()->routeIs('admin.subjects.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-chalkboard sdb-child-icon"></i>Subjects
                    </a>
                </div>
            </div>

            {{-- Academic Calendar --}}
            <div>
                <button @click="toggleGroup('calendar')"
                        class="sdb-link sdb-group {{ request()->routeIs('admin.school-years.*','admin.semesters.*') ? 'sdb-active' : '' }}"
                        :class="collapsed ? 'sdb-collapsed' : ''">
                    <i class="fa-solid fa-calendar-days sdb-icon"></i>
                    <span x-show="!collapsed" class="sdb-label" style="flex:1;text-align:left;">Academic Calendar</span>
                    <i x-show="!collapsed" class="fa-solid sdb-chevron" :class="openGroup==='calendar'?'fa-chevron-down':'fa-chevron-right'"></i>
                </button>
                <div x-show="openGroup==='calendar' && !collapsed"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="sdb-children">
                    <a href="{{ route('admin.school-years.index') }}" class="sdb-child {{ request()->routeIs('admin.school-years.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-calendar sdb-child-icon"></i>School Years
                    </a>
                    <a href="{{ route('admin.semesters.index') }}" class="sdb-child {{ request()->routeIs('admin.semesters.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-layer-group sdb-child-icon"></i>Semesters
                    </a>
                </div>
            </div>

        @endif

        {{-- ════ DEAN ════ --}}
        @if(auth()->user()->hasRole('dean'))
            <a href="{{ route('dean.dashboard') }}" class="sdb-link {{ request()->routeIs('dean.dashboard') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
            <a href="{{ route('dean.students.index') }}" class="sdb-link {{ request()->routeIs('dean.students.*') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-user-graduate sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Students</span>
            </a>
        @endif

        {{-- ════ FACULTY ════ --}}
        @if(auth()->user()->hasRole('faculty'))
            <a href="{{ route('faculty.dashboard') }}" class="sdb-link {{ request()->routeIs('faculty.dashboard') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
            <a href="{{ route('faculty.subjects') }}" class="sdb-link {{ request()->routeIs('faculty.subjects*') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-chalkboard-user sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">My Subjects</span>
            </a>
        @endif

        {{-- ════ REGISTRAR ════ --}}
        @if(auth()->user()->hasRole('registrar'))
            <a href="{{ route('registrar.dashboard') }}" class="sdb-link {{ request()->routeIs('registrar.dashboard') ? 'sdb-active' : '' }}" :class="collapsed ? 'sdb-collapsed' : ''">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span x-show="!collapsed" class="sdb-label">Dashboard</span>
            </a>
        @endif

    </nav>

    {{-- ── FOOTER ── --}}
    <div style="flex-shrink:0;border-top:1px solid rgba(201,168,76,0.1);position:relative;z-index:1;">

        <a href="{{ route('profile.edit') }}"
           :style="collapsed ? 'justify-content:center;padding:14px 0' : 'padding:14px 16px'"
           style="display:flex;align-items:center;gap:11px;text-decoration:none;transition:background 0.15s;"
           onmouseover="this.style.background='rgba(201,168,76,0.06)'"
           onmouseout="this.style.background='transparent'">
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(201,168,76,0.12);border:1.5px solid rgba(201,168,76,0.28);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#c9a84c;font-size:0.85rem;font-weight:700;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            </div>
            <div x-show="!collapsed" style="flex:1;min-width:0;">
                <div style="font-size:0.85rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name }}</div>
                <div style="font-size:0.68rem;color:#4a5a7a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->email }}</div>
            </div>
            <i x-show="!collapsed" class="fa-solid fa-pen-to-square" style="font-size:0.7rem;color:#4a5a7a;flex-shrink:0;"></i>
        </a>

        <form method="POST" action="{{ route('logout') }}" style="border-top:1px solid rgba(201,168,76,0.08);">
            @csrf
            <button type="submit"
                    :style="collapsed ? 'justify-content:center;padding-left:0;padding-right:0' : 'padding-left:16px;padding-right:16px'"
                    style="display:flex;align-items:center;gap:11px;width:100%;padding:13px 16px;color:#4a5a7a;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background 0.15s,color 0.15s;"
                    onmouseover="this.style.background='rgba(239,68,68,0.07)';this.style.color='#f87171'"
                    onmouseout="this.style.background='transparent';this.style.color='#4a5a7a'">
                <i class="fa-solid fa-right-from-bracket" style="font-size:1rem;flex-shrink:0;"></i>
                <span x-show="!collapsed" style="font-size:0.875rem;font-weight:500;">Log Out</span>
            </button>
        </form>
    </div>

</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.sdb-toggle-btn {
    width:30px;height:30px;border-radius:8px;
    background:transparent;border:1px solid rgba(201,168,76,0.12);
    color:#6b7a99;cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:background 0.15s,color 0.15s,border-color 0.15s;flex-shrink:0;
}
.sdb-toggle-btn:hover { background:rgba(201,168,76,0.1);color:#c9a84c;border-color:rgba(201,168,76,0.3); }

.sdb-section-label {
    padding:16px 8px 5px;
    font-size:0.62rem;letter-spacing:0.14em;text-transform:uppercase;
    color:rgba(201,168,76,0.4);font-weight:700;
}
.sdb-divider { margin:12px 0 8px;border-top:1px solid rgba(201,168,76,0.08); }

.sdb-link {
    display:flex;align-items:center;gap:12px;
    width:100%;padding:10px 12px;margin-bottom:3px;
    border-radius:10px;text-decoration:none;
    color:#6b7a99;font-size:0.875rem;font-weight:500;
    transition:background 0.15s,color 0.15s;
    background:none;border:none;cursor:pointer;
}
.sdb-link:hover { background:rgba(201,168,76,0.08);color:#e2e8f0; }
.sdb-link:hover .sdb-icon { color:#c9a84c; }
.sdb-active { background:rgba(201,168,76,0.12) !important;color:#e8c96e !important; }
.sdb-active .sdb-icon { color:#c9a84c !important; }
.sdb-collapsed { justify-content:center;padding-left:0 !important;padding-right:0 !important; }
.sdb-group { text-align:left; }

.sdb-icon { font-size:1.05rem;width:20px;text-align:center;flex-shrink:0;color:#4a5a7a;transition:color 0.15s; }
.sdb-label { white-space:nowrap;overflow:hidden; }
.sdb-chevron { font-size:0.65rem;color:#4a5a7a;transition:transform 0.2s; }

.sdb-children {
    margin:3px 0 4px 20px;
    padding-left:14px;
    border-left:1px solid rgba(201,168,76,0.18);
}
.sdb-child {
    display:flex;align-items:center;gap:9px;
    padding:8px 10px;border-radius:8px;text-decoration:none;
    color:#4a5a7a;font-size:0.825rem;font-weight:500;
    transition:background 0.15s,color 0.15s;margin-bottom:2px;
}
.sdb-child:hover { background:rgba(201,168,76,0.07);color:#c9a84c; }
.sdb-child-active { color:#c9a84c !important; }
.sdb-child-icon { font-size:0.75rem;width:14px;text-align:center;flex-shrink:0; }

nav::-webkit-scrollbar { width:3px; }
nav::-webkit-scrollbar-track { background:transparent; }
nav::-webkit-scrollbar-thumb { background:rgba(201,168,76,0.2);border-radius:3px; }
</style>
