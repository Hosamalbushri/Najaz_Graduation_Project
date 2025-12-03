<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.categories.edit.title')
    </x-slot>

    @php
        $currentLocale = core()->getRequestedLocale();
    @endphp

    {!! view_render_event('najaz.admin.services.categories.edit.before') !!}

    <!-- Category Edit Form -->
    <x-admin::form
        :action="route('admin.services.categories.update', $category->id)"
        enctype="multipart/form-data"
        method="PUT"
    >
        {!! view_render_event('najaz.admin.services.categories.edit.edit_form_controls.before') !!}

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.categories.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.services.categories.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.common.cancel')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.categories.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Locale Switcher -->
        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-1">
                <x-admin::dropdown 
                    position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}" 
                    :class="core()->getAllLocales()->count() <= 1 ? 'hidden' : ''"
                >
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-language text-2xl"></span>

                            {{ $currentLocale->name }}

                            <input
                                type="hidden"
                                name="locale"
                                value="{{ $currentLocale->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach (core()->getAllLocales() as $locale)
                            <a
                                href="?{{ Arr::query(['locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        <!-- Full Panel -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('najaz.admin.services.categories.edit.card.general.before') !!}

                <!-- General -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.categories.edit.general')
                    </p>

                    <!-- Locales -->
                    <x-admin::form.control-group.control
                        type="hidden"
                        name="locale"
                        :value="core()->getRequestedLocaleCode()"
                    />

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.categories.edit.name')
                        </x-admin::form.control-group.label>

                        <v-field
                            type="text"
                            name="{{ core()->getRequestedLocaleCode() }}[name]"
                            rules="required"
                            value="{{ old(core()->getRequestedLocaleCode())['name'] ?? $category->translate(core()->getRequestedLocaleCode())?->name }}"
                            v-slot="{ field, errors }"
                            label="{{ trans('Admin::app.services.categories.edit.name') }}"
                        >
                            <input
                                type="text"
                                id="name"
                                :class="[errors.length ? 'border border-red-600 hover:border-red-600' : '']"
                                class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                name="{{ core()->getRequestedLocaleCode() }}[name]"
                                v-bind="field"
                                placeholder="{{ trans('Admin::app.services.categories.edit.name') }}"
                                v-slugify-target:slug="setValues"
                            />
                        </v-field>

                        <x-admin::form.control-group.error control-name="{{ core()->getRequestedLocaleCode() }}[name]" />
                    </x-admin::form.control-group>

                    <div>
                        <!-- Parent category -->
                        <label class="mb-2.5 block text-xs font-medium leading-6 text-gray-800 dark:text-white">
                            @lang('Admin::app.services.categories.edit.parent-category')
                        </label>

                        <!-- Radio select button -->
                        <div class="flex flex-col gap-3">
                            <x-admin::tree.view
                                input-type="radio"
                                id-field="id"
                                name-field="parent_id"
                                value-field="id"
                                :items="json_encode($categories)"
                                :value="json_encode($category->parent_id)"
                                :fallback-locale="config('app.fallback_locale')"
                            />
                        </div>
                    </div>
                </div>

                {!! view_render_event('najaz.admin.services.categories.edit.card.general.after') !!}

                {!! view_render_event('najaz.admin.services.categories.edit.card.description_images.before') !!}

                <!-- Description and images -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.categories.edit.images')
                    </p>

                    <!-- Description -->
                    <v-description v-slot="{ isDescriptionRequired }">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label ::class="{ 'required' : isDescriptionRequired}">
                                @lang('Admin::app.services.categories.edit.description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="description"
                                class="description"
                                name="{{ core()->getRequestedLocaleCode() }}[description]"
                                ::rules="{ 'required' : isDescriptionRequired}"
                                :value="old(core()->getRequestedLocaleCode())['description'] ?? $category->translate(core()->getRequestedLocaleCode())?->description"
                                :label="trans('Admin::app.services.categories.edit.description')"
                                :tinymce="true"
                            />

                            <x-admin::form.control-group.error control-name="{{ core()->getRequestedLocaleCode() }}[description]" />
                        </x-admin::form.control-group>
                    </v-description>

                    <div class="flex pt-5">
                        <!-- Add Logo -->
                        <div class="flex w-2/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('Admin::app.services.categories.edit.logo')
                            </p>

                            <x-admin::media.images
                                name="logo_path"
                                :uploaded-images="$category->logo_path ? [['id' => 'logo_path', 'url' => $category->logo_url]] : []"
                            />
                        </div>

                        <!-- Add Banner -->
                        <div class="flex w-3/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('Admin::app.services.categories.edit.banner')
                            </p>

                            <x-admin::media.images
                                name="banner_path"
                                width="220px"
                                :uploaded-images="$category->banner_path ? [['id' => 'banner_path', 'url' => $category->banner_url]] : []"
                            />
                        </div>
                    </div>
                </div>

                {!! view_render_event('najaz.admin.services.categories.edit.card.description_images.after') !!}

                {!! view_render_event('najaz.admin.services.categories.edit.card.seo.before') !!}

                <!-- SEO Details -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.categories.edit.seo')
                    </p>

                    <!-- SEO Title & Description Blade Component -->
                    <x-admin::seo />

                    <div class="mt-8">
                        <!-- Meta Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.categories.edit.meta-title')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="meta_title"
                                name="{{ core()->getRequestedLocaleCode() }}[meta_title]"
                                :value="old(core()->getRequestedLocaleCode())['meta_title'] ?? $category->translate(core()->getRequestedLocaleCode())?->meta_title"
                                :label="trans('Admin::app.services.categories.edit.meta-title')"
                                :placeholder="trans('Admin::app.services.categories.edit.meta-title')"
                            />
                        </x-admin::form.control-group>

                        <!-- Slug -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.categories.edit.slug')
                            </x-admin::form.control-group.label>

                            <v-field
                                type="text"
                                name="{{ core()->getRequestedLocaleCode() }}[slug]"
                                rules="required"
                                value="{{ old(core()->getRequestedLocaleCode())['slug'] ?? $category->translate(core()->getRequestedLocaleCode())?->slug }}"
                                label="{{ trans('Admin::app.services.categories.edit.slug') }}"
                                v-slot="{ field, errors }"
                            >
                                <input
                                    type="text"
                                    id="slug"
                                    :class="[errors.length ? 'border border-red-600 hover:border-red-600' : '']"
                                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    name="{{ core()->getRequestedLocaleCode() }}[slug]"
                                    v-bind="field"
                                    placeholder="{{ trans('Admin::app.services.categories.edit.slug') }}"
                                    v-slugify-target:slug
                                />
                            </v-field>

                            <x-admin::form.control-group.error control-name="{{ core()->getRequestedLocaleCode() }}[slug]" />
                        </x-admin::form.control-group>

                        <!-- Meta Keywords -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.categories.edit.meta-keywords')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="{{ core()->getRequestedLocaleCode() }}[meta_keywords]"
                                :value="old(core()->getRequestedLocaleCode())['meta_keywords'] ?? $category->translate(core()->getRequestedLocaleCode())?->meta_keywords"
                                :label="trans('Admin::app.services.categories.edit.meta-keywords')"
                                :placeholder="trans('Admin::app.services.categories.edit.meta-keywords')"
                            />
                        </x-admin::form.control-group>

                        <!-- Meta Description -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.categories.edit.meta-description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="meta_description"
                                name="{{ core()->getRequestedLocaleCode() }}[meta_description]"
                                :value="old(core()->getRequestedLocaleCode())['meta_description'] ?? $category->translate(core()->getRequestedLocaleCode())?->meta_description"
                                :label="trans('Admin::app.services.categories.edit.meta-description')"
                                :placeholder="trans('Admin::app.services.categories.edit.meta-description')"
                            />
                        </x-admin::form.control-group>
                    </div>
                </div>

                {!! view_render_event('najaz.admin.services.categories.edit.card.seo.after') !!}
            </div>

            <!-- Right Section -->
            <div class="flex w-[360px] max-w-full flex-col gap-2">
                <!-- Settings -->

                {!! view_render_event('najaz.admin.services.categories.edit.card.accordion.settings.before') !!}

                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.categories.edit.general')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Position -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required text-gray-800 dark:text-white">
                                @lang('Admin::app.services.categories.edit.position')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="position"
                                rules="required|integer"
                                :value="old('position') ?? $category->position"
                                :label="trans('Admin::app.services.categories.edit.position')"
                                :placeholder="trans('Admin::app.services.categories.edit.position')"
                            />

                            <x-admin::form.control-group.error control-name="position" />
                        </x-admin::form.control-group>

                        <!-- Display Mode  -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required font-medium text-gray-800 dark:text-white">
                                @lang('Admin::app.services.categories.edit.display-mode')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="display_mode"
                                class="cursor-pointer"
                                name="display_mode"
                                rules="required"
                                :value="old('display_mode') ?? $category->display_mode"
                                :label="trans('Admin::app.services.categories.edit.display-mode')"
                            >
                                <!-- Options -->
                                <option value="services_and_description" {{ old('display_mode') ?? $category->display_mode == 'services_and_description' ? 'selected' : '' }}>
                                    @lang('Admin::app.services.categories.edit.services-description')
                                </option>

                                <option value="services_only" {{ old('display_mode') ?? $category->display_mode == 'services_only' ? 'selected' : '' }}>
                                    @lang('Admin::app.services.categories.edit.services-only')
                                </option>

                                <option value="description_only" {{ old('display_mode') ?? $category->display_mode == 'description_only' ? 'selected' : '' }}>
                                    @lang('Admin::app.services.categories.edit.description-only')
                                </option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="display_mode" />
                        </x-admin::form.control-group>

                        <!-- Status -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="font-medium text-gray-800 dark:text-white">
                                @lang('Admin::app.services.categories.edit.status')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                class="cursor-pointer"
                                name="status"
                                value="1"
                                :checked="(bool) $category->status"
                                :label="trans('Admin::app.services.categories.edit.status')"
                            />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('najaz.admin.services.categories.edit.card.accordion.settings.after') !!}

            </div>
        </div>

        {!! view_render_event('najaz.admin.services.categories.edit.edit_form_controls.after') !!}

    </x-admin::form>

    {!! view_render_event('najaz.admin.services.categories.edit.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-description-template"
        >
            <div>
                <slot :is-description-required="isDescriptionRequired"></slot>
            </div>
        </script>

        <script type="module">
            app.component('v-description', {
                template: '#v-description-template',

                data() {
                    return {
                        isDescriptionRequired: true,

                        displayMode: "{{ old('display_mode') ?? $category->display_mode }}",
                    };
                },

                mounted() {
                    this.isDescriptionRequired = this.displayMode !== 'services_only';

                    this.$nextTick(() => {
                        document.querySelector('#display_mode').addEventListener('change', (e) => {
                            this.isDescriptionRequired = e.target.value !== 'services_only';
                        });
                    });
                },
            });
        </script>
    @endPushOnce

</x-admin::layouts>

