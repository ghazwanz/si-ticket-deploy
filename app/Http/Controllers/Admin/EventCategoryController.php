<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventCategoryRequest;
use App\Http\Requests\Admin\UpdateEventCategoryRequest;
use App\Models\EventCategory;
use App\Services\Admin\EventCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class EventCategoryController extends Controller
{
    public function __construct(
        private readonly EventCategoryService $categoryService
    ) {}

    /**
     * Display a listing of the event categories.
     */
    public function index(Request $request): View
    {
        $categories = $this->categoryService->getPaginatedCategories($request->all());

        return view('admin.event-categories.index', compact('categories'));
    }

    /**
     * Store a newly created event category in storage.
     */
    public function store(StoreEventCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()->route('admin.event-categories.index')
            ->with('status', 'Kategori event berhasil dibuat.');
    }

    /**
     * Update the specified event category in storage.
     */
    public function update(UpdateEventCategoryRequest $request, EventCategory $eventCategory): RedirectResponse
    {
        $this->categoryService->updateCategory($eventCategory, $request->validated());

        return redirect()->route('admin.event-categories.index')
            ->with('status', 'Kategori event berhasil diperbarui.');
    }

    /**
     * Remove the specified event category from storage.
     */
    public function destroy(EventCategory $eventCategory): RedirectResponse
    {
        try {
            $this->categoryService->deleteCategory($eventCategory);

            return redirect()->route('admin.event-categories.index')
                ->with('status', 'Kategori event berhasil diarsipkan.');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.event-categories.index')
                ->with('error', $e->getMessage());
        }
    }
}
