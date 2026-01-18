<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyAttachmentRequest;
use App\Http\Resources\SurveyAttachmentResource;
use App\Models\SurveyAttachment;
use App\Models\Organization;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SurveyAttachmentController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get paginated list of attachments for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'per_page' => 15,
                    'to' => null,
                    'total' => 0,
                ],
                'message' => 'No organization found. Please create an organization first.',
            ]);
        }

        $organizationId = $organization->id;

        // Get pagination parameters from request body (JSON) or query string
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        // Validate and sanitize per_page (between 1 and 100)
        $perPage = (int) $perPage;
        $perPage = min(max(1, $perPage), 100);

        // Validate and sanitize page (must be at least 1)
        $page = (int) $page;
        $page = max(1, $page);

        // Build query
        $query = SurveyAttachment::where('organization_id', $organizationId)
            ->where('user_id', $user->id);

        // Optional: Search by name
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Order by created_at (newest first)
        $query->orderBy('created_at', 'desc');

        // Get paginated results
        $attachments = $query->paginate($perPage, ['*'], 'page', $page);

        // Ensure requested page doesn't exceed last page
        $lastPage = $attachments->lastPage();
        if ($page > $lastPage && $lastPage > 0) {
            // Re-fetch with the last valid page
            $attachments = $query->paginate($perPage, ['*'], 'page', $lastPage);
        }

        // Format response with only required fields
        $formattedData = $attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'size' => $attachment->size,
                'type' => $attachment->type,
                'url' => $attachment->url,
            ];
        });

        return response()->json([
            'data' => $formattedData,
            'meta' => [
                'current_page' => $attachments->currentPage(),
                'from' => $attachments->firstItem(),
                'last_page' => $attachments->lastPage(),
                'per_page' => $attachments->perPage(),
                'to' => $attachments->lastItem(),
                'total' => $attachments->total(),
            ],
            'message' => 'Attachments retrieved successfully.',
        ]);
    }

    /**
     * Upload or update attachment
     */
    public function save(StoreSurveyAttachmentRequest $request)
    {
        $user = $request->user();
        
        // Get organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $attachmentId = $request->input('id');
            $file = $request->file('file');

            if ($attachmentId) {
                // Update existing attachment
                $attachment = SurveyAttachment::where('id', $attachmentId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$attachment) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Attachment not found or you do not have permission to update it.',
                    ], 404);
                }

                // If new file is provided, update file
                if ($file) {
                    // Delete old file
                    if (Storage::disk('public')->exists($attachment->path)) {
                        Storage::disk('public')->delete($attachment->path);
                    }

                    // Upload new file
                    $path = $file->store('attachments', 'public');
                    $url = Storage::disk('public')->url($path);

                    $attachment->update([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'url' => $url,
                    ]);
                }
            } else {
                // Create new attachment
                if (!$file) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'File is required.',
                    ], 422);
                }

                // Check storage limit before upload
                $fileSizeMB = (int) ($file->getSize() / (1024 * 1024));
                $currentStorageMB = $this->subscriptionService->calculateStorageUsage($organization);
                $subscription = $this->subscriptionService->getCurrentSubscription($organization);
                $storageLimitMB = $subscription->plan->limits['storage_mb'] ?? 0;

                if ($storageLimitMB !== -1 && ($currentStorageMB + $fileSizeMB) > $storageLimitMB) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "You've used your {$storageLimitMB}MB storage limit. Upgrade to Pro for 10GB storage.",
                        'data' => ['upgrade_required' => true]
                    ], 403);
                }

                $path = $file->store('attachments', 'public');
                $url = Storage::disk('public')->url($path);

                $attachment = SurveyAttachment::create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'url' => $url,
                ]);
            }

            DB::commit();

            return response()->json([
                'id' => $attachment->id,
                'name' => $attachment->name,
                'size' => $attachment->size,
                'type' => $attachment->type,
                'url' => $attachment->url,
            ], $attachmentId ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error saving attachment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show attachment
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $attachmentId = $request->input('id');

        if (!$attachmentId) {
            return response()->json([
                'message' => 'Attachment ID is required.',
            ], 422);
        }

        $attachment = SurveyAttachment::where('id', $attachmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$attachment) {
            return response()->json([
                'message' => 'Attachment not found or you do not have permission to access it.',
            ], 404);
        }

        return response()->json([
            'id' => $attachment->id,
            'name' => $attachment->name,
            'size' => $attachment->size,
            'type' => $attachment->type,
            'url' => $attachment->url,
        ]);
    }

    /**
     * Delete attachment
     */
    public function delete(Request $request)
    {
        $user = $request->user();
        
        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:survey_attachments,id',
        ]);

        $attachmentId = $request->input('id');

        // Get organization for permission check
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'No organization found.',
                'errors' => null,
            ], 404);
        }

        // Find attachment - check both user_id and organization_id for security
        $attachment = SurveyAttachment::where('id', $attachmentId)
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        if (!$attachment) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found or you do not have permission to delete it.',
                'errors' => null,
            ], 404);
        }

        DB::beginTransaction();
        try {
            $filePath = $attachment->path;
            $fileName = $attachment->name;
            
            // Delete file from storage
            $fileDeleted = false;
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                $fileDeleted = Storage::disk('public')->delete($filePath);
                \Log::info('Survey attachment file deleted', [
                    'attachment_id' => $attachmentId,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'deleted' => $fileDeleted,
                ]);
            } else {
                \Log::warning('Survey attachment file not found in storage', [
                    'attachment_id' => $attachmentId,
                    'file_path' => $filePath,
                ]);
            }

            // Delete database record
            $attachment->delete();

            DB::commit();

            \Log::info('Survey attachment deleted successfully', [
                'attachment_id' => $attachmentId,
                'file_name' => $fileName,
                'file_deleted' => $fileDeleted,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
                'data' => [
                    'id' => $attachmentId,
                    'name' => $fileName,
                    'file_deleted' => $fileDeleted,
                ],
                'errors' => null,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error deleting survey attachment', [
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting attachment: ' . $e->getMessage(),
                'errors' => null,
            ], 500);
        }
    }
}
