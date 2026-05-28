<script setup lang="ts">
import { ref } from 'vue'
import { supabase } from '../lib/supabase'
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
    // Test insert
    const { data, error: insertError } = await supabase
      .from('photo_post_likes')
      .insert([{ post_id: 1, user_id: authStore.user.id }])
      .select()

    if (insertError) {
      error.value = `Insert error: ${insertError.message} (${insertError.code})`
      return
    }

    result.value = `Insert successful: ${JSON.stringify(data)}`

    // Test delete
    const { error: deleteError } = await supabase
      .from('photo_post_likes')
      .delete()
      .eq('post_id', 1)
      .eq('user_id', authStore.user.id)

    if (deleteError) {
      error.value += ` | Delete error: ${deleteError.message} (${deleteError.code})`
    } else {
      result.value += ' | Delete successful'
    }
  } catch (err: any) {
    error.value = `Exception: ${err.message}`
  }
}

const testTables = async () => {
  try {
    // Test if tables exist
    const { data: likesData, error: likesError } = await supabase
      .from('photo_post_likes')
      .select('*')
      .limit(1)

    const { data: bookmarksData, error: bookmarksError } = await supabase
      .from('photo_post_bookmarks')
      .select('*')
      .limit(1)

    result.value = `Likes table: ${likesError ? 'ERROR: ' + likesError.message : 'OK'} | Bookmarks table: ${bookmarksError ? 'ERROR: ' + bookmarksError.message : 'OK'}`
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