<template>
  <div class="relative inline-block" ref="triggerRef" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
    <span class="cursor-help underline decoration-dotted decoration-[#8f9c5b] underline-offset-4">
      <slot />
    </span>

    <div
      v-if="showPreview"
      ref="previewRef"
      class="location-map-preview fixed z-50 rounded-3xl border border-[#d8c2a0] bg-[#fff8ed] p-3 shadow-2xl shadow-slate-900/20"
      :style="previewStyle"
    >
      <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#3f3a2f]">Locatie voorbeeld</div>
      <div :id="mapId" class="h-40 w-56 rounded-3xl"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, reactive, ref } from 'vue'
import L from 'leaflet'

const props = defineProps<{
  lat: number
  lng: number
}>()

const showPreview = ref(false)
const triggerRef = ref<HTMLElement | null>(null)
const previewRef = ref<HTMLDivElement | null>(null)
const mapInstance = ref<L.Map | null>(null)
const mapId = `location-preview-${Math.random().toString(36).slice(2)}`
const previewStyle = reactive<{ left: string; top: string }>({ left: '0px', top: '0px' })

const destroyMap = () => {
  if (mapInstance.value) {
    mapInstance.value.remove()
    mapInstance.value = null
  }
}

const updatePreviewPosition = () => {
  const trigger = triggerRef.value
  if (!trigger) return

  const rect = trigger.getBoundingClientRect()
  const previewWidth = 224
  const previewHeight = 204
  let left = rect.left + rect.width / 2 - previewWidth / 2
  left = Math.min(Math.max(10, left), window.innerWidth - previewWidth - 10)

  let top = rect.top - previewHeight - 12
  if (top < 10) {
    top = rect.bottom + 12
  }

  previewStyle.left = `${left + window.scrollX}px`
  previewStyle.top = `${top + window.scrollY}px`
}

const initMap = async () => {
  await nextTick()
  const mapContainer = document.getElementById(mapId)
  const lat = Number(props.lat)
  const lng = Number(props.lng)
  if (!mapContainer || mapInstance.value || !Number.isFinite(lat) || !Number.isFinite(lng)) return

  mapInstance.value = L.map(mapId, {
    zoomControl: false,
    attributionControl: false,
    dragging: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    boxZoom: false,
    keyboard: false,
  }).setView([props.lat, props.lng], 14)

  const map = mapInstance.value! as L.Map
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 12,
  }).addTo(map)

  L.marker([props.lat, props.lng]).addTo(map)
  setTimeout(() => mapInstance.value?.invalidateSize(), 50)
}

const handleMouseEnter = async () => {
  updatePreviewPosition()
  showPreview.value = true
  await initMap()
}

const handleMouseLeave = () => {
  showPreview.value = false
  destroyMap()
}
</script>

<style scoped>
.location-map-preview {
  min-width: 224px;
  max-width: 224px;
  padding: 0.9rem;
}
</style>
