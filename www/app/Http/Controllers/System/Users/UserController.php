<?php

namespace App\Http\Controllers\System\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Users\UserBulkActionRequest;
use App\Http\Requests\System\Users\UserStoreRequest;
use App\Http\Requests\System\Users\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role as SpatieRole;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Users', [
            'users' => $this->getUsers($request),
            'roles' => Role::all(['id', 'name', 'display_name']),
            'filters' => $request->only(['search', 'status', 'role']),
        ]);
    }

    /**
     * Get users data for API
     */
    public function getData(Request $request): JsonResponse
    {
        return response()->json($this->getUsers($request));
    }

    /**
     * Get filtered and paginated users
     */
    private function getUsers(Request $request)
    {
        $query = User::with(['roles'])
            ->select(['id', 'name', 'email', 'phone', 'status', 'last_login_at', 'created_at']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->inactive();
            }
        }

        // Role filter
        if ($request->filled('role')) {
            $query->role($request->get('role'));
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Transform data for frontend
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'status_text' => $user->status_text,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ];
                }),
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
                'last_login_human' => $user->last_login_at?->diffForHumans(),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $user->created_at->diffForHumans(),
            ];
        });

        return $users;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        \Log::info('UserController store method called', [
            'url' => $request->url(),
            'method' => $request->method(),
            'data' => $request->all(),
        ]);

        $validated = $request->validated();

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        // Assign roles if provided
        if (! empty($validated['roles'])) {
            // Get role names from IDs
            $roleNames = SpatieRole::whereIn('id', $validated['roles'])->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        }

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->load('roles'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'status' => $user->status,
                'status_text' => $user->status_text,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ];
                }),
                'role_names' => $user->role_names,
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        \Log::info('UserController update method called', [
            'url' => $request->url(),
            'method' => $request->method(),
            'user_id' => $user->id,
            'data' => $request->all(),
        ]);

        $validated = $request->validated();

        \Log::info('Validated data:', $validated);
        \Log::info('Roles from validated data:', $validated['roles'] ?? 'not present');

        // Hash password if provided
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Update user
        $user->update($validated);

        // Sync roles if provided
        if (array_key_exists('roles', $validated)) {
            if (! empty($validated['roles'])) {
                // Get role names from IDs
                $roleNames = SpatieRole::whereIn('id', $validated['roles'])->pluck('name')->toArray();
                $user->syncRoles($roleNames);
            } else {
                $user->syncRoles([]);
            }
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->fresh()->load('roles'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Bulk actions for users
     */
    public function bulkAction(UserBulkActionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userIds = $validated['user_ids'];
        $action = $validated['action'];

        // Prevent action on self
        if (in_array(auth()->id(), $userIds)) {
            return response()->json([
                'message' => 'You cannot perform this action on your own account',
            ], 422);
        }

        $users = User::whereIn('id', $userIds)->get();
        $affectedCount = 0;

        foreach ($users as $user) {
            switch ($action) {
                case 'activate':
                    $user->update(['status' => true]);
                    $affectedCount++;
                    break;

                case 'deactivate':
                    $user->update(['status' => false]);
                    $affectedCount++;
                    break;

                case 'delete':
                    $user->delete();
                    $affectedCount++;
                    break;

                case 'assign_role':
                    // Get role name from ID
                    $roleName = SpatieRole::find($validated['role_id'])->name ?? null;
                    if ($roleName) {
                        $user->syncRoles([$roleName]);
                        $affectedCount++;
                    }
                    break;
            }
        }

        $actionText = match ($action) {
            'activate' => 'activated',
            'deactivate' => 'deactivated',
            'delete' => 'deleted',
            'assign_role' => 'assigned role to',
            default => 'processed'
        };

        return response()->json([
            'message' => "{$affectedCount} users {$actionText} successfully",
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user): JsonResponse
    {
        // Prevent toggling self
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot change your own status',
            ], 422);
        }

        $user->update(['status' => ! $user->status]);

        return response()->json([
            'message' => 'User status updated successfully',
            'data' => [
                'status' => $user->status,
                'status_text' => $user->status_text,
            ],
        ]);
    }

    /**
     * Get user statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'inactive_users' => User::inactive()->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'users_with_roles' => User::has('roles')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
        ];

        return response()->json($stats);
    }
}
