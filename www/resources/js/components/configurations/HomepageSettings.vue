<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Upload, Save, Loader2, Image, X, Plus, Trash2, Move } from 'lucide-vue-next';

interface Configuration {
    [key: string]: {
        value: any;
        type: string;
        description: string;
        is_public: boolean;
    };
}

interface Props {
    configurations: Configuration;
    isLoading: boolean;
    isSaving: boolean;
}

interface Banner {
    image: string;
    title: string;
    subtitle: string;
    link: string;
}

interface Feature {
    title: string;
    description: string;
    icon: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    save: [group: string, key: string, value: any, type: string];
    update: [configId: number, group: string, key: string, value: any, type: string];
    bulkSave: [group: string, changes: Array<{key: string, value: any, type: string}>];
}>();

const form = reactive({
    hero_title: '',
    hero_subtitle: '',
    banners: [] as Banner[],
    features: [] as Feature[],
});

const bannerFiles = ref<File[]>([]);

// Watch for configuration changes and update form
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        form.hero_title = newConfigs.homepage_hero_title?.value || '';
        form.hero_subtitle = newConfigs.homepage_hero_subtitle?.value || '';
        
        try {
            form.banners = JSON.parse(newConfigs.homepage_banners?.value || '[]');
        } catch {
            form.banners = [];
        }
        
        try {
            form.features = JSON.parse(newConfigs.homepage_features?.value || '[]');
        } catch {
            form.features = [
                { title: 'Aman & Terpercaya', description: 'Sistem keamanan berlapis dengan teknologi enkripsi terdepan', icon: 'shield' },
                { title: 'Bunga Kompetitif', description: 'Dapatkan return investasi hingga 15% per tahun', icon: 'trending-up' },
                { title: 'Proses Cepat', description: 'Approval dalam 24 jam dengan persyaratan yang mudah', icon: 'clock' }
            ];
        }
    }
}, { immediate: true, deep: true });

const addBanner = () => {
    form.banners.push({
        image: '',
        title: '',
        subtitle: '',
        link: ''
    });
};

const removeBanner = (index: number) => {
    form.banners.splice(index, 1);
    bannerFiles.value.splice(index, 1);
};

const handleBannerUpload = (event: Event, index: number) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        bannerFiles.value[index] = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            form.banners[index].image = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const addFeature = () => {
    form.features.push({
        title: '',
        description: '',
        icon: 'star'
    });
};

const removeFeature = (index: number) => {
    form.features.splice(index, 1);
};

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    console.log('Checking homepage changes:');
    console.log('Current hero_title:', props.configurations.homepage_hero_title?.value, 'Form value:', form.hero_title);
    console.log('Current hero_subtitle:', props.configurations.homepage_hero_subtitle?.value, 'Form value:', form.hero_subtitle);
    console.log('Current banners:', props.configurations.homepage_banners?.value, 'Form value:', JSON.stringify(form.banners));
    console.log('Current features:', props.configurations.homepage_features?.value, 'Form value:', JSON.stringify(form.features));

    if (props.configurations.homepage_hero_title?.value !== form.hero_title) {
        changes.push({ key: 'homepage_hero_title', value: form.hero_title, type: 'string' });
    }

    if (props.configurations.homepage_hero_subtitle?.value !== form.hero_subtitle) {
        changes.push({ key: 'homepage_hero_subtitle', value: form.hero_subtitle, type: 'text' });
    }

    const currentBannersStr = props.configurations.homepage_banners?.value || '[]';
    const formBannersStr = JSON.stringify(form.banners);
    if (currentBannersStr !== formBannersStr) {
        changes.push({ key: 'homepage_banners', value: form.banners, type: 'json' });
    }

    const currentFeaturesStr = props.configurations.homepage_features?.value || '[]';
    const formFeaturesStr = JSON.stringify(form.features);
    if (currentFeaturesStr !== formFeaturesStr) {
        changes.push({ key: 'homepage_features', value: form.features, type: 'json' });
    }

    console.log('Homepage changes detected:', changes);

    // Only save if there are changes
    if (changes.length > 0) {
        emit('bulkSave', 'homepage', changes);
    } else {
        console.log('No homepage changes to save');
    }
};

const iconOptions = [
    'shield', 'trending-up', 'clock', 'star', 'heart', 'award', 'check-circle',
    'users', 'globe', 'lock', 'zap', 'target', 'thumbs-up', 'gift'
];
</script>

<template>
    <div class="space-y-6">
        <!-- Hero Section -->
        <Card>
            <CardHeader>
                <CardTitle>Hero Section</CardTitle>
                <CardDescription>
                    Main homepage banner content that visitors see first
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="space-y-2">
                    <Label for="hero_title">Hero Title</Label>
                    <Input
                        id="hero_title"
                        v-model="form.hero_title"
                        placeholder="Enter hero title"
                        :disabled="isLoading"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="hero_subtitle">Hero Subtitle</Label>
                    <Textarea
                        id="hero_subtitle"
                        v-model="form.hero_subtitle"
                        placeholder="Enter hero subtitle"
                        :disabled="isLoading"
                        rows="3"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Banner Slides -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center justify-between">
                    Banner Slides
                    <Button @click="addBanner" variant="outline" size="sm">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Banner
                    </Button>
                </CardTitle>
                <CardDescription>
                    Image slides for homepage carousel/banner section
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="form.banners.length === 0" class="text-center py-8 text-muted-foreground">
                    No banners configured. Click "Add Banner" to create one.
                </div>

                <div v-for="(banner, index) in form.banners" :key="index" class="border rounded-lg p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <Badge variant="outline">Banner {{ index + 1 }}</Badge>
                        <Button @click="removeBanner(index)" variant="destructive" size="sm">
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Banner Image</Label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                                    <div v-if="banner.image" class="space-y-2">
                                        <img :src="banner.image" alt="Banner Preview" class="w-full h-32 object-cover rounded" />
                                        <Button size="sm" variant="outline" @click="$refs[`bannerInput${index}`][0].click()">
                                            <Upload class="w-4 h-4 mr-2" />
                                            Change Image
                                        </Button>
                                    </div>
                                    <div v-else class="text-center">
                                        <Image class="mx-auto h-8 w-8 text-gray-400" />
                                        <Button size="sm" variant="outline" @click="$refs[`bannerInput${index}`][0].click()">
                                            <Upload class="w-4 h-4 mr-2" />
                                            Upload Image
                                        </Button>
                                    </div>
                                    <input
                                        :ref="`bannerInput${index}`"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleBannerUpload($event, index)"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Banner Title</Label>
                                <Input
                                    v-model="banner.title"
                                    placeholder="Enter banner title"
                                    :disabled="isLoading"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label>Banner Subtitle</Label>
                                <Textarea
                                    v-model="banner.subtitle"
                                    placeholder="Enter banner subtitle"
                                    :disabled="isLoading"
                                    rows="2"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label>Link URL (Optional)</Label>
                                <Input
                                    v-model="banner.link"
                                    placeholder="https://example.com"
                                    :disabled="isLoading"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Features Section -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center justify-between">
                    Features Section
                    <Button @click="addFeature" variant="outline" size="sm">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Feature
                    </Button>
                </CardTitle>
                <CardDescription>
                    Key features and benefits displayed on homepage
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-for="(feature, index) in form.features" :key="index" class="border rounded-lg p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <Badge variant="outline">Feature {{ index + 1 }}</Badge>
                        <Button @click="removeFeature(index)" variant="destructive" size="sm">
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label>Feature Title</Label>
                            <Input
                                v-model="feature.title"
                                placeholder="Enter feature title"
                                :disabled="isLoading"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Icon</Label>
                            <select 
                                v-model="feature.icon" 
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                                :disabled="isLoading"
                            >
                                <option v-for="icon in iconOptions" :key="icon" :value="icon">
                                    {{ icon }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label>Description</Label>
                            <Textarea
                                v-model="feature.description"
                                placeholder="Enter feature description"
                                :disabled="isLoading"
                                rows="2"
                            />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="flex justify-end pt-4">
            <Button 
                @click="saveSettings" 
                :disabled="isSaving || isLoading"
                class="min-w-[120px]"
            >
                <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                <Save v-else class="w-4 h-4 mr-2" />
                {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </Button>
        </div>
    </div>
</template>