<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Save, Loader2 } from 'lucide-vue-next';

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
    app_name: '',
    app_description: '',
    app_timezone: 'Asia/Jakarta',
});

const timezones = [
    { value: 'Asia/Jakarta', label: 'Asia/Jakarta (WIB)' },
    { value: 'Asia/Makassar', label: 'Asia/Makassar (WITA)' },
    { value: 'Asia/Jayapura', label: 'Asia/Jayapura (WIT)' },
    { value: 'UTC', label: 'UTC' },
];

// Watch for configuration changes and update form
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        form.app_name = newConfigs.app_name?.value || '';
        form.app_description = newConfigs.app_description?.value || '';
        form.app_timezone = newConfigs.app_timezone?.value || 'Asia/Jakarta';
    }
}, { immediate: true, deep: true });

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    console.log('Checking changes:');
    console.log('Current app_name:', props.configurations.app_name?.value, 'Form value:', form.app_name);
    console.log('Current app_description:', props.configurations.app_description?.value, 'Form value:', form.app_description);
    console.log('Current app_timezone:', props.configurations.app_timezone?.value, 'Form value:', form.app_timezone);

    if (props.configurations.app_name?.value !== form.app_name) {
        changes.push({ key: 'app_name', value: form.app_name, type: 'string' });
    }

    if (props.configurations.app_description?.value !== form.app_description) {
        changes.push({ key: 'app_description', value: form.app_description, type: 'text' });
    }

    if (props.configurations.app_timezone?.value !== form.app_timezone) {
        changes.push({ key: 'app_timezone', value: form.app_timezone, type: 'string' });
    }

    console.log('Changes detected:', changes);

    // Only save if there are changes
    if (changes.length > 0) {
        // Emit bulk save for all changes at once
        emit('bulkSave', 'general', changes);
    } else {
        console.log('No changes to save');
    }
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>General Settings</CardTitle>
            <CardDescription>
                Basic application settings and configurations
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <div class="grid gap-4">
                <div class="space-y-2">
                    <Label for="app_name">Application Name</Label>
                    <Input
                        id="app_name"
                        v-model="form.app_name"
                        placeholder="Enter application name"
                        :disabled="isLoading"
                    />
                    <p class="text-sm text-muted-foreground">
                        The name of your application that will be displayed throughout the site
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="app_description">Application Description</Label>
                    <Textarea
                        id="app_description"
                        v-model="form.app_description"
                        placeholder="Enter application description"
                        :disabled="isLoading"
                        :rows="3"
                    />
                    <p class="text-sm text-muted-foreground">
                        A brief description of your application for SEO and marketing purposes
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="app_timezone">Application Timezone</Label>
                    <Select v-model:modelValue="form.app_timezone" :disabled="isLoading">
                        <option value="" disabled>Select timezone</option>
                        <option 
                            v-for="timezone in timezones" 
                            :key="timezone.value" 
                            :value="timezone.value"
                        >
                            {{ timezone.label }}
                        </option>
                    </Select>
                    <p class="text-sm text-muted-foreground">
                        Default timezone for the application
                    </p>
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