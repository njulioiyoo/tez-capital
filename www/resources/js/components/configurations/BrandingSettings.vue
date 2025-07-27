<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Upload, Save, Loader2, Image, X } from 'lucide-vue-next';

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

const props = defineProps<Props>();
const emit = defineEmits<{
    save: [group: string, key: string, value: any, type: string];
    update: [configId: number, group: string, key: string, value: any, type: string];
    bulkSave: [group: string, changes: Array<{key: string, value: any, type: string}>];
}>();

const companyName = ref<string>('');
const logoFile = ref<File | null>(null);
const logoPreview = ref<string>('');
const logoDarkFile = ref<File | null>(null);
const logoDarkPreview = ref<string>('');
const faviconFile = ref<File | null>(null);
const faviconPreview = ref<string>('');

// Watch for configuration changes and update previews
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        companyName.value = newConfigs.company_name?.value || 'Tez Capital Dashboard';
        logoPreview.value = newConfigs.company_logo?.value || '';
        logoDarkPreview.value = newConfigs.company_logo_dark?.value || '';
        faviconPreview.value = newConfigs.favicon?.value || '';
    }
}, { immediate: true, deep: true });

const handleLogoUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        logoFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleLogoDarkUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        logoDarkFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoDarkPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleFaviconUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        faviconFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            faviconPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const clearLogo = () => {
    logoFile.value = null;
    logoPreview.value = '';
};

const clearLogoDark = () => {
    logoDarkFile.value = null;
    logoDarkPreview.value = '';
};

const clearFavicon = () => {
    faviconFile.value = null;
    faviconPreview.value = '';
};

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    console.log('Checking branding changes:');
    console.log('Company name:', companyName.value);
    console.log('Logo file:', logoFile.value);
    console.log('Dark logo file:', logoDarkFile.value);
    console.log('Favicon file:', faviconFile.value);

    // Check company name changes
    if (companyName.value !== (props.configurations.company_name?.value || 'Tez Capital Dashboard')) {
        changes.push({ key: 'company_name', value: companyName.value, type: 'string' });
    }

    if (logoFile.value) {
        changes.push({ key: 'company_logo', value: logoFile.value, type: 'file' });
    }

    if (logoDarkFile.value) {
        changes.push({ key: 'company_logo_dark', value: logoDarkFile.value, type: 'file' });
    }

    if (faviconFile.value) {
        changes.push({ key: 'favicon', value: faviconFile.value, type: 'file' });
    }

    console.log('Branding changes detected:', changes);

    // Only save if there are changes
    if (changes.length > 0) {
        emit('bulkSave', 'branding', changes);
        
        // Clear file refs after successful save initiation
        logoFile.value = null;
        logoDarkFile.value = null;
        faviconFile.value = null;
    } else {
        console.log('No branding changes to save');
    }
};

const hasChanges = () => {
    const nameChanged = companyName.value !== (props.configurations.company_name?.value || 'Tez Capital Dashboard');
    return nameChanged || logoFile.value || logoDarkFile.value || faviconFile.value;
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Branding & Assets</CardTitle>
            <CardDescription>
                Upload and manage your brand assets including logos and favicon
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Company Name -->
            <div class="space-y-2">
                <Label for="company_name">Company Name</Label>
                <Input 
                    id="company_name"
                    v-model="companyName"
                    placeholder="Enter company name"
                    class="w-full"
                />
                <p class="text-sm text-muted-foreground">
                    Name displayed in login page and sidebar
                </p>
            </div>

            <!-- Company Logo -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label>Company Logo</Label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div v-if="logoPreview" class="space-y-4">
                            <div class="flex items-center justify-center">
                                <img 
                                    :src="logoPreview" 
                                    alt="Logo Preview" 
                                    class="max-h-24 max-w-48 object-contain"
                                />
                            </div>
                            <div class="flex justify-center gap-2">
                                <Button size="sm" variant="outline" @click="clearLogo">
                                    <X class="w-4 h-4 mr-2" />
                                    Remove
                                </Button>
                                <Button size="sm" variant="outline" @click="$refs.logoInput.click()">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Change
                                </Button>
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <Image class="mx-auto h-12 w-12 text-gray-400" />
                            <div class="mt-2">
                                <Button variant="outline" @click="$refs.logoInput.click()">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Upload Logo
                                </Button>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">PNG, JPG, SVG up to 5MB</p>
                        </div>
                        <input
                            ref="logoInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleLogoUpload"
                        />
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Main logo for light theme (recommended size: 200x60px)
                    </p>
                </div>
            </div>

            <!-- Dark Theme Logo -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label>Dark Theme Logo</Label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-900">
                        <div v-if="logoDarkPreview" class="space-y-4">
                            <div class="flex items-center justify-center">
                                <img 
                                    :src="logoDarkPreview" 
                                    alt="Dark Logo Preview" 
                                    class="max-h-24 max-w-48 object-contain"
                                />
                            </div>
                            <div class="flex justify-center gap-2">
                                <Button size="sm" variant="outline" @click="clearLogoDark">
                                    <X class="w-4 h-4 mr-2" />
                                    Remove
                                </Button>
                                <Button size="sm" variant="outline" @click="$refs.logoDarkInput.click()">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Change
                                </Button>
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <Image class="mx-auto h-12 w-12 text-white" />
                            <div class="mt-2">
                                <Button variant="outline" @click="$refs.logoDarkInput.click()">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Upload Dark Logo
                                </Button>
                            </div>
                            <p class="text-sm text-gray-300 mt-2">PNG, JPG, SVG up to 5MB</p>
                        </div>
                        <input
                            ref="logoDarkInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleLogoDarkUpload"
                        />
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Logo version for dark theme (recommended size: 200x60px)
                    </p>
                </div>
            </div>

            <!-- Favicon -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label>Favicon</Label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div v-if="faviconPreview" class="space-y-4">
                            <div class="flex items-center justify-center">
                                <img 
                                    :src="faviconPreview" 
                                    alt="Favicon Preview" 
                                    class="w-8 h-8 object-contain"
                                />
                            </div>
                            <div class="flex justify-center gap-2">
                                <Button size="sm" variant="outline" @click="clearFavicon">
                                    <X class="w-4 h-4 mr-2" />
                                    Remove
                                </Button>
                                <Button size="sm" variant="outline" @click="$refs.faviconInput.click()">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Change
                                </Button>
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <Image class="mx-auto h-8 w-8 text-gray-400" />
                            <div class="mt-2">
                                <Button variant="outline" @click="$refs.faviconInput.click()">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Upload Favicon
                                </Button>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">ICO, PNG 16x16 or 32x32</p>
                        </div>
                        <input
                            ref="faviconInput"
                            type="file"
                            accept="image/*,.ico"
                            class="hidden"
                            @change="handleFaviconUpload"
                        />
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Website favicon that appears in browser tabs (recommended: 32x32px)
                    </p>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <Button 
                    @click="saveSettings" 
                    :disabled="!hasChanges() || isSaving || isLoading"
                    class="min-w-[120px]"
                >
                    <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                    <Save v-else class="w-4 h-4 mr-2" />
                    {{ isSaving ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </CardContent>
    </Card>
</template>