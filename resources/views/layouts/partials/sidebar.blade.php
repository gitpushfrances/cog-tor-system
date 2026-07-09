{{-- SIDEBAR NAVIGATION — Pure Vanilla JS, Zero Alpine --}}
<style>
html, body { height: 100%; }

#sidebar {
    position: fixed;
    top: 0; left: 0;
    z-index: 40;
    height: 100vh;
    display: flex;
    flex-direction: column;
    background: #f5f0e8;
    border-right: 2px solid #e2d9c8;
    font-family: 'DM Sans', sans-serif;
    overflow: visible;
    box-shadow: 4px 0 24px rgba(0,0,0,0.08);
    width: 260px;
    /* NO transition here — added by JS after load to prevent flicker */
}

@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.sdb-section-label {
    padding: 16px 8px 5px;
    font-size: 0.62rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #b8a88a;
    font-weight: 700;
}
.sdb-divider {
    margin: 10px 8px;
    border-top: 1px solid #e2d9c8;
}
.sdb-link {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 2px;
    border-radius: 10px;
    text-decoration: none;
    color: #4a4535;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background 0.15s, color 0.15s;
    background: none;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
}
.sdb-link:hover { background: rgba(201,168,76,0.12); color: #1a1a2e; }
.sdb-link:hover .sdb-icon { color: #c9a84c; }
.sdb-active { background: rgba(201,168,76,0.18) !important; color: #7a5c1e !important; }
.sdb-active .sdb-icon { color: #c9a84c !important; }
.sdb-collapsed { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }

.sdb-icon { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; color: #8a7a60; transition: color 0.15s; }
.sdb-label { white-space: nowrap; overflow: hidden; }
.sdb-chevron { font-size: 0.65rem; color: #b8a88a; transition: transform 0.2s; flex-shrink: 0; }

.sdb-children {
    margin: 3px 0 4px 20px;
    padding-left: 14px;
    border-left: 2px solid #e2d9c8;
    display: none;
}
.sdb-children.open { display: block; }

.sdb-child {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 10px;
    border-radius: 8px;
    text-decoration: none;
    color: #6b5f4a;
    font-size: 0.825rem;
    font-weight: 500;
    transition: background 0.15s, color 0.15s;
    margin-bottom: 2px;
}
.sdb-child:hover { background: rgba(201,168,76,0.1); color: #7a5c1e; }
.sdb-child-active { color: #c9a84c !important; font-weight: 600; }
.sdb-child-icon { font-size: 0.75rem; width: 14px; text-align: center; flex-shrink: 0; }

.sdb-logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 7px 11px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid #d4c9b4;
    color: #8a7a60;
    cursor: pointer;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    font-family: 'DM Sans', sans-serif;
    white-space: nowrap;
}

nav::-webkit-scrollbar { width: 3px; }
nav::-webkit-scrollbar-track { background: transparent; }
nav::-webkit-scrollbar-thumb { background: #d4c9b4; border-radius: 3px; }

#main-content { transition: margin-left 0.3s ease; }

/* Collapsed state classes applied by JS */
#sidebar.is-collapsed { width: 72px; }
#sidebar.is-collapsed .sidebar-label { display: none; }
#sidebar.is-collapsed .sidebar-section-label { display: none; }
#sidebar.is-collapsed .sdb-link { justify-content: center; padding-left: 0; padding-right: 0; }
#sidebar.is-collapsed .sdb-chevron { display: none; }
#sidebar.is-collapsed .sdb-children { display: none !important; }
#sidebar.is-collapsed .sidebar-footer-text { display: none; }
#sidebar.is-collapsed .sidebar-footer-row { justify-content: center; flex-direction: column; padding: 12px 0; gap: 8px; }
#sidebar.is-collapsed .sidebar-profile-link { flex: none; padding: 5px; }
</style>

<div id="sidebar">

    {{-- ── LOGO ROW ── --}}
    <div style="height:68px;display:flex;align-items:center;padding:0 16px;border-bottom:1px solid #e2d9c8;flex-shrink:0;position:relative;z-index:2;">

        <a href="{{ route('dashboard') }}"
           title="COG-TOR System"
           style="display:flex;align-items:center;gap:11px;text-decoration:none;min-width:0;overflow:hidden;flex:1;">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(145deg,#e8c96e,#c9a84c,#9e7428);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 10px rgba(201,168,76,0.35);">
                <i class="fa-solid fa-graduation-cap" style="color:#fff;font-size:1.05rem;"></i>
            </div>
            <div class="sidebar-label" style="min-width:0;">
                <div style="font-weight:700;font-size:0.95rem;color:#1a1a2e;letter-spacing:0.01em;line-height:1.1;">COG-TOR</div>
                <div style="font-size:0.6rem;letter-spacing:0.14em;text-transform:uppercase;color:#c9a84c;font-weight:600;">System</div>
            </div>
        </a>

        {{-- Toggle button --}}
        <button id="sidebar-toggle-btn"
                onclick="sidebarToggle()"
                title="Toggle sidebar"
                style="position:absolute;right:-14px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;background:#f5f0e8;border:2px solid #e2d9c8;color:#c9a84c;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:2px 0 8px rgba(0,0,0,0.12);transition:background 0.15s,color 0.15s,border-color 0.15s;z-index:50;flex-shrink:0;"
                onmouseover="this.style.background='#c9a84c';this.style.color='#fff';this.style.borderColor='#c9a84c';"
                onmouseout="this.style.background='#f5f0e8';this.style.color='#c9a84c';this.style.borderColor='#e2d9c8';">
            <i id="toggle-icon" class="fa-solid fa-angles-left" style="font-size:0.65rem;"></i>
        </button>
    </div>

    {{-- ── NAV ITEMS ── --}}
    <nav style="flex:1;overflow-y:auto;overflow-x:hidden;padding:12px 10px;">

        @if(auth()->user()->hasRole('admin'))

            <a href="{{ route('admin.dashboard') }}"
               title="Dashboard"
               class="sdb-link {{ request()->routeIs('admin.dashboard') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span class="sidebar-label sdb-label">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               title="Users"
               class="sdb-link {{ request()->routeIs('admin.users.*') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-users sdb-icon"></i>
                <span class="sidebar-label sdb-label">Users</span>
            </a>

            <a href="{{ route('admin.backup.index') }}"
               title="Backup &amp; Restore"
               class="sdb-link {{ request()->routeIs('admin.backup.*') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-database sdb-icon"></i>
                <span class="sidebar-label sdb-label">Backup &amp; Restore</span>
            </a>

            <div class="sdb-section-label sidebar-section-label">Academic</div>
            <div class="sdb-divider sidebar-divider-collapsed" style="display:none;"></div>

            {{-- Academic Setup group --}}
            <div>
                <button onclick="sidebarToggleGroup('academic')"
                        title="Academic Setup"
                        class="sdb-link sdb-group {{ request()->routeIs('admin.departments.*','admin.courses.*','admin.subjects.*') ? 'sdb-active' : '' }}">
                    <i class="fa-solid fa-building-columns sdb-icon"></i>
                    <span class="sidebar-label sdb-label" style="flex:1;text-align:left;">Academic Setup</span>
                    <i id="chevron-academic" class="fa-solid fa-chevron-right sdb-chevron"></i>
                </button>
                <div id="group-academic" class="sdb-children">
                    <a href="{{ route('admin.departments.index') }}" title="Departments"
                       class="sdb-child {{ request()->routeIs('admin.departments.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-sitemap sdb-child-icon"></i>Departments
                    </a>
                    <a href="{{ route('admin.courses.index') }}" title="Courses"
                       class="sdb-child {{ request()->routeIs('admin.courses.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-book-open sdb-child-icon"></i>Courses
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" title="Subjects"
                       class="sdb-child {{ request()->routeIs('admin.subjects.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-chalkboard sdb-child-icon"></i>Subjects
                    </a>
                </div>
            </div>

            {{-- Academic Calendar group --}}
            <div>
                <button onclick="sidebarToggleGroup('calendar')"
                        title="Academic Calendar"
                        class="sdb-link sdb-group {{ request()->routeIs('admin.school-years.*','admin.semesters.*') ? 'sdb-active' : '' }}">
                    <i class="fa-solid fa-calendar-days sdb-icon"></i>
                    <span class="sidebar-label sdb-label" style="flex:1;text-align:left;">Academic Calendar</span>
                    <i id="chevron-calendar" class="fa-solid fa-chevron-right sdb-chevron"></i>
                </button>
                <div id="group-calendar" class="sdb-children">
                    <a href="{{ route('admin.school-years.index') }}" title="School Years"
                       class="sdb-child {{ request()->routeIs('admin.school-years.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-calendar sdb-child-icon"></i>School Years
                    </a>
                    <a href="{{ route('admin.semesters.index') }}" title="Semesters"
                       class="sdb-child {{ request()->routeIs('admin.semesters.*') ? 'sdb-child-active' : '' }}">
                        <i class="fa-solid fa-layer-group sdb-child-icon"></i>Semesters
                    </a>
                </div>
            </div>

        @endif



        @if(auth()->user()->hasRole('registrar'))
            <a href="{{ route('registrar.dashboard') }}" title="Dashboard"
               class="sdb-link {{ request()->routeIs('registrar.dashboard') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-gauge-high sdb-icon"></i>
                <span class="sidebar-label sdb-label">Dashboard</span>
            </a>

            <div class="sdb-section-label sidebar-section-label">Academic</div>

            <a href="{{ route('registrar.students.index') }}" title="Students"
               class="sdb-link {{ request()->routeIs('registrar.students.*') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-user-graduate sdb-icon"></i>
                <span class="sidebar-label sdb-label">Students</span>
            </a>
            <a href="{{ route('registrar.enrollments.index') }}" title="Enrollment"
               class="sdb-link {{ request()->routeIs('registrar.enrollments.*') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-clipboard-list sdb-icon"></i>
                <span class="sidebar-label sdb-label">Enrollment</span>
            </a>

            <div class="sdb-section-label sidebar-section-label">Grades</div>

            <a href="{{ route('registrar.encode-grades') }}" title="Encode Grades"
               class="sdb-link {{ request()->routeIs('registrar.encode-grades*') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-pen-to-square sdb-icon"></i>
                <span class="sidebar-label sdb-label">Encode Grades</span>
            </a>
            <a href="{{ route('registrar.documents.index') }}" title="Documents"
               class="sdb-link {{ request()->routeIs('registrar.documents.*') ? 'sdb-active' : '' }}">
                <i class="fa-solid fa-folder-open sdb-icon"></i>
                <span class="sidebar-label sdb-label">Documents</span>
            </a>
        @endif

    </nav>

    {{-- ── FOOTER ── --}}
    <div style="flex-shrink:0;border-top:2px solid #e2d9c8;background:#ede8de;">
        <div id="sidebar-footer-row" class="sidebar-footer-row"
             style="display:flex;align-items:center;padding:12px 14px;gap:10px;">

            <a href="{{ route('profile.edit') }}"
               id="sidebar-profile-link"
               class="sidebar-profile-link"
               title="Edit Profile"
               style="display:flex;align-items:center;gap:10px;text-decoration:none;flex:1;min-width:0;border-radius:8px;padding:5px 6px;transition:background 0.15s;"
               onmouseover="this.style.background='rgba(201,168,76,0.12)'"
               onmouseout="this.style.background='transparent'">
                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#e8c96e,#c9a84c);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(201,168,76,0.3);">
                    <span style="color:#fff;font-size:0.85rem;font-weight:700;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <div class="sidebar-label sidebar-footer-text" style="flex:1;min-width:0;">
                    <div style="font-size:0.85rem;font-weight:600;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name }}</div>
                    <div style="font-size:0.68rem;color:#8a7a60;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->email }}</div>
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0;">
                @csrf
                <button type="submit"
                        title="Log Out"
                        class="sdb-logout-btn"
                        onmouseover="this.style.background='rgba(239,68,68,0.1)';this.style.borderColor='rgba(239,68,68,0.4)';this.style.color='#dc2626';"
                        onmouseout="this.style.background='transparent';this.style.borderColor='#d4c9b4';this.style.color='#8a7a60';">
                    <i class="fa-solid fa-right-from-bracket" style="font-size:0.85rem;"></i>
                    <span class="sidebar-label sidebar-footer-text"
                          style="font-size:0.8rem;font-weight:500;white-space:nowrap;">Log Out</span>
                </button>
            </form>

        </div>
    </div>

</div>

<script>
(function () {
    const COLLAPSED_KEY = 'sidebar_collapsed';
    const GROUP_KEY     = 'sidebar_open_group';

    // Read saved state immediately
    let isCollapsed = localStorage.getItem(COLLAPSED_KEY) === 'true';
    let openGroup   = localStorage.getItem(GROUP_KEY) || '';

    function applyState(animate) {
        const sidebar  = document.getElementById('sidebar');
        const main     = document.getElementById('main-content');
        const icon     = document.getElementById('toggle-icon');
        if (!sidebar) return;

        // Sidebar width
        sidebar.style.width = isCollapsed ? '72px' : '260px';

        // Main content margin
        if (main) main.style.marginLeft = isCollapsed ? '72px' : '260px';

        // Toggle icon direction
        if (icon) {
            icon.classList.toggle('fa-angles-left',  !isCollapsed);
            icon.classList.toggle('fa-angles-right',  isCollapsed);
        }

        // Collapsed CSS class (handles labels, chevrons, footer via CSS)
        sidebar.classList.toggle('is-collapsed', isCollapsed);

        // Divider — show only when collapsed
        const divider = sidebar.querySelector('.sidebar-divider-collapsed');
        if (divider) divider.style.display = isCollapsed ? '' : 'none';

        // Groups — hide all when collapsed, restore saved group when expanded
        ['academic', 'calendar'].forEach(function(name) {
            const grp  = document.getElementById('group-' + name);
            const chev = document.getElementById('chevron-' + name);
            const isOpen = !isCollapsed && openGroup === name;

            if (grp) grp.classList.toggle('open', isOpen);
            if (chev) {
                chev.classList.toggle('fa-chevron-down',  isOpen);
                chev.classList.toggle('fa-chevron-right', !isOpen);
            }
        });
    }

    // Global toggle — called by button onclick
    window.sidebarToggle = function () {
        isCollapsed = !isCollapsed;
        localStorage.setItem(COLLAPSED_KEY, String(isCollapsed));
        if (isCollapsed) {
            openGroup = '';
            localStorage.setItem(GROUP_KEY, '');
        }
        // Enable transition only on manual toggle, not on page load
        const sidebar = document.getElementById('sidebar');
        const main    = document.getElementById('main-content');
        if (sidebar) sidebar.style.transition = 'width 0.3s ease';
        if (main)    main.style.transition    = 'margin-left 0.3s ease';
        applyState(true);
    };

    // Global group toggle — called by group button onclick
    window.sidebarToggleGroup = function (name) {
        // If collapsed, expand first then open group
        if (isCollapsed) {
            isCollapsed = false;
            localStorage.setItem(COLLAPSED_KEY, 'false');
        }
        openGroup = (openGroup === name) ? '' : name;
        localStorage.setItem(GROUP_KEY, openGroup);
        applyState(false);
    };

    // Apply state immediately — before any paint, no transition
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { applyState(false); });
    } else {
        applyState(false);
    }
})();
</script>
