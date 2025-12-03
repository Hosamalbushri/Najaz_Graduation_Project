<?php

namespace Najaz\Service\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Najaz\Service\Contracts\ServiceCategory;
use Najaz\Service\Models\ServiceCategoryTranslationProxy;
use Webkul\Core\Eloquent\Repository;

class ServiceCategoryRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ServiceCategory::class;
    }

    /**
     * Get categories.
     *
     * @return void
     */
    public function getAll(array $params = [])
    {
        $queryBuilder = $this->query()
            ->select('service_categories.*')
            ->leftJoin('service_category_translations', 'service_category_translations.service_category_id', '=', 'service_categories.id');

        foreach ($params as $key => $value) {
            switch ($key) {
                case 'name':
                    $queryBuilder->where('service_category_translations.name', 'like', '%'.urldecode($value).'%');

                    break;
                case 'description':
                    $queryBuilder->where('service_category_translations.description', 'like', '%'.urldecode($value).'%');

                    break;
                case 'status':
                    $queryBuilder->where('service_categories.status', $value);

                    break;
                case 'only_children':
                    $queryBuilder->whereNotNull('service_categories.parent_id');

                    break;
                case 'parent_id':
                    $parentIds = array_filter(array_map('trim', explode(',', $value)));
                    $queryBuilder->whereIn('service_categories.parent_id', $parentIds);

                    break;
                case 'locale':
                    $queryBuilder->where('service_category_translations.locale', $value);

                    break;
            }
        }

        return $queryBuilder->paginate($params['limit'] ?? 10);
    }

    /**
     * Create category.
     *
     * @return \Najaz\Service\Contracts\ServiceCategory
     */
    public function create(array $data)
    {
        if (
            isset($data['locale'])
            && $data['locale'] == 'all'
        ) {
            $model = app()->make($this->model());

            foreach (core()->getAllLocales() as $locale) {
                foreach ($model->translatedAttributes as $attribute) {
                    if (isset($data[$attribute])) {
                        $data[$locale->code][$attribute] = $data[$attribute];

                        $data[$locale->code]['locale_id'] = $locale->id;
                    }
                }
            }
        } else {
            // For single locale, ensure locale_id is set
            $localeCode = $data['locale'] ?? core()->getRequestedLocaleCode();
            
            if (isset($data[$localeCode]) && is_array($data[$localeCode])) {
                $locale = core()->getAllLocales()->where('code', $localeCode)->first();
                
                if ($locale) {
                    $data[$localeCode]['locale_id'] = $locale->id;
                }
            }
        }

        $category = $this->model->create($data);

        $this->uploadImages($data, $category);

        $this->uploadImages($data, $category, 'banner_path');

        return $category;
    }

    /**
     * Update category.
     *
     * @param  int  $id
     * @param  string  $attribute
     * @return \Najaz\Service\Contracts\ServiceCategory
     */
    public function update(array $data, $id)
    {
        $category = $this->find($id);

        // Ensure locale_id is set for the current locale
        $localeCode = $data['locale'] ?? core()->getRequestedLocaleCode();
        
        if (isset($data[$localeCode]) && is_array($data[$localeCode])) {
            $locale = core()->getAllLocales()->where('code', $localeCode)->first();
            
            if ($locale) {
                $data[$localeCode]['locale_id'] = $locale->id;
            }
        }

        $data = $this->setSameAttributeValueToAllLocale($data, 'slug');

        $category->update($data);

        $this->uploadImages($data, $category);

        $this->uploadImages($data, $category, 'banner_path');

        return $category;
    }

    /**
     * Specify category tree.
     *
     * @return \Najaz\Service\Contracts\ServiceCategory
     */
    public function getCategoryTree(?int $id = null)
    {
        return $id
            ? $this->model::orderBy('position', 'ASC')->where('id', '!=', $id)->get()->toTree()
            : $this->model::orderBy('position', 'ASC')->get()->toTree();
    }

    /**
     * Specify category tree.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryTreeWithoutDescendant(?int $id = null)
    {
        return $id
            ? $this->model::orderBy('position', 'ASC')->where('id', '!=', $id)->whereNotDescendantOf($id)->get()->toTree()
            : $this->model::orderBy('position', 'ASC')->get()->toTree();
    }

    /**
     * Get root categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRootCategories()
    {
        return $this->getModel()->where('parent_id', null)->get();
    }

    /**
     * Get child categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getChildCategories($parentId)
    {
        return $this->getModel()->where('parent_id', $parentId)->get();
    }

    /**
     * get visible category tree.
     *
     * @param  int  $id
     * @return \Illuminate\Support\Collection
     */
    public function getVisibleCategoryTree($id = null)
    {
        return $id
            ? $this->model::orderBy('position', 'ASC')->where('status', 1)->descendantsAndSelf($id)->toTree($id)
            : $this->model::orderBy('position', 'ASC')->where('status', 1)->get()->toTree();
    }

    /**
     * Checks slug is unique or not based on locale.
     *
     * @param  int  $id
     * @param  string  $slug
     * @return bool
     */
    public function isSlugUnique($id, $slug)
    {
        $exists = ServiceCategoryTranslationProxy::modelClass()::where('service_category_id', '<>', $id)
            ->where('slug', $slug)
            ->limit(1)
            ->select(DB::raw(1))
            ->exists();

        return ! $exists;
    }

    /**
     * Retrieve category from slug.
     *
     * @param  string  $slug
     * @return \Najaz\Service\Contracts\ServiceCategory
     */
    public function findBySlug($slug)
    {
        if ($category = $this->model->whereTranslation('slug', $slug)->first()) {
            return $category;
        }
    }

    /**
     * Retrieve category from slug.
     *
     * @param  string  $slug
     * @return \Najaz\Service\Contracts\ServiceCategory
     */
    public function findBySlugOrFail($slug)
    {
        return $this->model->whereTranslation('slug', $slug)->firstOrFail();
    }

    /**
     * Upload category's images.
     *
     * @param  array  $data
     * @param  \Najaz\Service\Contracts\ServiceCategory  $category
     * @param  string  $type
     * @return void
     */
    public function uploadImages($data, $category, $type = 'logo_path')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($category->{$type}) {
                        Storage::delete($category->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $category->{$type} = 'service_category/'.$category->id.'/'.Str::random(40).'.webp';

                    Storage::put($category->{$type}, $image);

                    $category->save();
                }
            }
        } else {
            if ($category->{$type}) {
                Storage::delete($category->{$type});
            }

            $category->{$type} = null;

            $category->save();
        }
    }

    /**
     * Get partials.
     *
     * @param  array|null  $columns
     * @return array
     */
    public function getPartial($columns = null)
    {
        $categories = $this->model->all();

        $trimmed = [];

        foreach ($categories as $key => $category) {
            if (! empty($category->name)) {
                $trimmed[$key] = [
                    'id'   => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            }
        }

        return $trimmed;
    }

    /**
     * Set same value to all locales in category.
     *
     * To Do: Move column from the `service_category_translations` to `service_categories` table. And remove
     * this created method.
     *
     * @param  string  $attributeNames
     * @return array
     */
    private function setSameAttributeValueToAllLocale(array $data, ...$attributeNames)
    {
        $requestedLocale = core()->getRequestedLocaleCode();

        $model = app()->make($this->model());

        foreach ($attributeNames as $attributeName) {
            foreach (core()->getAllLocales() as $locale) {
                if ($requestedLocale == $locale->code) {
                    foreach ($model->translatedAttributes as $attribute) {
                        if ($attribute === $attributeName) {
                            $data[$locale->code][$attribute] = $data[$requestedLocale][$attribute] ?? $data[$data['locale']][$attribute];
                        }
                    }
                }
            }
        }

        return $data;
    }
}

