import { ref, computed, readonly } from 'vue';

// Global state - shared across all components
const brandingConfigs = ref<any>({});
const loading = ref(false);
const error = ref<string | null>(null);

export function useBranding() {
    // Fetch branding configurations from API
    const fetchBrandingConfigs = async () => {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await fetch('/api/configurations/public', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch branding configurations');
            }
            
            const data = await response.json();
            brandingConfigs.value = data.data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'An error occurred';
            console.error('Error fetching branding configurations:', err);
        } finally {
            loading.value = false;
        }
    };

    // Computed properties for specific branding configs
    const companyLogo = computed(() => brandingConfigs.value.company_logo || null);
    const companyLogoDark = computed(() => brandingConfigs.value.company_logo_dark || null);
    const favicon = computed(() => brandingConfigs.value.favicon || null);
    const companyName = computed(() => brandingConfigs.value.company_name || 'Tez Capital Dashboard');

    // Get logo based on theme (fallback to light logo if dark not available)
    const getLogoForTheme = (isDark: boolean = false) => {
        if (isDark && companyLogoDark.value) {
            return companyLogoDark.value;
        }
        return companyLogo.value;
    };

    // Initialize branding configs on first load (only if not already loaded)
    if (Object.keys(brandingConfigs.value).length === 0 && !loading.value) {
        fetchBrandingConfigs();
    }

    return {
        brandingConfigs: readonly(brandingConfigs),
        loading: readonly(loading),
        error: readonly(error),
        companyLogo,
        companyLogoDark,
        favicon,
        companyName,
        getLogoForTheme,
        fetchBrandingConfigs,
    };
}