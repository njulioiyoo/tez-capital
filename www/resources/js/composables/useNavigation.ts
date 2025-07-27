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
    Bookmark,
    Activity,
    Archive,
    Award,
    Book,
    Briefcase,
    Building,
    CheckCircle,
    Clock,
    Cloud,
    Code,
    Command,
    Compass,
    Download,
    Edit,
    Eye,
    Folder,
    Gift,
    HelpCircle,
    Image,
    Inbox,
    Key,
    Layers,
    Link,
    MapPin,
    MessageCircle,
    Monitor,
    Package,
    PieChart,
    Play,
    Plus,
    Power,
    Printer,
    RefreshCw,
    Save,
    Search,
    Send,
    Server,
    Share,
    ShoppingCart,
    Smartphone,
    Tag,
    Target,
    Trash,
    TrendingUp,
    Trophy,
    Truck,
    Upload,
    Wifi,
    Zap,
    GraduationCap,
    Music,
    Video,
    Headphones,
    Gamepad2,
    Coffee,
    Pizza,
    Car,
    Plane,
    Train,
    Ship,
    Bike,
    Wallet,
    DollarSign,
    Euro,
    Calculator,
    TrendingDown,
    BarChart,
    LineChart,
    MousePointer,
    Keyboard,
    Tablet,
    Laptop,
    Monitor as Desktop,
    Battery,
    Volume2,
    VolumeX,
    Sun,
    Moon,
    CloudRain,
    CloudSnow,
    Thermometer,
    Umbrella,
    Wind
} from 'lucide-vue-next';

// Icon mapping for dynamic icons
export const iconMap = {
    // Layout & Navigation
    'LayoutGrid': LayoutGrid,
    'Home': Home,
    'Compass': Compass,
    'MapPin': MapPin,
    'Layers': Layers,
    'Menu': LayoutGrid,
    
    // Users & People
    'Users': Users,
    'User': User,
    'Shield': Shield,
    'Award': Award,
    'Crown': Trophy,
    
    // System & Settings
    'Settings': Settings,
    'Command': Command,
    'Key': Key,
    'Lock': Lock,
    'Server': Server,
    'Database': Database,
    'Monitor': Monitor,
    'Power': Power,
    
    // Content & Files
    'FileText': FileText,
    'Folder': Folder,
    'Archive': Archive,
    'Book': Book,
    'Image': Image,
    'Edit': Edit,
    'Eye': Eye,
    'Save': Save,
    
    // Communication
    'Mail': Mail,
    'MessageCircle': MessageCircle,
    'Send': Send,
    'Bell': Bell,
    'Share': Share,
    'Link': Link,
    
    // Analytics & Charts
    'BarChart3': BarChart3,
    'BarChart': BarChart,
    'LineChart': LineChart,
    'PieChart': PieChart,
    'TrendingUp': TrendingUp,
    'TrendingDown': TrendingDown,
    'Activity': Activity,
    'Target': Target,
    
    // E-commerce & Finance
    'CreditCard': CreditCard,
    'ShoppingCart': ShoppingCart,
    'DollarSign': DollarSign,
    'Euro': Euro,
    'Wallet': Wallet,
    'Calculator': Calculator,
    'Package': Package,
    'Truck': Truck,
    
    // Actions & Tools
    'Plus': Plus,
    'Search': Search,
    'Download': Download,
    'Upload': Upload,
    'RefreshCw': RefreshCw,
    'Trash': Trash,
    'CheckCircle': CheckCircle,
    'Play': Play,
    'Printer': Printer,
    
    // Education & Learning
    'GraduationCap': GraduationCap,
    'BookOpen': Book,
    'Trophy': Trophy,
    'HelpCircle': HelpCircle,
    
    // Technology & Devices
    'Smartphone': Smartphone,
    'Tablet': Tablet,
    'Laptop': Laptop,
    'Desktop': Desktop,
    'Keyboard': Keyboard,
    'MousePointer': MousePointer,
    'Wifi': Wifi,
    'Cloud': Cloud,
    'Code': Code,
    'Battery': Battery,
    
    // Business & Work
    'Briefcase': Briefcase,
    'Building': Building,
    'Calendar': Calendar,
    'Clock': Clock,
    'Inbox': Inbox,
    
    // Entertainment & Media
    'Music': Music,
    'Video': Video,
    'Headphones': Headphones,
    'Gamepad': Gamepad2,
    'Camera': Camera,
    'Volume2': Volume2,
    'VolumeX': VolumeX,
    
    // Lifestyle & Fun
    'Coffee': Coffee,
    'Pizza': Pizza,
    'Gift': Gift,
    'Star': Star,
    'Heart': Heart,
    'Bookmark': Bookmark,
    'Tag': Tag,
    
    // Transportation
    'Car': Car,
    'Plane': Plane,
    'Train': Train,
    'Ship': Ship,
    'Bike': Bike,
    
    // Weather & Nature
    'Sun': Sun,
    'Moon': Moon,
    'CloudRain': CloudRain,
    'CloudSnow': CloudSnow,
    'Thermometer': Thermometer,
    'Umbrella': Umbrella,
    'Wind': Wind,
    
    // Design & UI
    'Palette': Palette,
    'Zap': Zap,
    'Globe': Globe,
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
                    'X-CSRF-TOKEN': getCsrfToken(),
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
                    'X-CSRF-TOKEN': getCsrfToken(),
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

    // Helper function to get CSRF token
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

    // Remove menu item
    const removeMenuItem = async (id: string) => {
        try {
            const response = await fetch(`/api/system/menu/items/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
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
                    'X-CSRF-TOKEN': getCsrfToken(),
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