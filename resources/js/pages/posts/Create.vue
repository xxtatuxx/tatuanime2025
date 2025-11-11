<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { PostForm, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add moves + series',
        href: '/posts',
    },
];

const form = useForm<PostForm>({
    title: '',
    content: '',
    image: null,
});

const imagePreview = ref<string | null>(null);

const submit = () => {
    form.post(route('posts.store'));
};

const handleImageInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

</script>

<template>
    <Head title="Create Post" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
            <div class="p-6 mx-auto mt-8 bg-white shadow-lg min-w-md dark:bg-neutral-800 rounded-xl">
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                type="text"
                                autofocus
                                :tabindex="1"
                                autocomplete="title"
                                v-model="form.title"
                                placeholder="First post"
                            />
                            <InputError :message="form.errors.title" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="title">Image</Label>
                            <Input
                                id="image"
                                type="file"
                                :tabindex="2"
                                @change="handleImageInput"
                            />
                            <img v-if="imagePreview" :src="imagePreview" alt="" class="object-cover w-12 h-12 rounded">
                            <InputError :message="form.errors.image" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="content">Content</Label>
                            <Textarea
                                id="content"
                                autofocus
                                :tabindex="3"
                                autocomplete="content"
                                v-model="form.content"
                                placeholder="First post"
                            />
                            <InputError :message="form.errors.content" />
                        </div>
                        <Button type="submit" class="w-full mt-4" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                           Create
                        </Button>
                    </div>
        </form>
            </div>
        </div>
    </AppLayout>
</template>
