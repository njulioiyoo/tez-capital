<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, nextTick } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { toast } from '@/components/ui/toast';
import { 
    Settings, 
    Palette, 
    Home, 
    CreditCard, 
    Wrench, 
    Phone,
    Globe,
    Save,
    RefreshCw
} from 'lucide-vue-next';
import GeneralSettings from '@/components/configurations/GeneralSettings.vue';
import BrandingSettings from '@/components/configurations/BrandingSettings.vue';
import HomepageSettings from '@/components/configurations/HomepageSettings.vue';
import CreditSettings from '@/components/configurations/CreditSettings.vue';
import MaintenanceSettings from '@/components/configurations/MaintenanceSettings.vue';
import ContactSettings from '@/components/configurations/ContactSettings.vue';
import LanguageSettings from '@/components/configurations/LanguageSettings.vue';

interface Configuration {
    [key: string]: {
        value: any;
        type: string;
        description: string;
        is_public: boolean;
    };
}

interface ConfigurationData {
    [group: string]: Configuration;
}

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'System', href: '/dashboard' },
    { label: 'Configurations', href: '/configurations' }
];

const activeTab = ref('general');
const configurations = ref<ConfigurationData>({});
const isLoading = ref(false);
const isSaving = ref(false);

// Helper function to get CSRF token
const getCsrfToken = () => {
    // Try meta tag first (most reliable for Laravel)
    const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (tokenFromMeta) {
        return tokenFromMeta;
    }
    
    // Fallback to cookie (decode properly)
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
    
    console.warn('No CSRF token found in meta tag or cookie');
    return '';
};

const loadConfigurations = async () => {
    isLoading.value = true;
    try {
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            console.warn('CSRF token not found for loading configurations');
        }

        const response = await fetch('/api/system/configurations', {
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        configurations.value = data.data;
    } catch (error) {
        console.error('Error loading configurations:', error);
        toast({
            title: 'Error',
            description: 'Failed to load configurations',
            variant: 'error',
        });
    } finally {
        isLoading.value = false;
    }
};

const saveOrUpdateConfiguration = async (group: string, key: string, value: any, type: string = 'string') => {
    return saveBulkConfigurations(group, [{ key, value, type }]);
};

const saveBulkConfigurations = async (group: string, changes: Array<{key: string, value: any, type: string}>) => {
    isSaving.value = true;
    try {
        // Check if any file uploads are involved
        const hasFileUploads = changes.some(change => 
            change.type === 'file' && change.value instanceof File
        );

        if (hasFileUploads) {
            // Handle file uploads individually
            for (const change of changes) {
                if (change.type === 'file' && change.value instanceof File) {
                    const formData = new FormData();
                    formData.append('file', change.value);
                    formData.append('key', change.key);
                    formData.append('type', change.type);
                    formData.append('group', group);
                    formData.append('description', '');
                    formData.append('is_public', '1');
                    
                    const csrfToken = getCsrfToken();
                    if (!csrfToken) {
                        throw new Error('CSRF token not found. Please refresh the page and try again.');
                    }

                    const response = await fetch(`/api/system/configurations`, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        console.error('File upload error:', {
                            status: response.status,
                            statusText: response.statusText,
                            data: errorData
                        });
                        
                        if (response.status === 419 || (errorData.message && errorData.message.includes('CSRF'))) {
                            throw new Error('Session expired or CSRF token mismatch. Please refresh the page and try again.');
                        }
                        
                        throw new Error(errorData.message || `Failed to save ${change.key}`);
                    }
                }
            }
            
            // Handle non-file changes with bulk update
            const nonFileChanges = changes.filter(change => 
                !(change.type === 'file' && change.value instanceof File)
            );
            
            if (nonFileChanges.length > 0) {
                const result = await saveBulkNonFileConfigurations(group, nonFileChanges);
                
                // Update local state with server response for non-file changes
                if (result && result.data) {
                    console.log('Updating non-file changes with server response:', result.data);
                    for (const config of result.data) {
                        if (!configurations.value[config.group]) {
                            configurations.value[config.group] = {};
                        }
                        
                        console.log(`Updating non-file ${config.group}.${config.key}:`, {
                            old: configurations.value[config.group][config.key]?.value,
                            new: config.value
                        });
                        
                        // Force Vue reactivity by creating new object reference
                        configurations.value[config.group] = {
                            ...configurations.value[config.group],
                            [config.key]: {
                                value: config.value,
                                type: config.type,
                                description: config.description,
                                is_public: config.is_public
                            }
                        };
                    }
                }
                
                // Force Vue to update DOM  
                await nextTick();
            }
            
            // For file uploads, we need to reload configurations since file handling is different
            if (changes.some(change => change.type === 'file' && change.value instanceof File)) {
                await loadConfigurations();
            }
        } else {
            // All non-file data, use bulk update
            const result = await saveBulkNonFileConfigurations(group, changes);
            
            // Update local state with server response data for accuracy
            if (result && result.data) {
                console.log('Updating local state with server response:', result.data);
                for (const config of result.data) {
                    if (!configurations.value[config.group]) {
                        configurations.value[config.group] = {};
                    }
                    
                    console.log(`Updating ${config.group}.${config.key}:`, {
                        old: configurations.value[config.group][config.key]?.value,
                        new: config.value
                    });
                    
                    // Force Vue reactivity by creating new object reference
                    configurations.value[config.group] = {
                        ...configurations.value[config.group],
                        [config.key]: {
                            value: config.value,
                            type: config.type,
                            description: config.description,
                            is_public: config.is_public
                        }
                    };
                }
                
                console.log('Local state after update:', configurations.value[group]);
                
                // Force Vue to update DOM
                await nextTick();
            } else {
                // Fallback: reload all configurations if no response data
                console.log('No response data, reloading configurations');
                await loadConfigurations();
            }
        }
        
        toast({
            title: 'Success',
            description: `${changes.length} configuration${changes.length > 1 ? 's' : ''} saved successfully`,
            variant: 'success',
        });
    } catch (error) {
        console.error('Error saving configurations:', error);
        
        if (error.message && error.message.includes('CSRF token mismatch')) {
            toast({
                title: 'Session Expired',
                description: 'Your session has expired. The page will reload automatically.',
                variant: 'error',
            });
            
            // Reload page after short delay to show toast
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            toast({
                title: 'Error',
                description: error.message || 'Failed to save configurations',
                variant: 'error',
            });
        }
    } finally {
        isSaving.value = false;
    }
};

// Helper function to refresh CSRF token from server
const refreshCsrfToken = async () => {
    try {
        const response = await fetch('/api/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.csrf_token) {
                // Update meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                }
                return data.csrf_token;
            }
        }
    } catch (e) {
        console.warn('Failed to refresh CSRF token:', e);
    }
    return null;
};

const saveBulkNonFileConfigurations = async (group: string, changes: Array<{key: string, value: any, type: string}>) => {
    const payload = {
        configurations: changes.map(change => ({
            key: change.key,
            value: typeof change.value === 'object' ? JSON.stringify(change.value) : String(change.value),
            type: change.type,
            group: group,
            description: '',
            is_public: true
        }))
    };

    console.log('Saving bulk configurations:', payload);

    let csrfToken = getCsrfToken();
    if (!csrfToken) {
        console.log('No CSRF token found, attempting to refresh...');
        csrfToken = await refreshCsrfToken();
        if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
        }
    }
    
    console.log('Bulk update CSRF token:', csrfToken);

    const makeRequest = async (token: string) => {
        const response = await fetch(`/api/system/configurations/bulk-update`, {
            method: 'POST',
            body: JSON.stringify(payload),
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
            console.error('API Response Error:', {
                status: response.status,
                statusText: response.statusText,
                data: errorData
            });
            
            if (response.status === 419 || (errorData.message && errorData.message.includes('CSRF'))) {
                throw new Error('CSRF_TOKEN_MISMATCH');
            }
            
            throw new Error(errorData.message || 'Failed to save configurations');
        }

        return await response.json();
    };

    try {
        return await makeRequest(csrfToken);
    } catch (error) {
        if (error.message === 'CSRF_TOKEN_MISMATCH') {
            console.log('CSRF token mismatch, refreshing token and retrying...');
            const newToken = await refreshCsrfToken();
            if (newToken) {
                return await makeRequest(newToken);
            }
            throw new Error('CSRF token mismatch. Please refresh the page and try again.');
        }
        throw error;
    }
};

// Test function for debugging toast
const testToast = () => {
    console.log('Test toast clicked'); // Debug log
    toast({
        title: 'Test Toast',
        description: 'This is a test toast notification',
        variant: 'success'
    });
    
    // Test error toast after 2 seconds
    setTimeout(() => {
        toast({
            title: 'Error Test',
            description: 'This is a test error notification',
            variant: 'error'
        });
    }, 2000);
};

// Alias for backward compatibility
const saveConfiguration = saveOrUpdateConfiguration;
const updateConfiguration = (configId: number, group: string, key: string, value: any, type: string = 'string') => {
    return saveOrUpdateConfiguration(group, key, value, type);
};

onMounted(() => {
    loadConfigurations();
});
</script>

<template>
    <Head title="Website Configurations" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Website Configurations
                </h2>
                <div class="flex gap-2">
                    <Button 
                        @click="loadConfigurations" 
                        variant="outline" 
                        size="sm"
                        :disabled="isLoading"
                    >
                        <RefreshCw :class="{ 'animate-spin': isLoading }" class="w-4 h-4 mr-2" />
                        Refresh
                    </Button>
                    <Button 
                        @click="testToast" 
                        variant="outline" 
                        size="sm"
                    >
                        Test Toast
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Settings class="w-5 h-5" />
                            Website Configuration Management
                        </CardTitle>
                        <CardDescription>
                            Manage your website settings, branding, content, and system configurations
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs v-model:modelValue="activeTab" class="w-full">
                            <TabsList class="grid w-full grid-cols-7">
                                <TabsTrigger value="general" class="flex items-center gap-2">
                                    <Settings class="w-4 h-4" />
                                    General
                                </TabsTrigger>
                                <TabsTrigger value="branding" class="flex items-center gap-2">
                                    <Palette class="w-4 h-4" />
                                    Branding
                                </TabsTrigger>
                                <TabsTrigger value="homepage" class="flex items-center gap-2">
                                    <Home class="w-4 h-4" />
                                    Homepage
                                </TabsTrigger>
                                <TabsTrigger value="credit" class="flex items-center gap-2">
                                    <CreditCard class="w-4 h-4" />
                                    Credit
                                </TabsTrigger>
                                <TabsTrigger value="maintenance" class="flex items-center gap-2">
                                    <Wrench class="w-4 h-4" />
                                    Maintenance
                                </TabsTrigger>
                                <TabsTrigger value="contact" class="flex items-center gap-2">
                                    <Phone class="w-4 h-4" />
                                    Contact
                                </TabsTrigger>
                                <TabsTrigger value="language" class="flex items-center gap-2">
                                    <Globe class="w-4 h-4" />
                                    Language
                                </TabsTrigger>
                            </TabsList>

                            <div class="mt-6">
                                <TabsContent value="general" class="space-y-4">
                                    <GeneralSettings 
                                        :configurations="configurations.general || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>

                                <TabsContent value="branding" class="space-y-4">
                                    <BrandingSettings 
                                        :configurations="configurations.branding || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>

                                <TabsContent value="homepage" class="space-y-4">
                                    <HomepageSettings 
                                        :configurations="configurations.homepage || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>

                                <TabsContent value="credit" class="space-y-4">
                                    <CreditSettings 
                                        :configurations="configurations.credit || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>

                                <TabsContent value="maintenance" class="space-y-4">
                                    <MaintenanceSettings 
                                        :configurations="configurations.maintenance || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>

                                <TabsContent value="contact" class="space-y-4">
                                    <ContactSettings 
                                        :configurations="configurations.contact || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>

                                <TabsContent value="language" class="space-y-4">
                                    <LanguageSettings 
                                        :configurations="configurations.language || {}"
                                        :is-loading="isLoading"
                                        :is-saving="isSaving"
                                        @save="saveConfiguration"
                                        @update="updateConfiguration"
                                        @bulk-save="saveBulkConfigurations"
                                    />
                                </TabsContent>
                            </div>
                        </Tabs>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>