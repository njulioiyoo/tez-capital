<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, reactive, computed, withDefaults } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/components/ui/toast';
import { 
    GraduationCap, 
    Plus,
    Search,
    Filter,
    BookOpen,
    Users,
    Clock,
    Eye,
    Edit,
    Trash2,
    FileText,
    Globe,
    Languages
} from 'lucide-vue-next';

interface Education {
    id: number;
    title_id: string;
    title_en: string;
    description_id: string;
    description_en: string;
    category: string;
    tags: string[];
    is_published: boolean;
    published_at: string | null;
    view_count: number;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    education: {
        data?: Education[];
        links?: any[];
        meta?: any;
    };
    categories: Record<string, string>;
    filters: {
        search?: string;
        category?: string;
        status?: string;
    };
    bilingualEnabled: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    education: () => ({ data: [], links: [], meta: { total: 0 } }),
    categories: () => ({}),
    filters: () => ({}),
    bilingualEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Content', href: '/dashboard' },
    { label: 'Education', href: '/education' }
];

const searchForm = reactive({
    search: props.filters.search || '',
    category: props.filters.category || '',
    status: props.filters.status || '',
});

const selectedItems = ref<number[]>([]);
const isLoading = ref(false);

const handleSearch = () => {
    router.get('/education', {
        search: searchForm.search || undefined,
        category: searchForm.category || undefined,
        status: searchForm.status || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchForm.search = '';
    searchForm.category = '';
    searchForm.status = '';
    handleSearch();
};

const handleBulkAction = async (action: string) => {
    if (selectedItems.value.length === 0) {
        toast({
            title: 'Warning',
            description: 'Please select items first',
            variant: 'error',
        });
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch('/api/system/education/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                action,
                ids: selectedItems.value,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to perform bulk action');
        }

        const data = await response.json();
        
        toast({
            title: 'Success',
            description: data.message,
            variant: 'success',
        });

        selectedItems.value = [];
        router.reload({ only: ['education'] });
    } catch (error) {
        toast({
            title: 'Error',
            description: 'Failed to perform bulk action',
            variant: 'error',
        });
    } finally {
        isLoading.value = false;
    }
};

const deleteEducation = async (id: number) => {
    if (!confirm('Are you sure you want to delete this education content?')) {
        return;
    }

    try {
        const response = await fetch(`/api/system/education/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to delete education content');
        }

        const data = await response.json();
        
        toast({
            title: 'Success',
            description: data.message,
            variant: 'success',
        });

        router.reload({ only: ['education'] });
    } catch (error) {
        toast({
            title: 'Error',
            description: 'Failed to delete education content',
            variant: 'error',
        });
    }
};

const toggleSelectAll = () => {
    if (selectedItems.value.length === (props.education.data?.length || 0)) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.education.data?.map(item => item.id) || [];
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const getCategoryBadgeVariant = (category: string) => {
    const variants: Record<string, string> = {
        tutorial: 'default',
        guide: 'secondary',
        tips: 'outline',
        news: 'destructive',
        announcement: 'default',
    };
    return variants[category] || 'default';
};
</script>

<template>
    <Head title="Education Content Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <GraduationCap class="w-8 h-8 text-primary" />
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">Education Content</h1>
                            <p class="text-muted-foreground">
                                Manage educational content with bilingual support
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button 
                            v-if="props.bilingualEnabled"
                            variant="outline" 
                            size="sm"
                            class="text-blue-600 border-blue-200"
                        >
                            <Languages class="w-4 h-4 mr-2" />
                            Bilingual Enabled
                        </Button>
                        <Button 
                            @click="router.visit('/education/create')"
                            class="bg-primary hover:bg-primary/90"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            Create Content
                        </Button>
                    </div>
                </div>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <BookOpen class="h-8 w-8 text-blue-600" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Content</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ education.meta?.total || education.data?.length || 0 }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <Users class="h-8 w-8 text-green-600" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Published</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ education.data?.filter(item => item.is_published).length || 0 }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <Clock class="h-8 w-8 text-yellow-600" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Draft</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ education.data?.filter(item => !item.is_published).length || 0 }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <Eye class="h-8 w-8 text-purple-600" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Views</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ education.data?.reduce((sum, item) => sum + item.view_count, 0) || 0 }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Filters and Search -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Filter class="w-5 h-5" />
                            Filters & Search
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <Input
                                    v-model="searchForm.search"
                                    placeholder="Search content..."
                                    class="pl-10"
                                    @keyup.enter="handleSearch"
                                />
                            </div>
                            
                            <Select v-model:modelValue="searchForm.category" @change="handleSearch">
                                <option value="">All Categories</option>
                                <option v-for="(label, value) in categories" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </Select>

                            <Select v-model:modelValue="searchForm.status" @change="handleSearch">
                                <option value="">All Status</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </Select>

                            <div class="flex gap-2">
                                <Button @click="handleSearch" class="flex-1">
                                    <Search class="w-4 h-4 mr-2" />
                                    Search
                                </Button>
                                <Button @click="clearFilters" variant="outline">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bulk Actions -->
                <Card v-if="selectedItems.length > 0">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">
                                {{ selectedItems.length }} item(s) selected
                            </span>
                            <div class="flex gap-2">
                                <Button 
                                    @click="handleBulkAction('publish')" 
                                    size="sm"
                                    :disabled="isLoading"
                                >
                                    Publish
                                </Button>
                                <Button 
                                    @click="handleBulkAction('unpublish')" 
                                    size="sm" 
                                    variant="outline"
                                    :disabled="isLoading"
                                >
                                    Unpublish
                                </Button>
                                <Button 
                                    @click="handleBulkAction('delete')" 
                                    size="sm" 
                                    variant="destructive"
                                    :disabled="isLoading"
                                >
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Content List -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="w-5 h-5" />
                                Education Content
                            </CardTitle>
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    :checked="selectedItems.length === (education.data?.length || 0) && (education.data?.length || 0) > 0"
                                    @change="toggleSelectAll"
                                    class="rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <label class="text-sm text-gray-600">Select All</label>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!education.data || education.data.length === 0" class="text-center py-8">
                            <GraduationCap class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No education content found</h3>
                            <p class="text-gray-600 mb-4">Get started by creating your first educational content.</p>
                            <Button @click="router.visit('/education/create')">
                                <Plus class="w-4 h-4 mr-2" />
                                Create Content
                            </Button>
                        </div>

                        <div v-else class="space-y-4">
                            <div 
                                v-for="item in education.data || []" 
                                :key="item.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-start gap-4">
                                    <input
                                        v-model="selectedItems"
                                        :value="item.id"
                                        type="checkbox"
                                        class="mt-1 rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-lg text-gray-900 mb-1">
                                                    🇮🇩 {{ item.title_id }}
                                                </h3>
                                                <h4 v-if="props.bilingualEnabled" class="font-medium text-gray-700 mb-2">
                                                    🇺🇸 {{ item.title_en }}
                                                </h4>
                                                <p class="text-gray-600 text-sm mb-3">
                                                    {{ item.description_id }}
                                                </p>
                                            </div>
                                            
                                            <div class="flex items-center gap-2">
                                                <Badge :variant="item.is_published ? 'default' : 'secondary'">
                                                    {{ item.is_published ? 'Published' : 'Draft' }}
                                                </Badge>
                                                <Badge :variant="getCategoryBadgeVariant(item.category)">
                                                    {{ categories[item.category] }}
                                                </Badge>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                                <span class="flex items-center gap-1">
                                                    <Eye class="w-4 h-4" />
                                                    {{ item.view_count }} views
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <Clock class="w-4 h-4" />
                                                    {{ formatDate(item.created_at) }}
                                                </span>
                                                <div v-if="item.tags?.length" class="flex gap-1">
                                                    <Badge v-for="tag in item.tags.slice(0, 3)" :key="tag" variant="outline" class="text-xs">
                                                        {{ tag }}
                                                    </Badge>
                                                    <span v-if="item.tags.length > 3" class="text-xs text-gray-400">
                                                        +{{ item.tags.length - 3 }} more
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    @click="router.visit(`/education/${item.id}`)"
                                                >
                                                    <Eye class="w-4 h-4" />
                                                </Button>
                                                <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    @click="router.visit(`/education/${item.id}/edit`)"
                                                >
                                                    <Edit class="w-4 h-4" />
                                                </Button>
                                                <Button 
                                                    size="sm" 
                                                    variant="destructive"
                                                    @click="deleteEducation(item.id)"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pagination -->
                <div v-if="education.links && education.links.length > 3" class="flex justify-center">
                    <nav class="flex items-center gap-2">
                        <Link
                            v-for="link in education.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-md',
                                link.active 
                                    ? 'bg-primary text-white' 
                                    : 'bg-white text-gray-700 hover:bg-gray-50 border'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>