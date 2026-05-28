<template>
  <div v-if="marker" class="rounded-4xl border border-[#d8c2a0] bg-[#fff8ed] p-5 shadow-2xl shadow-slate-900/10 overflow-hidden">
    <div v-if="imageUrl" class="mb-4 overflow-hidden rounded-[28px] bg-[#e4dccb] cursor-pointer hover:opacity-85 transition-opacity" @click="openZoom">
      <img
        :src="imageUrl"
        @error="handleImageError"
        class="h-64 w-full object-cover"
        alt="Marker image"
      />
    </div>
    <div v-else class="mb-4 flex h-64 items-center justify-center rounded-[28px] bg-[#e4dccb] text-sm text-[#6f6a54]">
      <span>Geen afbeelding beschikbaar</span>
    </div>

    <div class="space-y-4">
      <div>
        <h5 class="text-xl font-semibold text-[#3f3a2f]">{{ marker.name || 'Marker' }}</h5>
        <p v-if="marker.description" class="text-sm leading-6 text-[#4f513f]">{{ marker.description }}</p>
        <p class="text-sm text-[#6f6a54]">Lat: {{ marker.lat.toFixed(4) }}, Lng: {{ marker.lng.toFixed(4) }}</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button
          v-if="isAuthenticated"
          type="button"
          @click="$emit('create-photodate')"
          class="rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg"
        >
          FotoDatum Maken
        </button>
        <button
          v-if="isAdmin"
          type="button"
          @click="$emit('delete-marker')"
          class="rounded-full border border-[#d8c2a0] bg-[#fff1e0] px-5 py-2 text-sm font-semibold text-[#8a3a2c] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#f2d7c1] hover:shadow-lg"
        >
          Marker verwijderen
        </button>
      </div>
    </div>
  </div>

  <ImageZoomModal :is-open="zoomOpen" :image-url="imageUrl" :image-alt="marker?.name || 'Marker'" @close="zoomOpen = false" />
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ImageZoomModal from './ImageZoomModal.vue'

const zoomOpen = ref(false)

// Open the image preview modal for this marker
const openZoom = () => {
  zoomOpen.value = true
}

const props = defineProps<{
  marker: any
  imageUrl: string
  isAdmin: boolean
  isAuthenticated: boolean
  handleImageError: () => void
}>()

defineEmits<{
  (event: 'delete-marker'): void
  (event: 'create-photodate'): void
}>()
</script>
