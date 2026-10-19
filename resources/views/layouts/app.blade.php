<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    </head>
    <body class="font-sans antialiased" style="background-color:#eef4e7;overflow-y:auto;">
        <script>
            // Apply margin before paint — zero flicker
            document.documentElement.style.setProperty(
                '--sidebar-w',
                localStorage.getItem('sidebar_collapsed') === 'true' ? '72px' : '260px'
            );
        </script>
        <div class="flex" style="min-height:100vh;">

            @auth
                @include('layouts.partials.sidebar')
            @endauth

            <div id="main-content"
                 class="flex flex-col flex-1"
                 style="min-height:100vh; margin-left:var(--sidebar-w); transition:margin-left 0.3s ease;">

                @if (isset($header))
                    <header class="flex-shrink-0 px-6 py-4 bg-white border-b border-gray-200">
                        {{ $header }}
                    </header>
                @endif

                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>

            </div>
        </div>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        // Password confirmation for deletes and high-risk changes (checked again on the server).
        window.confirmWithPassword = function (options) {
            return Swal.fire({
                title: options.title || 'Confirm with your password',
                html: options.html,
                text: options.html ? undefined : options.text,
                icon: options.icon || 'warning',
                input: 'password',
                inputLabel: 'Enter your password to continue',
                inputPlaceholder: 'Your account password',
                inputAttributes: { autocomplete: 'current-password', autocapitalize: 'off' },
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Confirm',
                confirmButtonColor: options.confirmButtonColor || '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                inputValidator: (value) => value ? undefined : 'Enter your password.',
            }).then((result) => (result.isConfirmed ? result.value : null));
        };

        window.submitWithPassword = function (form, options) {
            return window.confirmWithPassword(options).then((password) => {
                if (password === null) return;
                let input = form.querySelector('input[name="confirm_password"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'confirm_password';
                    form.appendChild(input);
                }
                input.value = password;
                form.submit();
            });
        };

        // Past school year grades are locked: a correction needs a written reason and the password.
        window.confirmWithPasswordAndReason = function (options) {
            const minLength = 10;
            return Swal.fire({
                title: options.title || 'Correct a locked grade',
                icon: 'warning',
                html: (options.html ? `<div style="font-size:0.9rem;color:#374151;margin-bottom:12px;">${options.html}</div>` : '') +
                    '<textarea id="swal-override-reason" class="swal2-textarea" maxlength="500" style="margin:0 0 10px;width:100%;box-sizing:border-box;" ' +
                    'placeholder="Reason, e.g. INC completed; re-exam taken 2026-10-20"></textarea>' +
                    '<input id="swal-override-password" type="password" class="swal2-input" autocomplete="current-password" ' +
                    'style="margin:0;width:100%;box-sizing:border-box;" placeholder="Your account password">',
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Save Correction',
                confirmButtonColor: options.confirmButtonColor || '#c9a84c',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                focusConfirm: false,
                didOpen: () => document.getElementById('swal-override-reason').focus(),
                preConfirm: () => {
                    const reason = document.getElementById('swal-override-reason').value.trim();
                    const password = document.getElementById('swal-override-password').value;
                    if (reason.length < minLength) return Swal.showValidationMessage(`Give a reason of at least ${minLength} characters.`);
                    if (!password) return Swal.showValidationMessage('Enter your password.');
                    return { reason, password };
                },
            }).then((result) => (result.isConfirmed ? result.value : null));
        };

        window.submitWithPasswordAndReason = function (form, options) {
            return window.confirmWithPasswordAndReason(options).then((answer) => {
                if (answer === null) return;
                const set = (name, value) => {
                    let input = form.querySelector(`input[name="${name}"]`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        form.appendChild(input);
                    }
                    input.value = value;
                };
                set('confirm_password', answer.password);
                set('override_reason', answer.reason);
                form.submit();
            });
        };

        // Forms marked data-password-confirm="Question?" ask for the password on submit.
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-password-confirm')) return;
            event.preventDefault();
            window.submitWithPassword(form, {
                title: form.getAttribute('data-password-confirm'),
                text: form.getAttribute('data-password-text') || undefined,
                confirmButtonText: form.getAttribute('data-password-button') || 'Confirm',
            });
        });
    </script>
    @if(session('password_error'))
        <script>
            Swal.fire({ icon: 'error', title: 'Not changed', text: @js(session('password_error')), confirmButtonColor: '#c9a84c' });
        </script>
    @endif
    @stack('scripts')
    </body>
</html>
