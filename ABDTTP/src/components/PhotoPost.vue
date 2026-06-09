<template>
  <div class="overflow-hidden rounded-4xl border border-[#d8c2a0] bg-[#f8f3e7] shadow-2xl shadow-slate-900/10 min-h-125 flex flex-col">
    <img :src="post.display_image" :alt="post.caption" class="h-72 w-full object-cover hover:opacity-85 transition-opacity cursor-zoom-in" @click="openZoom" />
    <div class="flex flex-col gap-4 p-5 grow">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h4 class="text-base font-semibold text-[#3f3a2f]">{{ post.created_by_username || 'Onbekend' }}</h4>
          <p class="text-xs uppercase tracking-[0.2em] text-[#6f6a54]">{{ formatDate(post.created_at) }}</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-[#3f3a2f]">{{ post.marker_name || 'Marker' }}</span>
      </div>
      <p class="text-sm leading-6 text-[#4f513f]">{{ post.caption || 'Geen beschrijving' }}</p>
      <p class="text-sm text-[#6f6a54] flex items-center gap-2">
        <font-awesome-icon :icon="['fas', 'map-marker-alt']" class="text-[#6f6a54]" />
        <LocationMapPreview :lat="post.lat" :lng="post.lng">
          {{ formatCoordinate(post.lat) }}, {{ formatCoordinate(post.lng) }}
        </LocationMapPreview>
      </p>

      <div class="mt-auto space-y-3">
        <div class="flex flex-wrap gap-3">
          <button
            v-if="isAuthenticated"
            type="button"
            :class="['rounded-full px-4 py-2 text-sm font-semibold transition duration-300 ease-out hover:-translate-y-0.5 hover:shadow-lg', hasLiked ? 'bg-rose-500 text-white' : 'bg-[#e9dcc5] text-[#3f3a2f] hover:bg-[#d8c9ae]']"
            @click="toggleLike"
            :disabled="loadingLike"
          >
            <font-awesome-icon :icon="hasLiked ? ['fas', 'heart'] : ['far', 'heart']" class="mr-2" />
            {{ likeCount }}
          </button>
          <div v-else class="rounded-full bg-[#f0d7d7] px-4 py-2 text-sm font-semibold text-[#7f4b55]">
            <font-awesome-icon :icon="['far', 'heart']" class="mr-2" />{{ likeCount }}
          </div>

          <button
            v-if="isAuthenticated"
            type="button"
            :class="['rounded-full px-4 py-2 text-sm font-semibold transition duration-300 ease-out hover:-translate-y-0.5 hover:shadow-lg', hasBookmarked ? 'bg-amber-400 text-slate-950' : 'bg-[#e9dcc5] text-[#3f3a2f] hover:bg-[#d8c9ae]']"
            @click="toggleBookmark"
            :disabled="loadingBookmark"
          >
            <font-awesome-icon :icon="hasBookmarked ? ['fas', 'bookmark'] : ['far', 'bookmark']" class="mr-2" />
            {{ bookmarkCount }}
          </button>
          <div v-else class="rounded-full bg-[#f9ebc9] px-4 py-2 text-sm font-semibold text-[#7f5f26]">
            <font-awesome-icon :icon="['far', 'bookmark']" class="mr-2" />{{ bookmarkCount }}
          </div>
        </div>
        <div v-if="error" class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-3 text-sm text-[#624d34]">
          {{ error }}
        </div>
      </div>
    </div>
  </div>


<ImageZoomModal :is-open="zoomOpen" :image-url="post.display_image" :image-alt="post.caption" @close="zoomOpen = false"/>

</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'
import ImageZoomModal from './ImageZoomModal.vue'
import LocationMapPreview from './LocationMapPreview.vue'

// Photo post card with zoom, like, and bookmark actions

const props = defineProps<{
  post: any
  isAuthenticated: boolean
}>()

const emit = defineEmits<{
  (event: 'updated'): void
}>()

const zoomOpen = ref(false)

const openZoom = () => {
  zoomOpen.value = true
}

const authStore = useAuthStore()
const loadingLike = ref(false)
const loadingBookmark = ref(false)
const error = ref('')
const likeCount = ref(0)
const bookmarkCount = ref(0)
const hasLiked = ref(false)
const hasBookmarked = ref(false)

const checkUserInteractions = () => {
  hasLiked.value = !!props.post?.liked_by_user
  hasBookmarked.value = !!props.post?.bookmarked_by_user
}

watch(
  () => props.post,
  (post) => {
    if (post) {
      likeCount.value = post.likes_count || 0
      bookmarkCount.value = post.bookmarks_count || 0
      checkUserInteractions()
    }
  },
  { immediate: true }
)

// Toggle the like state for the current post and refresh counts
const toggleLike = async () => {
  if (!props.isAuthenticated || !props.post) return

  loadingLike.value = true
  error.value = ''

  try {
    let updatedPost: any
    if (hasLiked.value) {
      updatedPost = await api.del(`/photo-posts/${props.post.id}/likes`)
    } else {
      updatedPost = await api.post(`/photo-posts/${props.post.id}/likes`)
    }

    if (updatedPost) {
      likeCount.value = updatedPost.likes_count || 0
      hasLiked.value = updatedPost.liked_by_user || false
    }
    emit('updated')
  } catch (err: any) {
    error.value = err.message || 'Failed to like post.'
  } finally {
    loadingLike.value = false
  }
}

const toggleBookmark = async () => {
  if (!props.isAuthenticated || !props.post) return

  loadingBookmark.value = true
  error.value = ''

  try {
    let updatedPost: any
    if (hasBookmarked.value) {
      updatedPost = await api.del(`/photo-posts/${props.post.id}/bookmarks`)
    } else {
      updatedPost = await api.post(`/photo-posts/${props.post.id}/bookmarks`)
    }

    if (updatedPost) {
      bookmarkCount.value = updatedPost.bookmarks_count || 0
      hasBookmarked.value = updatedPost.bookmarked_by_user || false
    }
    emit('updated')
  } catch (err: any) {
    error.value = err.message || 'Failed to bookmark post.'
  } finally {
    loadingBookmark.value = false
  }
}

const formatCoordinate = (value: unknown) => {
  const num = Number(value)
  return Number.isFinite(num) ? num.toFixed(4) : 'Onbekend'
}

const formatDate = (value: string) => {
  const date = new Date(value)
  return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.alert-sm {
  padding: 0.25rem 0.5rem;
  font-size: 1rem;
}
</style>
