import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCamera, faMapMarkerAlt, faSpinner, faXmark, faHeart as faHeartSolid, faBookmark as faBookmarkSolid, faWind, faCompass, faCloudRain } from '@fortawesome/free-solid-svg-icons'
import { faHeart as faHeartRegular, faBookmark as faBookmarkRegular } from '@fortawesome/free-regular-svg-icons'

import App from './App.vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'leaflet/dist/leaflet.css'
import './styles.css'

library.add(faCamera, faMapMarkerAlt, faSpinner, faXmark, faWind, faCompass, faCloudRain, faHeartSolid, faBookmarkSolid, faHeartRegular, faBookmarkRegular)

const app = createApp(App)
app.component('FontAwesomeIcon', FontAwesomeIcon)

app.use(createPinia())
app.use(router)

app.mount('#app')

// Initialize global auth state after app setup
import { useAuthStore } from './stores/auth'
const authStore = useAuthStore()
authStore.initialize()
