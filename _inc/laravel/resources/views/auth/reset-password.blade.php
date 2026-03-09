@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        SettingsConstants as SC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Log;

    $setting ??= [];
    $lang ??= 'en';
    $token ??= request()->route('token') ?? '';
    $email ??= request()->get('email', old('email', ''));

    try {
        $setting = Utility::colorset() ?? [];
        $lang = Utility::fetchUserLang() ?? 'en';
    } catch (\Throwable $e) {
        Log::error('auth/reset-password — ' . get_class($e) . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
@endphp

@extends(ExtendingLayoutsConstants::AUTH)

@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Reset Password') }}
@endsection

@section(YieldingConstants::AUTH_CTT)
    <div class="card">
        <div class="{{ VC::CD_BD }}">
            <h4 class="{{ VC::MB3_FW600 }}">{{ __('Reset Password') }}</h4>

            @if(session('status'))
                <div class="{{ VC::ALT_SUC_MB3 }}" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="{{ VC::FM_GB3 }}">
                    <label for="email" class="{{ VC::FM_LB }}">{{ __('Email Address') }}</label>
                    <input id="email"
                           type="email"
                           class="{{ VC::FM_CT }} @error('email') is-invalid @enderror"
                           name="email"
                           value="{{ $email }}"
                           required
                           autofocus
                           autocomplete="username" />
                    @error('email')
                        <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_GB3 }}">
                    <label for="password" class="{{ VC::FM_LB }}">{{ __('New Password') }}</label>
                    <input id="password"
                           type="password"
                           class="{{ VC::FM_CT }} @error('password') is-invalid @enderror"
                           name="password"
                           required
                           autocomplete="new-password" />
                    @error('password')
                        <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_GB3 }}">
                    <label for="password_confirmation" class="{{ VC::FM_LB }}">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation"
                           type="password"
                           class="{{ VC::FM_CT }}"
                           name="password_confirmation"
                           required
                           autocomplete="new-password" />
                </div>

                <div class="{{ VC::D_GR }}">
                    <button type="submit" class="{{ VC::BT_PRM_BLK_MT2 }}">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
