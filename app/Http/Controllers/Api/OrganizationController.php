<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\OrganizationRepository;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    /**
     * Get organization by name
     */
    public function getByName(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $organization = Organization::where('name', $request->name)
            ->orWhere('slug', $request->name)
            ->first();

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'data' => $organization,
            'message' => 'Organization found'
        ]);
    }

    /**
     * Get all organizations (legacy method - kept for backward compatibility)
     */
    public function index()
    {
        $organizations = Organization::where('status', 'active')->get();

        return response()->json([
            'data' => $organizations,
            'message' => 'Organizations retrieved successfully'
        ]);
    }

    /**
     * Get paginated list of organizations for the authenticated user
     */
    public function indexPost(Request $request)
    {
        $user = $request->user();

        // Get pagination parameters from request body (JSON) or query string
        $perPage = $request->input('per_page', 15); // Default 15 per page
        $page = $request->input('page', 1); // Default page 1

        // Validate and sanitize per_page (between 1 and 100)
        $perPage = (int) $perPage;
        $perPage = min(max(1, $perPage), 100);

        // Validate and sanitize page (must be at least 1)
        $page = (int) $page;
        $page = max(1, $page);

        // Get organizations that belong to the authenticated user
        $query = Organization::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        });

        // Optional: Search by name, email, or slug
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        // Optional: Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Optional: Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Order by created_at (newest first)
        $query->orderBy('created_at', 'desc');

        // Get paginated results
        $organizations = $query->paginate($perPage, ['*'], 'page', $page);

        // Ensure requested page doesn't exceed last page
        $lastPage = $organizations->lastPage();
        if ($page > $lastPage && $lastPage > 0) {
            // Re-fetch with the last valid page
            $organizations = $query->paginate($perPage, ['*'], 'page', $lastPage);
        }

        return response()->json([
            'data' => OrganizationResource::collection($organizations->items()),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'from' => $organizations->firstItem(),
                'last_page' => $organizations->lastPage(),
                'per_page' => $organizations->perPage(),
                'to' => $organizations->lastItem(),
                'total' => $organizations->total(),
            ],
            'message' => 'Organizations retrieved successfully.',
        ]);
    }

    /**
     * Create or update an organization
     */
    public function save(StoreOrganizationRequest $request, OrganizationRepository $repository)
    {
        $user = $request->user();

        DB::beginTransaction();
        try {
            $data = $request->validated();

            // If id is present: update existing organization; else create
            if ($request->id) {
                // Verify organization belongs to user
                $organization = $user->organizations()->where('organizations.id', $request->id)->first();
                
                if (!$organization) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Organization not found or you do not have permission to update it.',
                    ], 404);
                }

                $organization = $repository->update($organization, $data);

                DB::commit();

                return response()->json([
                    'data' => new OrganizationResource($organization),
                    'message' => 'Organization updated successfully.',
                ], 200);
            } else {
                // Create new organization
                $data['status'] = 'active';
                $organization = $repository->create($data);
                
                // Attach user to organization with admin role
                $user->organizations()->attach($organization->id, ['role' => 'admin']);

                DB::commit();

                return response()->json([
                    'data' => new OrganizationResource($organization),
                    'message' => 'Organization created successfully.',
                ], 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error saving organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single organization by ID
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:organizations,id',
        ]);

        $organizationId = $request->id;

        // Get organization that belongs to the authenticated user
        $organization = $user->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found or you do not have permission to view it.',
            ], 404);
        }

        return response()->json([
            'data' => new OrganizationResource($organization),
            'message' => 'Organization retrieved successfully.',
        ]);
    }

    /**
     * Delete an organization by ID
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:organizations,id',
        ]);

        $organizationId = $request->id;

        // Verify organization belongs to user and get pivot information
        $organization = $user->organizations()->where('organizations.id', $organizationId)->first();

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found or you do not have permission to delete it.',
            ], 404);
        }

        // Check if user is admin of the organization
        if ($organization->pivot->role !== 'admin') {
            return response()->json([
                'message' => 'You do not have permission to delete this organization. Only admins can delete organizations.',
            ], 403);
        }

        // Delete the organization (soft delete)
        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully.',
        ], 200);
    }

    /**
     * Create a new organization (legacy method - kept for backward compatibility)
     */
    public function store(StoreOrganizationRequest $request, OrganizationRepository $repository)
    {
        return $this->save($request, $repository);
    }
}

