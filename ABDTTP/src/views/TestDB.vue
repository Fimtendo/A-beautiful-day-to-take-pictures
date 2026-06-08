<script setup lang="ts">
import { ref } from 'vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const result = ref('')
const error = ref('')

const testLike = async () => {
  if (!authStore.user) {
    error.value = 'Not authenticated'
    return
  }

  try {
    // Test insert via API
    await api.post('/photo-posts/1/likes')
    result.value = 'Insert (like) successful'

    // Test delete via API
    await api.del('/photo-posts/1/likes')
    result.value += ' | Delete successful'
  } catch (err: any) {
    error.value = `Exception: ${err.message}`
  }
}

const testTables = async () => {
  try {
    // Use admin API to list tables
    const tables = await api.get('/admin/tables')
    result.value = JSON.stringify(tables)
  } catch (err: any) {
    error.value = `Exception: ${err.message}`
  }
}
</script>

<template>
  <div class="container py-5">
    <h2>Test Database Operations</h2>

    <div class="mb-3">
      <button @click="testTables" class="btn btn-info me-2">Test Tables</button>
      <button @click="testLike" class="btn btn-primary me-2">Test Like</button>
    </div>

    <div v-if="result" class="alert alert-success">{{ result }}</div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>
  </div>
</template>