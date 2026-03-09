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
    try {
        $setting = Utility::colorset() ?? [];
        $lang = Utility::fetchUserLang() ?? 'en';
    } catch (\Throwable $e) {
        Log::error('auth/confirm-password — ' . get_class($e) . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
@endphp

@extends(ExtendingLayoutsConstants::AUTH)

@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Confirm Password') }}
@endsection

@section(YieldingConstants::AUTH_CTT)
    <div class="card">
        <div class="{{ VC::CD_BD }}">
            <h4 class="{{ VC::MB3_FW600 }}">{{ __('Confirm Password') }}</h4>
            <p class="{{ VC::MB4_TXMT }}">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>

            @if(session('status'))
                <div class="{{ VC::ALT_SUC_MB3 }}" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="{{ VC::FM_GB3 }}">
                    <label for="password" class="{{ VC::FM_LB }}">{{ __('Password') }}</label>
                    <input id="password"
                           type="password"
                           class="{{ VC::FM_CT }} @error('password') is-invalid @enderror"
                           name="password"
                           required
                           autocomplete="current-password" />
                    @error('password')
                        <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::D_GR }}">
                    <button type="submit" class="{{ VC::BT_PRM_BLK_MT2 }}">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
