<template>
  <div v-if="isOpen" class="photo-post-modal">
    <div class="modal-overlay" @click="close"></div>
    <div class="modal-content">
      <div class="modal-header">
        <h5>FotoPost Maken</h5>
        <button type="button" class="modal-close-button" @click="close">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitForm" class="space-y-5">
          <!-- Image Upload -->
          <div class="form-group">
            <label for="photoInput" class="form-label">Foto Selecteren</label>
            <input
              id="photoInput"
              type="file"
              accept="image/*"
              class="form-input"
              @change="handleImageSelect"
              required
            />
            <div v-if="imagePreview" class="mt-3 rounded-3xl border border-[#d8c2a0] bg-[#f7efe3] p-3 shadow-sm">
              <img :src="imagePreview" alt="Voorvertoning" class="w-full max-h-72 rounded-3xl object-cover" />
            </div>
          </div>

          <!-- Caption -->
          <div class="form-group">
            <label for="caption" class="form-label">Onderschrift (optioneel)</label>
            <textarea id="caption" class="form-input min-h-30" v-model="caption" rows="3" placeholder="Beschrijf deze mooie plek..."></textarea>
          </div>

          <div class="form-group">
            <label for="markerSelect" class="form-label">Kies een marker</label>
            <select id="markerSelect" class="form-input" v-model.number="selectedMarkerId" required>
              <option :value="null" disabled>-- Kies een locatie --</option>
              <option v-for="marker in markers" :key="marker.id" :value="marker.id">
                {{ marker.name || 'Marker' }} ({{ formatCoordinate(marker.lat) }}, {{ formatCoordinate(marker.lng) }})
              </option>
            </select>
          </div>

          <div v-if="selectedMarker" class="info-box">
            <p class="text-sm font-semibold">Geselecteerde locatie</p>
            <p>{{ selectedMarker.name || 'Marker' }}</p>
            <p class="text-sm text-[#6f6a54]">{{ formatCoordinate(selectedMarker.lat) }}, {{ formatCoordinate(selectedMarker.lng) }}</p>
          </div>

          <div v-if="error" class="error-box">{{ error }}</div>

          <div class="modal-footer">
            <button type="button" class="modal-cancel-button" @click="close">Annuleren</button>
            <button type="submit" class="modal-submit-button" :disabled="loading || !selectedMarker || !imageFile">
              {{ loading ? 'Uploaden...' : 'Post Maken' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'saved'): void
}>()

const authStore = useAuthStore()
const imageFile = ref<File | null>(null)
const imagePreview = ref('')
const caption = ref('')
const selectedMarkerId = ref<number | null>(null)
const markers = ref<any[]>([])
const error = ref('')
const loading = ref(false)

const selectedMarker = computed(() => {
  return markers.value.find((marker) => marker.id === selectedMarkerId.value) ?? null
})

const formatCoordinate = (value: unknown) => {
  const num = Number(value)
  return Number.isFinite(num) ? num.toFixed(4) : 'Onbekend'
}

watch(
  () => props.isOpen,
  async (open) => {
    if (open) {
      await loadMarkers()
    } else {
      resetForm()
    }
  }
)

const handleImageSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (file) {
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const loadMarkers = async () => {
  try {
    const data = await api.get('/markers')
    markers.value = Array.isArray(data) ? data : data?.data ?? []
    selectedMarkerId.value = markers.value.length > 0 ? markers.value[0].id : null
  } catch (err: any) {
    error.value = err.message || 'Kon geen markers laden.'
  }
}

const resetForm = () => {
  imageFile.value = null
  imagePreview.value = ''
  caption.value = ''
  selectedMarkerId.value = null
  error.value = ''
  loading.value = false
}

const close = () => {
  emit('close')
  resetForm()
}

const submitForm = async () => {
  if (!authStore.user || !imageFile.value || !selectedMarker.value) {
    error.value = 'Please fill in all required fields.'
    return
  }

  loading.value = true
  error.value = ''

  try {
    const imageUrl = imagePreview.value || ''

    const payload = {
      marker_id: Number(selectedMarker.value.id),
      marker_name: String(selectedMarker.value.name || ''),
      lat: Number(selectedMarker.value.lat),
      lng: Number(selectedMarker.value.lng),
      image_url: imageUrl,
      caption: caption.value,
    }

    await api.post('/photo-posts', payload)

    emit('saved')
    close()
  } catch (err: any) {
    error.value = err.message || 'Failed to create photo post.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.photo-post-modal {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  z-index: 1050;
}

.modal-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
}

.modal-content {
  position: relative;
  width: min(760px, 100%);
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 32px;
  border: 1px solid #d8c2a0;
  background: #f7efe3;
  box-shadow: 0 25px 80px rgba(45, 37, 18, 0.18);
  padding: 1.5rem;
}

.modal-header,
.modal-footer {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: center;
}

.modal-close-button,
.modal-cancel-button,
.modal-submit-button {
  border-radius: 9999px;
  border: 1px solid transparent;
  font-weight: 600;
  transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
}

.modal-close-button {
  background: #e9dcc5;
  color: #3f3a2f;
  padding: 0.6rem 1rem;
}

.modal-close-button:hover,
.modal-cancel-button:hover,
.modal-submit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 24px rgba(69, 52, 26, 0.15);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-label {
  font-size: 1.05rem;
  font-weight: 600;
  color: #3f3a2f;
}

.form-input {
  width: 100%;
  min-height: 3rem;
  border-radius: 1.25rem;
  border: 1px solid #d8c2a0;
  background: white;
  padding: 0.9rem 1rem;
  color: #1f2937;
  outline: none;
}

.form-input:focus {
  border-color: #8f9c5b;
  box-shadow: 0 0 0 4px rgba(143, 156, 91, 0.15);
}

.info-box,
.warning-box,
.error-box {
  border-radius: 1.5rem;
  padding: 1rem;
  font-size: 1.05rem;
}

.info-box {
  background: #f6f0e6;
  border: 1px solid #d8c2a0;
  color: #3f3a2f;
}

.warning-box {
  background: #fff4d9;
  border: 1px solid #d8c2a0;
  color: #4f3b12;
}

.error-box {
  background: #fbe8e8;
  border: 1px solid #d8a2a2;
  color: #7f2a2a;
}

.modal-cancel-button {
  background: #d8c9ae;
  color: #3f3a2f;
  padding: 0.9rem 1.3rem;
}

.modal-submit-button {
  background: #5a6640;
  color: #f6f0e6;
  padding: 0.9rem 1.7rem;
}

.modal-submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
