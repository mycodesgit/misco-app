<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ticketing System - Login</title>

    <!-- Main combined CSS containing typography & Tabler icons -->
    <link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}?v={{ time() }}">
    <!-- Custom login responsive styles -->
    <link rel="stylesheet" href="{{ asset('uilibs/css/login-style.css') }}?v={{ time() }}">
    <!-- Logo  -->
    <link rel="shortcut icon" type="" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
</head>

<body>

    <div class="login-shell">
        <!-- Left: brand panel -->
        <aside class="login-hero">
            <h1 class="hero-title"></h1>
            <p class="hero-subtitle">A ticketing system that turns every IT concern into a ticket — filed, tracked, and resolved in one place so no request goes unnoticed.</p>
            <div class="hero-dashes" aria-hidden="true"><span></span><span></span><span></span></div>

            <div class="hero-art hero-logo-stage" aria-hidden="true">
                <div class="hero-dots"></div>
                <svg class="hero-man" viewBox="0 0 420 320" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Support agent at desk">
                    <!-- floor shadow -->
                    <ellipse cx="210" cy="294" rx="140" ry="12" fill="rgba(0,0,0,0.22)"/>
                    <!-- chair -->
                    <rect x="146" y="168" width="26" height="86" rx="12" fill="#374151"/>
                    <rect x="146" y="226" width="76" height="14" rx="7" fill="#374151"/>
                    <rect x="178" y="240" width="9" height="46" rx="4" fill="#1f2937"/>
                    <!-- desk legs -->
                    <rect x="88" y="250" width="10" height="42" rx="4" fill="#ffffff"/>
                    <rect x="322" y="250" width="10" height="42" rx="4" fill="#ffffff"/>
                    <!-- torso (suit) -->
                    <rect x="162" y="160" width="88" height="84" rx="22" fill="#22304a"/>
                    <polygon points="196,162 226,162 211,202" fill="#ffffff"/>
                    <polygon points="206,166 216,166 211,196" fill="#046706"/>
                    <!-- seated thigh + calf + shoe -->
                    <rect x="168" y="230" width="94" height="22" rx="11" fill="#1b2540"/>
                    <rect x="238" y="240" width="18" height="42" rx="8" fill="#1b2540"/>
                    <ellipse cx="252" cy="288" rx="20" ry="8" fill="#111827"/>
                    <!-- neck + head -->
                    <rect x="201" y="144" width="20" height="20" fill="#f2c184"/>
                    <circle cx="211" cy="126" r="27" fill="#f2c184"/>
                    <!-- hair -->
                    <path d="M184,124 a27,25 0 0 1 54,0 l0,-8 a27,25 0 0 0 -54,0 z" fill="#2b2b2b"/>
                    <rect x="183" y="112" width="8" height="22" rx="4" fill="#2b2b2b"/>
                    <!-- beard -->
                    <path d="M193,140 q18,14 36,0 l-2,12 q-16,10 -32,0 z" fill="#2b2b2b"/>
                    <!-- glasses -->
                    <rect x="191" y="118" width="17" height="13" rx="4" stroke="#111827" stroke-width="2.5" fill="rgba(255,255,255,0.15)"/>
                    <rect x="213" y="118" width="17" height="13" rx="4" stroke="#111827" stroke-width="2.5" fill="rgba(255,255,255,0.15)"/>
                    <line x1="208" y1="123" x2="213" y2="123" stroke="#111827" stroke-width="2.5"/>
                    <!-- headset -->
                    <path d="M183,112 a28,32 0 0 1 56,0" stroke="#111827" stroke-width="5" stroke-linecap="round"/>
                    <rect x="230" y="112" width="11" height="26" rx="5.5" fill="#111827"/>
                    <path d="M235,138 q2,16 -16,18" stroke="#111827" stroke-width="3.5" stroke-linecap="round"/>
                    <circle cx="216" cy="157" r="4" fill="#111827"/>
                    <!-- arms -->
                    <line x1="172" y1="176" x2="148" y2="228" stroke="#22304a" stroke-width="19" stroke-linecap="round"/>
                    <line x1="250" y1="176" x2="288" y2="222" stroke="#22304a" stroke-width="19" stroke-linecap="round"/>
                    <circle cx="148" cy="230" r="10" fill="#f2c184"/>
                    <circle cx="289" cy="223" r="10" fill="#f2c184"/>
                    <!-- desk top -->
                    <rect x="66" y="238" width="288" height="12" rx="5" fill="#e8b06e"/>
                    <rect x="66" y="238" width="288" height="4" rx="2" fill="#f2c887"/>
                    <!-- laptop -->
                    <rect x="272" y="188" width="66" height="46" rx="5" fill="#e5e7eb"/>
                    <rect x="277" y="193" width="56" height="36" rx="3" fill="#046706"/>
                    <rect x="283" y="200" width="30" height="5" rx="2.5" fill="#ffffff"/>
                    <rect x="283" y="209" width="44" height="5" rx="2.5" fill="#bbf7d0"/>
                    <rect x="283" y="218" width="22" height="5" rx="2.5" fill="#ffffff"/>
                    <rect x="265" y="234" width="80" height="6" rx="3" fill="#cbd5e1"/>
                    <!-- mug -->
                    <rect x="100" y="212" width="20" height="26" rx="4" fill="#ffffff"/>
                    <rect x="100" y="222" width="20" height="7" fill="#fdfa04"/>
                    <path d="M120,217 q9,1 8,9 q-1,8 -8,7" stroke="#ffffff" stroke-width="3.5"/>
                    <!-- plant -->
                    <polygon points="348,238 368,238 363,220 353,220" fill="#b45309"/>
                    <ellipse cx="358" cy="208" rx="7" ry="14" fill="#16a34a"/>
                    <ellipse cx="348" cy="212" rx="6" ry="11" fill="#15803d" transform="rotate(-24 348 212)"/>
                    <ellipse cx="368" cy="212" rx="6" ry="11" fill="#15803d" transform="rotate(24 368 212)"/>
                </svg>
                <span class="float-badge fb-question"><i class="ti ti-question-mark"></i></span>
                <span class="float-badge fb-sla"><i class="ti ti-device-laptop"></i></span>
                <span class="float-badge fb-gear"><i class="ti ti-settings"></i></span>
                <span class="float-badge fb-mail"><i class="ti ti-mail"></i></span>
                <span class="float-badge fb-chat"><i class="ti ti-messages"></i></span>
                <span class="float-badge fb-ticket"><i class="ti ti-ticket"></i></span>
                <span class="float-badge fb-headset"><i class="ti ti-headset"></i></span>
                <span class="float-badge fb-life"><i class="ti ti-lifebuoy"></i></span>
                <span class="float-badge fb-clock"><i class="ti ti-clock"></i></span>
                <span class="float-badge fb-shield"><i class="ti ti-shield-check"></i></span>
            </div>
        </aside>

        <!-- Right: sign-in stage -->
        <main class="login-stage">
            <span class="doodle dd-ticket-1"><i class="ti ti-ticket"></i></span>
            <span class="doodle dd-chat-1"><i class="ti ti-messages"></i></span>
            <span class="doodle dd-bell-1"><i class="ti ti-bell"></i></span>
            <span class="doodle dd-chart-1"><i class="ti ti-chart-bar"></i></span>
            <span class="doodle dd-mail-1"><i class="ti ti-mail"></i></span>
            <div class="doodle-dots"></div>
            <div class="doodle-ring"></div>

            <div class="login-card">
                <div class="sheet-logo" aria-hidden="true"><img src="{{ asset('uilibs/images/cpsulogov4.png') }}" alt="" width="68" height="68"></div>
                <div class="card-header">
                    <h2 class="text-center">Service Now - Ticketing System</h2>
                    <p class="text-center">Login to your Ticketing account to pick up right where you left off.</p>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger" role="alert" style="margin-bottom: 1.125rem; font-size: 0.8125rem; padding: 0.625rem 0.75rem; border-radius: 8px; background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;">
                        <i class="ti ti-alert-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <form class="login-form" action="{{ route('postLogin') }}" method="POST" novalidate>
                    @csrf
                    <!-- Email / Username Field -->
                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="text" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required aria-label="Email">
                            <i class="ti ti-mail input-icon icon-right"></i>
                        </div>
                        @error('email')
                            <small style="color: #b91c1c; font-size: 0.75rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" placeholder="Password" required aria-label="Password">
                            <button type="button" id="togglePasswordBtn" class="toggle-password" aria-label="Toggle Password">
                                <i class="ti ti-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <small style="color: #b91c1c; font-size: 0.75rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot Password</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn">
                        <span>Login</span>
                        <i class="ti ti-arrow-right"></i>
                    </button>
                </form>

                <p class="form-footer">Maintained and Managed by Management Information System Office (MISO).</p>
            </div>
        </main>
    </div>

    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('uilibs/js/main.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('uilibs/js/login.js') }}"></script>
</body>

</html>
