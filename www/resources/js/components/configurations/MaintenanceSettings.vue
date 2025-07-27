<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Save, Loader2, AlertTriangle, CheckCircle, Clock } from 'lucide-vue-next';

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
    maintenance_mode: false,
    maintenance_message: '',
    maintenance_end_time: '',
});

// Watch for configuration changes and update form
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        form.maintenance_mode = newConfigs.maintenance_mode?.value || false;
        form.maintenance_message = newConfigs.maintenance_message?.value || 'Situs sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.';
        form.maintenance_end_time = newConfigs.maintenance_end_time?.value || '';
    }
}, { immediate: true, deep: true });

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    console.log('Checking maintenance changes:');
    console.log('Current maintenance_mode:', props.configurations.maintenance_mode?.value, 'Form value:', form.maintenance_mode);
    console.log('Current maintenance_message:', props.configurations.maintenance_message?.value, 'Form value:', form.maintenance_message);
    console.log('Current maintenance_end_time:', props.configurations.maintenance_end_time?.value, 'Form value:', form.maintenance_end_time);

    if (props.configurations.maintenance_mode?.value !== form.maintenance_mode) {
        changes.push({ key: 'maintenance_mode', value: form.maintenance_mode, type: 'boolean' });
    }

    if (props.configurations.maintenance_message?.value !== form.maintenance_message) {
        changes.push({ key: 'maintenance_message', value: form.maintenance_message, type: 'text' });
    }

    if (props.configurations.maintenance_end_time?.value !== form.maintenance_end_time) {
        changes.push({ key: 'maintenance_end_time', value: form.maintenance_end_time, type: 'string' });
    }

    console.log('Maintenance changes detected:', changes);

    // Only save if there are changes
    if (changes.length > 0) {
        emit('bulkSave', 'maintenance', changes);
    } else {
        console.log('No maintenance changes to save');
    }
};

const getCurrentDateTime = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const setMaintenanceEndTime = (hours: number) => {
    const now = new Date();
    now.setHours(now.getHours() + hours);
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hour = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');
    form.maintenance_end_time = `${year}-${month}-${day}T${hour}:${minute}`;
};

const formatEndTime = (datetime: string) => {
    if (!datetime) return '';
    try {
        const date = new Date(datetime);
        return date.toLocaleString('id-ID', {
            dateStyle: 'full',
            timeStyle: 'short',
        });
    } catch {
        return datetime;
    }
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Maintenance Mode</CardTitle>
            <CardDescription>
                Configure maintenance mode settings to temporarily disable public access
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Maintenance Status Alert -->
            <Alert v-if="form.maintenance_mode" variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <strong>Warning:</strong> Maintenance mode is currently ENABLED. 
                    Users will see the maintenance message instead of the normal website.
                </AlertDescription>
            </Alert>

            <Alert v-else>
                <CheckCircle class="h-4 w-4" />
                <AlertDescription>
                    Website is currently accessible to all users.
                </AlertDescription>
            </Alert>

            <!-- Maintenance Mode Toggle -->
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="space-y-1">
                        <Label class="text-base font-medium">Enable Maintenance Mode</Label>
                        <p class="text-sm text-muted-foreground">
                            When enabled, visitors will see a maintenance page instead of your website
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge v-if="form.maintenance_mode" variant="destructive">Enabled</Badge>
                        <Badge v-else variant="default">Disabled</Badge>
                        <Switch
                            :checked="form.maintenance_mode"
                            @update:checked="form.maintenance_mode = $event"
                            :disabled="isLoading"
                        />
                    </div>
                </div>
            </div>

            <!-- Maintenance Message -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="maintenance_message">Maintenance Message</Label>
                    <Textarea
                        id="maintenance_message"
                        v-model="form.maintenance_message"
                        placeholder="Enter the message users will see during maintenance"
                        :disabled="isLoading"
                        :rows="4"
                    />
                    <p class="text-sm text-muted-foreground">
                        This message will be displayed to users when maintenance mode is enabled
                    </p>
                </div>

                <!-- Message Preview -->
                <div class="border rounded-lg p-4 bg-gray-50">
                    <Label class="text-sm font-medium text-gray-700">Preview:</Label>
                    <div class="mt-2 text-center p-6 bg-white border rounded-lg">
                        <AlertTriangle class="mx-auto h-12 w-12 text-orange-500 mb-4" />
                        <h2 class="text-xl font-semibold mb-2">Maintenance Mode</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ form.maintenance_message || 'No message set' }}</p>
                    </div>
                </div>
            </div>

            <!-- Maintenance End Time -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="maintenance_end_time">Estimated End Time (Optional)</Label>
                    <Input
                        id="maintenance_end_time"
                        v-model="form.maintenance_end_time"
                        type="datetime-local"
                        :min="getCurrentDateTime()"
                        :disabled="isLoading"
                    />
                    <p class="text-sm text-muted-foreground">
                        Let users know when the maintenance is expected to be completed
                    </p>
                </div>

                <!-- Quick Time Buttons -->
                <div class="flex flex-wrap gap-2">
                    <Button 
                        size="sm" 
                        variant="outline" 
                        @click="setMaintenanceEndTime(1)"
                        :disabled="isLoading"
                    >
                        +1 Hour
                    </Button>
                    <Button 
                        size="sm" 
                        variant="outline" 
                        @click="setMaintenanceEndTime(2)"
                        :disabled="isLoading"
                    >
                        +2 Hours
                    </Button>
                    <Button 
                        size="sm" 
                        variant="outline" 
                        @click="setMaintenanceEndTime(4)"
                        :disabled="isLoading"
                    >
                        +4 Hours
                    </Button>
                    <Button 
                        size="sm" 
                        variant="outline" 
                        @click="setMaintenanceEndTime(24)"
                        :disabled="isLoading"
                    >
                        +1 Day
                    </Button>
                </div>

                <!-- End Time Display -->
                <div v-if="form.maintenance_end_time" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center gap-2 text-blue-800">
                        <Clock class="w-4 h-4" />
                        <span class="text-sm font-medium">
                            Estimated completion: {{ formatEndTime(form.maintenance_end_time) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Important Notes -->
            <Alert>
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <strong>Important Notes:</strong>
                    <ul class="mt-2 ml-4 list-disc text-sm space-y-1">
                        <li>Admin users may still access the admin panel during maintenance mode</li>
                        <li>API endpoints may still be accessible depending on configuration</li>
                        <li>Remember to disable maintenance mode when work is completed</li>
                        <li>Test the maintenance page before enabling it for users</li>
                    </ul>
                </AlertDescription>
            </Alert>

            <div class="flex justify-end pt-4">
                <Button 
                    @click="saveSettings" 
                    :disabled="isSaving || isLoading"
                    class="min-w-[120px]"
                    :variant="form.maintenance_mode ? 'destructive' : 'default'"
                >
                    <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                    <Save v-else class="w-4 h-4 mr-2" />
                    {{ isSaving ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </CardContent>
    </Card>
</template>