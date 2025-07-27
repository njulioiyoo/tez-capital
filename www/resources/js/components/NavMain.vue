<script setup lang="ts">
import { 
    SidebarGroup, 
    SidebarGroupLabel, 
    SidebarMenu, 
    SidebarMenuButton, 
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem
} from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Separator } from 'lucide-vue-next';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const legacyToNewMapping: Record<string, string> = {
    '/users': '/system/users',
    '/roles': '/system/roles',
    '/configurations': '/system/configurations',
    '/menu-manager': '/system/menu',
    '/audit-log': '/system/audit-log'
};

const reverseMapping = Object.fromEntries(
    Object.entries(legacyToNewMapping).map(([key, value]) => [value, key])
);

const isUrlMatch = (itemHref: string, currentUrl: string): boolean => {
    if (itemHref === currentUrl) return true;
    
    // Check legacy to new mapping
    const mappedUrl = legacyToNewMapping[itemHref];
    if (mappedUrl && mappedUrl === currentUrl) return true;
    
    // Check reverse mapping (new URL in DB, legacy current URL)
    const reverseMappedUrl = reverseMapping[itemHref];
    if (reverseMappedUrl && reverseMappedUrl === currentUrl) return true;
    
    return false;
};

const isChildActive = (child: NavItem): boolean => {
    if (!child.href) return false;
    return isUrlMatch(child.href, page.url);
};

const isActive = (item: NavItem): boolean => {
    if (item.href && isUrlMatch(item.href, page.url)) return true;
    
    if (item.children) {
        return item.children.some(child => isChildActive(child));
    }
    return false;
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.id">
                <!-- Separator -->
                <li v-if="item.is_separator" class="my-2">
                    <hr class="border-sidebar-border" />
                </li>
                
                <!-- Menu item with children -->
                <Collapsible 
                    v-else-if="item.children && item.children.length > 0" 
                    as-child 
                    :default-open="isActive(item)"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton 
                                :tooltip="item.title"
                                :is-active="isActive(item)"
                                :disabled="item.disabled"
                            >
                                <component v-if="item.icon" :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <span v-if="item.badge" class="ml-auto px-1.5 py-0.5 text-xs bg-sidebar-accent text-sidebar-accent-foreground rounded">
                                    {{ item.badge }}
                                </span>
                                <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="subItem in item.children" :key="subItem.id">
                                    <SidebarMenuSubButton 
                                        as-child 
                                        :is-active="isChildActive(subItem)"
                                        :disabled="subItem.disabled"
                                    >
                                        <Link v-if="subItem.href" :href="subItem.href">
                                            <component v-if="subItem.icon" :is="subItem.icon" />
                                            <span>{{ subItem.title }}</span>
                                            <span v-if="subItem.badge" class="ml-auto px-1.5 py-0.5 text-xs bg-sidebar-accent text-sidebar-accent-foreground rounded">
                                                {{ subItem.badge }}
                                            </span>
                                        </Link>
                                        <div v-else class="flex items-center w-full opacity-50 cursor-not-allowed">
                                            <component v-if="subItem.icon" :is="subItem.icon" />
                                            <span>{{ subItem.title }}</span>
                                            <span v-if="subItem.badge" class="ml-auto px-1.5 py-0.5 text-xs bg-sidebar-accent text-sidebar-accent-foreground rounded">
                                                {{ subItem.badge }}
                                            </span>
                                        </div>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
                
                <!-- Regular menu item -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton 
                        as-child 
                        :is-active="item.href ? isUrlMatch(item.href, page.url) : false" 
                        :tooltip="item.title"
                        :disabled="item.disabled"
                    >
                        <Link v-if="item.href && !item.disabled" :href="item.href">
                            <component v-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                            <span v-if="item.badge" class="ml-auto px-1.5 py-0.5 text-xs bg-sidebar-accent text-sidebar-accent-foreground rounded">
                                {{ item.badge }}
                            </span>
                        </Link>
                        <div v-else class="flex items-center w-full opacity-50 cursor-not-allowed">
                            <component v-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                            <span v-if="item.badge" class="ml-auto px-1.5 py-0.5 text-xs bg-sidebar-accent text-sidebar-accent-foreground rounded">
                                {{ item.badge }}
                            </span>
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
