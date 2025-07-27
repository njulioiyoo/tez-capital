<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Save, Loader2, Phone, Mail, MapPin, MessageCircle, Globe } from 'lucide-vue-next';

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

interface SocialMedia {
    facebook?: string;
    instagram?: string;
    twitter?: string;
    linkedin?: string;
    youtube?: string;
    tiktok?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    save: [group: string, key: string, value: any, type: string];
    update: [configId: number, group: string, key: string, value: any, type: string];
    bulkSave: [group: string, changes: Array<{key: string, value: any, type: string}>];
}>();

const form = reactive({
    phone: '',
    email: '',
    address: '',
    whatsapp: '',
    social_media: {
        facebook: '',
        instagram: '',
        twitter: '',
        linkedin: '',
        youtube: '',
        tiktok: '',
    } as SocialMedia,
});

// Watch for configuration changes and update form
watch(() => props.configurations, (newConfigs) => {
    if (newConfigs) {
        form.phone = newConfigs.contact_phone?.value || '';
        form.email = newConfigs.contact_email?.value || '';
        form.address = newConfigs.contact_address?.value || '';
        form.whatsapp = newConfigs.contact_whatsapp?.value || '';
        
        try {
            form.social_media = JSON.parse(newConfigs.social_media?.value || '{}');
        } catch {
            form.social_media = {
                facebook: '',
                instagram: '',
                twitter: '',
                linkedin: '',
                youtube: '',
                tiktok: '',
            };
        }
    }
}, { immediate: true, deep: true });

const saveSettings = () => {
    const changes = [];

    // Check for changes and collect them
    console.log('Checking contact changes:');
    console.log('Current phone:', props.configurations.contact_phone?.value, 'Form value:', form.phone);
    console.log('Current email:', props.configurations.contact_email?.value, 'Form value:', form.email);
    console.log('Current address:', props.configurations.contact_address?.value, 'Form value:', form.address);
    console.log('Current whatsapp:', props.configurations.contact_whatsapp?.value, 'Form value:', form.whatsapp);
    console.log('Current social_media:', props.configurations.social_media?.value, 'Form value:', JSON.stringify(form.social_media));

    if (props.configurations.contact_phone?.value !== form.phone) {
        changes.push({ key: 'contact_phone', value: form.phone, type: 'string' });
    }

    if (props.configurations.contact_email?.value !== form.email) {
        changes.push({ key: 'contact_email', value: form.email, type: 'email' });
    }

    if (props.configurations.contact_address?.value !== form.address) {
        changes.push({ key: 'contact_address', value: form.address, type: 'text' });
    }

    if (props.configurations.contact_whatsapp?.value !== form.whatsapp) {
        changes.push({ key: 'contact_whatsapp', value: form.whatsapp, type: 'string' });
    }

    const currentSocialStr = props.configurations.social_media?.value || '{}';
    const formSocialStr = JSON.stringify(form.social_media);
    if (currentSocialStr !== formSocialStr) {
        changes.push({ key: 'social_media', value: form.social_media, type: 'json' });
    }

    console.log('Contact changes detected:', changes);

    // Only save if there are changes
    if (changes.length > 0) {
        emit('bulkSave', 'contact', changes);
    } else {
        console.log('No contact changes to save');
    }
};

const validateURL = (url: string) => {
    if (!url) return true;
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
};

const formatWhatsAppLink = (number: string) => {
    if (!number) return '';
    const cleaned = number.replace(/\D/g, '');
    return `https://wa.me/${cleaned}`;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Contact Information -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Phone class="w-5 h-5" />
                    Basic Contact Information
                </CardTitle>
                <CardDescription>
                    Primary contact details for customer support and inquiries
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="contact_phone">Phone Number</Label>
                        <Input
                            id="contact_phone"
                            v-model="form.phone"
                            placeholder="+62 21 1234 5678"
                            :disabled="isLoading"
                        />
                        <p class="text-sm text-muted-foreground">
                            Main phone number for customer service
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="contact_email">Email Address</Label>
                        <Input
                            id="contact_email"
                            v-model="form.email"
                            type="email"
                            placeholder="support@tezcapital.com"
                            :disabled="isLoading"
                        />
                        <p class="text-sm text-muted-foreground">
                            Primary email for customer inquiries
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="contact_address">Business Address</Label>
                    <Textarea
                        id="contact_address"
                        v-model="form.address"
                        placeholder="Enter your complete business address"
                        :disabled="isLoading"
                        :rows="3"
                    />
                    <p class="text-sm text-muted-foreground">
                        Complete business address for official correspondence
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="contact_whatsapp">WhatsApp Number</Label>
                    <Input
                        id="contact_whatsapp"
                        v-model="form.whatsapp"
                        placeholder="+62 812 3456 7890"
                        :disabled="isLoading"
                    />
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <MessageCircle class="w-4 h-4" />
                        <span>WhatsApp contact for instant messaging</span>
                    </div>
                    <div v-if="form.whatsapp" class="text-sm">
                        <a 
                            :href="formatWhatsAppLink(form.whatsapp)" 
                            target="_blank" 
                            class="text-blue-600 hover:underline"
                        >
                            Test WhatsApp Link: {{ formatWhatsAppLink(form.whatsapp) }}
                        </a>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Social Media Links -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Globe class="w-5 h-5" />
                    Social Media Links
                </CardTitle>
                <CardDescription>
                    Configure your social media presence and links
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="facebook">Facebook</Label>
                        <Input
                            id="facebook"
                            v-model="form.social_media.facebook"
                            placeholder="https://facebook.com/tezcapital"
                            :disabled="isLoading"
                            :class="{ 'border-red-500': form.social_media.facebook && !validateURL(form.social_media.facebook) }"
                        />
                        <p v-if="form.social_media.facebook && !validateURL(form.social_media.facebook)" class="text-sm text-red-500">
                            Please enter a valid URL
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="instagram">Instagram</Label>
                        <Input
                            id="instagram"
                            v-model="form.social_media.instagram"
                            placeholder="https://instagram.com/tezcapital"
                            :disabled="isLoading"
                            :class="{ 'border-red-500': form.social_media.instagram && !validateURL(form.social_media.instagram) }"
                        />
                        <p v-if="form.social_media.instagram && !validateURL(form.social_media.instagram)" class="text-sm text-red-500">
                            Please enter a valid URL
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="twitter">Twitter / X</Label>
                        <Input
                            id="twitter"
                            v-model="form.social_media.twitter"
                            placeholder="https://twitter.com/tezcapital"
                            :disabled="isLoading"
                            :class="{ 'border-red-500': form.social_media.twitter && !validateURL(form.social_media.twitter) }"
                        />
                        <p v-if="form.social_media.twitter && !validateURL(form.social_media.twitter)" class="text-sm text-red-500">
                            Please enter a valid URL
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="linkedin">LinkedIn</Label>
                        <Input
                            id="linkedin"
                            v-model="form.social_media.linkedin"
                            placeholder="https://linkedin.com/company/tezcapital"
                            :disabled="isLoading"
                            :class="{ 'border-red-500': form.social_media.linkedin && !validateURL(form.social_media.linkedin) }"
                        />
                        <p v-if="form.social_media.linkedin && !validateURL(form.social_media.linkedin)" class="text-sm text-red-500">
                            Please enter a valid URL
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="youtube">YouTube</Label>
                        <Input
                            id="youtube"
                            v-model="form.social_media.youtube"
                            placeholder="https://youtube.com/@tezcapital"
                            :disabled="isLoading"
                            :class="{ 'border-red-500': form.social_media.youtube && !validateURL(form.social_media.youtube) }"
                        />
                        <p v-if="form.social_media.youtube && !validateURL(form.social_media.youtube)" class="text-sm text-red-500">
                            Please enter a valid URL
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="tiktok">TikTok</Label>
                        <Input
                            id="tiktok"
                            v-model="form.social_media.tiktok"
                            placeholder="https://tiktok.com/@tezcapital"
                            :disabled="isLoading"
                            :class="{ 'border-red-500': form.social_media.tiktok && !validateURL(form.social_media.tiktok) }"
                        />
                        <p v-if="form.social_media.tiktok && !validateURL(form.social_media.tiktok)" class="text-sm text-red-500">
                            Please enter a valid URL
                        </p>
                    </div>
                </div>

                <!-- Social Media Preview -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <Label class="text-sm font-medium text-gray-700 mb-3 block">Active Social Media Links:</Label>
                    <div class="flex flex-wrap gap-2">
                        <Badge v-if="form.social_media.facebook && validateURL(form.social_media.facebook)" variant="outline">
                            Facebook
                        </Badge>
                        <Badge v-if="form.social_media.instagram && validateURL(form.social_media.instagram)" variant="outline">
                            Instagram
                        </Badge>
                        <Badge v-if="form.social_media.twitter && validateURL(form.social_media.twitter)" variant="outline">
                            Twitter
                        </Badge>
                        <Badge v-if="form.social_media.linkedin && validateURL(form.social_media.linkedin)" variant="outline">
                            LinkedIn
                        </Badge>
                        <Badge v-if="form.social_media.youtube && validateURL(form.social_media.youtube)" variant="outline">
                            YouTube
                        </Badge>
                        <Badge v-if="form.social_media.tiktok && validateURL(form.social_media.tiktok)" variant="outline">
                            TikTok
                        </Badge>
                        <span v-if="Object.values(form.social_media).every(url => !url || !validateURL(url))" class="text-sm text-muted-foreground">
                            No valid social media links configured
                        </span>
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