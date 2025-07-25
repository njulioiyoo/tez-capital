import { type NavItem } from '@/types';
import { ref, computed, readonly } from 'vue';
import { router } from '@inertiajs/vue3';
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
    Home,
    Database,
    Globe,
    Mail,
    Calendar,
    Camera,
    Star,
    Heart,
    Bookmark
} from 'lucide-vue-next';

// Icon mapping for dynamic icons
export const iconMap = {
    'LayoutGrid': LayoutGrid,
    'Users': Users,
    'Settings': Settings,
    'FileText': FileText,
    'BarChart3': BarChart3,
    'CreditCard': CreditCard,
    'Shield': Shield,
    'Bell': Bell,
    'User': User,
    'Lock': Lock,
    'Palette': Palette,
    'Home': Home,
    'Database': Database,
    'Globe': Globe,
    'Mail': Mail,
    'Calendar': Calendar,
    'Camera': Camera,
    'Star': Star,
    'Heart': Heart,
    'Bookmark': Bookmark,
};

// Global state - shared across all components
const navigation = ref<NavItem[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

export function useNavigation() {

    // Convert API response to NavItem with proper icon
    const transformMenuItem = (item: any): NavItem => {
        return {
            id: item.id,
            title: item.title,
            href: item.href,
            icon: item.icon ? iconMap[item.icon as keyof typeof iconMap] : undefined,
            position: item.position,
            parent_id: item.parent_id,
            children: item.children ? item.children.map(transformMenuItem) : [],
            is_separator: item.is_separator,
            badge: item.badge,
            disabled: item.disabled,
        };
    };

    // Fetch menu items from API
    const fetchMenuItems = async () => {
        loading.value = true;
        error.value = null;
        
        try {
            console.log('Fetching menu items...');
            const response = await fetch('/api/menu-items', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch menu items');
            }
            
            const data = await response.json();
            navigation.value = data.data.map(transformMenuItem);
            console.log('Menu items fetched successfully:', navigation.value.length, 'items');
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'An error occurred';
            console.error('Error fetching menu items:', err);
        } finally {
            loading.value = false;
        }
    };

    // Get menu tree (already structured from API)
    const menuTree = computed(() => navigation.value);

    // Add new menu item
    const addMenuItem = async (item: Omit<NavItem, 'id'>) => {
        try {
            const payload = {
                title: item.title,
                href: item.href,
                icon: item.icon ? getIconName(item.icon) : null,
                position: item.position,
                parent_id: item.parent_id,
                badge: item.badge,
                disabled: item.disabled || false,
                is_separator: item.is_separator || false,
            };

            const response = await fetch('/api/system/menu/items', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to create menu item');
            }

            await fetchMenuItems(); // Refresh menu items
            return true;
        } catch (err) {
            console.error('Error creating menu item:', err);
            error.value = err instanceof Error ? err.message : 'Failed to create menu item';
            return false;
        }
    };

    // Update menu item
    const updateMenuItem = async (id: string, updates: Partial<NavItem>) => {
        try {
            const payload = {
                title: updates.title,
                href: updates.href,
                icon: updates.icon ? getIconName(updates.icon) : null,
                position: updates.position,
                parent_id: updates.parent_id,
                badge: updates.badge,
                disabled: updates.disabled || false,
                is_separator: updates.is_separator || false,
                is_active: true,
            };

            const response = await fetch(`/api/system/menu/items/${id}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to update menu item');
            }

            await fetchMenuItems(); // Refresh menu items
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update menu item';
            return false;
        }
    };

    // Remove menu item
    const removeMenuItem = async (id: string) => {
        try {
            const response = await fetch(`/api/system/menu/items/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to delete menu item');
            }

            await fetchMenuItems(); // Refresh menu items
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete menu item';
            return false;
        }
    };

    // Reorder menu items
    const reorderMenuItems = async (items: { id: string; position: number }[]) => {
        try {
            const response = await fetch('/api/system/menu/items/reorder', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ items }),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to reorder menu items');
            }

            console.log('Reorder successful, refreshing menu items...');
            await fetchMenuItems(); // Refresh menu items
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to reorder menu items';
            return false;
        }
    };

    // Get icon name from component
    const getIconName = (icon: any): string => {
        const entry = Object.entries(iconMap).find(([, component]) => component === icon);
        return entry ? entry[0] : 'LayoutGrid';
    };

    // Initialize navigation on first load (only if not already loaded)
    if (navigation.value.length === 0 && !loading.value) {
        fetchMenuItems();
    }

    return {
        navigation: readonly(navigation),
        menuTree,
        loading: readonly(loading),
        error: readonly(error),
        fetchMenuItems,
        addMenuItem,
        updateMenuItem,
        removeMenuItem,
        reorderMenuItems,
    };
}