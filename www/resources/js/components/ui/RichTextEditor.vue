<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

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
const quillEditor = ref<any>(null);

// Watch for external model changes
watch(() => props.modelValue, (newValue) => {
    if (newValue !== content.value) {
        content.value = newValue;
    }
});

const handleTextChange = () => {
    const html = quillEditor.value?.getHTML();
    content.value = html || '';
    emit('update:modelValue', html || '');
};

// Custom image upload handler
const imageHandler = () => {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
        const file = input.files?.[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        try {
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
                const quill = quillEditor.value?.getQuill();
                const range = quill?.getSelection();
                if (range) {
                    quill.insertEmbed(range.index, 'image', result.url);
                }
            } else {
                console.error('Upload failed:', result.message);
            }
        } catch (error) {
            console.error('Image upload failed:', error);
        }
    };
};

// Quill configuration
const editorOptions = {
    theme: 'snow',
    placeholder: props.placeholder,
    readOnly: props.readonly || props.disabled,
    modules: {
        toolbar: {
            container: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                ['clean']
            ],
            handlers: {
                image: imageHandler
            }
        }
    }
};

onMounted(() => {
    // Set initial content when component mounts
    if (props.modelValue && quillEditor.value) {
        quillEditor.value.setHTML(props.modelValue);
    }
});
</script>

<template>
    <div class="rich-text-editor" :style="{ minHeight: height + 'px' }">
        <QuillEditor
            ref="quillEditor"
            v-model:content="content"
            content-type="html"
            :options="editorOptions"
            @textChange="handleTextChange"
        />
    </div>
</template>

<style scoped>
.rich-text-editor {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    overflow: hidden;
}

/* Quill Editor Styling */
:deep(.ql-toolbar) {
    background-color: #f9fafb;
    border-bottom: 1px solid #d1d5db;
    border-top: none;
    border-left: none;
    border-right: none;
}

:deep(.ql-container) {
    border: none;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    font-size: 14px;
    line-height: 1.6;
}

:deep(.ql-editor) {
    padding: 1rem;
    min-height: 300px;
}

:deep(.ql-editor h1) {
    font-size: 2em;
    font-weight: 600;
    margin: 1.5em 0 0.5em 0;
}

:deep(.ql-editor h2) {
    font-size: 1.5em;
    font-weight: 600;
    margin: 1.3em 0 0.5em 0;
}

:deep(.ql-editor h3) {
    font-size: 1.17em;
    font-weight: 600;
    margin: 1.17em 0 0.5em 0;
}

:deep(.ql-editor p) {
    margin-bottom: 1em;
}

:deep(.ql-editor img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

:deep(.ql-editor blockquote) {
    border-left: 4px solid #e5e7eb;
    margin: 1em 0;
    padding: 0.5em 1em;
    background-color: #f9fafb;
    font-style: italic;
}

:deep(.ql-editor code) {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 3px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

:deep(.ql-editor pre) {
    background-color: #f3f4f6;
    padding: 1em;
    border-radius: 6px;
    overflow-x: auto;
    margin: 1rem 0;
}

:deep(.ql-editor ul, .ql-editor ol) {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

:deep(.ql-editor li) {
    margin-bottom: 0.25rem;
}

/* Focus state */
:deep(.ql-container.ql-snow) {
    border: none;
}

:deep(.ql-toolbar.ql-snow) {
    border: none;
}

.rich-text-editor:focus-within {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>