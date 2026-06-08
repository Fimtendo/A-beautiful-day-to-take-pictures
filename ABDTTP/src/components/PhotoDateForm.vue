<template>
  <div v-if="isOpen" class="photodate-modal">
    <div class="modal-overlay" @click="close"></div>
    <div class="modal-content">
      <div class="modal-header">
        <h5>FotoDatum Maken</h5>
        <button type="button" class="modal-close-button" @click="close">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitForm" class="space-y-5">
          <div class="form-group">
            <label for="photoDateTitle" class="form-label">Titel</label>
            <input
              id="photoDateTitle"
              class="form-input"
              type="text"
              v-model="title"
              placeholder="Optionele korte titel"
            />
          </div>

          <div class="form-group">
            <label for="photoDateDescription" class="form-label">Beschrijving</label>
            <textarea
              id="photoDateDescription"
              class="form-input min-h-30"
              v-model="description"
              rows="4"
              placeholder="Beschrijf wat je wilt doen"
            ></textarea>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
              <label for="startDateTime" class="form-label">Startdatum & tijd</label>
              <input
                id="startDateTime"
                type="datetime-local"
                class="form-input"
                v-model="startDateTime"
                required
              />
            </div>
            <div class="form-group">
              <label for="endDateTime" class="form-label">Einddatum & tijd</label>
              <input
                id="endDateTime"
                type="datetime-local"
                class="form-input"
                v-model="endDateTime"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="capacity" class="form-label">Aantal mensen</label>
            <input
              id="capacity"
              type="number"
              min="1"
              class="form-input"
              v-model.number="capacity"
              required
            />
          </div>

          <div v-if="error" class="error-box">{{ error }}</div>

          <div class="modal-footer">
            <button type="button" class="modal-cancel-button" @click="close">Annuleren</button>
            <button type="submit" class="modal-submit-button" :disabled="loading">
              {{ loading ? 'Opslaan...' : 'FotoDatum Opslaan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'

// Modal for creating a new FotoDatum linked to a selected marker

const props = defineProps<{
  isOpen: boolean
  marker: any | null
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'saved'): void
}>()

const title = ref('')
const description = ref('')
const startDateTime = ref('')
const endDateTime = ref('')
const capacity = ref(4)
const error = ref('')
const loading = ref(false)

const authStore = useAuthStore()

watch(
  () => props.marker,
  (marker) => {
    if (marker) {
      title.value = marker.name ? `PhotoDate at ${marker.name}` : ''
      description.value = marker.description ?? ''
      const now = new Date()
      const inOneHour = new Date(now.getTime() + 60 * 60 * 1000)
      const inTwoHours = new Date(now.getTime() + 2 * 60 * 60 * 1000)
      startDateTime.value = inOneHour.toISOString().slice(0, 16)
      endDateTime.value = inTwoHours.toISOString().slice(0, 16)
      capacity.value = 4
      error.value = ''
    }
  },
  { immediate: true }
)

const close = () => {
  emit('close')
}

const submitForm = async () => {
  if (!props.marker || !props.marker.id) {
    error.value = 'No valid marker selected.'
    return
  }

  const authStore = useAuthStore()
  if (!authStore.user) {
    error.value = 'Please log in first.'
    return
  }

  if (new Date(endDateTime.value) <= new Date(startDateTime.value)) {
    error.value = 'End time must come after start time.'
    return
  }

  loading.value = true
  error.value = ''

  const creator = {
    id: authStore.user.id,
    username: authStore.user.username || authStore.user.email || 'Unknown',
  }

  try {
    const payload = {
      title: title.value || `PhotoDate at ${props.marker.name || 'marker'}`,
      description: description.value,
      start_time: new Date(startDateTime.value).toISOString(),
      end_time: new Date(endDateTime.value).toISOString(),
      capacity: capacity.value,
      marker_id: props.marker.id,
      marker_name: props.marker.name,
      lat: props.marker.lat,
      lng: props.marker.lng,
      attendees: [creator],
    }

    await api.post('/photo-dates', payload)

    emit('saved')
    close()
  } catch (err: any) {
    if (err?.errors) {
      const messages = Object.values(err.errors).flat().join(' ')
      error.value = messages || err.message || 'Failed to save photo date.'
    } else {
      error.value = err?.data?.message || err?.message || 'Failed to save photo date.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.photodate-modal {
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
  width: min(700px, 100%);
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
  font-size: 1rem;
}

.form-input[type="datetime-local"],
.form-input[type="date"],
.form-input[type="time"] {
  position: relative;
  color-scheme: light;
}

.form-input[type="datetime-local"]::-webkit-calendar-picker-indicator,
.form-input[type="date"]::-webkit-calendar-picker-indicator,
.form-input[type="time"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  border-radius: 4px;
  margin-right: 2px;
  opacity: 0.6;
  filter: invert(0.8);
}

.form-input:focus {
  border-color: #8f9c5b;
  box-shadow: 0 0 0 4px rgba(143, 156, 91, 0.15);
}

.error-box {
  border-radius: 1.5rem;
  padding: 1rem;
  font-size: 1.05rem;
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
