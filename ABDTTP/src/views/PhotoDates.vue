<template>
  <div class="w-full px-6 py-10 space-y-8">
    <div class="flex flex-col gap-4 rounded-4xl border border-[#d8c2a0] bg-[#f7efe3] p-6 shadow-2xl shadow-slate-900/10 lg:flex-2">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-2xl font-semibold tracking-tight text-[#3f3a2f]">FotoDates</h2>
          <p class="text-sm leading-6 text-[#4f513f]">Vind hier de uitnodigingen voor aankomende fotodates.</p>
        </div>
        <div v-if="loading" class="text-sm text-[#6f6a54]">Laden…</div>
      </div>

      <div v-if="error" class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#624d34] shadow-sm">
        {{ error }}
      </div>

      <div v-if="photoDates.length === 0 && !loading" class="rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#4f513f] shadow-sm">
        Geen FotoDates gevonden. Maak er de eerste aan vanuit een marker op de kaart.
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div v-for="photoDate in photoDates" :key="photoDate.id" class="rounded-[28px] border border-[#d8c2a0] bg-[#fff8ed] p-6 shadow-sm">
          <div class="flex flex-col gap-3">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-lg font-semibold text-[#3f3a2f]">{{ photoDate.title }}</h3>
                <p class="text-sm text-[#6f6a54]">door {{ photoDate.created_by_username || 'Onbekend' }}</p>
              </div>
              <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-[#3f3a2f]">{{ photoDate.marker_name || 'Marker' }}</span>
            </div>

            <p class="text-sm text-[#4f513f]">{{ photoDate.description || 'Geen beschrijving opgegeven.' }}</p>

            <div class="grid gap-2 rounded-3xl border border-[#d4b18b] bg-[#f7efe3] p-4 text-sm text-[#4f513f]">
              <div><strong>Startdatum:</strong> {{ formatDateTime(photoDate.start_time) }}</div>
              <div><strong>Einddatum:</strong> {{ formatDateTime(photoDate.end_time) }}</div>
              <div>
                <strong>Locatie:</strong>
                <LocationMapPreview :lat="photoDate.lat" :lng="photoDate.lng">
                  {{ formatCoordinate(photoDate.lat) }}, {{ formatCoordinate(photoDate.lng) }}
                </LocationMapPreview>
              </div>
              <div><strong>deelnemers:</strong> {{ attendeesCount(photoDate) }} / {{ photoDate.capacity }}</div>
            </div>

            <button
              type="button"
              class="w-full rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="!canJoin(photoDate)"
              @click="joinPhotoDate(photoDate)"
            >
              {{ joinButtonText(photoDate) }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../lib/api'
import LocationMapPreview from '../components/LocationMapPreview.vue'

const router = useRouter()
const authStore = useAuthStore()
const photoDates = ref<any[]>([])
const loading = ref(false)
const error = ref('')

const fetchPhotoDates = async () => {
  loading.value = true
  error.value = ''

  try {
    // Fetch all photo dates
    const data = await api.get('/photo-dates')
    // Only keep upcoming photo dates
    photoDates.value = (data ?? []).filter((item: any) => {
      const endTime = new Date(item.end_time)
      return endTime > new Date()
    })
  } catch (err: any) {
    console.error('Error fetching photo dates:', err)
    error.value = err.message || 'Failed to load PhotoDates.'
  } finally {
    loading.value = false
  }
}

const formatDateTime = (value: string) => {
  const date = new Date(value)
  return date.toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })
}

const attendeesCount = (photoDate: any) => {
  return Array.isArray(photoDate.attendees) ? photoDate.attendees.length : 0
}

const hasJoined = (photoDate: any) => {
  if (!authStore.user) return false
  return Array.isArray(photoDate.attendees) && photoDate.attendees.some((attendee: any) => attendee.id === authStore.user?.id)
}

const isFull = (photoDate: any) => {
  return attendeesCount(photoDate) >= photoDate.capacity
}

const formatCoordinate = (value: unknown) => {
  const num = Number(value)
  return Number.isFinite(num) ? num.toFixed(4) : 'Onbekend'
}

const canJoin = (photoDate: any) => {
  if (!authStore.user) return true
  if (hasJoined(photoDate)) return false
  if (isFull(photoDate)) return false
  const now = new Date()
  const startTime = new Date(photoDate.start_time)
  const endTime = new Date(photoDate.end_time)
  // Conditie 1: De photodate moet nog beginnen (in de toekomst)
  const isFuture = startTime > now
  // Conditie 2: De photodate is nu bezig (starttijd is geweest, maar eindtijd nog niet)
  const isInProgress = startTime <= now && endTime > now
  // Je mag deelnemen als het in de toekomst is OF als het nu bezig is
  return isFuture || isInProgress
}


const joinButtonText = (photoDate: any) => {
  if (!authStore.user) return 'Inloggen om deel te nemen'
  if (hasJoined(photoDate)) return 'Je doet al mee'
  if (isFull(photoDate)) return 'Vol'

  const now = new Date()
  const startTime = new Date(photoDate.start_time)
  const endTime = new Date(photoDate.end_time)

  // Als de activiteit al bezig is, toon een aangepaste tekst
  if (startTime <= now && endTime > now) {
    return 'Nu instappen / Deelnemen'
  }

  return 'Deelnemen aan FotoDatum'
}


const joinPhotoDate = async (photoDate: any) => {
  if (!authStore.user) {
    router.push('/login')
    return
  }

  if (hasJoined(photoDate) || isFull(photoDate)) return

  try {
    await api.post(`/photo-dates/${photoDate.id}/attendees`)
    await fetchPhotoDates()
  } catch (err: any) {
    error.value = err.message || 'Failed to join PhotoDate.'
  }
}

onMounted(fetchPhotoDates)
</script>
<!-- 
hello there! im making a web application for posting photos and creating so called "photodates". the frontend is built in Vue, and the backend is in laravel. -->