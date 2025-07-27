<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Save, Loader2, Globe, Languages } from 'lucide-vue-next';

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

const form = reactive({
    bilingual_enabled: false,
    default_language: 'id',
    language_switcher_enabled: true,
    auto_detect_language: false,
});

const languages = [
    { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' },
    { code: 'en', name: 'English', flag: '🇺🇸' },
];

// Watch for configuration changes and update form
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        form.bilingual_enabled = newConfigs.bilingual_enabled?.value || false;
        form.default_language = newConfigs.default_language?.value || 'id';
        form.language_switcher_enabled = newConfigs.language_switcher_enabled?.value || true;
        form.auto_detect_language = newConfigs.auto_detect_language?.value || false;
    }
}, { immediate: true, deep: true });

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    if (props.configurations.bilingual_enabled?.value !== form.bilingual_enabled) {
        changes.push({ key: 'bilingual_enabled', value: form.bilingual_enabled, type: 'boolean' });
    }

    if (props.configurations.default_language?.value !== form.default_language) {
        changes.push({ key: 'default_language', value: form.default_language, type: 'string' });
    }

    if (props.configurations.language_switcher_enabled?.value !== form.language_switcher_enabled) {
        changes.push({ key: 'language_switcher_enabled', value: form.language_switcher_enabled, type: 'boolean' });
    }

    if (props.configurations.auto_detect_language?.value !== form.auto_detect_language) {
        changes.push({ key: 'auto_detect_language', value: form.auto_detect_language, type: 'boolean' });
    }

    // Only save if there are changes
    if (changes.length > 0) {
        emit('bulkSave', 'language', changes);
    }
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Languages class="w-5 h-5" />
                Bilingual Settings
            </CardTitle>
            <CardDescription>
                Configure bilingual support for Indonesian and English languages
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <div class="grid gap-6">
                <!-- Enable Bilingual -->
                <div class="flex items-center justify-between space-x-2">
                    <div class="space-y-1">
                        <Label for="bilingual_enabled">Enable Bilingual Support</Label>
                        <p class="text-sm text-muted-foreground">
                            Allow users to switch between Indonesian and English
                        </p>
                    </div>
                    <Switch
                        id="bilingual_enabled"
                        v-model:checked="form.bilingual_enabled"
                        :disabled="isLoading"
                    />
                </div>

                <!-- Available Languages Display -->
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label>Available Languages</Label>
                        <p class="text-sm text-muted-foreground">
                            Your application supports these two languages
                        </p>
                    </div>

                    <!-- Languages List -->
                    <div class="grid gap-3">
                        <div 
                            v-for="language in languages" 
                            :key="language.code"
                            class="flex items-center justify-between p-4 border rounded-lg bg-muted/5"
                            :class="{ 'ring-2 ring-primary ring-offset-2': form.default_language === language.code }"
                        >
                            <div class="flex items-center gap-3">
                                <div class="text-2xl">{{ language.flag }}</div>
                                <div>
                                    <p class="font-medium">{{ language.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        Language code: {{ language.code }}
                                        <span v-if="form.default_language === language.code" class="ml-2 text-primary font-medium">
                                            (Default)
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Default Language -->
                <div class="space-y-2">
                    <Label for="default_language">Default Language</Label>
                    <Select v-model:modelValue="form.default_language" :disabled="isLoading">
                        <option 
                            v-for="language in languages" 
                            :key="language.code" 
                            :value="language.code"
                        >
                            {{ language.flag }} {{ language.name }} ({{ language.code }})
                        </option>
                    </Select>
                    <p class="text-sm text-muted-foreground">
                        The default language when no user preference is set
                    </p>
                </div>

                <!-- Language Switcher -->
                <div class="flex items-center justify-between space-x-2">
                    <div class="space-y-1">
                        <Label for="language_switcher_enabled">Show Language Switcher</Label>
                        <p class="text-sm text-muted-foreground">
                            Display language switcher in the frontend interface
                        </p>
                    </div>
                    <Switch
                        id="language_switcher_enabled"
                        v-model:checked="form.language_switcher_enabled"
                        :disabled="isLoading || !form.bilingual_enabled"
                    />
                </div>

                <!-- Auto-detect Language -->
                <div class="flex items-center justify-between space-x-2">
                    <div class="space-y-1">
                        <Label for="auto_detect_language">Auto-detect Language</Label>
                        <p class="text-sm text-muted-foreground">
                            Automatically detect user's preferred language from browser settings
                        </p>
                    </div>
                    <Switch
                        id="auto_detect_language"
                        v-model:checked="form.auto_detect_language"
                        :disabled="isLoading || !form.bilingual_enabled"
                    />
                </div>

                <!-- Bilingual Features Info -->
                <div v-if="form.bilingual_enabled" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <Globe class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <h4 class="font-medium text-blue-900 mb-1">Bilingual Features Enabled</h4>
                            <p class="text-sm text-blue-700">
                                When enabled, your application will support both Indonesian and English languages. 
                                Users can switch between languages using the language switcher in the frontend.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
        </CardContent>
    </Card>
</template>