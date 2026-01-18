<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\ImportContactsRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\ContactFavourite;
use App\Models\Organization;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function save(StoreContactRequest $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations (from organization_users pivot table)
        $organization = $user->organizations()->first();
        
        // If user doesn't have an organization, create a personal one
        if (!$organization) {
            $organizationName = $user->name . "'s Organization";
            $slug = Str::slug($organizationName . '-' . $user->id);
            
            // Create personal organization for the user
            $organization = Organization::create([
                'name' => $organizationName,
                'slug' => $slug,
                'description' => 'Personal organization created automatically',
                'status' => 'active',
            ]);
            
            // Attach user to organization with admin role
            $user->organizations()->attach($organization->id, ['role' => 'admin']);
        }

        // Check contact limit for new contacts
        if (!$request->id) {
            $result = $this->subscriptionService->checkLimit($organization, 'create_contact');

            if (!$result['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => ['upgrade_required' => true]
                ], 403);
            }
        }

        $organizationId = $organization->id;

        // If id is present: update within user's organization; else create
        if ($request->id) {
            $contact = Contact::where('id', $request->id)
                ->where('organization_id', $organizationId)
                ->firstOrFail();

            $contact->update([
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'company'     => $request->company,
                'job_title'   => $request->job_title,
                'referrer_id' => $request->referrer_id,
                'groups'      => $request->groups ?? [],
                'address'     => $request->address,
                'notes'       => $request->notes,
                'avatar_color' => $request->avatar_color ?? 'bg-teal',
            ]);

            return response()->json([
                'data'    => new ContactResource($contact->fresh()),
                'message' => 'Contact updated successfully.',
            ]);
        }

        $contact = Contact::create([
            'organization_id' => $organizationId,
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'company'         => $request->company,
            'job_title'       => $request->job_title,
            'referrer_id'     => $request->referrer_id,
            'groups'          => $request->groups ?? [],
            'address'         => $request->address,
            'notes'           => $request->notes,
            'avatar_color'    => $request->avatar_color ?? 'bg-teal',
            'created_by'      => $user->id,
        ]);

        return response()->json([
            'data'    => new ContactResource($contact),
            'message' => 'Contact created successfully.',
        ], 201);
    }

    /**
     * Get contacts list for dropdown (returns only id and name)
     */
    public function dropdown(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'message' => 'No organization found. Please create a contact first.',
            ]);
        }

        $organizationId = $organization->id;

        // Get contacts for this organization, return only id and name
        $contacts = Contact::where('organization_id', $organizationId)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => trim($contact->first_name . ' ' . $contact->last_name),
                ];
            });

        return response()->json([
            'data' => $contacts,
            'message' => 'Contacts retrieved successfully.',
        ]);
    }

    /**
     * Get paginated list of contacts with search and filter
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
                'message' => 'No organization found. Please create a contact first.',
            ]);
        }

        $organizationId = $organization->id;

        // Get pagination parameters from request body (JSON) or query string
        $perPage = $request->input('per_page', 15); // Default 15 per page
        $page = $request->input('page', 1); // Default page 1

        // Validate and sanitize per_page (between 1 and 100)
        $perPage = (int) $perPage;
        $perPage = min(max(1, $perPage), 100);

        // Validate and sanitize page (must be at least 1)
        $page = (int) $page;
        $page = max(1, $page); // Ensure page is at least 1

        // Build query
        $query = Contact::where('organization_id', $organizationId);

        // Optional: Search by name, email, phone, or company
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        // Optional: Filter by group
        if ($request->has('group') && !empty($request->group)) {
            $query->whereJsonContains('groups', $request->group);
        }

        // Order by created_at (newest first)
        $query->orderBy('created_at', 'desc');

        // Get paginated results
        $contacts = $query->paginate($perPage, ['*'], 'page', $page);

        // Ensure requested page doesn't exceed last page
        $lastPage = $contacts->lastPage();
        if ($page > $lastPage && $lastPage > 0) {
            // Re-fetch with the last valid page
            $contacts = $query->paginate($perPage, ['*'], 'page', $lastPage);
        }

        return response()->json([
            'data' => ContactResource::collection($contacts->items()),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'from' => $contacts->firstItem(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'to' => $contacts->lastItem(),
                'total' => $contacts->total(),
            ],
            'message' => 'Contacts retrieved successfully.',
        ]);
    }

    /**
     * Get contact statistics (Total Contacts, Recently Added, This Month, Favorites)
     */
    public function state(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();

        if (!$organization) {
            return response()->json([
                'data' => [
                    'total_contacts' => 0,
                    'this_month' => 0,
                    'recently_added' => 0,
                    'favorites' => 0,
                ],
                'message' => 'Statistics retrieved successfully.',
            ]);
        }

        $organizationId = $organization->id;

        // Calculate statistics for dashboard
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfToday = $now->copy()->startOfDay();
        $endOfToday = $now->copy()->endOfDay();

        // Total contacts count
        $totalContacts = Contact::where('organization_id', $organizationId)->count();

        // Contacts created this month
        $thisMonth = Contact::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Contacts created today (recently added)
        $recentlyAdded = Contact::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startOfToday, $endOfToday])
            ->count();

        // Favorites count (contacts marked as favorite by current user)
        $favorites = ContactFavourite::where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->where('is_favourite', true)
            ->count();

        return response()->json([
            'data' => [
                'total_contacts' => $totalContacts,
                'this_month' => $thisMonth,
                'recently_added' => $recentlyAdded,
                'favorites' => $favorites,
            ],
            'message' => 'Statistics retrieved successfully.',
        ]);
    }

    /**
     * Get single contact by ID
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:contacts,id',
        ]);

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create a contact first.',
            ], 404);
        }

        $organizationId = $organization->id;
        $contactId = $request->id;

        // Get contact from user's organization
        $contact = Contact::where('id', $contactId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        return response()->json([
            'data' => new ContactResource($contact),
            'message' => 'Contact retrieved successfully.',
        ]);
    }

    /**
     * Delete a contact by ID
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:contacts,id',
        ]);

        $contactId = $request->id;

        // First, check if contact exists at all
        $contact = Contact::find($contactId);
        
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found.',
            ], 404);
        }

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        // Check permission: contact must belong to user's organization OR user must be the creator
        $hasPermission = false;
        
        if ($organization) {
            // Check if contact belongs to user's organization
            if ($contact->organization_id == $organization->id) {
                $hasPermission = true;
            }
        }
        
        // Also allow if user created the contact (created_by matches)
        if ($contact->created_by == $user->id) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'You do not have permission to delete this contact.',
            ], 403);
        }

        // Delete the contact
        $contact->delete();

        return response()->json([
            'message' => 'Contact deleted successfully.',
        ], 200);
    }

    /**
     * Toggle favourite status for a contact
     */
    public function toggleFavourite(Request $request)
    {
        $user = $request->user();

        // Validate request
        $request->validate([
            'contact_id' => 'required|integer|exists:contacts,id',
            'is_favourite' => 'required|boolean',
        ]);

        $contactId = $request->contact_id;
        // Accept 1/0 or true/false, convert to boolean
        $isFavourite = filter_var($request->is_favourite, FILTER_VALIDATE_BOOLEAN);

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create a contact first.',
            ], 404);
        }

        $organizationId = $organization->id;

        // Verify contact belongs to user's organization
        $contact = Contact::where('id', $contactId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        if ($isFavourite) {
            // Create or update favourite record when marking as favourite
            $favourite = ContactFavourite::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
                ],
                [
                    'is_favourite' => true,
                ]
            );

            return response()->json([
                'data' => [
                    'contact_id' => $contactId,
                    'is_favourite' => 1,
                ],
                'message' => 'Contact marked as favourite.',
            ], 200);
        } else {
            // Delete favourite record when removing from favourites
            $deleted = ContactFavourite::where('organization_id', $organizationId)
                ->where('user_id', $user->id)
                ->where('contact_id', $contactId)
                ->delete();

            return response()->json([
                'data' => [
                    'contact_id' => $contactId,
                    'is_favourite' => 0,
                ],
                'message' => 'Contact removed from favourites.',
            ], 200);
        }
    }

    /**
     * Normalize phone number for comparison
     * Removes all non-digit characters except leading +, then removes leading +
     * and leading zeros
     */
    private function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters except leading +
        $normalized = preg_replace('/[^\d+]/', '', $phone);
        
        // Remove leading + if present
        if (str_starts_with($normalized, '+')) {
            $normalized = substr($normalized, 1);
        }
        
        // Remove leading zeros
        $normalized = ltrim($normalized, '0');
        
        return $normalized;
    }

    /**
     * Check if two phone numbers match (with normalization)
     */
    private function phonesMatch(string $phone1, string $phone2): bool
    {
        $norm1 = $this->normalizePhone($phone1);
        $norm2 = $this->normalizePhone($phone2);
        
        if (empty($norm1) || empty($norm2)) {
            return false;
        }
        
        // Exact match
        if ($norm1 === $norm2) {
            return true;
        }
        
        // Match last 10 digits (for local numbers)
        if (strlen($norm1) >= 10 && strlen($norm2) >= 10) {
            if (substr($norm1, -10) === substr($norm2, -10)) {
                return true;
            }
        }
        
        // Match last 12 digits (for numbers with country code)
        if (strlen($norm1) >= 12 && strlen($norm2) >= 12) {
            if (substr($norm1, -12) === substr($norm2, -12)) {
                return true;
            }
        }
        
        // Subset matching (country code difference)
        if (str_ends_with($norm1, $norm2) || str_ends_with($norm2, $norm1)) {
            $diff = abs(strlen($norm1) - strlen($norm2));
            if ($diff <= 3) { // Country codes are typically 1-3 digits
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if a contact is a duplicate in the database
     * Returns array with [is_duplicate: bool, reason: string|null, existing_contact: Contact|null]
     */
    private function checkDuplicate(array $contactData, $existingContacts, bool $allowDuplicates): array
    {
        if ($allowDuplicates) {
            return [false, null, null];
        }

        // Normalize email for comparison
        $email = !empty($contactData['email']) ? strtolower(trim($contactData['email'])) : null;
        $phone = !empty($contactData['phone']) ? trim($contactData['phone']) : null;
        $firstName = !empty($contactData['first_name']) ? trim($contactData['first_name']) : null;
        $lastName = !empty($contactData['last_name']) ? trim($contactData['last_name']) : null;

        foreach ($existingContacts as $existing) {
            // Check email match (case-insensitive)
            if ($email && $existing->email) {
                if (strtolower(trim($existing->email)) === $email) {
                    return [true, "duplicate email: {$existing->email}", $existing];
                }
            }

            // Check phone match (normalized)
            if ($phone && $existing->phone) {
                if ($this->phonesMatch($phone, $existing->phone)) {
                    return [true, "duplicate phone: {$existing->phone}", $existing];
                }
            }

            // Check name match (case-insensitive)
            if ($firstName && $lastName && $existing->first_name && $existing->last_name) {
                if (strtolower(trim($existing->first_name)) === strtolower($firstName) &&
                    strtolower(trim($existing->last_name)) === strtolower($lastName)) {
                    return [true, "duplicate name: {$existing->first_name} {$existing->last_name}", $existing];
                }
            }
        }

        return [false, null, null];
    }

    /**
     * Bulk import contacts with duplicate detection
     */
    public function import(ImportContactsRequest $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'No organization found. Please create an organization first.',
                'errors' => [
                    'organization' => ['No organization found']
                ],
            ], 404);
        }

        // Check subscription limits before import
        $contacts = $request->input('contacts', []);
        $newContactsCount = count($contacts); // Simplified - in real scenario, count only non-duplicates
        
        if ($newContactsCount > 0) {
            $result = $this->subscriptionService->checkLimit($organization, 'create_contact');
            if (!$result['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => ['upgrade_required' => true]
                ], 403);
            }
        }

        $organizationId = $organization->id;
        $allowDuplicates = $request->input('allow_duplicates', false);
        $updateExisting = $request->input('update_existing', false);
        
        // Fetch all existing contacts once for efficient duplicate checking
        $existingContacts = !$allowDuplicates 
            ? Contact::where('organization_id', $organizationId)->get()
            : collect();
        
        $total = count($contacts);
        $successful = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];
        $processedEmails = []; // Track emails in current batch
        $processedPhones = []; // Track phones in current batch
        $processedNames = []; // Track names in current batch

        foreach ($contacts as $index => $contactData) {
            $row = $index + 1; // 1-indexed row number
            
            try {
                // Validate individual contact fields
                $validator = Validator::make($contactData, [
                    'first_name' => ['required', 'string', 'max:255'],
                    'last_name' => ['required', 'string', 'max:255'],
                    'phone' => ['required', 'string', 'max:50'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'company' => ['nullable', 'string', 'max:255'],
                    'job_title' => ['nullable', 'string', 'max:255'],
                    'groups' => ['nullable', 'array'],
                    'groups.*' => ['string', 'max:50'],
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $firstError = $validator->errors()->first();
                    $fieldName = array_key_first($validator->errors()->toArray());
                    
                    // Format error message with row number
                    $errorMessage = "Row {$row}: ";
                    if ($fieldName === 'first_name') {
                        $errorMessage .= "First Name is required but is empty";
                    } elseif ($fieldName === 'last_name') {
                        $errorMessage .= "Last Name is required but is empty";
                    } elseif ($fieldName === 'phone') {
                        $errorMessage .= "Phone is required but is empty";
                    } elseif ($fieldName === 'email') {
                        $errorMessage .= "Invalid email format \"{$contactData['email']}\"";
                    } else {
                        $errorMessage .= $firstError;
                    }
                    
                    $errors[] = [
                        'row' => $row,
                        'message' => $errorMessage,
                        'contact' => $contactData,
                        'type' => 'validation',
                    ];
                    continue;
                }

                // Normalize email (lowercase, trim)
                $email = !empty($contactData['email']) ? strtolower(trim($contactData['email'])) : null;
                $phone = trim($contactData['phone']);
                $firstName = trim($contactData['first_name']);
                $lastName = trim($contactData['last_name']);

                // Check for duplicates within current batch
                $isDuplicateInBatch = false;
                $duplicateReason = null;

                if (!$allowDuplicates) {
                    // Check email duplicate in batch
                    if ($email && isset($processedEmails[$email])) {
                        $isDuplicateInBatch = true;
                        $duplicateReason = "duplicate email: {$email}";
                    }

                    // Check phone duplicate in batch
                    if (!$isDuplicateInBatch && $phone) {
                        foreach ($processedPhones as $processedPhone) {
                            if ($this->phonesMatch($phone, $processedPhone)) {
                                $isDuplicateInBatch = true;
                                $duplicateReason = "duplicate phone: {$phone}";
                                break;
                            }
                        }
                    }

                    // Check name duplicate in batch
                    if (!$isDuplicateInBatch) {
                        $nameKey = strtolower("{$firstName}|{$lastName}");
                        if (isset($processedNames[$nameKey])) {
                            $isDuplicateInBatch = true;
                            $duplicateReason = "duplicate name: {$firstName} {$lastName}";
                        }
                    }

                    if ($isDuplicateInBatch) {
                        $failed++;
                        $errors[] = [
                            'row' => $row,
                            'message' => "Row {$row}: Contact already exists in import batch ({$duplicateReason})",
                            'contact' => $contactData,
                            'type' => 'duplicate',
                        ];
                        continue;
                    }
                }

                // Check for duplicates in database
                [$isDuplicate, $duplicateReason, $existingContact] = $this->checkDuplicate(
                    $contactData,
                    $existingContacts,
                    $allowDuplicates
                );

                if ($isDuplicate) {
                    if ($updateExisting && $existingContact) {
                        // Update existing contact
                        $existingContact->update([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $email,
                            'phone' => substr($phone, 0, 50),
                            'company' => !empty($contactData['company']) ? trim($contactData['company']) : null,
                            'job_title' => !empty($contactData['job_title']) ? trim($contactData['job_title']) : null,
                            'groups' => !empty($contactData['groups']) && is_array($contactData['groups'])
                                ? array_values(array_filter(array_map('trim', $contactData['groups']), fn($g) => !empty($g) && strlen($g) <= 50))
                                : [],
                        ]);
                        
                        $successful++;
                        
                        // Track updated contact to prevent duplicates in batch
                        if ($email) {
                            $processedEmails[$email] = true;
                        }
                        if ($phone) {
                            $processedPhones[] = $phone;
                        }
                        $nameKey = strtolower("{$firstName}|{$lastName}");
                        $processedNames[$nameKey] = true;
                    } else {
                        // Skip duplicate
                        $skipped++;
                        $failed++;
                        $errors[] = [
                            'row' => $row,
                            'message' => "Row {$row}: Contact already exists ({$duplicateReason})",
                            'contact' => $contactData,
                            'type' => 'duplicate',
                        ];
                    }
                    continue;
                }

                // Normalize groups
                $groups = [];
                if (!empty($contactData['groups']) && is_array($contactData['groups'])) {
                    $groups = array_filter(
                        array_map(function($group) {
                            $group = trim($group);
                            return !empty($group) && strlen($group) <= 50 ? $group : null;
                        }, $contactData['groups']),
                        fn($group) => $group !== null
                    );
                    $groups = array_values($groups); // Re-index array
                }

                // Create contact
                $contact = Contact::create([
                    'organization_id' => $organizationId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => substr($phone, 0, 50),
                    'company' => !empty($contactData['company']) ? trim($contactData['company']) : null,
                    'job_title' => !empty($contactData['job_title']) ? trim($contactData['job_title']) : null,
                    'groups' => !empty($groups) ? $groups : [],
                    'created_by' => $user->id,
                ]);

                $successful++;
                
                // Track processed data
                if ($email) {
                    $processedEmails[$email] = true;
                }
                if ($phone) {
                    $processedPhones[] = $phone;
                }
                $nameKey = strtolower("{$firstName}|{$lastName}");
                $processedNames[$nameKey] = true;

            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $row,
                    'message' => "Row {$row}: An error occurred while saving contact: " . $e->getMessage(),
                    'contact' => $contactData,
                    'type' => 'error',
                ];
                
                \Log::error('Error importing contact', [
                    'row' => $row,
                    'contact' => $contactData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Determine response status code and message
        $statusCode = 200;
        $message = 'All contacts imported successfully';
        
        if ($failed > 0 && $successful > 0) {
            $statusCode = 207; // Multi-Status (partial success)
            $message = 'Some contacts imported successfully';
        } elseif ($failed === $total) {
            $statusCode = 422; // All failed
            if ($skipped === $total) {
                $message = 'All contacts are duplicates';
            } else {
                $message = 'All contacts failed validation or are duplicates';
            }
        }

        return response()->json([
            'success' => $successful > 0,
            'message' => $message,
            'data' => [
                'total' => $total,
                'successful' => $successful,
                'failed' => $failed,
                'skipped' => $skipped,
                'errors' => $errors,
            ],
        ], $statusCode);
    }
}


