<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useNavigation, iconMap } from '@/composables/useNavigation';
import { type NavItem, type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { 
    LayoutGrid, 
    Users, 
    Settings, 
    FileText, 
    BarChart3, 
    CreditCard, 
    Shield, 
    Bell,
    User,
    Lock,
    Palette,
    Plus,
    Edit,
    Trash2,
    GripVertical,
    ChevronDown,
    ChevronRight,
    Loader2
} from 'lucide-vue-next';

const { 
    navigation, 
    menuTree, 
    loading, 
    error, 
    fetchMenuItems,
    addMenuItem, 
    updateMenuItem, 
    removeMenuItem,
    reorderMenuItems
} = useNavigation();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/settings',
    },
    {
        title: 'Menu Manager',
        href: '/menu-manager',
    },
];

const isDialogOpen = ref(false);
const editingItem = ref<NavItem | null>(null);
// Expand all items by default so we can see child items for drag and drop
const expandedItems = ref<Set<string>>(new Set(['2', '8'])); // System Management & Reports

const iconOptions = Object.entries(iconMap).map(([name, component]) => ({
    name,
    component
}));

const formData = ref({
    title: '',
    href: '',
    icon: 'LayoutGrid',
    position: 1,
    parent_id: '',
    badge: '',
    disabled: false,
    is_separator: false,
});

const parentOptions = computed(() => {
    const getAllItems = (items: NavItem[]): NavItem[] => {
        let result: NavItem[] = [];
        for (const item of items) {
            if (!item.is_separator) {
                result.push(item);
                if (item.children && item.children.length > 0) {
                    result = result.concat(getAllItems(item.children));
                }
            }
        }
        return result;
    };
    
    return getAllItems(navigation.value);
});

const resetForm = () => {
    formData.value = {
        title: '',
        href: '',
        icon: 'LayoutGrid',
        position: 1,
        parent_id: '',
        badge: '',
        disabled: false,
        is_separator: false,
    };
    editingItem.value = null;
};

const openAddDialog = () => {
    resetForm();
    isDialogOpen.value = true;
};

const openEditDialog = (item: NavItem) => {
    editingItem.value = item;
    formData.value = {
        title: item.title,
        href: item.href || '',
        icon: getIconName(item.icon),
        position: item.position,
        parent_id: item.parent_id || '',
        badge: item.badge || '',
        disabled: item.disabled || false,
        is_separator: item.is_separator || false,
    };
    isDialogOpen.value = true;
};

const getIconName = (icon: any): string => {
    const foundIcon = iconOptions.find(opt => opt.component === icon);
    return foundIcon?.name || 'LayoutGrid';
};

const getIconComponent = (iconName: string) => {
    const foundIcon = iconOptions.find(opt => opt.name === iconName);
    return foundIcon?.component || LayoutGrid;
};

const saveMenuItem = async () => {
    const iconComponent = getIconComponent(formData.value.icon);
    
    const menuItem: Omit<NavItem, 'id'> = {
        title: formData.value.title,
        href: formData.value.href || undefined,
        icon: iconComponent,
        position: formData.value.position,
        parent_id: formData.value.parent_id || undefined,
        badge: formData.value.badge || undefined,
        disabled: formData.value.disabled,
        is_separator: formData.value.is_separator,
    };

    let success = false;
    if (editingItem.value) {
        success = await updateMenuItem(editingItem.value.id, menuItem);
    } else {
        success = await addMenuItem(menuItem);
    }

    if (success) {
        isDialogOpen.value = false;
        resetForm();
    }
};

const deleteMenuItem = async (id: string) => {
    if (confirm('Are you sure you want to delete this menu item?')) {
        await removeMenuItem(id);
    }
};

const toggleExpanded = (id: string) => {
    if (expandedItems.value.has(id)) {
        expandedItems.value.delete(id);
    } else {
        expandedItems.value.add(id);
    }
};

// Create a computed property for flattened menu items for display
const flattenedMenuItems = computed(() => {
    const flatten = (items: NavItem[], level = 0): Array<NavItem & { level: number, hasChildren: boolean }> => {
        let result: Array<NavItem & { level: number, hasChildren: boolean }> = [];
        
        for (const item of items) {
            const hasChildren = item.children && item.children.length > 0;
            const isExpanded = expandedItems.value.has(item.id);
            
            result.push({
                ...item,
                level,
                hasChildren
            });
            
            if (hasChildren && isExpanded) {
                result = result.concat(flatten(item.children!, level + 1));
            }
        }
        
        return result;
    };
    
    return flatten(menuTree.value);
});

// Drag and drop functionality
const draggedItem = ref<string | null>(null);
const dragOverItem = ref<string | null>(null);

const handleDragStart = (event: DragEvent, itemId: string) => {
    draggedItem.value = itemId;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', itemId);
    }
};

const handleDragOver = (event: DragEvent, itemId: string) => {
    event.preventDefault();
    dragOverItem.value = itemId;
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const handleDragLeave = () => {
    dragOverItem.value = null;
};

const handleDrop = async (event: DragEvent, targetItemId: string) => {
    event.preventDefault();
    
    if (!draggedItem.value || draggedItem.value === targetItemId) {
        draggedItem.value = null;
        dragOverItem.value = null;
        return;
    }
    
    // Find dragged and target items in the original menu tree
    const findItemById = (items: NavItem[], id: string): NavItem | null => {
        for (const item of items) {
            if (item.id === id) return item;
            if (item.children) {
                const found = findItemById(item.children, id);
                if (found) return found;
            }
        }
        return null;
    };
    
    const draggedItemData = findItemById(navigation.value, draggedItem.value);
    const targetItemData = findItemById(navigation.value, targetItemId);
    
    if (!draggedItemData || !targetItemData) {
        draggedItem.value = null;
        dragOverItem.value = null;
        return;
    }
    
    console.log('Drag and drop data:', {
        draggedItemData: { 
            id: draggedItemData.id, 
            title: draggedItemData.title, 
            parent_id: draggedItemData.parent_id,
            position: draggedItemData.position 
        },
        targetItemData: { 
            id: targetItemData.id, 
            title: targetItemData.title, 
            parent_id: targetItemData.parent_id,
            position: targetItemData.position 
        }
    });

    // Reorder items at same level only
    if (draggedItemData.parent_id === targetItemData.parent_id) {
        console.log('✅ Same parent - proceeding with reorder');
        
        // Get all items at the same level (including nested children)
        const getSiblings = (items: NavItem[], parentId: string | undefined): NavItem[] => {
            const findAllItems = (menuItems: NavItem[]): NavItem[] => {
                let allItems: NavItem[] = [];
                for (const item of menuItems) {
                    allItems.push(item);
                    if (item.children && item.children.length > 0) {
                        allItems = allItems.concat(findAllItems(item.children));
                    }
                }
                return allItems;
            };
            
            const allItems = findAllItems(items);
            return allItems.filter(item => item.parent_id === parentId);
        };
        
        const siblings = getSiblings(navigation.value, draggedItemData.parent_id);
        console.log('Siblings at same level:', siblings.map(s => ({ id: s.id, title: s.title, position: s.position })));
        
        // Sort siblings by current position
        siblings.sort((a, b) => a.position - b.position);
        
        // Remove dragged item from its current position
        const draggedIndex = siblings.findIndex(item => item.id === draggedItemData.id);
        const targetIndex = siblings.findIndex(item => item.id === targetItemData.id);
        
        if (draggedIndex !== -1 && targetIndex !== -1) {
            // Remove dragged item and insert at target position
            const reorderedSiblings = [...siblings];
            const [draggedSibling] = reorderedSiblings.splice(draggedIndex, 1);
            reorderedSiblings.splice(targetIndex, 0, draggedSibling);
            
            // Create reorder data with sequential positions
            const reorderData = reorderedSiblings.map((item, index) => ({
                id: item.id,
                position: index + 1
            }));
            
            console.log('Sending reorder data:', reorderData);
            
            const success = await reorderMenuItems(reorderData);
            
            if (success) {
                console.log('Reorder successful - menu should update');
            } else {
                console.error('Failed to reorder menu items');
            }
        }
    } else {
        // For cross-level moves, we'd need more complex logic
        console.log('❌ Different parents - cross-level reordering not implemented');
        console.log('   Dragged parent_id:', draggedItemData.parent_id);
        console.log('   Target parent_id:', targetItemData.parent_id);
    }
    
    draggedItem.value = null;
    dragOverItem.value = null;
};

const handleDragEnd = () => {
    draggedItem.value = null;
    dragOverItem.value = null;
};
</script>

<template>
    <Head title="Menu Manager" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Menu Manager</h1>
                <p class="text-muted-foreground">Manage your application's navigation menu structure</p>
            </div>
            
            <Dialog v-model:open="isDialogOpen">
                <DialogTrigger as-child>
                    <Button @click="openAddDialog">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Menu Item
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ editingItem ? 'Edit' : 'Add' }} Menu Item</DialogTitle>
                        <DialogDescription>
                            {{ editingItem ? 'Update the menu item details' : 'Create a new menu item for your navigation' }}
                        </DialogDescription>
                    </DialogHeader>
                    
                    <div class="space-y-4 py-4">
                        <div class="flex items-center space-x-2">
                            <input 
                                id="is_separator"
                                v-model="formData.is_separator" 
                                type="checkbox"
                                class="rounded border-gray-300"
                            />
                            <Label for="is_separator">Is Separator</Label>
                        </div>
                        
                        <template v-if="!formData.is_separator">
                            <div class="space-y-2">
                                <Label for="title">Title</Label>
                                <Input id="title" v-model="formData.title" placeholder="Menu title" required />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="href">URL (optional)</Label>
                                <Input id="href" v-model="formData.href" placeholder="/example-page" />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="icon">Icon</Label>
                                <select id="icon" v-model="formData.icon" class="w-full p-2 border rounded-md">
                                    <option v-for="icon in iconOptions" :key="icon.name" :value="icon.name">
                                        {{ icon.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="parent">Parent Menu (optional)</Label>
                                <select id="parent" v-model="formData.parent_id" class="w-full p-2 border rounded-md">
                                    <option value="">No Parent</option>
                                    <option v-for="parent in parentOptions" :key="parent.id" :value="parent.id">
                                        {{ parent.title }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="position">Position</Label>
                                <Input id="position" v-model.number="formData.position" type="number" min="1" required />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="badge">Badge (optional)</Label>
                                <Input id="badge" v-model="formData.badge" placeholder="New" />
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <input 
                                    id="disabled"
                                    v-model="formData.disabled" 
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                                <Label for="disabled">Disabled</Label>
                            </div>
                        </template>
                    </div>
                    
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDialogOpen = false">
                            Cancel
                        </Button>
                        <Button type="button" @click="saveMenuItem">
                            {{ editingItem ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Navigation Structure</CardTitle>
                <CardDescription>
                    Manage your navigation menu items. Click the chevron to expand/collapse parent items.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <Loader2 class="w-6 h-6 animate-spin mr-2" />
                    Loading menu items...
                </div>
                
                <div v-else-if="error" class="text-red-600 py-4">
                    Error: {{ error }}
                    <Button @click="fetchMenuItems" variant="outline" size="sm" class="ml-2">
                        Retry
                    </Button>
                </div>
                
                <div v-else class="space-y-2">
                    <div v-if="menuTree.length === 0" class="text-center py-8 text-muted-foreground">
                        No menu items found. Create your first menu item to get started.
                    </div>
                    <template v-else>
                        <div 
                            v-for="item in flattenedMenuItems" 
                            :key="item.id"
                            :class="[
                                'flex items-center gap-2 p-2 border rounded-lg bg-card transition-colors',
                                item.level === 1 ? 'ml-6' : '',
                                item.level === 2 ? 'ml-12' : '',
                                item.level === 3 ? 'ml-18' : '',
                                draggedItem === item.id ? 'opacity-50' : '',
                                dragOverItem === item.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-950' : ''
                            ]"
                            draggable="true"
                            @dragstart="handleDragStart($event, item.id)"
                            @dragover="handleDragOver($event, item.id)"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop($event, item.id)"
                            @dragend="handleDragEnd"
                        >
                            <!-- Drag handle -->
                            <GripVertical 
                                class="w-4 h-4 text-muted-foreground cursor-grab active:cursor-grabbing" 
                                @mousedown="$event.currentTarget.style.cursor = 'grabbing'"
                                @mouseup="$event.currentTarget.style.cursor = 'grab'"
                            />
                            
                            <!-- Expand/collapse button for parent items -->
                            <Button
                                v-if="item.hasChildren"
                                variant="ghost"
                                size="sm"
                                class="p-0 w-6 h-6"
                                @click="toggleExpanded(item.id)"
                            >
                                <ChevronDown v-if="expandedItems.has(item.id)" class="w-4 h-4" />
                                <ChevronRight v-else class="w-4 h-4" />
                            </Button>
                            <div v-else class="w-6"></div>
                            
                            <!-- Icon -->
                            <component :is="item.icon" v-if="item.icon" class="w-4 h-4" />
                            
                            <!-- Title and details -->
                            <div class="flex-1">
                                <div class="font-medium">{{ item.title || 'Separator' }}</div>
                                <div v-if="item.href" class="text-sm text-muted-foreground">{{ item.href }}</div>
                            </div>
                            
                            <!-- Badge -->
                            <span 
                                v-if="item.badge" 
                                class="px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded"
                            >
                                {{ item.badge }}
                            </span>
                            
                            <!-- Actions -->
                            <div class="flex gap-1">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditDialog(item)"
                                >
                                    <Edit class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="deleteMenuItem(item.id)"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </template>
                </div>
            </CardContent>
        </Card>
            </div>
        </div>
    </AppLayout>
</template>