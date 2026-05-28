<!-- Root app layout and auth-aware navigation -->

<script setup lang="ts">
import { RouterView } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'

// Auth state and dropdown menu control
const authStore = useAuthStore()
const { isAuthenticated, user, isAdmin } = storeToRefs(authStore)
const dropdownOpen = ref(false)

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleLogout = async () => {
  try {
    await authStore.signOut()
    closeDropdown()
  } catch (error) {
    console.error('Logout error:', error)
  }
}

const handleChangeAccount = async () => {
  try {
    await authStore.signOut()
    closeDropdown()
    window.location.href = '/login'
  } catch (error) {
    console.error('Change account error:', error)
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#ffffff] text-slate-950">
    <header class="bg-[#3b552d]/95 border-b border-[#a58b62] shadow-xl backdrop-blur-md">
      <div class="w-full mx-auto flex flex-col gap-4 px-6 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-col gap-2 xl:max-w-xl sm:max-w-3xl">
          <div class="inline-flex items-center gap-3 rounded-full bg-[#8f9c5b] px-4 py-2 shadow-xl shadow-[#2f351f]/20">
            <span class="text-lg font-semibold tracking-wide text-[#f6f0e6]">
              <H4 class="inline-flex items-center pl-1">A</H4>
              <H4 class="inline-flex items-center pl-2">B</H4>eautiful
              <H4 class="inline-flex items-center pl-1">D</H4>ay
              <H4 class="inline-flex items-center pl-1">T</H4>o
              <H4 class="inline-flex items-center pl-1">T</H4>ake
              <H4 class="inline-flex items-center pl-1">P</H4>ictures
            </span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 justify-center md:justify-end">
          <router-link to="/map" class="shrink-0 rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-medium text-black transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg">Kaart & Locaties</router-link>
          <router-link to="/photodates" class="shrink-0 rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-medium text-black transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg">FotoDates</router-link>
          <router-link to="/explore" class="shrink-0 rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-medium text-black transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg">Posts</router-link>

          <div v-if="isAuthenticated" class="relative">
            <button
              class="inline-flex items-center gap-2 rounded-full bg-[#e9dcc5] px-5 py-2 text-sm font-medium text-black transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg ring-1 ring-[#a58b62]" type="button" @click="toggleDropdown":aria-expanded="dropdownOpen">
              <span>{{ user?.user_metadata?.username || user?.email }}</span>
              <span class="text-xs">▾</span>
              <span v-if="isAdmin" class="rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-semibold text-slate-950">Admin</span>
            </button>
            <div v-if="dropdownOpen" class="absolute right-0 z-10 mt-2 w-56 overflow-hidden rounded-3xl border border-[#a58b62] bg-[#f7efe1]/95 shadow-2xl shadow-[#2f351f]/25 backdrop-blur-md">
              <router-link class="block px-4 py-3 text-sm text-black transition hover:bg-[#e7d9c1]" to="/mypage" @click="closeDropdown">Mijn Pagina</router-link>
              <div class="border-t border-[#d3b98a]"></div>
              <button class="w-full px-4 py-3 text-left text-sm text-[#3f3a2f] transition hover:bg-[#e7d9c1]" type="button" @click="handleChangeAccount">Account wisselen</button>
              <div class="border-t border-[#d3b98a]"></div>
              <button class="w-full px-4 py-3 text-left text-sm text-[#3f3a2f] transition hover:bg-[#e7d9c1]" type="button" @click="handleLogout">Uitloggen</button>
            </div>
          </div>

          <div v-else class="flex flex-wrap gap-2">
            <router-link to="/login" class="rounded-full bg-[#e9dcc5] px-4 py-2 text-sm font-medium text-black transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg">Inloggen</router-link>
            <router-link to="/register" class="rounded-full bg-[#e9dcc5] px-4 py-2 text-sm font-medium text-black transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-[#d8c9ae] hover:shadow-lg underline-disabled">Registreren</router-link>
          </div>
        </div>
      </div>
    </header>

    <main class="min-h-[calc(100vh-9rem)] bg-[#818b58]/90 py-10">
      <div class="w-full mx-auto px-6 lg:px-10">
        <RouterView />
      </div>
    </main>

    <footer class="bg-[#3b552d]/95 border-t border-[#a58b62] py-4 text-center text-sm text-[#f6f0e6]">
      <p>Powered by <a href="https://supabase.com" target="_blank" class="underline">Supabase</a> for database & auth, <a href="https://open-meteo.com" target="_blank" class="underline">Open-Meteo</a> for weather data, and <a href="https://leafletjs.com" target="_blank" class="underline">Leaflet</a> for maps.</p>
    </footer>
  </div>
</template>

<style scoped>
.router-link-active {
  background-color: rgb(97, 138, 43);
  color: #ffffff !important;
}

#map {
  height: 100%;
  width: 100%;
  min-height: 500px;
}

.mapholder {
  height: calc(100vh - 96px);
  background-color: transparent;
}
</style>