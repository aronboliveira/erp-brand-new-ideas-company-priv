@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.environment.wizard.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-magic fa-fw" aria-hidden="true"></i>
    {!! trans('installer_messages.environment.wizard.title') !!}
@endsection

@section('container')
    <div class="tabs tabs-full">
        <form method="post" action="{{ route('LaravelInstaller::environmentSaveWizard') }}" novalidate>
            @csrf

            {{-- ======================= APP / ENV ======================= --}}
            <label for="tab1" class="tab-label">
                <i class="fa fa-cog fa-2x fa-fw" aria-hidden="true"></i><br />
                {{ trans('installer_messages.environment.wizard.tabs.environment') }}
            </label>

            <div id="tab1content">
                {{-- App Name --}}
                <div class="form-group @error('app_name') has-error @enderror">
                    <label for="app_name">
                        {{ trans('installer_messages.environment.wizard.form.app_name_label') }}
                    </label>
                    <input
                        type="text"
                        id="app_name"
                        name="app_name"
                        value="{{ old('app_name', config('app.name', 'Laravel')) }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.app_name_placeholder') }}"
                        required
                        autocomplete="off"
                    />
                    @error('app_name')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- App URL --}}
                <div class="form-group @error('app_url') has-error @enderror">
                    <label for="app_url">
                        {{ trans('installer_messages.environment.wizard.form.app_url_label') }}
                    </label>
                    <input
                        type="url"
                        id="app_url"
                        name="app_url"
                        value="{{ old('app_url', url('/')) }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.app_url_placeholder') }}"
                        required
                        inputmode="url"
                        autocomplete="off"
                    />
                    @error('app_url')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Opinionated sensible defaults (hidden) --}}
                <input type="hidden" name="environment"      value="local" />
                <input type="hidden" name="app_debug"        value="false" />
                <input type="hidden" name="app_log_level"    value="debug" />
            </div>

            {{-- ======================= DATABASE ======================= --}}
            <label for="tab2" class="tab-label">
                <i class="fa fa-database fa-2x fa-fw" aria-hidden="true"></i><br />
                {{ trans('installer_messages.environment.wizard.tabs.database') }}
            </label>

            <div id="tab2content">
                {{-- DB Driver (fixed to MySQL for wizard simplicity) --}}
                <input type="hidden" name="database_connection" value="mysql" />
                <div class="form-group @error('database_connection') has-error @enderror">
                    <label for="database_connection">
                        {{ trans('installer_messages.environment.wizard.form.db_connection_label') }}
                    </label>
                    <b>{{ trans('installer_messages.environment.wizard.form.db_connection_label_mysql') }}</b>
                    @error('database_connection')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Host --}}
                <div class="form-group @error('database_hostname') has-error @enderror">
                    <label for="database_hostname">
                        {{ trans('installer_messages.environment.wizard.form.db_host_label') }}
                    </label>
                    <input
                        type="text"
                        id="database_hostname"
                        name="database_hostname"
                        value="{{ old('database_hostname', '127.0.0.1') }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.db_host_placeholder') }}"
                        required
                        autocomplete="off"
                    />
                    @error('database_hostname')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Port --}}
                <div class="form-group @error('database_port') has-error @enderror">
                    <label for="database_port">
                        {{ trans('installer_messages.environment.wizard.form.db_port_label') }}
                    </label>
                    <input
                        type="number"
                        id="database_port"
                        name="database_port"
                        value="{{ old('database_port', 3306) }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.db_port_placeholder') }}"
                        min="1"
                        step="1"
                        required
                        inputmode="numeric"
                        autocomplete="off"
                    />
                    @error('database_port')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Database Name --}}
                <div class="form-group @error('database_name') has-error @enderror">
                    <label for="database_name">
                        {{ trans('installer_messages.environment.wizard.form.db_name_label') }}
                    </label>
                    <input
                        type="text"
                        id="database_name"
                        name="database_name"
                        value="{{ old('database_name') }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.db_name_placeholder') }}"
                        required
                        autocomplete="off"
                    />
                    @error('database_name')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Username --}}
                <div class="form-group @error('database_username') has-error @enderror">
                    <label for="database_username">
                        {{ trans('installer_messages.environment.wizard.form.db_username_label') }}
                    </label>
                    <input
                        type="text"
                        id="database_username"
                        name="database_username"
                        value="{{ old('database_username') }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.db_username_placeholder') }}"
                        required
                        autocomplete="off"
                    />
                    @error('database_username')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group @error('database_password') has-error @enderror">
                    <label for="database_password">
                        {{ trans('installer_messages.environment.wizard.form.db_password_label') }}
                    </label>
                    <input
                        type="password"
                        id="database_password"
                        name="database_password"
                        value="{{ old('database_password') }}"
                        placeholder="{{ trans('installer_messages.environment.wizard.form.db_password_placeholder') }}"
                        autocomplete="new-password"
                    />
                    @error('database_password')
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            {{-- ======================= HIDDEN: sensible defaults ======================= --}}
            {{-- Cache / Queue / Broadcast --}}
            <input type="hidden" name="broadcast_driver" value="log" />
            <input type="hidden" name="cache_driver"      value="file" />
            <input type="hidden" name="session_driver"    value="file" />
            <input type="hidden" name="queue_driver"      value="sync" />

            {{-- Redis --}}
            <input type="hidden" name="redis_hostname" value="{{ old('redis_hostname', '127.0.0.1') }}" />
            <input type="hidden" name="redis_password" value="{{ old('redis_password', 'null') }}" />
            <input type="hidden" name="redis_port"     value="{{ old('redis_port', 6379) }}" />

            {{-- Mail --}}
            <input type="hidden" name="mail_driver"     value="{{ old('mail_driver', 'smtp') }}" />
            <input type="hidden" name="mail_host"       value="{{ old('mail_host', 'smtp.mailtrap.io') }}" />
            <input type="hidden" name="mail_port"       value="{{ old('mail_port', 2525) }}" />
            <input type="hidden" name="mail_username"   value="{{ old('mail_username', 'null') }}" />
            <input type="hidden" name="mail_password"   value="{{ old('mail_password', 'null') }}" />
            <input type="hidden" name="mail_encryption" value="{{ old('mail_encryption', 'null') }}" />

            {{-- Pusher --}}
            <input type="hidden" name="pusher_app_id"     value="{{ old('pusher_app_id') }}" />
            <input type="hidden" name="pusher_app_key"    value="{{ old('pusher_app_key') }}" />
            <input type="hidden" name="pusher_app_secret" value="{{ old('pusher_app_secret') }}" />

            {{-- ======================= Submit ======================= --}}
            <div class="buttons">
                <button class="button" type="submit">
                    {{ trans('installer_messages.environment.wizard.form.buttons.install') }}
                    <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
@endsection


@section('scripts')
    <script async src="{{ asset('assets/js/routes/installer/lang/env.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/installer/env.js') }}"></script>
@endsection
