<template>
  <div v-if="isOpen" class="image-zoom-modal">
    <div class="modal-overlay" @click="close"></div>
    <div class="modal-content">
      <button type="button" class="modal-close-button" @click="close">
        <font-awesome-icon :icon="['fas', 'xmark']" />
      </button>
      <img :src="imageUrl" :alt="imageAlt" class="modal-image" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  isOpen: boolean
  imageUrl: string
  imageAlt?: string
}>()

const emit = defineEmits<{
  (event: 'close'): void
}>()

const close = () => {
  emit('close')
}

// Close on escape key
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.isOpen) {
    close()
  }
}

if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleKeydown)
}
</script>

<style scoped>
.image-zoom-modal {
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
  inset: 0;
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(6px);
  cursor: pointer;
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-image {
  max-width: 100%;
  max-height: 100%;
  border-radius: 16px;
  box-shadow: 0 25px 80px rgba(45, 37, 18, 0.3);
  object-fit: contain;
}

.modal-close-button {
  position: absolute;
  top: -50px;
  right: 0;
  background: white;
  color: #3f3a2f;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 28px;
  font-weight: 300;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-close-button:hover {
  background: #f0f0f0;
  transform: rotate(90deg);
}

@media (max-width: 640px) {
  .modal-close-button {
    top: 10px;
    right: 10px;
    width: 36px;
    height: 36px;
    font-size: 24px;
  }
}
</style>
