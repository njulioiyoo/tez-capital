<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Editor from '@tinymce/tinymce-vue';

interface Props {
    modelValue: string;
    placeholder?: string;
    height?: number;
    disabled?: boolean;
    readonly?: boolean;
}

interface Emits {
    (e: 'update:modelValue', value: string): void;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Start typing...',
    height: 400,
    disabled: false,
    readonly: false,
});

const emit = defineEmits<Emits>();

const content = ref(props.modelValue);

// Watch for external model changes
watch(() => props.modelValue, (newValue) => {
    if (newValue !== content.value) {
        content.value = newValue;
    }
});

const handleInput = (value: string) => {
    content.value = value;
    emit('update:modelValue', value);
};

// TinyMCE configuration optimized for education content
const editorConfig = {
    height: props.height,
    menubar: true,
    base_url: '/tinymce',
    suffix: '.min',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount',
        'emoticons', 'template', 'paste', 'textcolor', 'colorpicker'
    ],
    toolbar: [
        'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify',
        'bullist numlist outdent indent | removeformat | help',
        'link image media | table | code | fullscreen preview'
    ].join(' | '),
    toolbar_mode: 'sliding',
    content_style: `
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; 
            font-size: 14px; 
            line-height: 1.6;
            color: #333;
        }
        h1, h2, h3, h4, h5, h6 { 
            margin-top: 1.5em; 
            margin-bottom: 0.5em; 
            font-weight: 600;
        }
        p { margin-bottom: 1em; }
        img { max-width: 100%; height: auto; }
        table { width: 100%; border-collapse: collapse; }
        table td, table th { border: 1px solid #ddd; padding: 8px; }
        table th { background-color: #f4f4f4; font-weight: 600; }
        blockquote { 
            border-left: 4px solid #e5e7eb; 
            margin: 1em 0; 
            padding: 0.5em 1em; 
            background-color: #f9fafb;
            font-style: italic;
        }
        code { 
            background-color: #f3f4f6; 
            padding: 0.2em 0.4em; 
            border-radius: 3px; 
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }
        pre { 
            background-color: #f3f4f6; 
            padding: 1em; 
            border-radius: 6px; 
            overflow-x: auto;
        }
    `,
    placeholder: props.placeholder,
    branding: false,
    promotion: false,
    resize: true,
    contextmenu: 'link image table',
    paste_data_images: true,
    automatic_uploads: true,
    file_picker_types: 'image',
    images_upload_handler: async (blobInfo: any) => {
        // Create FormData for image upload
        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        
        try {
            // Upload to your Laravel backend
            const response = await fetch('/api/system/education/upload-image', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });
            
            const result = await response.json();
            if (response.ok) {
                return result.url;
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Image upload failed:', error);
            throw error;
        }
    },
    setup: (editor: any) => {
        // Add custom styles for better content presentation
        editor.on('init', () => {
            editor.getDoc().documentElement.style.fontSize = '14px';
        });
    }
};
</script>

<template>
    <div class="rich-text-editor">
        <Editor
            v-model="content"
            :api-key="'no-api-key'"
            :init="editorConfig"
            :disabled="disabled || readonly"
            @input="handleInput"
        />
    </div>
</template>

<style scoped>
.rich-text-editor {
    @apply w-full;
}

/* Override TinyMCE theme to match our design system */
:deep(.tox-tinymce) {
    border: 1px solid hsl(var(--border));
    border-radius: calc(var(--radius) - 2px);
}

:deep(.tox-editor-header) {
    background: hsl(var(--background));
    border-bottom: 1px solid hsl(var(--border));
}

:deep(.tox-toolbar) {
    background: hsl(var(--background));
    border-bottom: 1px solid hsl(var(--border));
}

:deep(.tox-edit-area) {
    background: hsl(var(--background));
}

:deep(.tox-edit-area__iframe) {
    background: hsl(var(--background));
}

/* Focus state */
:deep(.tox-tinymce:focus-within) {
    outline: 2px solid hsl(var(--ring));
    outline-offset: 2px;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    :deep(.tox) {
        --tox-icon-color: hsl(var(--foreground));
        --tox-toolbar-bg: hsl(var(--background));
    }
}
</style>