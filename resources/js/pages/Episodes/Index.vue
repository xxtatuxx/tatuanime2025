<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Episode, type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, router as inertia } from '@inertiajs/vue3';
import { ref, watch } from 'vue'; // تم إزالة onMounted و onUnmounted و ref(allEpisodes, nextPageUrl)
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Trash2, Edit, Eye } from 'lucide-vue-next';

// تعريف هيكل الترقيم الكامل لاستقباله من Laravel
interface PaginatedEpisodes {
  data: Episode[];
  links: { url: string | null; label: string; active: boolean }[];
  current_page: number;
  last_page: number;
  from: number;
  to: number;
  total: number;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Episodes', href: '/episodes' },
];

const page = usePage<{
  episodes: PaginatedEpisodes; // استخدام الهيكل الجديد
  flash?: { success?: string };
  filters?: { search?: string };
  auth: { user: any };
}>();

const search = ref(page.props.filters?.search || '');
const initialLoading = ref(false); // نعتمد على التحميل الأولي من props
// تمت إزالة loadingMore = ref(false);

// عرض إشعارات الفلاش
if (page.props.flash?.success) {
  toast.success(page.props.flash.success);
  page.props.flash.success = null;
}

// البحث
watch(search, (value) => {
  inertia.get(
    '/episodes',
    { search: value },
    {
      preserveState: true,
      replace: true,
      onStart: () => (initialLoading.value = true),
      // لا حاجة لتعديل episodes.value يدوياً، Inertia ستقوم بتحديث page.props.episodes
      onFinish: () => (initialLoading.value = false),
    }
  );
});

// حذف الحلقة
const deleteEpisode = (id: number) => {
  if (!confirm('هل أنت متأكد من حذف هذه الحلقة؟')) return;

  inertia.delete(route('episodes.destroy', id), {
    // بعد الحذف، نطلب إعادة تحميل قائمة الحلقات فقط للحصول على حالة جديدة
    onSuccess: () => {
        inertia.reload({ only: ['episodes'] });
        toast.success('تم حذف الحلقة بنجاح');
    },
    onError: () => {
      toast.error('حدث خطأ أثناء الحذف');
    },
  });
};

// *** تم حذف جميع الدوال المتعلقة بالتمرير اللانهائي (loadMore, onScroll, onMounted listener, onUnmounted) ***

// لم نعد نحتاج إلى هذه الدالة لأن Inertia تُحدث الـ props مباشرة
// const episodes = ref<Episode[]>([]); 
// const allEpisodes = ref<Episode[]>([]);
// const nextPageUrl = ref<string | null>(null);

</script>

<template>
  <Head>
    <title>Episodes</title>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap"
    />
  </Head>

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col flex-1 gap-4 p-4 rounded-xl font-[Cairo]">
      <div class="flex items-center w-full gap-2 mb-4">
        <Input
          v-model="search"
          placeholder="بحث عن الحلقات..."
          class="w-64 h-9 text-sm font-[Cairo]"
        />
        <Link 
          href="/episodes/create"
          class="bg-white-600 text-purple-600 text-sm font-semibold px-3 py-1.5 rounded-lg shadow hover:bg-gray-200 transition-colors"
        >
          + إضافة حلقة
        </Link>
      </div>

      <div
        v-if="initialLoading"
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
      >
        <div
          v-for="n in 10"
          :key="n"
          role="status"
          class="max-w-sm p-4 rounded-lg shadow animate-pulse dark:border-gray-300"
        >
          <div class="w-full h-40 mb-4 bg-gray-300 rounded-md"></div>
          <div class="h-3 bg-gray-300 rounded-full mb-2.5"></div>
          <div class="w-3/4 h-3 bg-gray-300 rounded-full"></div>
        </div>
      </div>

      <div
        v-else
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 font-[Cairo]"
      >
        <div
          v-for="episode in page.props.episodes.data"
          :key="episode.id"
          @click="inertia.visit(route('episodes.show', episode.id))"
          class="relative overflow-hidden transition transform bg-white rounded-lg shadow cursor-pointer dark:bg-black hover:scale-105 hover:shadow-lg"
        >
          <div class="relative">
            <img
              v-if="episode.thumbnail"
              :src="`/storage/${episode.thumbnail}`"
              alt="thumbnail"
              class="object-cover w-full h-40"
            />
            <div
              v-else
              class="flex items-center justify-center w-full h-40 text-gray-500 bg-gray-200 dark:bg-gray-700"
            >
              No Image
            </div>

            <div
              v-if="episode.is_published"
              class="absolute top-2 left-2 bg-green-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5 shadow-md"
            >
              يعرض الآن
            </div>

            <div
              v-if="episode.video_format"
              class="absolute top-2 right-2 bg-blue-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5 shadow-md"
            >
              {{ episode.video_format }}
            </div>

            <div
              class="absolute bottom-0 left-0 right-0 py-1 text-xl font-bold text-center text-white bg-black bg-opacity-60"
            >
              {{ episode.episode_number }} الحلقة
            </div>
          </div>
          <div class="flex flex-col gap-1 p-2">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold truncate">{{ episode.title }}</span>
              <span
                v-if="episode.series?.type"
                class="px-2 py-0.5 bg-indigo-500 text-white text-[10px] rounded"
              >
                {{ episode.series.type }}
              </span>
            </div>
          </div>

          <div
            class="flex justify-around p-2 border-t border-gray-200 dark:border-t-black dark:bg-black bg-gray-50 dark:bg-gray-900"
            @click.stop
          >
            <Link
              :href="route('episodes.show', episode.id)"
              class="flex items-center gap-1 text-xs text-blue-500 hover:text-blue-600"
            >
              <Eye class="w-4 h-4" /> عرض
            </Link>
            <Link
              :href="route('episodes.edit', episode.id)"
              class="flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600"
            >
              <Edit class="w-4 h-4" /> تعديل
            </Link>
            <button
              @click="deleteEpisode(episode.id)"
              class="flex items-center gap-1 text-xs text-red-500 hover:text-red-600"
            >
              <Trash2 class="w-4 h-4" /> حذف
            </button>
          </div>
        </div>
      </div>
      
      <div 
        v-if="page.props.episodes.links.length > 3" 
        class="flex justify-center w-full mt-6"
      >
        <template v-for="link in page.props.episodes.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            class="px-4 py-2 mx-1 text-sm transition-colors duration-200 rounded-lg"
            :class="{ 
              'bg-purple-600 text-white font-bold': link.active,
              'bg-gray-100 text-gray-700 hover:bg-gray-200': !link.active,
              // تعديلات للموبايل: تصغير المساحات
              'text-xs px-3 py-1.5 mx-0.5': link.label.length < 3, // الأرقام
              'text-xs px-2 py-1.5 mx-0.5': link.label.includes('Previous') || link.label.includes('Next') // الأسهم
            }"
            preserve-scroll
          />
          <span
            v-else
            v-html="link.label"
            class="px-4 py-2 mx-1 text-sm text-gray-400 rounded-lg cursor-not-allowed bg-gray-50"
            :class="{
              'text-xs px-3 py-1.5 mx-0.5': link.label.length < 3,
              'text-xs px-2 py-1.5 mx-0.5': link.label.includes('Previous') || link.label.includes('Next')
            }"
          />
        </template>
      </div>
      <div
        v-if="!initialLoading && page.props.episodes.data.length === 0"
        class="py-4 text-center text-gray-500 col-span-full"
      >
        لا توجد حلقات
      </div>
    </div>
  </AppLayout>
</template>

<style>
.loader {
  width: 48px; /* تأكد أن لا يوجد أي حرف غريب هنا */
  height: 48px;
  border-radius: 50%;
  display: inline-block;
  position: relative;
  border: 3px solid;
  border-color: #FFF #FFF transparent transparent;
  box-sizing: border-box;
  animation: rotation 1s linear infinite;
}
.loader::after,
.loader::before {
  content: '';
  box-sizing: border-box;
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  margin: auto;
  border: 3px solid;
  border-color: transparent transparent #FF3D00 #FF3D00;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  box-sizing: border-box;
  animation: rotationBack 0.5s linear infinite;
  transform-origin: center center;
}
.loader::before {
  width: 32px;
  height: 32px;
  border-color: #FFF #FFF transparent transparent;
  animation: rotation 1.5s linear infinite;
}
    
@keyframes rotation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
} 
@keyframes rotationBack {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(-360deg);
  }
}
</style>
