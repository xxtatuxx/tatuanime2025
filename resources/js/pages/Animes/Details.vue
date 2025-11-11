<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const props = defineProps<{ anime: Record<string, any> }>();

const coverUrl = computed(() => props.anime.cover ? `/storage/${props.anime.cover}` : null);
const posterUrl = computed(() => props.anime.image ? `/storage/${props.anime.image}` : null);

const coverStyle = computed(() => {
    if (!coverUrl.value) {
        return {
            background: 'linear-gradient(135deg, rgba(79,70,229,0.8), rgba(147,51,234,0.8))',
        } as const;
    }
    return {
        backgroundImage: `linear-gradient(135deg, rgba(17,24,39,0.65), rgba(17,24,39,0.1)), url(${coverUrl.value})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
    } as const;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Animes', href: '/animes' },
    { title: props.anime.title_en || props.anime.title || 'Details', href: `/animes/${props.anime.id}` },
];

const formattedReleaseDate = computed(() => {
    if (!props.anime.release_date) return '-';
    const date = new Date(props.anime.release_date);
    if (Number.isNaN(date.getTime())) return props.anime.release_date;
    return date.toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });
});

const infoItems = computed(() => [
    { label: 'الحالة', value: props.anime.status || '-' },
    { label: 'الموسم', value: props.anime.season?.name || '-' },
    { label: 'التقييم', value: props.anime.rating ? `${props.anime.rating} / 10` : '-' },
    { label: 'النوع', value: props.anime.type || '-' },
    { label: 'المدة', value: props.anime.duration ? `${props.anime.duration} دقيقة` : '-' },
    { label: 'اللغة', value: props.anime.language || '-' },
    { label: 'تاريخ الإصدار', value: formattedReleaseDate.value },
    { label: 'الاستوديو', value: props.anime.studio_name || '-' },
    { label: 'الحالة الحالية', value: props.anime.is_active ? 'نشط' : 'غير نشط' },
]);


const links = computed(() => [
    { label: 'مشاهدة البث', value: props.anime.stream_video },
    { label: 'مشاهدة الإعلان', value: props.anime.trailer },
]);
</script>

<template>
    <Head :title="props.anime.title_en || props.anime.title || 'تفاصيل الأنمي'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col flex-1 min-h-full gap-6 p-4">

            <!-- Cover & Poster -->
            <div class="relative w-full">
                <div class="h-56 border md:h-72 lg:h-80 rounded-3xl border-sidebar-border/60" :style="coverStyle"></div>
                <div class="max-w-6xl px-4 mx-auto -mt-24 md:-mt-32">
                    <div class="flex flex-col gap-6 md:flex-row md:items-end">
                        <!-- Poster -->
                        <div class="relative flex-shrink-0 w-40 overflow-hidden border shadow-xl h-60 md:w-52 md:h-72 rounded-2xl border-sidebar-border/50">
                            <img v-if="posterUrl" :src="posterUrl" alt="Anime poster" class="object-cover w-full h-full" />
                            <div v-else class="flex items-center justify-center w-full h-full text-sm text-white bg-indigo-500">
                                لا توجد صورة
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <div class="flex-1 p-6 space-y-4 bg-white shadow-xl dark:bg-neutral-900/90 rounded-3xl">
                            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold">{{ props.anime.title_en || props.anime.title }}</h1>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ props.anime.slug_en || props.anime.slug }}</p>
                                    <div v-if="props.anime.description_en || props.anime.description" class="mt-3 text-base leading-relaxed text-muted-foreground">
                                        {{ props.anime.description_en || props.anime.description }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 mt-3 md:mt-0">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="route('animes.edit', props.anime.id)">تعديل الأنمي</Link>
                                    </Button>
                                    <Button as-child size="sm" variant="secondary">
                                        <Link :href="route('animes.index')">العودة للقائمة</Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info & Links -->
            <div class="w-full max-w-6xl px-4 mx-auto">
                <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">

                    <!-- معلومات الأنمي -->
                    <div class="p-6 space-y-6 bg-white shadow-lg dark:bg-neutral-900/80 rounded-3xl">
                        <h2 class="mb-4 text-xl font-semibold">معلومات الأنمي</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-for="item in infoItems" :key="item.label" class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">{{ item.label }}</p>
                                <p class="text-sm font-medium text-foreground">{{ item.value }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- الروابط والتصنيفات والمستخدم -->
                    <div class="flex flex-col gap-6">

                        <!-- روابط مفيدة -->
                        <div class="p-6 space-y-4 bg-white shadow-lg dark:bg-neutral-900/80 rounded-3xl">
                            <h2 class="mb-3 text-xl font-semibold">روابط مفيدة</h2>
                            <div class="flex flex-col gap-3">
                                <template v-for="link in links" :key="link.label">
                                    <div v-if="link.value" class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-medium">{{ link.label }}</span>
                                        <a :href="link.value" target="_blank" rel="noopener" class="text-sm font-semibold text-indigo-500 hover:text-indigo-600">
                                            فتح الرابط
                                        </a>
                                    </div>
                                </template>
                                <p v-if="!links.some(link => link.value)" class="text-sm text-muted-foreground">لا توجد روابط متاحة.</p>
                            </div>
                        </div>

                        <!-- التصنيفات -->
                        <div class="p-6 space-y-2 bg-white shadow-lg dark:bg-neutral-900/80 rounded-3xl">
                            <h2 class="mb-3 text-xl font-semibold">التصنيفات</h2>
                            <div v-if="props.anime.categories && props.anime.categories.length">
                                <span v-for="(category, index) in props.anime.categories" :key="index" class="inline-block px-3 py-1 mb-1 mr-1 text-xs font-semibold text-white bg-indigo-500 rounded-full">
                                    {{ category.name }}
                                </span>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">لا توجد تصنيفات مرتبطة.</p>
                        </div>

                        <!-- معلومات المستخدم -->
                        <div class="p-6 space-y-2 bg-white shadow-lg dark:bg-neutral-900/80 rounded-3xl">
                            <h2 class="mb-2 text-xl font-semibold">المستخدم</h2>
                            <div class="space-y-1 text-sm">
                                <p class="font-medium">{{ props.anime.user?.name ?? 'غير متوفر' }}</p>
                                <p class="text-muted-foreground">{{ props.anime.user?.email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الحلقات -->
            <div v-if="props.anime.episodes && props.anime.episodes.length > 0" class="w-full max-w-6xl px-4 mx-auto">
                <div class="p-6 space-y-4 bg-white shadow-lg dark:bg-neutral-900/80 rounded-3xl">
                    <h2 class="mb-4 text-xl font-semibold">الحلقات</h2>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        <Link
                            v-for="episode in props.anime.episodes"
                            :key="episode.id"
                            :href="route('episodes.show', episode.id)"
                            class="relative overflow-hidden transition-all border group rounded-xl border-sidebar-border/60 hover:border-indigo-500"
                        >
                            <div class="relative aspect-video bg-gradient-to-br from-indigo-500 to-purple-600">
                                <img
                                    v-if="episode.thumbnail"
                                    :src="`/storage/${episode.thumbnail}`"
                                    :alt="episode.title_en || episode.title"
                                    class="object-cover w-full h-full transition-transform group-hover:scale-105"
                                />
                                <div v-else class="flex items-center justify-center w-full h-full px-2 text-sm text-center text-white">
                                    {{ episode.title_en || episode.title }}
                                </div>
                                <div class="absolute px-2 py-1 text-xs text-white rounded top-2 right-2 bg-black/70">
                                    #{{ episode.episode_number }}
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-semibold transition-colors line-clamp-2 group-hover:text-indigo-500">
                                    {{ episode.title_en || episode.title }}
                                </h3>
                                <p v-if="episode.duration" class="mt-1 text-xs text-muted-foreground">{{ episode.duration }} دقيقة</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
