<x-admin::layouts.anonymous>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.users.sessions.title')
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
                @lang('admin::app.users.sessions.title')
            </h1>

            <div class="mt-8 rounded max-sm:mt-6">
                <x-admin::form :action="route('admin.session.store')">
                    <!-- Email -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.sessions.email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            class="px-4 py-3 max-md:py-2.5 max-sm:py-2"
                            id="email"
                            name="email"
                            rules="required|email"
                            :label="trans('admin::app.users.sessions.email')"
                            placeholder="email@example.com"
                            :aria-label="trans('admin::app.users.sessions.email')"
                            aria-required="true"
                        />

                        <x-admin::form.control-group.error control-name="email" />
                    </x-admin::form.control-group>

                    <!-- Password -->
                    <x-admin::form.control-group class="relative">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.sessions.password')
                        </x-admin::form.control-group.label>

                        <div class="relative">
                            <x-admin::form.control-group.control
                                type="password"
                                class="px-4 py-3 pr-12 max-md:py-2.5 max-sm:py-2"
                                id="password"
                                name="password"
                                rules="required|min:6"
                                :label="trans('admin::app.users.sessions.password')"
                                :placeholder="trans('admin::app.users.sessions.password')"
                                :aria-label="trans('admin::app.users.sessions.password')"
                                aria-required="true"
                            />

                            <!-- Password Toggle Button -->
                            <button
                                type="button"
                                class="login-password-toggle absolute top-1/2 -translate-y-1/2 cursor-pointer text-xl ltr:right-3 rtl:left-3"
                                onclick="switchVisibility()"
                                aria-label="{{ trans('admin::app.users.sessions.show-password') ?: 'إظهار كلمة المرور' }}"
                            >
                                <span class="login-password-icon icon-view"></span>
                            </button>
                        </div>

                        <x-admin::form.control-group.error control-name="password" />
                    </x-admin::form.control-group>

                    <div class="flex justify-end mt-4">
                        <a
                            href="{{ route('admin.forget_password.create') }}"
                            class="login-forgot-link"
                        >
                            <span>
                                @lang('admin::app.users.sessions.forget-password-link')
                            </span>
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex flex-wrap items-center gap-9 max-sm:justify-center max-sm:gap-5 max-sm:text-center">
                        <button
                            class="login-primary-button m-0 mx-auto block w-full max-w-full rounded-xl px-8 py-3 text-center text-sm max-md:max-w-full max-md:rounded-lg max-md:py-2.5 max-sm:py-2 ltr:ml-0 rtl:mr-0"
                            type="submit"
                            aria-label="{{ trans('admin::app.users.sessions.submit-btn') }}"
                        >
                            @lang('admin::app.users.sessions.submit-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchVisibility() {
                let passwordField = document.getElementById("password");
                let passwordIcon = document.querySelector('.login-password-icon');

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