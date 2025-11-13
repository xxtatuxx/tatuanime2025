
<script setup lang="ts">
import AppLayout from '@/layouts/AR-HomeLayout.vue';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router as inertia } from '@inertiajs/vue3';
import { Episode } from '@/types';

// ----- السلايدر -----
const slides = ref([
  { id: 1, image: '192.168.0.3:8080/animes/onepiece_cover.jpg', title: 'Slide 1', subtitle: 'Subtitle 1' },
  { id: 2, image: '/animes/naruto.jpg', title: 'Slide 2', subtitle: 'Subtitle 2' },
  { id: 3, image: '/animes/onepiece_cover.jpg', title: 'Slide 3', subtitle: 'Subtitle 3' },
]);

const currentSlide = ref(0);
let interval: number;

function nextSlide() { currentSlide.value = (currentSlide.value + 1) % slides.value.length; }
function prevSlide() { currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length; }
function goToSlide(index: number) { currentSlide.value = index; }

onMounted(() => { interval = setInterval(nextSlide, 5000); });
onUnmounted(() => { clearInterval(interval); });

// ----- حلقات Carousel -----
const page = usePage<{
  episodes: { data: Episode[]; next_page_url?: string };
  filters?: { search?: string };
}>();

const episodes = ref<Episode[]>(page.props.episodes.data || []);
const nextPageUrl = ref(page.props.episodes.next_page_url || null);
const currentPage = ref(1);
const loadingMore = ref(false);
const search = ref(page.props.filters?.search || '');
const carouselRef = ref<HTMLDivElement | null>(null);

// البحث
watch(search, (value) => {
  currentPage.value = 1;
  inertia.get('/ar/home', { search: value, page: currentPage.value }, {
    preserveState: true,
    preserveScroll: true,   // ← يحافظ على Scroll الحالي
    only: ['episodes', 'filters'],
    onSuccess: (pageResponse) => {
      episodes.value = pageResponse.props.episodes.data;
      nextPageUrl.value = pageResponse.props.episodes.next_page_url || null;
    },
  });
});

// تحميل المزيد عند تمرير الشريط
const loadMoreEpisodes = () => {
  if (!nextPageUrl.value || loadingMore.value) return;
  loadingMore.value = true;
  currentPage.value++;

  inertia.get('/ar/home', { page: currentPage.value, search: search.value }, {
    preserveState: true,
    preserveScroll: true,   // ← يحافظ على Scroll الحالي
    only: ['episodes'],
    onSuccess: (pageResponse) => {
      const newData = pageResponse.props.episodes.data;
      episodes.value.push(...newData);
      nextPageUrl.value = pageResponse.props.episodes.next_page_url || null;
    },
    onFinish: () => { loadingMore.value = false; },
  });
};

// مراقبة تمرير الشريط
const onCarouselScroll = () => {
  const el = carouselRef.value;
  if (!el) return;
  if (el.scrollLeft + el.clientWidth >= el.scrollWidth - 50) {
    loadMoreEpisodes();
  }
};

// فتح الحلقة عند الضغط
const goToEpisode = (id: number) => {
  inertia.visit(`/episodes/${id}`);
};

// أزرار تحريك الشريط
const scrollLeft = () => { carouselRef.value?.scrollBy({ left: -300, behavior: 'smooth' }); };
const scrollRight = () => { carouselRef.value?.scrollBy({ left: 300, behavior: 'smooth' }); };
</script>

<template>
  <Head title="home" />
  <AppLayout>
    <!-- السلايدر -->
    <div class="relative overflow-hidden" style="width: 100vw; left: 50%; transform: translateX(-50%);">
      <div class="flex transition-transform duration-700 ease-in-out" :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
        <div v-for="slide in slides" :key="slide.id" class="flex-shrink-0 w-screen h-80 md:h-[350px] relative">
          <img :src="slide.image" :alt="slide.title" class="object-cover w-full h-full max-w-full max-h-full"/>
          <div class="absolute p-4 text-white rounded-lg bottom-8 left-8 md:bottom-16 md:left-16 bg-black/40 backdrop-blur-md">
            <h2 class="text-xl font-bold md:text-3xl">{{ slide.title }}</h2>
            <p class="text-sm md:text-lg">{{ slide.subtitle }}</p>
          </div>
        </div>
      </div>
      <button @click="prevSlide" class="absolute p-3 text-white -translate-y-1/2 rounded-full shadow-lg top-1/2 left-4 bg-black/50 md:p-4 hover:bg-black/70">‹</button>
      <button @click="nextSlide" class="absolute p-3 text-white -translate-y-1/2 rounded-full shadow-lg top-1/2 right-4 bg-black/50 md:p-4 hover:bg-black/70">›</button>
    </div>

    <!-- عنوان الحلقات + أزرار الشريط -->
    <div class="flex items-center justify-between px-4 mt-6">
      <h2 class="text-2xl font-bold font-[Cairo] mb-4">آخر الحلقات المضافة</h2>
      <div class="flex gap-2">
        <button @click="scrollLeft" class="px-3 py-1 text-white bg-purple-600 rounded hover:bg-purple-700">‹</button>
        <button @click="scrollRight" class="px-3 py-1 text-white bg-purple-600 rounded hover:bg-purple-700">›</button>
      </div>
    </div>

    <!-- Carousel الحلقات -->
    <div ref="carouselRef" @scroll="onCarouselScroll" class="flex gap-4 px-4 pb-4 overflow-x-auto scrollbar-hide" style="scroll-behavior: smooth;">
      <div v-for="episode in episodes" :key="episode.id" @click="goToEpisode(episode.id)" class="flex-shrink-0 w-48 transition-transform bg-white rounded-lg shadow cursor-pointer hover:scale-105">
        <div class="relative w-full h-40">
          <img v-if="episode.thumbnail" :src="`/storage/${episode.thumbnail}`" alt="thumbnail" class="object-cover w-full h-full rounded-t-lg"/>
          <div v-else class="flex items-center justify-center w-full h-full text-gray-500 bg-gray-200 rounded-t-lg">No Image</div>
          <div v-if="episode.is_published" class="absolute top-2 left-2 bg-green-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5 shadow-md">يعرض الآن</div>
          <div v-if="episode.video_format" class="absolute top-2 right-2 bg-blue-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5 shadow-md">{{ episode.video_format }}</div>
        </div>
        <div class="flex flex-col gap-1 p-2">
          <span class="text-sm font-semibold truncate">{{ episode.title }}</span>
          <span class="text-xs text-gray-500 truncate">{{ episode.episode_number }} الحلقة</span>
        </div>
      </div>
      <div v-if="loadingMore" class="flex gap-4">
        <div v-for="n in 3" :key="n" class="flex-shrink-0 w-48 h-56 p-4 bg-gray-300 rounded-lg shadow animate-pulse"></div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
div[style*='translateX'] { touch-action: pan-y; }
html, body { margin: 0; padding: 0; overflow-x: hidden; }
</style>
