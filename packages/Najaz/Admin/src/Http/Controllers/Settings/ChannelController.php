<?php

namespace Najaz\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Settings\ChannelDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Repositories\ChannelRepository;

class ChannelController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ChannelRepository $channelRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ChannelDataGrid::class)->process();
        }

        return view('admin::settings.channels.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::settings.channels.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $data = $this->validate(request(), [
            /* general */
            'code'                  => ['required', 'unique:channels,code', new \Webkul\Core\Rules\Code],
            'name'                  => 'required',
            'description'           => 'nullable',
            'hostname'              => 'unique:channels,hostname',

            /* currencies and locales */
            'locales'               => 'required|array|min:1',
            'default_locale_id'     => 'required|in_array:locales.*',

            /* design */
            'logo.*'                => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            'favicon.*'             => 'nullable|mimes:bmp,jpeg,jpg,png,webp,ico',

            /* seo - hidden but keep defaults */
            'seo_title'             => 'nullable|string',
            'seo_description'       => 'nullable|string',
            'seo_keywords'          => 'nullable|string',

            /* maintenance mode - hidden but keep defaults */
            'is_maintenance_on'     => 'boolean',
            'maintenance_mode_text' => 'nullable',
            'allowed_ips'           => 'nullable',
        ]);

        // Set default values for hidden fields
        $data['inventory_sources'] = [];
        $data['currencies'] = [];
        
        // Get default currency ID - use first available currency or default to 1
        $data['base_currency_id'] = $this->getDefaultCurrencyId();
        
        $data['theme'] = null;
        $data['root_category_id'] = null;
        $data['seo_title'] = $data['seo_title'] ?? '';
        $data['seo_description'] = $data['seo_description'] ?? '';
        $data['seo_keywords'] = $data['seo_keywords'] ?? '';
        $data['is_maintenance_on'] = $data['is_maintenance_on'] ?? false;
        $data['maintenance_mode_text'] = $data['maintenance_mode_text'] ?? null;
        $data['allowed_ips'] = $data['allowed_ips'] ?? null;

        $data = $this->setSEOContent($data);

        Event::dispatch('core.channel.create.before');

        $channel = $this->channelRepository->create($data);

        if ($channel->is_maintenance_on) {
            app()->maintenanceMode()->activate([]);
        } else {
            app()->maintenanceMode()->deactivate();
        }

        Event::dispatch('core.channel.create.after', $channel);

        session()->flash('success', trans('admin::app.settings.channels.create.create-success'));

        return redirect()->route('admin.settings.channels.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        // Only load locales relationship, avoid currencies and inventory_sources to prevent ModelProxy errors
        $channel = $this->channelRepository->with(['locales'])->findOrFail($id);

        return view('admin::settings.channels.edit', compact('channel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(int $id)
    {
        $locale = core()->getRequestedLocaleCode();

        $data = $this->validate(request(), [
            /* general */
            'code'                             => ['required', 'unique:channels,code,'.$id, new \Webkul\Core\Rules\Code],
            $locale.'.name'                    => 'required',
            $locale.'.description'             => 'nullable',
            'hostname'                         => 'unique:channels,hostname,'.$id,

            /* currencies and locales */
            'locales'                          => 'required|array|min:1',
            'default_locale_id'                => 'required|in_array:locales.*',

            /* design */
            'logo.*'                           => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            'favicon.*'                        => 'nullable|mimes:bmp,jpeg,jpg,png,webp,ico',

            /* seo - hidden but keep defaults */
            $locale.'.seo_title'               => 'nullable|string',
            $locale.'.seo_description'         => 'nullable|string',
            $locale.'.seo_keywords'            => 'nullable|string',

            /* maintenance mode - hidden but keep defaults */
            'is_maintenance_on'                => 'boolean',
            $locale.'.maintenance_mode_text'   => 'nullable',
            'allowed_ips'                      => 'nullable',
        ]);

        // Get existing channel to preserve relationships
        $channel = $this->channelRepository->findOrFail($id);
        
        // Set default values for hidden fields - preserve existing relationships
        // Always use empty arrays for disabled modules to avoid ModelProxy errors
        $data['inventory_sources'] = [];
        $data['currencies'] = [];
        
        // Get inventory_sources using DB query directly to avoid ModelProxy issues
        // Don't check Proxy at all, just use DB if table exists
        try {
            if (DB::getSchemaBuilder()->hasTable('channel_inventory_sources')) {
                $inventorySourceIds = DB::table('channel_inventory_sources')
                    ->where('channel_id', $channel->id)
                    ->pluck('inventory_source_id')
                    ->toArray();
                $data['inventory_sources'] = $inventorySourceIds;
            }
        } catch (\Exception $e) {
            $data['inventory_sources'] = [];
        }
        
        // Get currencies using DB query to avoid ModelProxy issues
        try {
            $currencyIds = \DB::table('channel_currencies')
                ->where('channel_id', $channel->id)
                ->pluck('currency_id')
                ->toArray();
            $data['currencies'] = $currencyIds;
        } catch (\Exception $e) {
            $data['currencies'] = [];
        }
        
        // Ensure these are set even if null
        // base_currency_id cannot be null, so use existing or default
        $data['base_currency_id'] = $channel->base_currency_id ?? $this->getDefaultCurrencyId();
        $data['theme'] = $channel->theme ?? null;
        $data['root_category_id'] = $channel->root_category_id ?? null;
        
        // Preserve SEO and maintenance mode settings
        if (!isset($data[$locale]['seo_title'])) {
            $translation = $channel->translate($locale);
            $seo = $translation['home_seo'] ?? [];
            $data[$locale]['seo_title'] = $seo['meta_title'] ?? '';
            $data[$locale]['seo_description'] = $seo['meta_description'] ?? '';
            $data[$locale]['seo_keywords'] = $seo['meta_keywords'] ?? '';
        }
        
        $data['is_maintenance_on'] = request()->input('is_maintenance_on') == '1' ? true : ($channel->is_maintenance_on ?? false);
        
        $translation = $channel->translate($locale);
        $data[$locale]['maintenance_mode_text'] = $data[$locale]['maintenance_mode_text'] ?? ($translation['maintenance_mode_text'] ?? $channel->maintenance_mode_text);
        $data['allowed_ips'] = $data['allowed_ips'] ?? $channel->allowed_ips;

        $data = $this->setSEOContent($data, $locale);

        // Ensure arrays are always arrays before sync
        $data['inventory_sources'] = is_array($data['inventory_sources']) ? $data['inventory_sources'] : [];
        $data['currencies'] = is_array($data['currencies']) ? $data['currencies'] : [];
        $data['locales'] = is_array($data['locales']) ? $data['locales'] : [];

        Event::dispatch('core.channel.update.before', $id);

        $channel = $this->channelRepository->update($data, $id);

        if ($channel->is_maintenance_on) {
            app()->maintenanceMode()->activate([]);
        } else {
            app()->maintenanceMode()->deactivate();
        }

        Event::dispatch('core.channel.update.after', $channel);

        // Reload channel with base_currency relationship
        $channel->load('base_currency');
        if ($channel->base_currency && $channel->base_currency->code !== session()->get('currency')) {
            session()->put('currency', $channel->base_currency->code);
        }

        session()->flash('success', trans('admin::app.settings.channels.edit.update-success'));

        return redirect()->route('admin.settings.channels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $channel = $this->channelRepository->findOrFail($id);

        if ($channel->code == config('app.channel')) {
            return new JsonResponse([
                'message'    => trans('admin::app.settings.channels.index.last-delete-error'),
            ], 400);
        }

        try {
            Event::dispatch('core.channel.delete.before', $id);

            $this->channelRepository->delete($id);

            Event::dispatch('core.channel.delete.after', $id);

            return new JsonResponse([
                'message'    => trans('admin::app.settings.channels.index.delete-success'),
            ], 200);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message'    => trans('admin::app.settings.channels.index.delete-failed'),
        ], 500);
    }

    /**
     * Set the seo content and return back the updated array.
     *
     * @param  string  $locale
     * @return array
     */
    private function setSEOContent(array $data, $locale = null)
    {
        $editedData = $data;

        if ($locale) {
            $editedData = $data[$locale] ?? [];
        }

        // Ensure SEO fields exist with defaults
        $seoTitle = $editedData['seo_title'] ?? '';
        $seoDescription = $editedData['seo_description'] ?? '';
        $seoKeywords = $editedData['seo_keywords'] ?? '';

        $editedData['home_seo']['meta_title'] = $seoTitle;
        $editedData['home_seo']['meta_description'] = $seoDescription;
        $editedData['home_seo']['meta_keywords'] = $seoKeywords;

        $editedData = $this->unsetKeys($editedData, ['seo_title', 'seo_description', 'seo_keywords']);

        if ($locale) {
            $data[$locale] = $editedData;
            $editedData = $data;
        }

        return $editedData;
    }

    /**
     * Unset keys.
     *
     * @param  array  $keys
     * @return array
     */
    private function unsetKeys($data, $keys)
    {
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * Get default currency ID.
     *
     * @return int
     */
    private function getDefaultCurrencyId(): int
    {
        try {
            // Try to get currency from config
            $currencyCode = config('app.currency');
            if ($currencyCode) {
                $currency = DB::table('currencies')
                    ->where('code', $currencyCode)
                    ->first();
                if ($currency) {
                    return $currency->id;
                }
            }
            
            // Get first available currency
            $currency = DB::table('currencies')->first();
            if ($currency) {
                return $currency->id;
            }
            
            // Default to 1 if no currency found
            return 1;
        } catch (\Exception $e) {
            // If any error, default to 1
            return 1;
        }
    }
}

