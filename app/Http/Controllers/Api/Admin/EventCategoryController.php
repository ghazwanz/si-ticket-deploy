<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventCategoryRequest;
use App\Http\Requests\Admin\UpdateEventCategoryRequest;
use App\Models\EventCategory;
use App\Services\Admin\EventCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class EventCategoryController extends Controller
{
    public function __construct(
        private readonly EventCategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getPaginatedCategories($request->all());

        return response()->json([
            'message' => 'Daftar kategori event berhasil diambil.',
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    public function store(StoreEventCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'message' => 'Kategori event berhasil dibuat.',
            'data' => $category,
        ], 201);
    }

    public function update(UpdateEventCategoryRequest $request, EventCategory $eventCategory): JsonResponse
    {
        $category = $this->categoryService->updateCategory($eventCategory, $request->validated());

        return response()->json([
            'message' => 'Kategori event berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    public function destroy(EventCategory $eventCategory): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($eventCategory);

            return response()->json([
                'message' => 'Kategori event berhasil diarsipkan.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}