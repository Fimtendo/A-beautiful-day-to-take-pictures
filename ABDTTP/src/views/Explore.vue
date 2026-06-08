<template>
  <div class="w-full px-6 py-10 space-y-8">
    <section class="rounded-4xl border border-[#d8c2a0] bg-[#f7efe3] p-6 shadow-2xl shadow-slate-900/10">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-2xl font-semibold tracking-tight text-[#3f3a2f]">Verkennen</h2>
          <p class="text-sm leading-6 text-[#4f513f]">Ontdek mooie plaatsen door foto's van onze gemeenschap.</p>
        </div>
        <button
          v-if="authStore.isAuthenticated"
          type="button"
          class="rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg"
          @click="showCreateForm = true"
        >
          <font-awesome-icon :icon="['fas', 'camera']" class="mr-2" />
          Maak Post
        </button>
      </div>

      <div v-if="error" class="mt-6 rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#624d34] shadow-sm">
        {{ error }}
      </div>

      <div v-if="loading && posts.length === 0" class="mt-6 text-center">
        <div class="inline-flex items-center gap-3 rounded-full bg-[#e9dcc5]/75 px-4 py-3 text-sm font-medium text-[#3f3a2f] shadow-inner">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-base" />
          Laden...
        </div>
      </div>

      <div v-if="posts.length === 0 && !loading" class="mt-6 rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#4f513f] shadow-sm">
        Nog geen fotoposts. Wees de eerste om je ontdekkingen te delen!
      </div>

      <div v-if="posts.length > 0" class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="post in posts" :key="post.id">
          <PhotoPost
            :post="post"
            :isAuthenticated="authStore.isAuthenticated"
            @updated="fetchPosts"
          />
        </div>
      </div>
    </section>

    <CreatePhotoPostForm :is-open="showCreateForm" @close="showCreateForm = false" @saved="handlePostSaved" />
  </div>
</template>

<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../lib/api'
import PhotoPost from '../components/PhotoPost.vue'
import CreatePhotoPostForm from '../components/CreatePhotoPostForm.vue'

const authStore = useAuthStore()
const posts = ref<any[]>([])
const loading = ref(false)
const error = ref('')
const showCreateForm = ref(false)

// Load all photoposts, then attach like/bookmark totals
const fetchPosts = async () => {
  loading.value = true
  error.value = ''

  try {
    const res = await api.get('/photo-posts')
    posts.value = res || []
  } catch (err: any) {
    error.value = err.message || 'Failed to load posts.'
  } finally {
    loading.value = false
  }
}

// Close the creation modal and refresh the post list
const handlePostSaved = () => {
  showCreateForm.value = false
  fetchPosts()
}

onBeforeUnmount(() => {
  showCreateForm.value = false
})

onMounted(fetchPosts)
</script>
