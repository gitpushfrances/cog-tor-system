<x-guest-layout>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert-success">
            <i class="fas fa-circle-check"></i>
            {{ session('status') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="form-header">
        <div class="eyebrow">Secure Access</div>
        <h1 class="form-title">Welcome<br>Back</h1>
        <p class="form-sub">Sign in to your account to continue.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Username --}}
        <div class="field">
            <label class="field-label" for="username">Username</label>
            <div class="field-input-wrap">
                <input
                    id="username"
                    class="field-input"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Your username"
                    required autofocus
                    autocomplete="username"
                    autocapitalize="off"
                    spellcheck="false"
                    style="padding-left: 2.5rem;"
                />
                <i class="fas fa-user field-icon"></i>
            </div>
            @error('username')
                <div class="field-err">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="field">
            <label class="field-label" for="password">Password</label>
            <div class="field-input-wrap">
                <input
                    id="password"
                    class="field-input"
                    type="password"
                    name="password"
                    placeholder="••••••••••"
                    required
                    autocomplete="current-password"
                    style="padding-left: 2.5rem; padding-right: 2.6rem;"
                />
                <i class="fas fa-lock field-icon"></i>
                <button type="button" class="eye-btn" id="eyeBtn" onclick="togglePwd()" title="Toggle password">
                    <i class="fas fa-eye" id="eyeIco"></i>
                </button>
            </div>
            @error('password')
                <div class="field-err">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="field-row">
            <label class="check-label" for="remember_me">
                <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            <span class="link-forgot" title="Passwords are reset by the administrator under Users.">Forgot password? Ask the administrator.</span>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-submit">
            <span class="btn-icon-wrap">
                <i class="fas fa-arrow-right-to-bracket"></i>
            </span>
            Sign In to System
        </button>
    </form>

    <div class="card-footer">
        <i class="fas fa-shield-halved"></i>
        Authorized personnel only &mdash; ESSU Guiuan Campus
    </div>

</x-guest-layout>

<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
// Sign-in problems (wrong username or password, inactive account, too many attempts) also show as a pop-up.
@php $loginProblem = $errors->first('username') ?: $errors->first('password') ?: session('error'); @endphp
@if($loginProblem)
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: @js(str_contains($loginProblem, 'inactive') ? 'Account inactive' : (str_contains($loginProblem, 'Too many') ? 'Too many attempts' : 'Sign-in failed')),
            text: @js($loginProblem),
            confirmButtonText: 'OK',
            confirmButtonColor: '#4b6043',
        }).then(function () { document.getElementById('{{ $errors->has('password') ? 'password' : 'username' }}').focus(); });
    });
@endif

function togglePwd() {
    const inp = document.getElementById('password');
    const ico = document.getElementById('eyeIco');
    const isHidden = inp.type === 'password';
    inp.type = isHidden ? 'text' : 'password';
    ico.classList.toggle('fa-eye',      !isHidden);
    ico.classList.toggle('fa-eye-slash', isHidden);
}
</script>
