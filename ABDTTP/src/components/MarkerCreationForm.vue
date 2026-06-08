<template>
  <div class="marker-creation-modal" v-if="isOpen">
    <div class="modal-overlay" @click="close"></div>
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="text-lg font-semibold text-[#3f3a2f]">Nieuwe Marker Maken</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitMarker" class="space-y-5">
          <!-- Name Field -->
          <div>
            <label for="markerName" class="block text-sm font-semibold text-[#3f3a2f] mb-2">Marker Naam *</label>
            <input
              type="text"
              class="w-full rounded-2xl border border-[#d8c2a0] bg-[#f7efe3] px-4 py-3 text-sm text-[#3f3a2f] outline-none transition focus:border-[#8f9c5b] focus:ring-2 focus:ring-[#8f9c5b]/20"
              id="markerName"
              v-model="form.name"
              placeholder="bijv., Mooie Park, Historisch Gebouw"
              required
            />
          </div>

          <!-- Description Field -->
          <div>
            <label for="markerDesc" class="block text-sm font-semibold text-[#3f3a2f] mb-2">Beschrijving</label>
            <textarea
              class="w-full rounded-2xl border border-[#d8c2a0] bg-[#f7efe3] px-4 py-3 text-sm text-[#3f3a2f] outline-none transition focus:border-[#8f9c5b] focus:ring-2 focus:ring-[#8f9c5b]/20"
              id="markerDesc"
              v-model="form.description"
              placeholder="Voeg details over deze locatie toe..."
              rows="3"
            ></textarea>
          </div>

          <!-- Marker Type -->
          <div>
            <label for="markerType" class="block text-sm font-semibold text-[#3f3a2f] mb-2">Marker Type</label>
            <select class="w-full rounded-2xl border border-[#d8c2a0] bg-[#f7efe3] px-4 py-3 text-sm text-[#3f3a2f] outline-none transition focus:border-[#8f9c5b] focus:ring-2 focus:ring-[#8f9c5b]/20" id="markerType" v-model.number="form.type">
              <option :value="1">Groen</option>
              <option :value="2">Oranje</option>
              <option :value="3">Rood</option>
            </select>
          </div>

          <!-- Image Upload -->
          <div>
            <label for="markerImage" class="block text-sm font-semibold text-[#3f3a2f] mb-2">Afbeelding Uploaden</label>
            <input
              type="file"
              class="w-full rounded-2xl border border-[#d8c2a0] bg-[#f7efe3] px-4 py-3 text-sm text-[#3f3a2f] outline-none transition focus:border-[#8f9c5b] focus:ring-2 focus:ring-[#8f9c5b]/20"
              id="markerImage"
              @change="handleImageChange"
              accept="image/*"
            />
            <p class="mt-2 text-xs text-[#6f6a54]">Max 8MB. Ondersteund: JPG, PNG, WebP</p>
          </div>

          <!-- Image Preview -->
          <div v-if="imagePreview" class="space-y-3">
            <img :src="imagePreview" alt="Voorvertoning" class="w-full rounded-3xl object-cover" style="max-height: 200px" />
            <button
              type="button"
              class="rounded-full border border-[#d8c2a0] bg-[#fff1e0] px-4 py-2 text-sm font-semibold text-[#8a3a2c] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#f2d7c1]"
              @click="removeImage"
            >
              Afbeelding Verwijderen
            </button>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="rounded-3xl border border-[#d8a2a2] bg-[#fbe8e8] p-3 text-sm text-[#7f2a2a]">
            {{ error }}
          </div>

          <!-- Buttons -->
          <div class="modal-footer">
            <button type="button" class="rounded-full border border-[#d8c2a0] bg-[#d8c9ae] px-4 py-2 text-sm font-semibold text-[#3f3a2f] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#c5b48e]" @click="close">Annuleren</button>
            <button type="submit" class="rounded-full bg-[#5a6640] px-4 py-2 text-sm font-semibold text-[#f6f0e6] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#76855b] disabled:opacity-60" :disabled="loading">
              {{ loading ? 'Aanmaken...' : 'Marker Maken' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'

// State and helper logic for creating a new marker from the modal
interface MarkerForm {
  name: string
  description: string
  type: number
}

interface Props {
  isOpen: boolean
  lat: number
  lng: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  close: []
  markerCreated: [marker: any]
}>()

const form = ref<MarkerForm>({
  name: '',
  description: '',
  type: 1
})

const selectedFile = ref<File | null>(null)
const imagePreview = ref<string>('')
const loading = ref(false)
const error = ref('')

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'File size must be less than 5MB'
    return
  }

  // Validate file type
  const validTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!validTypes.includes(file.type)) {
    error.value = 'Only JPG, PNG, and WebP images are supported'
    return
  }

  selectedFile.value = file
  error.value = ''

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  selectedFile.value = null
  imagePreview.value = ''
}

const submitMarker = async () => {
  if (!form.value.name.trim()) {
    error.value = 'Marker name is required'
    return
  }

  loading.value = true
  error.value = ''

  try {
      // 1. Maak een nieuw FormData object aan (in plaats van een JSON payload)
      const formData = new FormData()

      // Helpers voor de coördinaten-opmaak van de popup
      const safeLat = Number(props.lat)
      const safeLng = Number(props.lng)
      const formatCoordinate = (value: unknown) => {
        const num = Number(value)
        return Number.isFinite(num) ? num.toFixed(4) : 'Onbekend'
      }
      const popupText = `${form.value.name} - (${formatCoordinate(safeLat)}, ${formatCoordinate(safeLng)})`

      // 2. Voeg alle tekstvelden en getallen toe aan de FormData
      formData.append('name', form.value.name)
      formData.append('description', form.value.description || '')
      formData.append('lat', props.lat.toString())
      formData.append('lng', props.lng.toString())
      formData.append('type', form.value.type.toString())
      formData.append('popup', popupText)

      // 3. CRUCIAAL: Voeg het ECHTE geselecteerde bestand toe met de sleutel 'image'
      // Dit matcht exact met de $request->hasFile('image') in je Laravel controller!
      if (selectedFile.value) {
        formData.append('image', selectedFile.value)
      }

      // 4. Verstuur de FormData naar Laravel via je API-helper
      const res = await api.post('/markers', formData)
      
      emit('markerCreated', res)
      close()

  } catch (err: any) {
    error.value = err.message || 'Failed to create marker'
    console.error('Marker creation error:', err)
  } finally {
    loading.value = false
  }
}


const close = () => {
  form.value = { name: '', description: '', type: 1 }
  selectedFile.value = null
  imagePreview.value = ''
  error.value = ''
  emit('close')
}
</script>

<style scoped>
.marker-creation-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  cursor: pointer;
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
}

.modal-header h5 {
  margin: 0;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px;
  border-top: 1px solid #e9ecef;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  padding: 0;
  color: #6c757d;
}

.btn-close:hover {
  color: #000;
}
</style>