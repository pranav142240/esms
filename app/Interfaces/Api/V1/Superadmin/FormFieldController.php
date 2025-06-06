<?php

namespace App\Interfaces\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\FormFieldRequest;
use App\Http\Resources\Superadmin\FormFieldResource;
use App\Models\FormField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormFieldController extends Controller
{
    /**
     * Display a listing of form fields.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $search = $request->input('search');
            $type = $request->input('type');
            $isActive = $request->input('is_active');
            $isRequired = $request->input('is_required');
            $isDefault = $request->input('is_default');

            $query = FormField::query();

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('label', 'like', "%{$search}%");
                });
            }

            // Apply type filter
            if ($type) {
                $query->where('type', $type);
            }

            // Apply active filter
            if ($isActive !== null) {
                $query->where('is_active', (bool) $isActive);
            }

            // Apply required filter
            if ($isRequired !== null) {
                $query->where('is_required', (bool) $isRequired);
            }

            // Apply default filter
            if ($isDefault !== null) {
                $query->where('is_default', (bool) $isDefault);
            }

            $fields = $query->orderBy('sort_order')->orderBy('created_at')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Form fields retrieved successfully',
                'data' => FormFieldResource::collection($fields->items()),
                'meta' => [
                    'current_page' => $fields->currentPage(),
                    'last_page' => $fields->lastPage(),
                    'per_page' => $fields->perPage(),
                    'total' => $fields->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created form field.
     */
    public function store(FormFieldRequest $request): JsonResponse
    {
        try {
            $field = FormField::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Form field created successfully',
                'data' => new FormFieldResource($field)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create form field',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified form field.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $field = FormField::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Form field retrieved successfully',
                'data' => new FormFieldResource($field)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form field not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified form field.
     */
    public function update(FormFieldRequest $request, string $id): JsonResponse
    {
        try {
            $field = FormField::findOrFail($id);
            $field->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Form field updated successfully',
                'data' => new FormFieldResource($field->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form field',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified form field (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $field = FormField::findOrFail($id);
            
            // Prevent deletion of default fields
            if ($field->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete default form fields'
                ], 422);
            }

            $field->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form field deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete form field',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update field ordering.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'fields' => 'required|array',
                'fields.*.id' => 'required|exists:form_fields,id',
                'fields.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($request->fields as $fieldData) {
                FormField::where('id', $fieldData['id'])
                    ->update(['sort_order' => $fieldData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Field order updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active form fields for public registration form.
     */
    public function getActiveFields(): JsonResponse
    {
        try {
            $fields = FormField::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Active form fields retrieved successfully',
                'data' => FormFieldResource::collection($fields)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active form fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle field status.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $field = FormField::findOrFail($id);
            $field->update(['is_active' => !$field->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Form field status updated successfully',
                'data' => new FormFieldResource($field->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form field status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
