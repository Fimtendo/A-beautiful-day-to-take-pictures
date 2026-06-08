import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '../lib/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any | null>(null)
  const token = ref<string | null>(localStorage.getItem('api_token'))
  const loading = ref(true)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  const initialize = async () => {
    if (!token.value) {
      loading.value = false
      return
    }

    try {
      const me = await api.get('/user')
      user.value = me
    } catch (err) {
      user.value = null
      token.value = null
      localStorage.removeItem('api_token')
    } finally {
      loading.value = false
    }
  }

  const signUp = async (username: string, email: string, password: string, passwordConfirmation: string, role: string = 'user') => {
    const payload: any = {
      name: username,
      username,
      email,
      password,
      password_confirmation: passwordConfirmation,
      role,
    }
    const res = await api.post('/register', payload)
    localStorage.setItem('api_token', res.token)
    token.value = res.token
    user.value = res.user
    return res
  }

  const signIn = async (email: string, password: string) => {
    const res = await api.post('/login', { email, password })
    localStorage.setItem('api_token', res.token)
    token.value = res.token
    user.value = res.user
    return res
  }

  const signOut = async () => {
    try {
      await api.post('/logout')
    } catch (e) {
      // ignore
    }
    localStorage.removeItem('api_token')
    token.value = null
    user.value = null
  }

  const updateUserRole = async (role: string) => {
    if (!user.value) return
    // simple endpoint not implemented yet; update locally
    user.value.role = role
    return user.value
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isAdmin,
    initialize,
    signUp,
    signIn,
    signOut,
    updateUserRole,
  }
})