<template>
  <div class="w-full px-6 py-10 space-y-8">
    <div class="w-full flex flex-col gap-4 rounded-4xl border border-[#d8c2a0] bg-[#f7efe3] p-6 shadow-2xl shadow-slate-900/10">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-2xl font-semibold tracking-tight text-[#3f3a2f]">Mijn Pagina</h2>
        </div>
      </div>

      <div v-if="authStore.loading" class="text-center py-10">
        <div class="inline-flex items-center gap-3 rounded-full bg-[#e9dcc5]/75 px-4 py-3 text-sm font-medium text-[#3f3a2f] shadow-inner">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-base" />
          Laden...
        </div>
      </div>

      <div v-else-if="authStore.isAuthenticated" class="space-y-6">
        <section class="rounded-[28px] border border-[#d8c2a0] bg-[#fff8ed] px-5 py-3 shadow-sm">
          <h3 class="text-lg font-semibold text-[#3f3a2f]">Profielinformatie</h3>
          <div class="mt-3 space-y-2 text-sm text-[#4f513f]">
            <p><span class="font-semibold">E-mail:</span> {{ authStore.user?.email }}</p>
            <p><span class="font-semibold">Rol:</span>
              <span v-if="authStore.isAdmin" class="rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-semibold text-slate-950">Admin</span>
              <span v-else class="inline-flex items-center rounded-full bg-slate-300 px-2 py-1 text-xs font-semibold text-slate-800">Gebruiker</span>
            </p>
          </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
          <section class="rounded-[28px] border border-[#d8c2a0] bg-[#fff8ed] px-5 py-3 shadow-sm">
            <h3 class="text-lg font-semibold text-[#3f3a2f] mb-4">Mijn Gemaakte FotoDates</h3>
            <div v-if="createdPhotoDates.length === 0" class="text-sm text-[#6f6a54]">
              Je hebt nog geen FotoDates gemaakt.
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="photoDate in createdPhotoDates"
                :key="photoDate.id"
                class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4"
              >
                <div class="flex items-start justify-between gap-4 mb-3">
                  <div>
                    <h4 class="text-base font-semibold text-[#3f3a2f]">{{ photoDate.title }}</h4>
                    <p class="text-sm text-[#6f6a54]">Start {{ formatDateTime(photoDate.start_time) }}</p>
                  </div>
                  <button
                    type="button"
                    class="rounded-full border border-[#d8c2a0] bg-[#e9dcc5] px-4 py-2 text-xs font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg"
                    @click="deletePhotoDate(photoDate)"
                  >
                    Verwijderen
                  </button>
                </div>
                <p class="text-sm text-[#4f513f]">{{ photoDate.description || 'Geen beschrijving' }}</p>
              </div>
            </div>
          </section>

          <section class="rounded-[28px] border border-[#d8c2a0] bg-[#fff8ed] px-5 py-3 shadow-sm">
            <h3 class="text-lg font-semibold text-[#3f3a2f] mb-4">Mijn Deelgenomen FotoDates</h3>
            <div v-if="joinedPhotoDates.length === 0" class="text-sm text-[#6f6a54]">
              Je hebt nog niet deelgenomen aan FotoDates.
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="photoDate in joinedPhotoDates"
                :key="photoDate.id"
                class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4"
              >
                <div class="flex items-start justify-between gap-4 mb-3">
                  <div>
                    <h4 class="text-base font-semibold text-[#3f3a2f]">{{ photoDate.title }}</h4>
                    <p class="text-sm text-[#6f6a54]">Start {{ formatDateTime(photoDate.start_time) }}</p>
                  </div>
                  <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-[#3f3a2f]">{{ photoDate.marker_name || 'Marker' }}</span>
                </div>
                <p class="text-sm text-[#4f513f]">{{ photoDate.description || 'Geen beschrijving' }}</p>
                <div class="mt-4 flex justify-end">
                  <button
                    type="button"
                    class="rounded-full bg-[#e9dcc5] px-4 py-2 text-xs font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg"
                    @click="leavePhotoDate(photoDate)"
                  >
                    FotoDate verlaten
                  </button>
                </div>
              </div>
            </div>
          </section>
        </div>

        <section class="rounded-[28px] border border-[#d8c2a0] bg-[#fff8ed] px-5 py-3 shadow-sm">
          <h3 class="text-lg font-semibold text-[#3f3a2f] mb-4">Mijn FotoPosts</h3>
          <div v-if="myPhotoPosts.length === 0" class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#4f513f]">
            Je hebt nog geen fotoposts gemaakt. Deel je ontdekkingen op de Verkennen pagina!
          </div>
          <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="post in myPhotoPosts" :key="post.id">
              <PhotoPost :post="post" :isAuthenticated="true" @updated="fetchData" />
                  <button 
                    @click="deletePhotoPost(post)" 
                    :disabled="loading"
                    class="mt-4 w-full rounded-2xl bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100 disabled:opacity-50 transition-colors"
                  >
                    {{ loading ? 'Verwijderen...' : 'Foto Verwijderen' }}
                  </button>
            </div>
          </div>
        </section>

        <section class="rounded-[28px] border border-[#d8c2a0] bg-[#fff8ed] px-5 py-3 shadow-sm">
          <h3 class="text-lg font-semibold text-[#3f3a2f] mb-4">Mijn Bladwijzers</h3>
          <div v-if="bookmarkedPhotoPosts.length === 0" class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#4f513f]">
            Je hebt nog geen fotoposts als bladwijzer gemarkeerd. Verken en markeer je favorieten.
          </div>
          <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="post in bookmarkedPhotoPosts" :key="post.id">
              <PhotoPost :post="post" :isAuthenticated="true" @updated="fetchData" />
            </div>
          </div>
        </section>
      </div>
      <div v-else class="rounded-[28px] border border-[#d8c2a0] bg-[#f7efe3] p-6 text-sm text-[#4f513f] shadow-2xl shadow-slate-900/10">
        <p>Log in om je pagina te bekijken.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../lib/api'
import PhotoPost from '../components/PhotoPost.vue'

const authStore = useAuthStore()
const photoDates = ref<any[]>([])
const photoPosts = ref<any[]>([])
const bookmarkedPostIds = ref<number[]>([])
const loading = ref(false)
const error = ref('')

const createdPhotoDates = computed(() => {
  if (!authStore.user) return []
  return photoDates.value.filter((item) => item.created_by === authStore.user?.id)
})

const joinedPhotoDates = computed(() => {
  if (!authStore.user) return []
  const now = new Date()
  return photoDates.value.filter((item) =>
    new Date(item.end_time) > now &&
    Array.isArray(item.attendees) &&
    item.attendees.some((attendee: any) => attendee.id === authStore.user?.id)
  )
})

const myPhotoPosts = computed(() => {
  if (!authStore.user) return []
  return photoPosts.value.filter((post) => post.created_by === authStore.user?.id)
})

const bookmarkedPhotoPosts = computed(() => {
  return photoPosts.value.filter((post) => bookmarkedPostIds.value.includes(post.id))
})

const fetchData = async () => {
  await Promise.all([fetchPhotoDates(), fetchPhotoPosts(), fetchBookmarks()])
}

const fetchPhotoDates = async () => {
  loading.value = true
  error.value = ''

  try {
    const data = await api.get('/photo-dates')
    photoDates.value = data ?? []
  } catch (err: any) {
    error.value = err.message || 'Failed to load PhotoDates.'
  } finally {
    loading.value = false
  }
}

const fetchPhotoPosts = async () => {
  try {
    const posts = await api.get('/photo-posts')
    photoPosts.value = posts || []
    // Track which posts the current user has bookmarked
    bookmarkedPostIds.value = posts
      .filter((post: any) => post.bookmarked_by_user)
      .map((post: any) => post.id)
  } catch (err: any) {
    error.value = err.message || 'Failed to load photo posts.'
  }
}

const fetchBookmarks = async () => {
  // Bookmarks are now tracked from photo posts fetch
}

const deletePhotoDate = async (photoDate: any) => {
  if (!authStore.user || photoDate.created_by !== authStore.user.id) {
    error.value = 'Only the creator can delete this PhotoDate.'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await api.del(`/photo-dates/${photoDate.id}`)
    await fetchData()
  } catch (err: any) {
    error.value = err.message || 'Failed to delete PhotoDate.'
  } finally {
    loading.value = false
  }
}

const deletePhotoPost = async (post: any) => {
  if (!authStore.user || post.created_by !== authStore.user.id) {
    error.value = 'Alleen de maker kan deze FotoPost verwijderen.'
    return
  }
  if (!confirm('Weet je zeker dat je deze fotopost wilt verwijderen? Dit kan niet ongedaan worden gemaakt.')) {
    return
  }

  loading.value = true
  error.value = ''

  try {
    // Aanroep naar je Laravel backend
    await api.del(`/photo-posts/${post.id}`)
    // Data opnieuw ophalen zodat de post uit de lijst verdwijnt
    await fetchData()
  } catch (err: any) {
    error.value = err.message || 'Het verwijderen van de FotoPost is mislukt.'
  } finally {
    loading.value = false
  }
}

const leavePhotoDate = async (photoDate: any) => {
  if (!authStore.user) {
    error.value = 'Log in om deze FotoDatum te verlaten.'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await api.del(`/photo-dates/${photoDate.id}/attendees`)
    await fetchData()
  } catch (err: any) {
    error.value = err.message || 'Failed to leave PhotoDate.'
  } finally {
    loading.value = false
  }
}

const formatDateTime = (value: string) => {
  const date = new Date(value)
  return date.toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })
}

watch(
  () => authStore.user?.id,
  (userId) => {
    if (userId) {
      fetchData()
    }
  },
  { immediate: true }
)
</script>