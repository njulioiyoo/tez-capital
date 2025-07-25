<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Save, Loader2, Plus, Trash2 } from 'lucide-vue-next';

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
    min_amount: 1000000,
    max_amount: 500000000,
    tenors: [6, 12, 18, 24, 36, 48] as number[],
    interest_rates: {} as Record<number, number>,
    admin_fee: 2.5,
});

const newTenor = ref<number>(12);

// Watch for configuration changes and update form
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        form.min_amount = newConfigs.credit_min_amount?.value || 1000000;
        form.max_amount = newConfigs.credit_max_amount?.value || 500000000;
        form.admin_fee = parseFloat(newConfigs.credit_admin_fee?.value || '2.5');

        // Handle tenors - could be array or JSON string
        const tenorsValue = newConfigs.credit_tenors?.value;
        if (Array.isArray(tenorsValue)) {
            form.tenors = [...tenorsValue]; // Create new array to avoid proxy issues
        } else {
            try {
                form.tenors = JSON.parse(tenorsValue || '[6,12,18,24,36,48]');
            } catch {
                form.tenors = [6, 12, 18, 24, 36, 48];
            }
        }

        // Handle interest rates - could be object or JSON string
        const ratesValue = newConfigs.credit_interest_rates?.value;
        if (typeof ratesValue === 'object' && ratesValue !== null && !Array.isArray(ratesValue)) {
            form.interest_rates = { ...ratesValue }; // Create new object to avoid proxy issues
        } else {
            try {
                form.interest_rates = JSON.parse(ratesValue || '{"6":12,"12":13,"18":14,"24":15,"36":16,"48":17}');
            } catch {
                form.interest_rates = { 6: 12, 12: 13, 18: 14, 24: 15, 36: 16, 48: 17 };
            }
        }
    }
}, { immediate: true, deep: true });

const addTenor = () => {
    if (newTenor.value && !form.tenors.includes(newTenor.value)) {
        form.tenors.push(newTenor.value);
        form.tenors.sort((a, b) => a - b);
        form.interest_rates[newTenor.value] = 12; // Default interest rate
        newTenor.value = 12;
    }
};

const removeTenor = (tenor: number) => {
    const index = form.tenors.indexOf(tenor);
    if (index > -1) {
        form.tenors.splice(index, 1);
        delete form.interest_rates[tenor];
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    console.log('Checking credit changes:');
    console.log('Current min_amount:', props.configurations.credit_min_amount?.value, 'Form value:', form.min_amount);
    console.log('Current max_amount:', props.configurations.credit_max_amount?.value, 'Form value:', form.max_amount);
    console.log('Current tenors:', props.configurations.credit_tenors?.value, 'Form value:', JSON.stringify(form.tenors));
    console.log('Current interest_rates:', props.configurations.credit_interest_rates?.value, 'Form value:', JSON.stringify(form.interest_rates));
    console.log('Current admin_fee:', props.configurations.credit_admin_fee?.value, 'Form value:', form.admin_fee.toString());

    if (props.configurations.credit_min_amount?.value !== form.min_amount) {
        changes.push({ key: 'credit_min_amount', value: form.min_amount, type: 'integer' });
    }

    if (props.configurations.credit_max_amount?.value !== form.max_amount) {
        changes.push({ key: 'credit_max_amount', value: form.max_amount, type: 'integer' });
    }

    // Compare tenors properly - convert both to JSON strings for comparison
    const currentTenors = props.configurations.credit_tenors?.value || [6,12,18,24,36,48];
    const currentTenorsStr = JSON.stringify(Array.isArray(currentTenors) ? currentTenors : JSON.parse(currentTenors));
    const formTenorsStr = JSON.stringify(form.tenors);
    if (currentTenorsStr !== formTenorsStr) {
        changes.push({ key: 'credit_tenors', value: form.tenors, type: 'json' });
    }

    // Compare interest rates properly - convert both to JSON strings for comparison
    const currentRates = props.configurations.credit_interest_rates?.value || {"6":12,"12":13,"18":14,"24":15,"36":16,"48":17};
    const currentRatesStr = JSON.stringify(typeof currentRates === 'object' ? currentRates : JSON.parse(currentRates));
    const formRatesStr = JSON.stringify(form.interest_rates);
    if (currentRatesStr !== formRatesStr) {
        changes.push({ key: 'credit_interest_rates', value: form.interest_rates, type: 'json' });
    }

    if (props.configurations.credit_admin_fee?.value !== form.admin_fee.toString()) {
        changes.push({ key: 'credit_admin_fee', value: form.admin_fee.toString(), type: 'string' });
    }

    console.log('Credit changes detected:', changes);

    // Only save if there are changes
    if (changes.length > 0) {
        emit('bulkSave', 'credit', changes);
    } else {
        console.log('No credit changes to save');
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Credit Limits -->
        <Card>
            <CardHeader>
                <CardTitle>Credit Limits</CardTitle>
                <CardDescription>
                    Set minimum and maximum credit amounts available to customers
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="min_amount">Minimum Credit Amount (IDR)</Label>
                        <Input id="min_amount" v-model.number="form.min_amount" type="number" placeholder="1000000"
                            :disabled="isLoading" />
                        <p class="text-sm text-muted-foreground">
                            Current: {{ formatCurrency(form.min_amount) }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="max_amount">Maximum Credit Amount (IDR)</Label>
                        <Input id="max_amount" v-model.number="form.max_amount" type="number" placeholder="500000000"
                            :disabled="isLoading" />
                        <p class="text-sm text-muted-foreground">
                            Current: {{ formatCurrency(form.max_amount) }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Credit Tenors & Interest Rates -->
        <Card>
            <CardHeader>
                <CardTitle>Credit Tenors & Interest Rates</CardTitle>
                <CardDescription>
                    Configure available credit tenors and their corresponding interest rates
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Add New Tenor -->
                <div class="flex gap-2 items-end">
                    <div class="space-y-2">
                        <Label for="new_tenor">Add New Tenor (Months)</Label>
                        <Input id="new_tenor" v-model.number="newTenor" type="number" placeholder="12" class="w-32"
                            :disabled="isLoading" />
                    </div>
                    <Button @click="addTenor" variant="outline" :disabled="isLoading">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Tenor
                    </Button>
                </div>

                <!-- Tenor List -->
                <div class="space-y-4">
                    <div v-for="tenor in form.tenors" :key="tenor" class="border rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <Badge variant="outline">{{ tenor }} Months</Badge>
                            <Button @click="removeTenor(tenor)" variant="destructive" size="sm">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>

                        <div class="grid md:grid-cols-3 gap-4 items-end">
                            <div class="space-y-2">
                                <Label>Tenor Period</Label>
                                <div class="text-lg font-semibold">{{ tenor }} Months</div>
                            </div>

                            <div class="space-y-2">
                                <Label :for="`rate_${tenor}`">Interest Rate (%)</Label>
                                <Input :id="`rate_${tenor}`" v-model.number="form.interest_rates[tenor]" type="number"
                                    step="0.1" min="0" max="100" :disabled="isLoading" />
                            </div>

                            <div class="space-y-2">
                                <Label>Annual Rate</Label>
                                <div class="text-lg font-semibold text-green-600">
                                    {{ form.interest_rates[tenor] || 0 }}% per year
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="form.tenors.length === 0" class="text-center py-8 text-muted-foreground">
                    No tenors configured. Add a tenor to get started.
                </div>
            </CardContent>
        </Card>

        <!-- Admin Fee -->
        <Card>
            <CardHeader>
                <CardTitle>Admin Fee</CardTitle>
                <CardDescription>
                    Set the administrative fee percentage charged to customers
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="admin_fee">Admin Fee (%)</Label>
                        <Input id="admin_fee" v-model.number="form.admin_fee" type="number" step="0.1" min="0" max="10"
                            placeholder="2.5" :disabled="isLoading" />
                        <p class="text-sm text-muted-foreground">
                            Fee charged as percentage of credit amount
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label>Example Calculation</Label>
                        <div class="p-3 rounded-lg">
                            <div class="text-sm">
                                <div>Credit Amount: {{ formatCurrency(10000000) }}</div>
                                <div>Admin Fee ({{ form.admin_fee }}%): {{ formatCurrency(10000000 * form.admin_fee /
                                    100) }}</div>
                                <div class="font-semibold border-t pt-1 mt-1">
                                    Total to Pay: {{ formatCurrency(10000000 + (10000000 * form.admin_fee / 100)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="flex justify-end pt-4">
            <Button @click="saveSettings" :disabled="isSaving || isLoading" class="min-w-[120px]">
                <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                <Save v-else class="w-4 h-4 mr-2" />
                {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </Button>
        </div>
    </div>
</template>