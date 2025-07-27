<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/components/ui/toast';
import { 
    ArrowLeft,
    GraduationCap,
    Edit,
    Trash2,
    Eye,
    Calendar,
    User,
    Tag,
    Globe,
    Languages,
    Clock,
    Share2
} from 'lucide-vue-next';

interface Education {
    id: number;
    title_id: string;
    title_en: string;
    description_id: string;
    description_en: string;
    content_id: string;
    content_en: string;
    image?: string;
    category: string;
    tags: string[];
    is_published: boolean;
    published_at: string | null;
    view_count: number;
    sort_order: number;
    meta_title_id: string;
    meta_title_en: string;
    meta_description_id: string;
    meta_description_en: string;
    created_at: string;
    updated_at: string;
    author?: {
        name: string;
        email: string;
    };
}

interface Props {
    education: Education;
    categories?: Record<string, string>;
    bilingualEnabled: boolean;
    currentLanguage: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Content', href: '/dashboard' },
    { label: 'Education', href: '/education' },
    { label: 'View Content', href: '' }
];

const currentLanguage = ref(props.currentLanguage || 'id');

const currentTitle = computed(() => {
    return currentLanguage.value === 'en' ? props.education.title_en : props.education.title_id;
});

const currentDescription = computed(() => {
    return currentLanguage.value === 'en' ? props.education.description_en : props.education.description_id;
});

const currentContent = computed(() => {
    return currentLanguage.value === 'en' ? props.education.content_en : props.education.content_id;
});

const toggleLanguage = () => {
    currentLanguage.value = currentLanguage.value === 'id' ? 'en' : 'id';
};

const goBack = () => {
    router.visit('/education');
};

const editEducation = () => {
    router.visit(`/education/${props.education.id}/edit`);
};

const deleteEducation = async () => {
    if (!confirm('Are you sure you want to delete this education content?')) {
        return;
    }

    try {
        const response = await fetch(`/api/system/education/${props.education.id}`, {
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

        router.visit('/education');
    } catch (error) {
        toast({
            title: 'Error',
            description: 'Failed to delete education content',
            variant: 'error',
        });
    }
};

const updateViewCount = async () => {
    try {
        await fetch(`/api/system/education/${props.education.id}/view`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
    } catch (error) {
        console.error('Failed to update view count:', error);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
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

const shareContent = async () => {
    const url = window.location.href;
    const title = currentTitle.value;
    const text = currentDescription.value;

    if (navigator.share) {
        try {
            await navigator.share({
                title,
                text,
                url,
            });
        } catch (error) {
            console.error('Error sharing:', error);
        }
    } else {
        // Fallback: copy to clipboard
        try {
            await navigator.clipboard.writeText(url);
            toast({
                title: 'Copied!',
                description: 'URL copied to clipboard',
                variant: 'success',
            });
        } catch (error) {
            console.error('Failed to copy:', error);
        }
    }
};

// Update view count when component mounts
updateViewCount();
</script>

<template>
    <Head :title="currentTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Button variant="ghost" size="sm" @click="goBack">
                            <ArrowLeft class="w-4 h-4" />
                        </Button>
                        <GraduationCap class="w-8 h-8 text-primary" />
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">{{ currentTitle }}</h1>
                            <p class="text-muted-foreground">
                                Education Content Details
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button 
                            v-if="props.bilingualEnabled"
                            @click="toggleLanguage" 
                            variant="outline" 
                            size="sm"
                            class="text-blue-600 border-blue-200"
                        >
                            <Languages class="w-4 h-4 mr-2" />
                            {{ currentLanguage === 'id' ? '🇮🇩 ID' : '🇺🇸 EN' }}
                        </Button>
                        <Button @click="shareContent" variant="outline" size="sm">
                            <Share2 class="w-4 h-4 mr-2" />
                            Share
                        </Button>
                        <Button @click="editEducation" variant="outline">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Button>
                        <Button @click="deleteEducation" variant="destructive">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Delete
                        </Button>
                    </div>
                </div>

                <!-- Content Info -->
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="text-2xl mb-2">{{ currentTitle }}</CardTitle>
                                <CardDescription class="text-base">{{ currentDescription }}</CardDescription>
                            </div>
                            <div class="ml-4">
                                <Badge :variant="education.is_published ? 'default' : 'secondary'">
                                    {{ education.is_published ? 'Published' : 'Draft' }}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <Tag class="w-4 h-4" />
                                <span>Category:</span>
                                <Badge :variant="getCategoryBadgeVariant(education.category)">
                                    {{ categories?.[education.category] || education.category }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <Eye class="w-4 h-4" />
                                <span>{{ education.view_count }} views</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <Calendar class="w-4 h-4" />
                                <span>{{ formatDate(education.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="education.tags?.length" class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Tags:</h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge 
                                    v-for="tag in education.tags" 
                                    :key="tag" 
                                    variant="outline" 
                                    class="text-xs"
                                >
                                    {{ tag }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Author Info -->
                        <div v-if="education.author" class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                            <User class="w-4 h-4" />
                            <span>Author: {{ education.author.name }}</span>
                        </div>

                        <!-- Publication Info -->
                        <div v-if="education.published_at" class="flex items-center gap-2 text-sm text-gray-600">
                            <Clock class="w-4 h-4" />
                            <span>Published: {{ formatDate(education.published_at) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Featured Image -->
                <Card v-if="education.image">
                    <CardContent class="p-6">
                        <img 
                            :src="education.image" 
                            :alt="currentTitle"
                            class="w-full max-w-2xl mx-auto rounded-lg shadow-md"
                        />
                    </CardContent>
                </Card>

                <!-- Main Content -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Globe class="w-5 h-5" />
                            Content
                            <Badge v-if="props.bilingualEnabled" variant="outline" class="ml-2">
                                {{ currentLanguage === 'id' ? 'Bahasa Indonesia' : 'English' }}
                            </Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div 
                            class="prose prose-lg"
                            v-html="currentContent"
                        ></div>
                    </CardContent>
                </Card>

                <!-- SEO Information (for content managers) -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">SEO Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium mb-2 flex items-center gap-2">
                                    🇮🇩 Indonesian SEO
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600">Meta Title:</span>
                                        <p class="text-gray-800">{{ education.meta_title_id || 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Meta Description:</span>
                                        <p class="text-gray-800">{{ education.meta_description_id || 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="props.bilingualEnabled">
                                <h4 class="font-medium mb-2 flex items-center gap-2">
                                    🇺🇸 English SEO
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600">Meta Title:</span>
                                        <p class="text-gray-800">{{ education.meta_title_en || 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Meta Description:</span>
                                        <p class="text-gray-800">{{ education.meta_description_en || 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bilingual Content Toggle (if bilingual enabled) -->
                <Card v-if="props.bilingualEnabled">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Languages class="w-5 h-5" />
                            Bilingual Content
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium mb-2 flex items-center gap-2">
                                    🇮🇩 Indonesian Version
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600">Title:</span>
                                        <p class="text-gray-800">{{ education.title_id }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Description:</span>
                                        <p class="text-gray-800">{{ education.description_id }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-medium mb-2 flex items-center gap-2">
                                    🇺🇸 English Version
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600">Title:</span>
                                        <p class="text-gray-800">{{ education.title_en || 'Not available' }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Description:</span>
                                        <p class="text-gray-800">{{ education.description_en || 'Not available' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Navigation -->
                <div class="flex justify-between">
                    <Button @click="goBack" variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Education List
                    </Button>
                    <Button @click="editEducation">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit Content
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.prose {
    color: #374151;
}

.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
    color: #111827;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose p {
    margin-bottom: 1rem;
    line-height: 1.75;
}

.prose ul, .prose ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.prose li {
    margin-bottom: 0.5rem;
}

.prose a {
    color: #3b82f6;
    text-decoration: underline;
}

.prose a:hover {
    color: #1d4ed8;
}

.prose blockquote {
    border-left: 4px solid #e5e7eb;
    padding-left: 1rem;
    font-style: italic;
    margin: 1.5rem 0;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.prose pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.prose img {
    margin: 1.5rem 0;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>