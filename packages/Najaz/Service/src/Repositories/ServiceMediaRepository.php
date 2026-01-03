<?php

namespace Najaz\Service\Repositories;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;

class ServiceMediaRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model()
    {
        /**
         * This repository is extended to `ServiceImageRepository` and `ServiceVideoRepository`
         * repository.
         *
         * And currently no model is assigned to this repo.
         */
    }

    /**
     * Get service directory.
     *
     * @param  \Najaz\Service\Contracts\Service  $service
     */
    public function getServiceDirectory($service): string
    {
        return 'services/'.$service->id;
    }

    /**
     * Upload.
     *
     * @param  array  $data
     * @param  \Najaz\Service\Contracts\Service  $service
     */
    public function upload($data, $service, string $uploadFileType): void
    {
        /**
         * Previous model ids for filtering.
         */
        $previousIds = $this->resolveFileTypeQueryBuilder($service, $uploadFileType)->pluck('id');

        $position = 0;

        if (! empty($data[$uploadFileType]['files'])) {
            foreach ($data[$uploadFileType]['files'] as $indexOrModelId => $file) {
                if ($file instanceof UploadedFile) {
                    if (Str::contains($file->getMimeType(), 'image')) {
                        $manager = new ImageManager;

                        $image = $manager->make($file)->encode('webp');

                        $path = $this->getServiceDirectory($service).'/'.Str::random(40).'.webp';

                        Storage::put($path, $image);
                    } else {
                        $path = $file->store($this->getServiceDirectory($service));
                    }

                    $this->create([
                        'type'       => $uploadFileType,
                        'path'       => $path,
                        'service_id' => $service->id,
                        'position'   => ++$position,
                    ]);
                } else {
                    if (is_numeric($index = $previousIds->search($indexOrModelId))) {
                        $previousIds->forget($index);
                    }

                    $this->update([
                        'position' => ++$position,
                    ], $indexOrModelId);
                }
            }
        }

        foreach ($previousIds as $indexOrModelId) {
            if (! $model = $this->find($indexOrModelId)) {
                continue;
            }

            Storage::delete($model->path);

            $this->delete($indexOrModelId);
        }
    }

    /**
     * Resolve file type query builder.
     *
     * @param  \Najaz\Service\Contracts\Service  $service
     * @return mixed
     *
     * @throws \Exception
     */
    private function resolveFileTypeQueryBuilder($service, string $uploadFileType)
    {
        if ($uploadFileType === 'images') {
            return $service->images();
        }

        throw new Exception('Unsupported file type.');
    }
}

