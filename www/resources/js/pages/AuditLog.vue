<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { 
    FileText, 
    Users,
    Calendar,
    Activity,
    Loader2,
    Search,
    Filter,
    RefreshCw,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';

interface AuditLog {
    id: number;
    event: string;
    auditable_type: string;
    auditable_id: number;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    old_values: Record<string, any>;
    new_values: Record<string, any>;
    url: string;
    ip_address: string;
    user_agent: string;
    created_at: string;
    created_at_human: string;
}

interface AuditStats {
    total_audits: number;
    today_audits: number;
    this_week_audits: number;
    events_breakdown: Record<string, number>;
    models_breakdown: Record<string, number>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'System Management',
        href: '/system',
    },
    {
        title: 'Audit Log',
        href: '/audit-log',
    },
];

const loading = ref(false);
const statsLoading = ref(false);
const audits = ref<AuditLog[]>([]);
const stats = ref<AuditStats | null>(null);
const currentPage = ref(1);
const totalPages = ref(1);
const filters = ref({
    auditable_type: '',
    event: '',
    date_from: '',
    date_to: '',
    user_id: '',
});

const eventColors = {
    created: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    updated: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    deleted: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    restored: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
};

const fetchAudits = async (page = 1) => {
    loading.value = true;
    
    try {
        const params = new URLSearchParams({
            page: page.toString(),
            ...Object.fromEntries(
                Object.entries(filters.value).filter(([, value]) => value)
            )
        });
        
        const response = await fetch(`/api/system/audit-log?${params}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch audit logs');
        }
        
        const data = await response.json();
        audits.value = data.data;
        currentPage.value = data.current_page;
        totalPages.value = data.last_page;
    } catch (error) {
        console.error('Error fetching audit logs:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    statsLoading.value = true;
    
    try {
        const response = await fetch('/api/system/audit-log/stats', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch audit stats');
        }
        
        stats.value = await response.json();
    } catch (error) {
        console.error('Error fetching audit stats:', error);
    } finally {
        statsLoading.value = false;
    }
};

const applyFilters = () => {
    currentPage.value = 1;
    fetchAudits(1);
};

const clearFilters = () => {
    filters.value = {
        auditable_type: '',
        event: '',
        date_from: '',
        date_to: '',
        user_id: '',
    };
    fetchAudits(1);
};

const refreshData = () => {
    fetchAudits(currentPage.value);
    fetchStats();
};

const goToPage = (page: number) => {
    fetchAudits(page);
};

const formatChanges = (oldValues: Record<string, any>, newValues: Record<string, any>) => {
    const changes: string[] = [];
    
    // Check for new values
    Object.keys(newValues || {}).forEach(key => {
        if (oldValues && oldValues[key] !== newValues[key]) {
            changes.push(`${key}: "${oldValues[key] || 'null'}" → "${newValues[key]}"`);
        } else if (!oldValues) {
            changes.push(`${key}: "${newValues[key]}"`);
        }
    });
    
    // Check for deleted values
    Object.keys(oldValues || {}).forEach(key => {
        if (!newValues || !(key in newValues)) {
            changes.push(`${key}: "${oldValues[key]}" → deleted`);
        }
    });
    
    return changes.join(', ');
};

onMounted(() => {
    fetchAudits();
    fetchStats();
});
</script>

<template>
    <Head title="Audit Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Audit Log</h1>
                        <p class="text-muted-foreground">Track all system activities and changes</p>
                    </div>
                    
                    <Button @click="refreshData" :disabled="loading">
                        <RefreshCw :class="['w-4 h-4 mr-2', loading ? 'animate-spin' : '']" />
                        Refresh
                    </Button>
                </div>

                <!-- Statistics Cards -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Audits</CardTitle>
                            <Activity class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div v-if="statsLoading" class="flex items-center">
                                <Loader2 class="w-4 h-4 animate-spin mr-2" />
                                Loading...
                            </div>
                            <div v-else class="text-2xl font-bold">
                                {{ stats?.total_audits || 0 }}
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Today</CardTitle>
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div v-if="statsLoading" class="flex items-center">
                                <Loader2 class="w-4 h-4 animate-spin mr-2" />
                                Loading...
                            </div>
                            <div v-else class="text-2xl font-bold">
                                {{ stats?.today_audits || 0 }}
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">This Week</CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div v-if="statsLoading" class="flex items-center">
                                <Loader2 class="w-4 h-4 animate-spin mr-2" />
                                Loading...
                            </div>
                            <div v-else class="text-2xl font-bold">
                                {{ stats?.this_week_audits || 0 }}
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Most Active</CardTitle>
                            <FileText class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div v-if="statsLoading" class="flex items-center">
                                <Loader2 class="w-4 h-4 animate-spin mr-2" />
                                Loading...
                            </div>
                            <div v-else class="text-sm font-medium">
                                {{ Object.keys(stats?.models_breakdown || {})[0] || 'No data' }}
                            </div>
                        </CardContent>
                    </Card>
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
                        <div class="grid gap-4 md:grid-cols-5">
                            <div class="space-y-2">
                                <Label for="model">Model</Label>
                                <select 
                                    id="model" 
                                    v-model="filters.auditable_type" 
                                    class="w-full p-2 border rounded-md"
                                >
                                    <option value="">All Models</option>
                                    <option value="App\Models\MenuItem">MenuItem</option>
                                    <option value="App\Models\User">User</option>
                                    <option value="App\Models\Role">Role</option>
                                    <option value="App\Models\Permission">Permission</option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="event">Event</Label>
                                <select 
                                    id="event" 
                                    v-model="filters.event" 
                                    class="w-full p-2 border rounded-md"
                                >
                                    <option value="">All Events</option>
                                    <option value="created">Created</option>
                                    <option value="updated">Updated</option>
                                    <option value="deleted">Deleted</option>
                                    <option value="restored">Restored</option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="date_from">From Date</Label>
                                <Input 
                                    id="date_from" 
                                    v-model="filters.date_from" 
                                    type="date" 
                                />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="date_to">To Date</Label>
                                <Input 
                                    id="date_to" 
                                    v-model="filters.date_to" 
                                    type="date" 
                                />
                            </div>
                            
                            <div class="space-y-2 flex items-end gap-2">
                                <Button @click="applyFilters" class="flex-1">
                                    <Search class="w-4 h-4 mr-2" />
                                    Apply
                                </Button>
                                <Button @click="clearFilters" variant="outline">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Audit Log Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>Activity Log</CardTitle>
                        <CardDescription>
                            Recent system activities and data changes
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                            Loading audit logs...
                        </div>
                        
                        <div v-else-if="audits.length === 0" class="text-center py-8 text-muted-foreground">
                            No audit logs found
                        </div>
                        
                        <div v-else class="space-y-4">
                            <div 
                                v-for="audit in audits" 
                                :key="audit.id"
                                class="flex items-start gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <Badge :class="eventColors[audit.event as keyof typeof eventColors] || 'bg-gray-100 text-gray-800'">
                                            {{ audit.event.toUpperCase() }}
                                        </Badge>
                                        <span class="font-medium">{{ audit.auditable_type }}</span>
                                        <span class="text-muted-foreground">#{{ audit.auditable_id }}</span>
                                    </div>
                                    
                                    <div class="text-sm text-muted-foreground">
                                        <strong>User:</strong> {{ audit.user?.name || 'System' }} 
                                        ({{ audit.user?.email || 'N/A' }})
                                    </div>
                                    
                                    <div v-if="audit.old_values || audit.new_values" class="text-sm">
                                        <strong>Changes:</strong> 
                                        {{ formatChanges(audit.old_values, audit.new_values) }}
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>{{ audit.created_at_human }}</span>
                                        <span>{{ audit.ip_address }}</span>
                                        <span class="truncate max-w-xs">{{ audit.url }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pagination -->
                            <div v-if="totalPages > 1" class="flex items-center justify-between pt-4">
                                <div class="text-sm text-muted-foreground">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <Button 
                                        @click="goToPage(currentPage - 1)"
                                        :disabled="currentPage <= 1"
                                        variant="outline"
                                        size="sm"
                                    >
                                        <ChevronLeft class="w-4 h-4" />
                                        Previous
                                    </Button>
                                    
                                    <Button 
                                        @click="goToPage(currentPage + 1)"
                                        :disabled="currentPage >= totalPages"
                                        variant="outline"
                                        size="sm"
                                    >
                                        Next
                                        <ChevronRight class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>