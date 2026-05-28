<template>
  <div>
    <div v-if="weatherData" class="weather-container">
      <div class="current-weather mb-4">
        <div class="weather-icon-large">
          <i :class="['wi', weatherCodeToIcon(weatherData.current.weatherCode)]"></i>
        </div>
        <div class="current-temp">{{ weatherData.current.temperature }}°C</div>
        <div class="weather-description">{{ weatherData.current.weatherText }}</div>
        <div class="weather-details mt-2">
          <div class="detail-item flex items-center gap-2 justify-center">
            <font-awesome-icon :icon="['fas', 'wind']" />
            <small>Wind: {{ weatherData.current.windspeed }} km/h</small>
          </div>
          <div class="detail-item flex items-center gap-2 justify-center">
            <font-awesome-icon :icon="['fas', 'compass']" />
            <small>Richting: {{ degreesToDirection(weatherData.current.winddirection) }}</small>
          </div>
          <div class="detail-item flex items-center gap-2 justify-center">
            <font-awesome-icon :icon="['fas', 'cloud-rain']" />
            <small>Regen kans: {{ weatherData.current.rainChance }}%</small>
          </div>
        </div>
      </div>

      <div class="forecast-container">
        <div
          v-for="(day, index) in weatherData.forecast"
          :key="index"
          class="forecast-card"
        >
          <div class="forecast-date">{{ formatDate(day.date) }}</div>
          <div class="forecast-icon">
            <i :class="['wi', weatherCodeToIcon(day.weatherCode)]"></i>
          </div>
          <div class="forecast-temps">
            <span class="temp-max">{{ Math.round(day.maxTemp) }}°</span>
            <span class="temp-min">{{ Math.round(day.minTemp) }}°</span>
          </div>
          <div class="forecast-details">
            <small class="inline-flex items-center gap-2"><font-awesome-icon :icon="['fas', 'cloud-rain']" />{{ day.precipProb }}%</small>
            <small class="inline-flex items-center gap-2"><font-awesome-icon :icon="['fas', 'wind']" />{{ Math.round(day.windspeedMax) }} km/h</small>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-box">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
// Weather panel display component
const props = defineProps<{
  weatherData: any
  weatherCodeToIcon: (code: number) => string
  formatDate: (dateStr: string) => string
  degreesToDirection: (degrees: number) => string
  error: string | null
}>()
</script>

<style scoped>
.weather-container {
  background: linear-gradient(135deg, #e8ddc7 0%, #f7efe3 100%);
  border-radius: 28px;
  padding: 24px;
  color: #3f3a2f;
  border: 1px solid #d8c2a0;
  box-shadow: 0 20px 40px rgba(45, 37, 18, 0.1);
}

.current-weather {
  text-align: center;
  border-bottom: 1px solid rgba(63, 58, 47, 0.14);
  padding-bottom: 20px;
}

.weather-icon-large {
  font-size: 60px;
  margin-bottom: 10px;
  line-height: 1;
}

.current-temp {
  font-size: 46px;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 8px;
}

.weather-description {
  font-size: 18px;
  opacity: 0.85;
  margin-bottom: 12px;
}

.weather-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-item {
  opacity: 0.9;
}

.forecast-container {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-top: 16px;
}

.forecast-card {
  background: rgba(255, 255, 255, 0.65);
  border-radius: 18px;
  padding: 16px;
  text-align: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(216, 194, 160, 0.7);
}

.forecast-date {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 8px;
  opacity: 0.85;
}

.forecast-icon {
  font-size: 34px;
  margin: 8px 0;
  line-height: 1;
}

.forecast-temps {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin: 8px 0;
  font-weight: 700;
}

.temp-max {
  font-size: 18px;
}

.error-box {
  margin-top: 1rem;
  border-radius: 1.5rem;
  border: 1px solid #d8a2a2;
  background: #fbe8e8;
  color: #7f2a2a;
  padding: 1rem;
}
</style>
