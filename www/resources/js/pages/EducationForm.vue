<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, computed, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/components/ui/toast';
import { 
    Save, 
    ArrowLeft, 
    GraduationCap, 
    Languages,
    FileImage,
    Tag,
    Eye,
    Calendar,
    Globe,
    Hash,
    X
} from 'lucide-vue-next';

interface Education {
    id?: number;
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
    meta_title_id: string;
    meta_title_en: string;
    meta_description_id: string;
    meta_description_en: string;
    sort_order: number;
}

interface Props {
    education: Education | null;
    categories: Record<string, string>;
    bilingualEnabled: boolean;
}

const props = defineProps<Props>();

const isEditing = computed(() => !!props.education?.id);
const pageTitle = computed(() => isEditing.value ? 'Edit Education Content' : 'Create Education Content');

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Content', href: '/dashboard' },
    { label: 'Education', href: '/education' },
    { label: isEditing.value ? 'Edit' : 'Create', href: '' }
];

const form = reactive<Education>({
    title_id: props.education?.title_id || '',
    title_en: props.education?.title_en || '',
    description_id: props.education?.description_id || '',
    description_en: props.education?.description_en || '',
    content_id: props.education?.content_id || '',
    content_en: props.education?.content_en || '',
    image: props.education?.image || '',
    category: props.education?.category || 'tutorial',
    tags: props.education?.tags || [],
    is_published: props.education?.is_published || false,
    published_at: props.education?.published_at || null,
    meta_title_id: props.education?.meta_title_id || '',
    meta_title_en: props.education?.meta_title_en || '',
    meta_description_id: props.education?.meta_description_id || '',
    meta_description_en: props.education?.meta_description_en || '',
    sort_order: props.education?.sort_order || 0,
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);
const newTag = ref('');
const imageFile = ref<File | null>(null);
const activeTab = ref('content');

// Auto-generate meta titles from main titles
watch(() => form.title_id, (newValue) => {
    if (!form.meta_title_id || form.meta_title_id === form.title_id) {
        form.meta_title_id = newValue ? `${newValue} - TEZ Capital` : '';
    }
});

watch(() => form.title_en, (newValue) => {
    if (!form.meta_title_en || form.meta_title_en === form.title_en) {
        form.meta_title_en = newValue ? `${newValue} - TEZ Capital` : '';
    }
});

// Auto-generate meta descriptions from descriptions
watch(() => form.description_id, (newValue) => {
    if (!form.meta_description_id || form.meta_description_id === form.description_id) {
        form.meta_description_id = newValue.length > 160 ? newValue.substring(0, 157) + '...' : newValue;
    }
});

watch(() => form.description_en, (newValue) => {
    if (!form.meta_description_en || form.meta_description_en === form.description_en) {
        form.meta_description_en = newValue.length > 160 ? newValue.substring(0, 157) + '...' : newValue;
    }
});

const addTag = () => {
    if (newTag.value.trim() && !form.tags.includes(newTag.value.trim())) {
        form.tags.push(newTag.value.trim());
        newTag.value = '';
    }
};

const removeTag = (index: number) => {
    form.tags.splice(index, 1);
};

const handleImageChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        imageFile.value = file;
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            form.image = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const validateForm = () => {
    errors.value = {};
    
    if (!form.title_id.trim()) {
        errors.value.title_id = 'Indonesian title is required';
    }
    
    if (props.bilingualEnabled && !form.title_en.trim()) {
        errors.value.title_en = 'English title is required';
    }
    
    if (!form.category) {
        errors.value.category = 'Category is required';
    }
    
    return Object.keys(errors.value).length === 0;
};

const submitForm = async () => {
    if (!validateForm()) {
        toast({
            title: 'Validation Error',
            description: 'Please fix the form errors before submitting',
            variant: 'error',
        });
        return;
    }

    isLoading.value = true;
    
    try {
        const formData = new FormData();
        
        // Add form fields
        Object.entries(form).forEach(([key, value]) => {
            if (key === 'tags') {
                formData.append(key, JSON.stringify(value));
            } else if (key === 'is_published') {
                formData.append(key, value ? '1' : '0');
            } else if (value !== null && value !== undefined && key !== 'image') {
                formData.append(key, String(value));
            }
        });
        
        // Add image file if selected
        if (imageFile.value) {
            formData.append('image', imageFile.value);
        }
        
        const url = isEditing.value 
            ? `/api/system/education/${props.education!.id}`
            : '/api/system/education';
        
        const method = isEditing.value ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to save education content');
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
            description: error instanceof Error ? error.message : 'Failed to save education content',
            variant: 'error',
        });
    } finally {
        isLoading.value = false;
    }
};

const goBack = () => {
    router.visit('/education');
};
</script>

<template>
    <Head :title="pageTitle" />

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
                            <h1 class="text-3xl font-bold tracking-tight">{{ pageTitle }}</h1>
                            <p class="text-muted-foreground">
                                {{ isEditing ? 'Edit existing' : 'Create new' }} educational content
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
                            Bilingual Mode
                        </Button>
                        <Button @click="goBack" variant="outline">
                            Cancel
                        </Button>
                        <Button @click="submitForm" :disabled="isLoading">
                            <Save class="w-4 h-4 mr-2" />
                            {{ isLoading ? 'Saving...' : 'Save Content' }}
                        </Button>
                    </div>
                </div>
                <Tabs v-model:modelValue="activeTab" class="w-full">
                    <TabsList class="grid w-full grid-cols-4">
                        <TabsTrigger value="content" class="flex items-center gap-2">
                            <FileImage class="w-4 h-4" />
                            Content
                        </TabsTrigger>
                        <TabsTrigger value="settings" class="flex items-center gap-2">
                            <Tag class="w-4 h-4" />
                            Settings
                        </TabsTrigger>
                        <TabsTrigger value="seo" class="flex items-center gap-2">
                            <Globe class="w-4 h-4" />
                            SEO
                        </TabsTrigger>
                        <TabsTrigger value="publish" class="flex items-center gap-2">
                            <Eye class="w-4 h-4" />
                            Publish
                        </TabsTrigger>
                    </TabsList>

                    <!-- Content Tab -->
                    <TabsContent value="content" class="space-y-6">
                        <!-- Indonesian Content -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    🇮🇩 Indonesian Content
                                </CardTitle>
                                <CardDescription>
                                    Main content in Bahasa Indonesia
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label for="title_id">Title (Indonesian) *</Label>
                                    <Input
                                        id="title_id"
                                        v-model="form.title_id"
                                        placeholder="Enter Indonesian title"
                                        :class="{ 'border-red-500': errors.title_id }"
                                    />
                                    <p v-if="errors.title_id" class="text-red-500 text-sm mt-1">{{ errors.title_id }}</p>
                                </div>

                                <div>
                                    <Label for="description_id">Description (Indonesian)</Label>
                                    <Textarea
                                        id="description_id"
                                        v-model="form.description_id"
                                        placeholder="Enter Indonesian description"
                                        rows="3"
                                    />
                                </div>

                                <div>
                                    <Label for="content_id">Content (Indonesian)</Label>
                                    <Textarea
                                        id="content_id"
                                        v-model="form.content_id"
                                        placeholder="Enter Indonesian content (HTML supported)"
                                        rows="8"
                                    />
                                    <p class="text-sm text-gray-500 mt-1">You can use HTML tags for formatting</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- English Content (if bilingual enabled) -->
                        <Card v-if="props.bilingualEnabled">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    🇺🇸 English Content
                                </CardTitle>
                                <CardDescription>
                                    Content translated to English
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label for="title_en">Title (English) *</Label>
                                    <Input
                                        id="title_en"
                                        v-model="form.title_en"
                                        placeholder="Enter English title"
                                        :class="{ 'border-red-500': errors.title_en }"
                                    />
                                    <p v-if="errors.title_en" class="text-red-500 text-sm mt-1">{{ errors.title_en }}</p>
                                </div>

                                <div>
                                    <Label for="description_en">Description (English)</Label>
                                    <Textarea
                                        id="description_en"
                                        v-model="form.description_en"
                                        placeholder="Enter English description"
                                        rows="3"
                                    />
                                </div>

                                <div>
                                    <Label for="content_en">Content (English)</Label>
                                    <Textarea
                                        id="content_en"
                                        v-model="form.content_en"
                                        placeholder="Enter English content (HTML supported)"
                                        rows="8"
                                    />
                                    <p class="text-sm text-gray-500 mt-1">You can use HTML tags for formatting</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Image Upload -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <FileImage class="w-5 h-5" />
                                    Featured Image
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div>
                                        <Label for="image">Upload Image</Label>
                                        <Input
                                            id="image"
                                            type="file"
                                            accept="image/*"
                                            @change="handleImageChange"
                                        />
                                    </div>
                                    
                                    <div v-if="form.image" class="mt-4">
                                        <img 
                                            :src="form.image" 
                                            alt="Preview" 
                                            class="max-w-xs rounded-lg border"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- Settings Tab -->
                    <TabsContent value="settings" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Tag class="w-5 h-5" />
                                    Category & Tags
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label for="category">Category *</Label>
                                    <Select v-model:modelValue="form.category">
                                        <option v-for="(label, value) in categories" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </Select>
                                    <p v-if="errors.category" class="text-red-500 text-sm mt-1">{{ errors.category }}</p>
                                </div>

                                <div>
                                    <Label>Tags</Label>
                                    <div class="flex gap-2 mb-2">
                                        <Input
                                            v-model="newTag"
                                            placeholder="Add tag"
                                            @keyup.enter="addTag"
                                            class="flex-1"
                                        />
                                        <Button @click="addTag" type="button">
                                            <Hash class="w-4 h-4" />
                                        </Button>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge 
                                            v-for="(tag, index) in form.tags" 
                                            :key="index"
                                            variant="secondary"
                                            class="flex items-center gap-1"
                                        >
                                            {{ tag }}
                                            <button @click="removeTag(index)" class="ml-1 hover:text-red-600">
                                                <X class="w-3 h-3" />
                                            </button>
                                        </Badge>
                                    </div>
                                </div>

                                <div>
                                    <Label for="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        v-model.number="form.sort_order"
                                        type="number"
                                        placeholder="0"
                                        min="0"
                                    />
                                    <p class="text-sm text-gray-500 mt-1">Lower numbers appear first</p>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- SEO Tab -->
                    <TabsContent value="seo" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Globe class="w-5 h-5" />
                                    SEO Settings
                                </CardTitle>
                                <CardDescription>
                                    Optimize your content for search engines
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Indonesian SEO -->
                                <div class="space-y-4">
                                    <h4 class="font-medium flex items-center gap-2">🇮🇩 Indonesian SEO</h4>
                                    <div>
                                        <Label for="meta_title_id">Meta Title (Indonesian)</Label>
                                        <Input
                                            id="meta_title_id"
                                            v-model="form.meta_title_id"
                                            placeholder="Auto-generated from title"
                                            maxlength="60"
                                        />
                                        <p class="text-sm text-gray-500 mt-1">{{ form.meta_title_id.length }}/60 characters</p>
                                    </div>
                                    <div>
                                        <Label for="meta_description_id">Meta Description (Indonesian)</Label>
                                        <Textarea
                                            id="meta_description_id"
                                            v-model="form.meta_description_id"
                                            placeholder="Auto-generated from description"
                                            rows="3"
                                            maxlength="160"
                                        />
                                        <p class="text-sm text-gray-500 mt-1">{{ form.meta_description_id.length }}/160 characters</p>
                                    </div>
                                </div>

                                <!-- English SEO (if bilingual enabled) -->
                                <div v-if="props.bilingualEnabled" class="space-y-4">
                                    <h4 class="font-medium flex items-center gap-2">🇺🇸 English SEO</h4>
                                    <div>
                                        <Label for="meta_title_en">Meta Title (English)</Label>
                                        <Input
                                            id="meta_title_en"
                                            v-model="form.meta_title_en"
                                            placeholder="Auto-generated from title"
                                            maxlength="60"
                                        />
                                        <p class="text-sm text-gray-500 mt-1">{{ form.meta_title_en.length }}/60 characters</p>
                                    </div>
                                    <div>
                                        <Label for="meta_description_en">Meta Description (English)</Label>
                                        <Textarea
                                            id="meta_description_en"
                                            v-model="form.meta_description_en"
                                            placeholder="Auto-generated from description"
                                            rows="3"
                                            maxlength="160"
                                        />
                                        <p class="text-sm text-gray-500 mt-1">{{ form.meta_description_en.length }}/160 characters</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- Publish Tab -->
                    <TabsContent value="publish" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Eye class="w-5 h-5" />
                                    Publishing Options
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <Label for="is_published">Publish Content</Label>
                                        <p class="text-sm text-gray-500">Make this content visible to the public</p>
                                    </div>
                                    <Switch
                                        id="is_published"
                                        v-model:checked="form.is_published"
                                    />
                                </div>

                                <div v-if="form.is_published">
                                    <Label for="published_at">Publication Date</Label>
                                    <Input
                                        id="published_at"
                                        v-model="form.published_at"
                                        type="datetime-local"
                                    />
                                    <p class="text-sm text-gray-500 mt-1">Leave empty to publish immediately</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Content Preview</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <h3 class="font-semibold text-lg mb-2">{{ form.title_id || 'Untitled' }}</h3>
                                    <p class="text-gray-600 mb-2">{{ form.description_id || 'No description' }}</p>
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <Badge>{{ categories[form.category] || 'Category' }}</Badge>
                                        <span>•</span>
                                        <Badge v-if="form.is_published" variant="default">Published</Badge>
                                        <Badge v-else variant="secondary">Draft</Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>