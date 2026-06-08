<template>
  <div class="mx-auto flex w-full flex-col items-center justify-center gap-6 px-4 lg:flex-row lg:items-start lg:justify-center lg:px-6">
    <!-- map container on left -->
    <div class="flex w-full flex-col rounded-4xl border border-[#d8c2a0] bg-[#f7efe3] p-4 shadow-2xl shadow-slate-900/10 lg:flex-1">
      <div class="rounded-[28px] border border-[#d8c2a0] bg-[#f2e9d8] p-4 shadow-sm mb-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-2 text-sm font-semibold text-[#3f3a2f]">
            Filter:
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-[#d8c2a0] px-2 py-2 text-sm shadow-sm transition-colors" :class="filters.green ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-white text-[#3f3a2f] hover:bg-[#f0e7d4]'">
              <input type="checkbox" id="filter-green" v-model="filters.green" @change="applyFilters" class="h-4 w-4 rounded text-emerald-600 border-gray-300 focus:ring-emerald-500" />
              <span class="inline-flex items-center px-2">Landschap</span>
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-[#d8c2a0] px-2 py-2 text-sm shadow-sm transition-colors" :class="filters.orange ? 'bg-yellow-500 text-white hover:bg-yellow-600' : 'bg-white text-[#3f3a2f] hover:bg-[#f0e7d4]'">
              <input type="checkbox" id="filter-orange" v-model="filters.orange" @change="applyFilters" class="h-4 w-4 rounded text-yellow-600 border-gray-300 focus:ring-yellow-500" />
              <span class="inline-flex items-center px-2">Natuur</span>
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-[#d8c2a0] px-2 py-2 text-sm shadow-sm transition-colors" :class="filters.red ? 'bg-rose-500 text-white hover:bg-rose-600' : 'bg-white text-[#3f3a2f] hover:bg-[#f0e7d4]'">
              <input type="checkbox" id="filter-red" v-model="filters.red" @change="applyFilters" class="h-4 w-4 rounded text-rose-600 border-gray-300 focus:ring-rose-500" />
              <span class="inline-flex items-center px-2">Architectuur</span>
            </label>
          </div>
        </div>
      </div>
      <div id="map" class="h-130 w-full rounded-[28px] border border-[#d8c2a0] bg-[#e4dccb] shadow-inner" @click="clearSelectedMarker"></div>
    </div>

    <!-- sidebar on right -->
    <aside class="w-full rounded-4xl border border-[#d8c2a0] bg-[#f7efe3] p-6 shadow-2xl shadow-slate-900/10 lg:w-160 lg:flex-[1.5]">
      <div class="mb-5" v-if="!selectedMarker">
        <h2 class="text-xl font-semibold text-[#3f3a2f]">Welkom!</h2>
        <p class="mt-2 text-sm leading-6 text-[#4f513f]">Op deze website kunt U mooie plekken ontdekken voor het maken van fotos, of mensen uitnodigen voor een "fotodate", om samen op pad te gaan in bijvoorbeeld de natuur. </p>
      </div>

      <div v-if="showMarkerButtons && authStore.isAdmin" class="mb-5 flex flex-col gap-3">
        <button type="button" @click="openMarkerForm" class="rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg">Create New Marker</button>
        <button type="button" @click="hideMarkerButtons" class="rounded-full bg-[#c3b08c] px-5 py-2 text-sm font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#b09c76] hover:shadow-lg">Cancel</button>
      </div>

      <div class="grid items-start gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
          <MarkerInfoPanel
            v-if="selectedMarker"
            :marker="selectedMarker"
            :imageUrl="selectedMarkerImageUrl"
            :handleImageError="handleImageError"
            :isAdmin="authStore.isAdmin"
            :isAuthenticated="authStore.isAuthenticated"
            @delete-marker="deleteSelectedMarker"
            @create-photodate="openPhotoDateForm"
          />

          <div v-else class="rounded-3xl border border-dashed border-[#d8c2a0] bg-[#faf6ee] p-6 text-sm text-[#4f513f] shadow-sm">
            <h4 class="text-base font-semibold text-[#3f3a2f]">Geen marker geselecteerd</h4>
            <p class="mt-3 leading-6">Selecteer een marker op de kaart om details en weerinformatie te bekijken, of om een fotodate te maken.</p>
          </div>
        </div>

        <WeatherReport
          :weatherData="weather.weatherData.value"
          :weatherCodeToIcon="weather.weatherCodeToIcon"
          :formatDate="formatDate"
          :degreesToDirection="degreesToDirection"
          :error="weather.error.value"
        />
      </div>
    </aside>

    <MarkerCreationForm
      :is-open="showMarkerForm"
      :lat="formLat"
      :lng="formLng"
      @close="showMarkerForm = false"
      @marker-created="handleMarkerCreated"
    />

    <PhotoDateForm
      :is-open="showPhotoDateForm"
      :marker="selectedPhotoDateMarker"
      @close="closePhotoDateForm"
      @saved="closePhotoDateForm"
    />
  </div>
</template>
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useMapMarkers } from '../composables/useMapMarkers'
import { useWeather } from '../composables/useWeather'
import { useAuthStore } from '../stores/auth'
import api from '../lib/api'

// Map view includes interactive Leaflet map, marker sidebar, weather panel, and photo date creation
import MarkerCreationForm from '../components/MarkerCreationForm.vue'
import MarkerInfoPanel from '../components/MarkerInfoPanel.vue'
import WeatherReport from '../components/WeatherReport.vue'
import PhotoDateForm from '../components/PhotoDateForm.vue'
import L from 'leaflet'
import 'weather-icons/css/weather-icons.css'

const mapMarkers = useMapMarkers()
const weather = useWeather()
const authStore = useAuthStore()

const showMarkerButtons = ref(false)
const tempMarker = ref<L.CircleMarker | L.Marker | null>(null)
const selectedMarkerId = ref<number | null>(null)
const selectedMarkerLatLng = ref<{ lat: number; lng: number } | null>(null)
const selectedMarker = ref<any | null>(null)
const selectedMarkerImageUrl = ref('')
const showMarkerForm = ref(false)
const showPhotoDateForm = ref(false)
const selectedPhotoDateMarker = ref<any | null>(null)
const formLat = ref(0)
const formLng = ref(0)

// filter state
const filters = ref({
  green: true,
  orange: true,
  red: true,
})

const allMarkers = ref<any[]>([])

const clearSelectedMarker = () => {
  selectedMarker.value = null
  selectedMarkerId.value = null
  selectedMarkerLatLng.value = null
  selectedMarkerImageUrl.value = ''
  weather.weatherData.value = null
  weather.error.value = null
}

// Rebuild the visible marker layer based on the current filter settings
const applyFilters = () => {
  mapMarkers.clearMarkers()
  allMarkers.value.forEach((m: any) => {
    const lat = Number(m.lat)
    const lng = Number(m.lng)
    const type = Number(m.type) || 1

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
      console.warn('Skipping marker with invalid coordinates', m)
      return
    }

    const shouldShow =
      (type === 1 && filters.value.green) ||
      (type === 2 && filters.value.orange) ||
      (type === 3 && filters.value.red)

    if (!shouldShow) {
      return
    }

    const latText = Number.isFinite(lat) ? lat.toFixed(4) : 'Onbekend'
    const lngText = Number.isFinite(lng) ? lng.toFixed(4) : 'Onbekend'

    const popupContent = `<div class="marker-popup">
        <h6 style="margin: 0 0 8px 0">${m.name || 'Marker'}</h6>
        ${m.description ? `<p style="margin: 0 0 8px 0; font-size: 14px">${m.description}</p>` : ''}
        ${m.image_url ? `<img src="${m.image_url}" alt="Marker" style="width: 100%; height: auto; max-height: 200px; border-radius: 4px; margin-bottom: 8px">` : ''}
        <small style="color: #666">Lat: ${latText}, Lng: ${lngText}</small>
      </div>`
    const marker = mapMarkers.addMarker(lat, lng, popupContent, type, m.id)
    if (marker) {
      marker.bindPopup(popupContent, { maxWidth: 300 })
      attachClickToMarker(marker, m)
    }
  })
}

const formatDate = (dateStr: string): string => {
  const date = new Date(dateStr)
  const days = ['Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za']
  return `${days[date.getDay()]} ${date.getDate()}/${date.getMonth() + 1}`
}

// Attempt geolocation first, then fallback to a safe baseline coordinate
const getInitialMapCenter = async () => {
  const fallback = { lat: 52.9607, lng: 5.9191, zoom: 10 }

  if (!navigator.geolocation) {
    return fallback
  }

  return new Promise<{ lat: number; lng: number; zoom: number }>((resolve) => {
    const timeoutId = window.setTimeout(() => {
      resolve(fallback)
    }, 5000)

    navigator.geolocation.getCurrentPosition(
      (position) => {
        window.clearTimeout(timeoutId)
        resolve({
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          zoom: 12,
        })
      },
      () => {
        window.clearTimeout(timeoutId)
        resolve(fallback)
      },
      { timeout: 5000 }
    )
  })
}

const degreesToDirection = (degrees: number): string => {
  const directions = ['N', 'NNO', 'NO', 'ONO', 'O', 'OZO', 'ZO', 'ZZO', 'Z', 'ZZW', 'ZW', 'WZW', 'W', 'WNW', 'NW', 'NNW']
  const index = Math.round(degrees / 22.5) % 16
  return directions[index] ?? 'N'
}

const placeholderFallbackImages = [
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%234A90E2" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" font-size="24" fill="white" text-anchor="middle" dominant-baseline="middle"%3ENo+image+available%3C/text%3E%3C/svg%3E',
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%237B68EE" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" font-size="24" fill="white" text-anchor="middle" dominant-baseline="middle"%3ENo+image+available%3C/text%3E%3C/svg%3E',
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%2350C878" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" font-size="24" fill="white" text-anchor="middle" dominant-baseline="middle"%3ENo+image+available%3C/text%3E%3C/svg%3E',
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%23FF6B6B" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" font-size="24" fill="white" text-anchor="middle" dominant-baseline="middle"%3ENo+image+available%3C/text%3E%3C/svg%3E',
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%23FFD93D" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" font-size="24" fill="white" text-anchor="middle" dominant-baseline="middle"%3ENo+image+available%3C/text%3E%3C/svg%3E'
]

const getRandomPlaceholderImage = (): string => {
  const index = Math.floor(Math.random() * placeholderFallbackImages.length)
  return placeholderFallbackImages[index] ?? placeholderFallbackImages[0] ?? ''
}

const getRandomRemoteImage = (): string => {
  const id = Math.floor(Math.random() * 100000)
  return `https://place.dog/300/300?id=${id}`
}

const handleImageError = () => {
  // Valt terug op je eigen lokale placeholder arrays als het internet faalt
  selectedMarkerImageUrl.value = getRandomPlaceholderImage()
}

const openPhotoDateForm = () => {
  if (!selectedMarker.value) return
  selectedPhotoDateMarker.value = selectedMarker.value
  showPhotoDateForm.value = true
}

const closePhotoDateForm = () => {
  showPhotoDateForm.value = false
  selectedPhotoDateMarker.value = null
}

const selectMarker = async (markerData: any) => {
  selectedMarkerId.value = markerData.id
  selectedMarkerLatLng.value = { lat: markerData.lat, lng: markerData.lng }
  selectedMarker.value = markerData
  showMarkerButtons.value = false

  // FIX: Gebruik ALTIJD de display_image van Laravel (dit is je eigen upload ÓF de eend)
  // Mocht die onverhoopt leeg zijn, dan vallen we direct terug op de lokale placeholder array
  if (markerData.display_image) {
    selectedMarkerImageUrl.value = markerData.display_image
  } else {
    selectedMarkerImageUrl.value = getRandomPlaceholderImage()
  }

  await fetchWeatherData()
}

const attachClickToMarker = (marker: L.Marker, markerData: any) => {
  marker.on('click', () => {
    selectMarker(markerData)
  })
}

const loadMarkers = async () => {
  try {
    const data = await api.get('/markers')
    allMarkers.value = Array.isArray(data) ? data : data?.data ?? []
    applyFilters()
  } catch (err) {
    console.error('Failed to load markers', err)
  }
}

onMounted(async () => {
  const initialCenter = await getInitialMapCenter()
  mapMarkers.initializeMap('map', initialCenter.lat, initialCenter.lng, initialCenter.zoom)
  await loadMarkers()

  const map = mapMarkers.getMap()
  if (!map) return

  map.on('click', (e) => {
    clearSelectedMarker()
    const { lat, lng } = e.latlng

    if (tempMarker.value) {
      map.removeLayer(tempMarker.value as any)
    }

    tempMarker.value = L.circleMarker([lat, lng], {
      radius: 6,
      color: 'blue',
      fillColor: 'blue',
      fillOpacity: 1,
    }).addTo(map)

    showMarkerButtons.value = true
  })
})

const openMarkerForm = () => {
  if (tempMarker.value) {
    const latlng = tempMarker.value.getLatLng()
    formLat.value = latlng.lat
    formLng.value = latlng.lng
    showMarkerForm.value = true
  }
}

const hideMarkerButtons = () => {
  showMarkerButtons.value = false
  if (tempMarker.value && mapMarkers.getMap()) {
    mapMarkers.getMap()?.removeLayer(tempMarker.value as any)
    tempMarker.value = null
  }
}

const handleMarkerCreated = (marker: any) => {
  const newMarker = mapMarkers.addMarker(marker.lat, marker.lng, marker.popup, marker.type, marker.id)
  if (newMarker) attachClickToMarker(newMarker, marker)
  
  const map = mapMarkers.getMap()
  if (tempMarker.value && map) {
    map.removeLayer(tempMarker.value as any)
    tempMarker.value = null
  }
  showMarkerButtons.value = false
}

const fetchWeatherData = async () => {
  if (!selectedMarkerLatLng.value) return
  await weather.getWeatherForecast(selectedMarkerLatLng.value.lat, selectedMarkerLatLng.value.lng)
}



const deleteSelectedMarker = async () => {
  if (selectedMarkerId.value == null) return

  try {
    await api.del(`/markers/${selectedMarkerId.value}`)
    mapMarkers.removeMarker(selectedMarkerId.value)
    selectedMarkerId.value = null
    selectedMarkerLatLng.value = null
    selectedMarker.value = null
  } catch (err) {
    console.error('Failed to delete marker', err)
  }
}
</script>

<style scoped>
/* make the Leaflet map fill the container */
#map {
  width: 100%;
  height: 500px;
}

/* keep the sidebar from feeling cramped */
aside {
  overflow: visible;
}

.temp-min {
  font-size: 16px;
  opacity: 0.8;
}

.forecast-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 14px;
  opacity: 0.85;
}

/* filter bar styles */
.filter-bar {
  flex-shrink: 0;
}

.marker-dot {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.marker-dot.green {
  background-color: #50C878;
}

.marker-dot.orange {
  background-color: #FF8C00;
}

.marker-dot.red {
  background-color: #DC143C;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}
</style>