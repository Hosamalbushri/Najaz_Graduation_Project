<x-admin::layouts.anonymous>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.users.forget-password.create.page-title')
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
                @lang('admin::app.users.forget-password.create.title')
            </h1>

            <div class="mt-8 rounded max-sm:mt-6">
                <x-admin::form :action="route('admin.forget_password.store')">
                    <!-- Email -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.users.forget-password.create.email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            class="px-4 py-3 max-md:py-2.5 max-sm:py-2"
                            id="email"
                            name="email"
                            rules="required|email"
                            :value="old('email')"
                            :label="trans('admin::app.users.forget-password.create.email')"
                            :placeholder="trans('admin::app.users.forget-password.create.email')"
                            :aria-label="trans('admin::app.users.forget-password.create.email')"
                            aria-required="true"
                        />

                        <x-admin::form.control-group.error control-name="email" />
                    </x-admin::form.control-group>

                    <div class="flex justify-end mt-4">
                        <a
                            href="{{ route('admin.session.create') }}"
                            class="login-forgot-link"
                        >
                            <span>
                                @lang('admin::app.users.forget-password.create.sign-in-link')
                            </span>
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex flex-wrap items-center gap-9 max-sm:justify-center max-sm:gap-5 max-sm:text-center">
                        <button
                            class="login-primary-button m-0 mx-auto block w-full max-w-full rounded-xl px-8 py-3 text-center text-sm max-md:max-w-full max-md:rounded-lg max-md:py-2.5 max-sm:py-2 ltr:ml-0 rtl:mr-0"
                            type="submit"
                            aria-label="{{ trans('admin::app.users.forget-password.create.submit-btn') }}"
                        >
                            @lang('admin::app.users.forget-password.create.submit-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        </div>
    </div>
</x-admin::layouts.anonymous>
