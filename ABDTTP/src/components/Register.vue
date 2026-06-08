<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Registreren</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="handleRegister">
              <div class="mb-3">
                <label for="username" class="form-label">Gebruikersnaam</label>
                <input
                  type="text"
                  class="form-control"
                  id="username"
                  v-model="username"
                  placeholder="Kies een weergavenaam"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  v-model="email"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord</label>
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  v-model="password"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Bevestig Wachtwoord</label>
                <input
                  type="password"
                  class="form-control"
                  id="confirmPassword"
                  v-model="confirmPassword"
                  required
                />
              </div>
               <div class="mb-3">
                 <label for="role" class="form-label">Rol (Debug)</label>
                 <select
                   class="form-control"
                   id="role"
                   v-model="role"
                 >
                   <option value="user">Gebruiker</option>
                   <option value="admin">Admin</option>
                 </select>
               </div>
               <button type="submit" class="btn btn-primary" :disabled="loading">
                {{ loading ? 'Registreren...' : 'Registreren' }}
              </button>
            </form>
            <p class="mt-3">
              Al een account?
              <router-link to="/login">Log hier in</router-link>
            </p>
          </div>
        </div>
        <div v-if="error" class="alert alert-danger mt-3">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const error = ref('')
const role = ref('user')

const handleRegister = async () => {
  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match'
    return
  }

  loading.value = true
  error.value = ''
  try {
    await authStore.signUp(username.value, email.value, password.value, confirmPassword.value, role.value)
    router.push('/login') // Redirect to login since no email verification
  } catch (err: any) {
    if (err?.errors) {
      error.value = Object.values(err.errors).flat().join(' ')
    } else {
      error.value = err?.message || 'Registration failed.'
    }
  } finally {
    loading.value = false
  }
}
</script>