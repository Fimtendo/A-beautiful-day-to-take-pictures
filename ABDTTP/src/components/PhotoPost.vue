<template>
  <div class="overflow-hidden rounded-4xl border border-[#d8c2a0] bg-[#f8f3e7] shadow-2xl shadow-slate-900/10 min-h-125 flex flex-col">
    <img :src="post.image_url" :alt="post.caption" class="h-72 w-full object-cover hover:opacity-85 transition-opacity cursor-zoom-in" @click="openZoom" />
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
          {{ post.lat.toFixed(4) }}, {{ post.lng.toFixed(4) }}
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

  <ImageZoomModal :is-open="zoomOpen" :image-url="post.image_url" :image-alt="post.caption" @close="zoomOpen = false" />
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { supabase } from '../lib/supabase'
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

const checkUserInteractions = async () => {
  if (!authStore.user || !props.post) return

  try {
    const [{ data: likes }, { data: bookmarks }] = await Promise.all([
      supabase
        .from('photo_post_likes')
        .select('id')
        .eq('post_id', props.post.id)
        .eq('user_id', authStore.user.id)
        .single(),
      supabase
        .from('photo_post_bookmarks')
        .select('id')
        .eq('post_id', props.post.id)
        .eq('user_id', authStore.user.id)
        .single(),
    ])

    hasLiked.value = !!likes
    hasBookmarked.value = !!bookmarks
  } catch {
    // User hasn't liked or bookmarked yet
  }
}

watch(
  () => props.post,
  (post) => {
    if (post) {
      likeCount.value = post.like_count || 0
      bookmarkCount.value = post.bookmark_count || 0
      checkUserInteractions()
    }
  },
  { immediate: true }
)

// Toggle the like state for the current post and refresh counts
const toggleLike = async () => {
  if (!authStore.user || !props.post) return

  loadingLike.value = true
  error.value = ''

  try {
    if (hasLiked.value) {
      const { error: deleteError } = await supabase
        .from('photo_post_likes')
        .delete()
        .eq('post_id', props.post.id)
        .eq('user_id', authStore.user.id)

      if (deleteError) throw deleteError
      likeCount.value = Math.max(0, likeCount.value - 1)
    } else {
      const { error: insertError } = await supabase
        .from('photo_post_likes')
        .insert([{ post_id: props.post.id, user_id: authStore.user.id }])

      if (insertError) throw insertError
      likeCount.value += 1
    }

    hasLiked.value = !hasLiked.value
    emit('updated')
  } catch (err: any) {
    error.value = err.message || 'Failed to like post.'
  } finally {
    loadingLike.value = false
  }
}

const toggleBookmark = async () => {
  if (!authStore.user || !props.post) return

  loadingBookmark.value = true
  error.value = ''

  try {
    if (hasBookmarked.value) {
      const { error: deleteError } = await supabase
        .from('photo_post_bookmarks')
        .delete()
        .eq('post_id', props.post.id)
        .eq('user_id', authStore.user.id)

      if (deleteError) throw deleteError
      bookmarkCount.value = Math.max(0, bookmarkCount.value - 1)
    } else {
      const { error: insertError } = await supabase
        .from('photo_post_bookmarks')
        .insert([{ post_id: props.post.id, user_id: authStore.user.id }])

      if (insertError) throw insertError
      bookmarkCount.value += 1
    }

    hasBookmarked.value = !hasBookmarked.value
    emit('updated')
  } catch (err: any) {
    error.value = err.message || 'Failed to bookmark post.'
  } finally {
    loadingBookmark.value = false
  }
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
