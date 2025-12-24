<?php

namespace Webkul\Core\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Eloquent\Repository;

class ChannelRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Core\Contracts\Channel';
    }

    /**
     * Create.
     *
     * @return \Webkul\Core\Contracts\Channel
     */
    public function create(array $data)
    {

        $model = $this->getModel();

        foreach (core()->getAllLocales() as $locale) {
            foreach ($model->translatedAttributes as $attribute) {
                if (isset($data[$attribute])) {
                    $data[$locale->code][$attribute] = $data[$attribute];
                }
            }
        }

        $channel = parent::create($data);

        $channel->locales()->sync($data['locales'] ?? []);

        $channel->currencies()->sync($data['currencies'] ?? []);

        // Only sync inventory_sources if Inventory module is enabled
        // Use DB query directly to avoid ModelProxy issues
        if (isset($data['inventory_sources']) && is_array($data['inventory_sources'])) {
            try {
                // Check if table exists
                if (Schema::hasTable('channel_inventory_sources')) {
                    // Use DB query directly without checking Proxy to avoid ModelProxy error
                    // This works even if Inventory module is disabled
                    DB::table('channel_inventory_sources')
                        ->where('channel_id', $channel->id)
                        ->delete();
                    
                    if (!empty($data['inventory_sources'])) {
                        $insertData = [];
                        foreach ($data['inventory_sources'] as $inventorySourceId) {
                            $insertData[] = [
                                'channel_id' => $channel->id,
                                'inventory_source_id' => $inventorySourceId,
                            ];
                        }
                        DB::table('channel_inventory_sources')->insert($insertData);
                    }
                }
            } catch (\Exception $e) {
                // Inventory module is disabled or table doesn't exist, skip sync
            }
        }

        $this->uploadImages($data, $channel);

        $this->uploadImages($data, $channel, 'favicon');

        return $channel;
    }

    /**
     * Update.
     *
     * @param  int  $id
     * @return \Webkul\Core\Contracts\Channel
     */
    public function update(array $data, $id)
    {
        $channel = parent::update($data, $id);

        $channel->locales()->sync($data['locales'] ?? []);

        $channel->currencies()->sync($data['currencies'] ?? []);

        // Only sync inventory_sources if Inventory module is enabled
        // Use DB query directly to avoid ModelProxy issues
        if (isset($data['inventory_sources']) && is_array($data['inventory_sources'])) {
            try {
                // Check if table exists
                if (Schema::hasTable('channel_inventory_sources')) {
                    // Use DB query directly without checking Proxy to avoid ModelProxy error
                    // This works even if Inventory module is disabled
                    DB::table('channel_inventory_sources')
                        ->where('channel_id', $channel->id)
                        ->delete();
                    
                    if (!empty($data['inventory_sources'])) {
                        $insertData = [];
                        foreach ($data['inventory_sources'] as $inventorySourceId) {
                            $insertData[] = [
                                'channel_id' => $channel->id,
                                'inventory_source_id' => $inventorySourceId,
                            ];
                        }
                        DB::table('channel_inventory_sources')->insert($insertData);
                    }
                }
            } catch (\Exception $e) {
                // Inventory module is disabled or table doesn't exist, skip sync
            }
        }

        $this->uploadImages($data, $channel);

        $this->uploadImages($data, $channel, 'favicon');

        return $channel;
    }

    /**
     * Upload images.
     *
     * @param  array  $data
     * @param  \Webkul\Core\Contracts\Channel  $channel
     * @param  string  $type
     * @return void
     */
    public function uploadImages($data, $channel, $type = 'logo')
    {
        if (request()->hasFile($type)) {
            $channel->{$type} = current(request()->file($type))->store('channel/'.$channel->id);

            $channel->save();
        } else {
            if (! isset($data[$type])) {
                if (! empty($data[$type])) {
                    Storage::delete($channel->{$type});
                }

                $channel->{$type} = null;

                $channel->save();
            }
        }
    }
}
