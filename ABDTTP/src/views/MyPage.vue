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
import { computed, onMounted, ref, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import { supabase } from '../lib/supabase'
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
  return photoDates.value.filter((item) =>
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
    await removeExpiredPhotoDates()

    const { data, error: fetchError } = await supabase
      .from('photo_dates')
      .select('*')
      .order('start_time', { ascending: true })

    if (fetchError) {
      throw fetchError
    }

    photoDates.value = (data ?? []).filter((item: any) => new Date(item.end_time) > new Date())
  } catch (err: any) {
    error.value = err.message || 'Failed to load PhotoDates.'
  } finally {
    loading.value = false
  }
}

const fetchPhotoPosts = async () => {
  try {
    // First get all posts
    const { data: postsData, error: postsError } = await supabase
      .from('photo_posts')
      .select('*')
      .order('created_at', { ascending: false })

    if (postsError) throw postsError

    if (!postsData || postsData.length === 0) {
      photoPosts.value = []
      return
    }

    // Get like counts for all posts
    const postIds = postsData.map(p => p.id)
    const { data: likesData, error: likesError } = await supabase
      .from('photo_post_likes')
      .select('post_id')
      .in('post_id', postIds)

    if (likesError) throw likesError

    // Get bookmark counts for all posts
    const { data: bookmarksData, error: bookmarksError } = await supabase
      .from('photo_post_bookmarks')
      .select('post_id')
      .in('post_id', postIds)

    if (bookmarksError) throw bookmarksError

    // Count likes and bookmarks per post
    const likeCounts = likesData?.reduce((acc: any, like: any) => {
      acc[like.post_id] = (acc[like.post_id] || 0) + 1
      return acc
    }, {}) || {}

    const bookmarkCounts = bookmarksData?.reduce((acc: any, bookmark: any) => {
      acc[bookmark.post_id] = (acc[bookmark.post_id] || 0) + 1
      return acc
    }, {}) || {}

    // Combine posts with counts
    photoPosts.value = postsData.map((post: any) => ({
      ...post,
      like_count: likeCounts[post.id] || 0,
      bookmark_count: bookmarkCounts[post.id] || 0,
    }))
  } catch (err: any) {
    error.value = err.message || 'Failed to load photo posts.'
  }
}

const fetchBookmarks = async () => {
  if (!authStore.user) {
    bookmarkedPostIds.value = []
    return
  }

  try {
    const { data, error: fetchError } = await supabase
      .from('photo_post_bookmarks')
      .select('post_id')
      .eq('user_id', authStore.user.id)

    if (fetchError) throw fetchError

    bookmarkedPostIds.value = (data ?? []).map((b: any) => b.post_id)
  } catch (err: any) {
    console.error('Failed to load bookmarks:', err)
  }
}

const removeExpiredPhotoDates = async () => {
  const now = new Date().toISOString()
  await supabase.from('photo_dates').delete().lte('end_time', now)
}

const deletePhotoDate = async (photoDate: any) => {
  if (!authStore.user || photoDate.created_by !== authStore.user.id) {
    error.value = 'Only the creator can delete this PhotoDate.'
    return
  }

  loading.value = true
  error.value = ''

  try {
    const { error: deleteError } = await supabase
      .from('photo_dates')
      .delete()
      .eq('id', photoDate.id)

    if (deleteError) {
      throw deleteError
    }

    await fetchData()
  } catch (err: any) {
    error.value = err.message || 'Failed to delete PhotoDate.'
  } finally {
    loading.value = false
  }
}

const leavePhotoDate = async (photoDate: any) => {
  if (!authStore.user) {
    error.value = 'Log in om deze FotoDatum te verlaten.'
    return
  }

  const attendees = Array.isArray(photoDate.attendees) ? photoDate.attendees.filter((attendee: any) => attendee.id !== authStore.user?.id) : []

  loading.value = true
  error.value = ''

  try {
    const { error: updateError } = await supabase
      .from('photo_dates')
      .update({ attendees })
      .eq('id', photoDate.id)

    if (updateError) {
      throw updateError
    }

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
  }
)

onMounted(fetchData)
</script>