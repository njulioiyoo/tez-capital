<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { toast } from '@/components/ui/toast';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { 
    Plus, 
    Edit, 
    Trash2, 
    Users as UsersIcon, 
    Search, 
    Filter,
    RefreshCw,
    Eye,
    MoreHorizontal,
    UserCheck,
    UserX,
    Shield,
    Loader2
} from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
    display_name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    status: boolean;
    status_text: string;
    roles: Role[];
    last_login_at: string;
    last_login_human: string;
    created_at: string;
    created_at_human: string;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        meta: any;
    };
    roles: Role[];
    filters: {
        search?: string;
        status?: string;
        role?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'System', href: '/system' },
    { title: 'Users', href: '/users' },
];

// State
const loading = ref(false);
const isDialogOpen = ref(false);
const isViewDialogOpen = ref(false);
const editingUser = ref<User | null>(null);
const viewingUser = ref<User | null>(null);
const selectedUsers = ref<number[]>([]);
const bulkAction = ref('');
const bulkActionRole = ref('');

// Filters
const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    role: props.filters.role || '',
});

// Form data
const formData = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    status: true,
    roles: [] as number[],
});

const errors = ref<Record<string, string>>({});

// Computed
const allSelected = computed(() => {
    return props.users.data.length > 0 && 
           selectedUsers.value.length === props.users.data.length;
});

const someSelected = computed(() => {
    return selectedUsers.value.length > 0 && 
           selectedUsers.value.length < props.users.data.length;
});

const canPerformBulkAction = computed(() => {
    return selectedUsers.value.length > 0 && bulkAction.value;
});

// Methods
const getCsrfToken = () => {
    const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const tokenFromCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];
    
    return tokenFromMeta || (tokenFromCookie ? decodeURIComponent(tokenFromCookie) : '');
};

const resetForm = () => {
    formData.name = '';
    formData.email = '';
    formData.password = '';
    formData.password_confirmation = '';
    formData.phone = '';
    formData.address = '';
    formData.status = true;
    formData.roles = []; // Create new array
    errors.value = {};
    editingUser.value = null;
};

const openAddDialog = () => {
    resetForm();
    isDialogOpen.value = true;
};

const openEditDialog = (user: User) => {
    editingUser.value = user;
    console.log('Opening edit dialog for user:', user);
    console.log('User roles:', user.roles);
    const mappedRoles = user.roles.map(role => role.id);
    console.log('Mapped role IDs:', mappedRoles);
    
    formData.name = user.name;
    formData.email = user.email;
    formData.password = '';
    formData.password_confirmation = '';
    formData.phone = user.phone || '';
    formData.address = ''; // Will be loaded from API
    formData.status = user.status;
    formData.roles = [...mappedRoles]; // Create new array to ensure reactivity
    
    console.log('FormData after assign:', formData);
    console.log('FormData roles after assign:', formData.roles);
    isDialogOpen.value = true;
    
    // Load full user data
    loadUserDetails(user.id);
};

const openViewDialog = async (user: User) => {
    viewingUser.value = user;
    isViewDialogOpen.value = true;
    
    // Load full user data
    await loadUserDetails(user.id);
};

const loadUserDetails = async (userId: number) => {
    try {
        const response = await fetch(`/api/system/users/${userId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'include',
        });
        
        if (response.ok) {
            const data = await response.json();
            if (editingUser.value) {
                formData.address = data.data.address || '';
            }
            if (viewingUser.value) {
                viewingUser.value = { ...viewingUser.value, ...data.data };
            }
        }
    } catch (error) {
        console.error('Error loading user details:', error);
    }
};

const saveUser = async () => {
    loading.value = true;
    errors.value = {};
    
    try {
        const url = editingUser.value 
            ? `/api/system/users/${editingUser.value.id}`
            : '/api/system/users';
        
        const method = editingUser.value ? 'PUT' : 'POST';
        
        console.log('Sending request to:', url, 'with method:', method);
        
        const csrfToken = getCsrfToken();
        console.log('CSRF Token:', csrfToken);
        
        console.log('FormData being sent:', formData);
        console.log('FormData roles:', formData.roles);
        
        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(formData),
            credentials: 'include',
        });
        
        console.log('Response status:', response.status);
        console.log('Response URL:', response.url);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (response.ok) {
            isDialogOpen.value = false;
            resetForm();
            
            toast({
                title: 'Success',
                description: editingUser.value ? 'User updated successfully' : 'User created successfully',
                variant: 'success'
            });
            
            // Reload page to refresh data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            if (response.status === 419 || (data.message && data.message.includes('CSRF'))) {
                toast({
                    title: 'Session Expired',
                    description: 'Your session has expired. The page will reload automatically.',
                    variant: 'error'
                });
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else if (data.errors) {
                errors.value = data.errors;
            } else {
                console.error('Error saving user:', data.message);
            }
        }
    } catch (error) {
        console.error('Error saving user:', error);
    } finally {
        loading.value = false;
    }
};

const deleteUser = async (user: User) => {
    if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/system/users/${user.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'include',
        });
        
        if (response.ok) {
            toast({
                title: 'Success',
                description: 'User deleted successfully',
                variant: 'success'
            });
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            const data = await response.json();
            toast({
                title: 'Error',
                description: data.message || 'Error deleting user',
                variant: 'error'
            });
        }
    } catch (error) {
        console.error('Error deleting user:', error);
    }
};

const toggleUserStatus = async (user: User) => {
    try {
        const response = await fetch(`/api/system/users/${user.id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'include',
        });
        
        if (response.ok) {
            toast({
                title: 'Success',
                description: `User status updated successfully`,
                variant: 'success'
            });
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            const data = await response.json();
            toast({
                title: 'Error',
                description: data.message || 'Error updating user status',
                variant: 'error'
            });
        }
    } catch (error) {
        console.error('Error updating user status:', error);
    }
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map(user => user.id);
    }
};

const performBulkAction = async () => {
    if (!canPerformBulkAction.value) return;
    
    const actionText = {
        activate: 'activate',
        deactivate: 'deactivate',
        delete: 'delete',
        assign_role: 'assign role to'
    }[bulkAction.value] || 'process';
    
    if (!confirm(`Are you sure you want to ${actionText} ${selectedUsers.value.length} users?`)) {
        return;
    }
    
    try {
        const payload: any = {
            action: bulkAction.value,
            user_ids: selectedUsers.value,
        };
        
        if (bulkAction.value === 'assign_role') {
            payload.role_id = parseInt(bulkActionRole.value);
        }
        
        const csrfToken = getCsrfToken();
        console.log('Bulk action CSRF token:', csrfToken);
        console.log('Bulk action payload:', payload);
        
        const response = await fetch('/api/system/users/bulk-action', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
            credentials: 'include',
        });
        
        if (response.ok) {
            selectedUsers.value = [];
            bulkAction.value = '';
            bulkActionRole.value = '';
            
            toast({
                title: 'Success',
                description: 'Bulk action completed successfully',
                variant: 'success'
            });
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            const data = await response.json();
            console.error('Bulk action failed:', {
                status: response.status,
                statusText: response.statusText,
                data: data
            });
            
            if (response.status === 419 || (data.message && data.message.includes('CSRF'))) {
                toast({
                    title: 'Session Expired',
                    description: 'Your session has expired. The page will reload automatically.',
                    variant: 'error'
                });
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                toast({
                    title: 'Error',
                    description: data.message || 'Error performing bulk action',
                    variant: 'error'
                });
            }
        }
    } catch (error) {
        console.error('Error performing bulk action:', error);
    }
};

const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.search) params.set('search', filters.search);
    if (filters.status) params.set('status', filters.status);
    if (filters.role) params.set('role', filters.role);
    
    const queryString = params.toString();
    const url = queryString ? `/users?${queryString}` : '/users';
    
    window.location.href = url;
};

const clearFilters = () => {
    Object.assign(filters, { search: '', status: '', role: '' });
    window.location.href = '/users';
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Users</h1>
                        <p class="text-muted-foreground">Manage system users and their roles</p>
                    </div>
                    
                    <Dialog v-model:open="isDialogOpen">
                        <DialogTrigger as-child>
                            <Button @click="openAddDialog">
                                <Plus class="w-4 h-4 mr-2" />
                                Add User
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>{{ editingUser ? 'Edit' : 'Add' }} User</DialogTitle>
                                <DialogDescription>
                                    {{ editingUser ? 'Update user information and roles' : 'Create a new user account' }}
                                </DialogDescription>
                            </DialogHeader>
                            
                            <div class="space-y-4 py-4">
                                <div class="space-y-2">
                                    <Label for="name">Name</Label>
                                    <Input 
                                        id="name" 
                                        v-model="formData.name" 
                                        placeholder="Full name" 
                                        required 
                                        :class="errors.name ? 'border-red-500' : ''"
                                    />
                                    <span v-if="errors.name" class="text-sm text-red-500">{{ errors.name[0] }}</span>
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="email">Email</Label>
                                    <Input 
                                        id="email" 
                                        v-model="formData.email" 
                                        type="email"
                                        placeholder="email@example.com" 
                                        required 
                                        :class="errors.email ? 'border-red-500' : ''"
                                    />
                                    <span v-if="errors.email" class="text-sm text-red-500">{{ errors.email[0] }}</span>
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="password">{{ editingUser ? 'New Password (leave blank to keep current)' : 'Password' }}</Label>
                                    <Input 
                                        id="password" 
                                        v-model="formData.password" 
                                        type="password"
                                        :placeholder="editingUser ? 'Leave blank to keep current password' : 'Password'"
                                        :required="!editingUser"
                                        :class="errors.password ? 'border-red-500' : ''"
                                    />
                                    <span v-if="errors.password" class="text-sm text-red-500">{{ errors.password[0] }}</span>
                                </div>
                                
                                <div class="space-y-2" v-if="formData.password">
                                    <Label for="password_confirmation">Confirm Password</Label>
                                    <Input 
                                        id="password_confirmation" 
                                        v-model="formData.password_confirmation" 
                                        type="password"
                                        placeholder="Confirm password"
                                    />
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="phone">Phone</Label>
                                    <Input 
                                        id="phone" 
                                        v-model="formData.phone" 
                                        placeholder="Phone number (optional)"
                                        :class="errors.phone ? 'border-red-500' : ''"
                                    />
                                    <span v-if="errors.phone" class="text-sm text-red-500">{{ errors.phone[0] }}</span>
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="address">Address</Label>
                                    <textarea 
                                        id="address" 
                                        v-model="formData.address" 
                                        class="w-full p-2 border rounded-md"
                                        :rows="3"
                                        placeholder="Address (optional)"
                                        :class="errors.address ? 'border-red-500' : ''"
                                    ></textarea>
                                    <span v-if="errors.address" class="text-sm text-red-500">{{ errors.address[0] }}</span>
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="roles">Roles</Label>
                                    <div class="space-y-2">
                                        <div v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
                                            <input 
                                                type="checkbox" 
                                                :id="`role-${role.id}`"
                                                :checked="formData.roles.includes(role.id)"
                                                @change="(e) => {
                                                    console.log('Checkbox changed:', e.target.checked, 'role ID:', role.id);
                                                    console.log('Current formData.roles before:', formData.roles);
                                                    
                                                    if (e.target.checked) {
                                                        if (!formData.roles.includes(role.id)) {
                                                            formData.roles.push(role.id);
                                                        }
                                                    } else {
                                                        const index = formData.roles.indexOf(role.id);
                                                        if (index > -1) {
                                                            formData.roles.splice(index, 1);
                                                        }
                                                    }
                                                    
                                                    console.log('Current formData.roles after:', formData.roles);
                                                }"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            />
                                            <Label :for="`role-${role.id}`">
                                                {{ role.display_name || role.name }}
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <Checkbox 
                                        id="status"
                                        v-model="formData.status"
                                    />
                                    <Label for="status">Active</Label>
                                </div>
                            </div>
                            
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="isDialogOpen = false">
                                    Cancel
                                </Button>
                                <Button type="button" @click="saveUser" :disabled="loading">
                                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                    {{ editingUser ? 'Update' : 'Create' }}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Filters -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Filter class="w-4 h-4" />
                            Filters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <Label for="search">Search</Label>
                                <Input 
                                    id="search" 
                                    v-model="filters.search" 
                                    placeholder="Search by name, email, or phone..."
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                            
                            <div>
                                <Label for="status">Status</Label>
                                <select id="status" v-model="filters.status" class="w-full p-2 border rounded-md">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            
                            <div>
                                <Label for="role">Role</Label>
                                <select id="role" v-model="filters.role" class="w-full p-2 border rounded-md">
                                    <option value="">All Roles</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.display_name || role.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-end gap-2">
                                <Button @click="applyFilters">
                                    <Search class="w-4 h-4 mr-2" />
                                    Search
                                </Button>
                                <Button variant="outline" @click="clearFilters">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bulk Actions -->
                <Card v-if="selectedUsers.length > 0">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium">
                                {{ selectedUsers.length }} users selected
                            </span>
                            
                            <select v-model="bulkAction" class="p-2 border rounded-md">
                                <option value="">Select Action</option>
                                <option value="activate">Activate</option>
                                <option value="deactivate">Deactivate</option>
                                <option value="assign_role">Assign Role</option>
                                <option value="delete">Delete</option>
                            </select>
                            
                            <select 
                                v-if="bulkAction === 'assign_role'" 
                                v-model="bulkActionRole" 
                                class="p-2 border rounded-md"
                            >
                                <option value="">Select Role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.display_name || role.name }}
                                </option>
                            </select>
                            
                            <Button 
                                @click="performBulkAction" 
                                :disabled="!canPerformBulkAction"
                                variant="destructive"
                                size="sm"
                            >
                                Apply
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Users Table -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <UsersIcon class="w-4 h-4" />
                            Users
                        </CardTitle>
                        <CardDescription>
                            Manage user accounts and permissions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left p-2">
                                            <Checkbox 
                                                :checked="allSelected"
                                                :indeterminate="someSelected"
                                                @update:checked="toggleSelectAll"
                                            />
                                        </th>
                                        <th class="text-left p-2">Name</th>
                                        <th class="text-left p-2">Email</th>
                                        <th class="text-left p-2">Phone</th>
                                        <th class="text-left p-2">Status</th>
                                        <th class="text-left p-2">Roles</th>
                                        <th class="text-left p-2">Last Login</th>
                                        <th class="text-left p-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="p-2">
                                            <Checkbox 
                                                :checked="selectedUsers.includes(user.id)"
                                                @update:checked="(checked) => {
                                                    if (checked) {
                                                        selectedUsers.push(user.id);
                                                    } else {
                                                        const index = selectedUsers.indexOf(user.id);
                                                        if (index > -1) selectedUsers.splice(index, 1);
                                                    }
                                                }"
                                            />
                                        </td>
                                        <td class="p-2 font-medium">{{ user.name }}</td>
                                        <td class="p-2">{{ user.email }}</td>
                                        <td class="p-2">{{ user.phone || '-' }}</td>
                                        <td class="p-2">
                                            <Badge 
                                                :variant="user.status ? 'default' : 'secondary'"
                                                class="cursor-pointer"
                                                @click="toggleUserStatus(user)"
                                            >
                                                {{ user.status_text }}
                                            </Badge>
                                        </td>
                                        <td class="p-2">
                                            <div class="flex flex-wrap gap-1">
                                                <Badge 
                                                    v-for="role in user.roles" 
                                                    :key="role.id" 
                                                    variant="outline"
                                                    class="text-xs"
                                                >
                                                    {{ role.display_name || role.name }}
                                                </Badge>
                                                <span v-if="user.roles.length === 0" class="text-gray-500">No roles</span>
                                            </div>
                                        </td>
                                        <td class="p-2 text-sm text-gray-500">
                                            {{ user.last_login_human || 'Never' }}
                                        </td>
                                        <td class="p-2">
                                            <div class="flex gap-1">
                                                <Button variant="ghost" size="sm" @click="openViewDialog(user)">
                                                    <Eye class="w-4 h-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm" @click="openEditDialog(user)">
                                                    <Edit class="w-4 h-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm" @click="deleteUser(user)">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div v-if="users.data.length === 0" class="text-center py-8 text-muted-foreground">
                                No users found. Create your first user to get started.
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="users.links" class="flex justify-center mt-4">
                            <div class="flex gap-2">
                                <template v-for="(link, index) in users.links" :key="index">
                                    <Button
                                        v-if="link.url"
                                        :variant="link.active ? 'default' : 'outline'"
                                        size="sm"
                                        @click="window.location.href = link.url"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="px-3 py-1 text-sm text-gray-500"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- View User Dialog -->
        <Dialog v-model:open="isViewDialogOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>User Details</DialogTitle>
                </DialogHeader>
                
                <div v-if="viewingUser" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Name</Label>
                            <p class="mt-1">{{ viewingUser.name }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Email</Label>
                            <p class="mt-1">{{ viewingUser.email }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Phone</Label>
                            <p class="mt-1">{{ viewingUser.phone || '-' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Status</Label>
                            <p class="mt-1">
                                <Badge :variant="viewingUser.status ? 'default' : 'secondary'">
                                    {{ viewingUser.status_text }}
                                </Badge>
                            </p>
                        </div>
                        <div class="col-span-2">
                            <Label class="text-sm font-medium text-gray-500">Address</Label>
                            <p class="mt-1">{{ viewingUser.address || '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <Label class="text-sm font-medium text-gray-500">Roles</Label>
                            <div class="mt-1 flex flex-wrap gap-1">
                                <Badge 
                                    v-for="role in viewingUser.roles" 
                                    :key="role.id" 
                                    variant="outline"
                                >
                                    {{ role.display_name || role.name }}
                                </Badge>
                                <span v-if="viewingUser.roles.length === 0" class="text-gray-500">No roles assigned</span>
                            </div>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Last Login</Label>
                            <p class="mt-1">{{ viewingUser.last_login_human || 'Never' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Created</Label>
                            <p class="mt-1">{{ viewingUser.created_at_human }}</p>
                        </div>
                    </div>
                </div>
                
                <DialogFooter>
                    <Button @click="isViewDialogOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>