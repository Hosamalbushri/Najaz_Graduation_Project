<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Najaz\Admin\DataGrids\Services\ServiceCategoryDataGrid;
use Najaz\Admin\Http\Requests\ServiceCategoryRequest;
use Najaz\Service\Repositories\ServiceCategoryRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Http\Resources\CategoryTreeResource;

class ServiceCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ServiceCategoryRepository $serviceCategoryRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ServiceCategoryDataGrid::class)->process();
        }

        return view('admin::services.categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = $this->serviceCategoryRepository->getCategoryTree();

        return view('admin::services.categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ServiceCategoryRequest $serviceCategoryRequest)
    {
        Event::dispatch('services.category.create.before');

        $locale = core()->getRequestedLocaleCode();

        $data = [
            'locale'       => $locale,
            'parent_id'    => $serviceCategoryRequest->input('parent_id'),
            'status'       => $serviceCategoryRequest->input('status', 0),
            'position'     => $serviceCategoryRequest->input('position', 0),
            'display_mode' => $serviceCategoryRequest->input('display_mode', 'services_and_description'),
            'logo_path'    => $serviceCategoryRequest->input('logo_path'),
            'banner_path'  => $serviceCategoryRequest->input('banner_path'),
        ];

        // Add translated attributes
        $data[$locale] = [
            'name'             => $serviceCategoryRequest->input($locale.'.name'),
            'slug'             => $serviceCategoryRequest->input($locale.'.slug'),
            'description'      => $serviceCategoryRequest->input($locale.'.description'),
            'meta_title'       => $serviceCategoryRequest->input($locale.'.meta_title'),
            'meta_keywords'    => $serviceCategoryRequest->input($locale.'.meta_keywords'),
            'meta_description' => $serviceCategoryRequest->input($locale.'.meta_description'),
        ];

        $category = $this->serviceCategoryRepository->create($data);

        Event::dispatch('services.category.create.after', $category);

        session()->flash('success', trans('Admin::app.services.categories.create-success'));

        return redirect()->route('admin.services.categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $category = $this->serviceCategoryRepository->findOrFail($id);

        $categories = $this->serviceCategoryRepository->getCategoryTreeWithoutDescendant($id);

        return view('admin::services.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(ServiceCategoryRequest $serviceCategoryRequest, int $id)
    {
        Event::dispatch('services.category.update.before', $id);

        $locale = core()->getRequestedLocaleCode();

        $data = [
            'locale'       => $locale,
            'parent_id'    => $serviceCategoryRequest->input('parent_id'),
            'status'       => $serviceCategoryRequest->input('status', 0),
            'position'     => $serviceCategoryRequest->input('position', 0),
            'display_mode' => $serviceCategoryRequest->input('display_mode', 'services_and_description'),
            'logo_path'    => $serviceCategoryRequest->input('logo_path'),
            'banner_path'  => $serviceCategoryRequest->input('banner_path'),
        ];

        // Add translated attributes
        $data[$locale] = [
            'name'             => $serviceCategoryRequest->input($locale.'.name'),
            'slug'             => $serviceCategoryRequest->input($locale.'.slug'),
            'description'      => $serviceCategoryRequest->input($locale.'.description'),
            'meta_title'       => $serviceCategoryRequest->input($locale.'.meta_title'),
            'meta_keywords'    => $serviceCategoryRequest->input($locale.'.meta_keywords'),
            'meta_description' => $serviceCategoryRequest->input($locale.'.meta_description'),
        ];

        $category = $this->serviceCategoryRepository->update($data, $id);

        Event::dispatch('services.category.update.after', $category);

        session()->flash('success', trans('Admin::app.services.categories.update-success'));

        return redirect()->route('admin.services.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = $this->serviceCategoryRepository->findOrFail($id);

        if (! $this->isCategoryDeletable($category)) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.categories.delete-category-root'),
            ], 400);
        }

        try {
            Event::dispatch('services.category.delete.before', $id);

            $category->delete($id);

            Event::dispatch('services.category.delete.after', $id);

            return new JsonResponse([
                'message' => trans('Admin::app.services.categories.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.categories.delete-failed'),
            ], 500);
        }
    }

    /**
     * Remove the specified resources from database.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $suppressFlash = true;

        $categoryIds = $massDestroyRequest->input('indices');

        foreach ($categoryIds as $categoryId) {
            $category = $this->serviceCategoryRepository->find($categoryId);

            if (isset($category)) {
                if (! $this->isCategoryDeletable($category)) {
                    $suppressFlash = false;

                    return new JsonResponse(['message' => trans('Admin::app.services.categories.delete-category-root')], 400);
                } else {
                    try {
                        $suppressFlash = true;

                        Event::dispatch('services.category.delete.before', $categoryId);

                        $this->serviceCategoryRepository->delete($categoryId);

                        Event::dispatch('services.category.delete.after', $categoryId);
                    } catch (\Exception $e) {
                        return new JsonResponse([
                            'message' => trans('Admin::app.services.categories.delete-failed'),
                        ], 500);
                    }
                }
            }
        }

        if (
            count($categoryIds) != 1
            || $suppressFlash == true
        ) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.categories.delete-success'),
            ]);
        }

        return redirect()->route('admin.services.categories.index');
    }

    /**
     * Mass update Category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest)
    {
        try {
            $categoryIds = $massUpdateRequest->input('indices');

            foreach ($categoryIds as $categoryId) {
                Event::dispatch('services.categories.mass-update.before', $categoryId);

                $category = $this->serviceCategoryRepository->find($categoryId);

                $category->status = $massUpdateRequest->input('value');

                $category->save();

                Event::dispatch('services.categories.mass-update.after', $category);
            }

            return new JsonResponse([
                'message' => trans('Admin::app.services.categories.update-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check whether the current category is deletable or not.
     *
     * This method will check if category id is 1 (root category).
     *
     * @param  \Najaz\Service\Contracts\ServiceCategory  $category
     * @return bool
     */
    private function isCategoryDeletable($category)
    {
        return $category->id !== 1;
    }

    /**
     * Get all categories in tree format.
     */
    public function tree(): JsonResource
    {
        $categories = $this->serviceCategoryRepository->getVisibleCategoryTree();

        return CategoryTreeResource::collection($categories);
    }

    /**
     * Get all the searched categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        $categories = $this->serviceCategoryRepository->getAll([
            'name'   => request()->input('query'),
            'locale' => app()->getLocale(),
        ]);

        return response()->json($categories);
    }
}

