<x-admin::layouts.anonymous>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.users.reset-password.title')
    </x-slot>

    <div class="container login-page-container">
        <!-- Form Container -->
        <div class="login-form-container">
            <!-- Company Logo -->
            <div class="login-logo-container">
                @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                    <img
                        src="{{ Storage::url($logo) }}"
                        alt="{{ config('app.name') }}"
                    />
                @else
                    <img
                        src="{{ bagisto_asset('images/logo.svg') }}"
                        alt="{{ config('app.name') }}"
                    />
                @endif
            </div>

            <h1 class="login-title">
                @lang('admin::app.users.reset-password.title')
            </h1>

            <div class="mt-8 rounded max-sm:mt-6">
                <x-admin::form :action="route('admin.reset_password.store')">
                    <x-admin::form.control-group.control
                        type="hidden"
                        name="token"
                        :value="$token"
                    />

                    <!-- Email -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.reset-password.email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            class="px-4 py-3 max-md:py-2.5 max-sm:py-2"
                            id="email"
                            name="email"
                            rules="required|email"
                            :label="trans('admin::app.users.reset-password.email')"
                            :placeholder="trans('admin::app.users.reset-password.email')"
                            :aria-label="trans('admin::app.users.reset-password.email')"
                            aria-required="true"
                        />

                        <x-admin::form.control-group.error control-name="email" />
                    </x-admin::form.control-group>

                    <!-- Password -->
                    <x-admin::form.control-group class="relative">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.reset-password.password')
                        </x-admin::form.control-group.label>

                        <div class="relative">
                            <x-admin::form.control-group.control
                                type="password"
                                class="px-4 py-3 pr-12 max-md:py-2.5 max-sm:py-2"
                                id="password"
                                name="password"
                                rules="required|min:6"
                                :label="trans('admin::app.users.reset-password.password')"
                                :placeholder="trans('admin::app.users.reset-password.password')"
                                :aria-label="trans('admin::app.users.reset-password.password')"
                                aria-required="true"
                                ref="password"
                            />

                            <!-- Password Toggle Button -->
                            <button
                                type="button"
                                class="login-password-toggle absolute top-1/2 -translate-y-1/2 cursor-pointer text-xl ltr:right-3 rtl:left-3"
                                onclick="switchVisibility('password')"
                                aria-label="{{ trans('admin::app.users.sessions.show-password') ?: 'إظهار كلمة المرور' }}"
                            >
                                <span class="login-password-icon icon-view" id="password-icon"></span>
                            </button>
                        </div>

                        <x-admin::form.control-group.error control-name="password" />
                    </x-admin::form.control-group>

                    <!-- Confirm Password -->
                    <x-admin::form.control-group class="relative">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.reset-password.confirm-password')
                        </x-admin::form.control-group.label>

                        <div class="relative">
                            <x-admin::form.control-group.control
                                type="password"
                                class="px-4 py-3 pr-12 max-md:py-2.5 max-sm:py-2"
                                id="password_confirmation"
                                name="password_confirmation"
                                rules="confirmed:@password"
                                :label="trans('admin::app.users.reset-password.confirm-password')"
                                :placeholder="trans('admin::app.users.reset-password.confirm-password')"
                                :aria-label="trans('admin::app.users.reset-password.confirm-password')"
                                aria-required="true"
                                ref="password"
                            />

                            <!-- Password Toggle Button -->
                            <button
                                type="button"
                                class="login-password-toggle absolute top-1/2 -translate-y-1/2 cursor-pointer text-xl ltr:right-3 rtl:left-3"
                                onclick="switchVisibility('password_confirmation')"
                                aria-label="{{ trans('admin::app.users.sessions.show-password') ?: 'إظهار كلمة المرور' }}"
                            >
                                <span class="login-password-icon icon-view" id="password_confirmation-icon"></span>
                            </button>
                        </div>

                        <x-admin::form.control-group.error control-name="password_confirmation" />
                    </x-admin::form.control-group>

                    <div class="flex justify-end mt-4">
                        <a
                            href="{{ route('admin.session.create') }}"
                            class="login-forgot-link"
                        >
                            <span>
                                @lang('admin::app.users.reset-password.back-link-title')
                            </span>
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex flex-wrap items-center gap-9 max-sm:justify-center max-sm:gap-5 max-sm:text-center">
                        <button
                            class="login-primary-button m-0 mx-auto block w-full max-w-full rounded-xl px-8 py-3 text-center text-sm max-md:max-w-full max-md:rounded-lg max-md:py-2.5 max-sm:py-2 ltr:ml-0 rtl:mr-0"
                            type="submit"
                            aria-label="{{ trans('admin::app.users.reset-password.submit-btn') }}"
                        >
                            @lang('admin::app.users.reset-password.submit-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchVisibility(fieldId) {
                let passwordField = document.getElementById(fieldId);
                let passwordIcon = document.getElementById(fieldId + '-icon');

                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    passwordIcon.classList.remove("icon-view");
                    passwordIcon.classList.add("icon-view-close");
                } else {
                    passwordField.type = "password";
                    passwordIcon.classList.remove("icon-view-close");
                    passwordIcon.classList.add("icon-view");
                }
            }
        </script>
    @endpush
</x-admin::layouts.anonymous>
