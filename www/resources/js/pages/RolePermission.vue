<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';
import { toast } from '@/components/ui/toast';
import { Plus, Edit, Trash2, Shield, Key } from 'lucide-vue-next';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    roles: Role[];
    permissions: Permission[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'System Management', href: '/system' },
    { title: 'Roles & Permissions', href: '/roles' },
];

const roleDialogOpen = ref(false);
const permissionDialogOpen = ref(false);
const editingRole = ref<Role | null>(null);
const editingPermission = ref<Permission | null>(null);
const confirmDialog = ref({
    open: false,
    type: '' as 'role' | 'permission',
    itemId: 0,
    loading: false
});

const roleForm = reactive({
    name: '',
    permissions: [] as number[],
});

const permissionForm = reactive({
    name: '',
});

const openRoleDialog = (role?: Role) => {
    if (role) {
        editingRole.value = role;
        roleForm.name = role.name;
        roleForm.permissions = role.permissions.map(p => p.id);
    } else {
        editingRole.value = null;
        roleForm.name = '';
        roleForm.permissions = [];
    }
    roleDialogOpen.value = true;
};

const openPermissionDialog = (permission?: Permission) => {
    if (permission) {
        editingPermission.value = permission;
        permissionForm.name = permission.name;
    } else {
        editingPermission.value = null;
        permissionForm.name = '';
    }
    permissionDialogOpen.value = true;
};

const saveRole = async () => {
    const url = editingRole.value ? `/api/system/roles-permissions/roles/${editingRole.value.id}` : '/api/system/roles-permissions/roles';
    const method = editingRole.value ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(roleForm),
        });
        
        if (response.ok) {
            roleDialogOpen.value = false;
            window.location.reload();
        }
    } catch (error) {
        console.error('Error saving role:', error);
    }
};

const savePermission = async () => {
    const url = editingPermission.value ? `/api/system/roles-permissions/permissions/${editingPermission.value.id}` : '/api/system/roles-permissions/permissions';
    const method = editingPermission.value ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(permissionForm),
        });
        
        if (response.ok) {
            permissionDialogOpen.value = false;
            window.location.reload();
        }
    } catch (error) {
        console.error('Error saving permission:', error);
    }
};

const deleteRole = (role: Role) => {
    confirmDialog.value.type = 'role';
    confirmDialog.value.itemId = role.id;
    confirmDialog.value.open = true;
};

const deletePermission = (permission: Permission) => {
    confirmDialog.value.type = 'permission';
    confirmDialog.value.itemId = permission.id;
    confirmDialog.value.open = true;
};

const confirmDelete = async () => {
    confirmDialog.value.loading = true;
    
    try {
        const endpoint = confirmDialog.value.type === 'role' 
            ? `/api/system/roles-permissions/roles/${confirmDialog.value.itemId}`
            : `/api/system/roles-permissions/permissions/${confirmDialog.value.itemId}`;
            
        // Helper function to get CSRF token (same as other modules)
        const getCsrfToken = () => {
            const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (tokenFromMeta) return tokenFromMeta;
            
            const tokenFromCookie = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];
            
            if (tokenFromCookie) {
                try {
                    return decodeURIComponent(tokenFromCookie);
                } catch (e) {
                    console.warn('Failed to decode CSRF token from cookie:', e);
                }
            }
            return '';
        };

        const response = await fetch(endpoint, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            toast({
                title: 'Success',
                description: `${confirmDialog.value.type === 'role' ? 'Role' : 'Permission'} deleted successfully`
            });
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            toast({
                title: 'Error',
                description: `Failed to delete ${confirmDialog.value.type}`,
                variant: 'destructive'
            });
        }
    } catch (error) {
        toast({
            title: 'Error',
            description: `Failed to delete ${confirmDialog.value.type}`,
            variant: 'destructive'
        });
    } finally {
        confirmDialog.value.loading = false;
        confirmDialog.value.open = false;
        confirmDialog.value.itemId = 0;
        confirmDialog.value.type = '' as any;
    }
};
</script>

<template>
    <Head title="Roles & Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Roles & Permissions</h1>
                        <p class="text-muted-foreground">Manage user roles and permissions</p>
                    </div>
                </div>

                <!-- Roles Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Shield class="w-5 h-5" />
                                    Roles
                                </CardTitle>
                                <CardDescription>Manage user roles</CardDescription>
                            </div>
                            <Dialog v-model:open="roleDialogOpen">
                                <DialogTrigger as-child>
                                    <Button @click="openRoleDialog()">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Role
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>{{ editingRole ? 'Edit Role' : 'Create Role' }}</DialogTitle>
                                        <DialogDescription>
                                            {{ editingRole ? 'Update role information' : 'Create a new role with permissions' }}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="space-y-4">
                                        <div>
                                            <Label for="role-name">Role Name</Label>
                                            <Input id="role-name" v-model="roleForm.name" placeholder="Enter role name" />
                                        </div>
                                        <div>
                                            <Label>Permissions</Label>
                                            <div class="grid grid-cols-2 gap-2 mt-2">
                                                <label v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-2">
                                                    <input 
                                                        type="checkbox" 
                                                        :value="permission.id"
                                                        v-model="roleForm.permissions"
                                                        class="rounded border-gray-300"
                                                    />
                                                    <span class="text-sm">{{ permission.name }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button @click="saveRole">
                                            {{ editingRole ? 'Update' : 'Create' }}
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="role in roles" :key="role.id" class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex-1">
                                    <h3 class="font-semibold">{{ role.name }}</h3>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <Badge v-for="permission in role.permissions" :key="permission.id" variant="secondary">
                                            {{ permission.name }}
                                        </Badge>
                                        <span v-if="role.permissions.length === 0" class="text-sm text-muted-foreground">No permissions assigned</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button @click="openRoleDialog(role)" variant="outline" size="sm">
                                        <Edit class="w-4 h-4" />
                                    </Button>
                                    <Button @click="deleteRole(role)" variant="destructive" size="sm">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Permissions Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Key class="w-5 h-5" />
                                    Permissions
                                </CardTitle>
                                <CardDescription>Manage system permissions</CardDescription>
                            </div>
                            <Dialog v-model:open="permissionDialogOpen">
                                <DialogTrigger as-child>
                                    <Button @click="openPermissionDialog()">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Permission
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>{{ editingPermission ? 'Edit Permission' : 'Create Permission' }}</DialogTitle>
                                        <DialogDescription>
                                            {{ editingPermission ? 'Update permission name' : 'Create a new permission' }}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="space-y-4">
                                        <div>
                                            <Label for="permission-name">Permission Name</Label>
                                            <Input id="permission-name" v-model="permissionForm.name" placeholder="Enter permission name" />
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button @click="savePermission">
                                            {{ editingPermission ? 'Update' : 'Create' }}
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div v-for="permission in permissions" :key="permission.id" class="flex items-center justify-between p-3 border rounded-lg">
                                <span class="font-medium">{{ permission.name }}</span>
                                <div class="flex items-center gap-2">
                                    <Button @click="openPermissionDialog(permission)" variant="outline" size="sm">
                                        <Edit class="w-4 h-4" />
                                    </Button>
                                    <Button @click="deletePermission(permission)" variant="destructive" size="sm">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
    
    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
        v-model:open="confirmDialog.open"
        :title="`Delete ${confirmDialog.type === 'role' ? 'Role' : 'Permission'}`"
        :description="`Are you sure you want to delete this ${confirmDialog.type}? This action cannot be undone.`"
        confirm-text="Delete"
        :loading="confirmDialog.loading"
        @confirm="confirmDelete"
    />
</template>