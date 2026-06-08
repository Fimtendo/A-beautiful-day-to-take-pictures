import { ref } from 'vue'

export interface WeatherDay {
  date: string
  minTemp: number
  maxTemp: number
  weatherCode: number
  weatherText: string
  precipProb: number
  windspeedMax: number
}

export interface WeatherForecast {
  current: {
    temperature: number
    weatherCode: number
    weatherText: string
    windspeed: number
    winddirection: number
    rainChance: number
  }
  forecast: WeatherDay[]
}

// Convert Open-Meteo weather codes into readable Dutch text
const weatherCodeToText = (code: number): string => {
  const map: Record<number, string> = {
    0: 'Heldere lucht',
    1: 'Gedeeltelijk bewolkt',
    2: 'Bewolkt',
    3: 'Overwegend bewolkt',
    45: 'Nevel',
    48: 'IJsnevel',
    51: 'Lichte motregen',
    53: 'Matige motregen',
    55: 'Zware motregen',
    56: 'IJzige motregen',
    57: 'Zware ijzige motregen',
    61: 'Regen',
    63: 'Aanhoudende regen',
    65: 'Zware regen',
    66: 'IJzige regen',
    67: 'Smerige IJzige regen',
    71: 'Sneeuwval',
    73: 'Matige sneeuwval',
    75: 'Zware sneeuwval',
    77: 'Hagel',
    80: 'Buien',
    81: 'Sterke buien',
    82: 'Zware buien',
    85: 'Sneeuwbuien',
    86: 'Zware sneeuwbuien',
    95: 'Onweer',
    96: 'Onweer met lichte hagel',
    99: 'Onweer met zware hagel',
  }
  return map[code] ?? 'Onbekend'
}

const weatherCodeToIcon = (code: number): string => {
  if (code === 0) return 'wi-day-sunny'
  if (code === 1 || code === 2) return 'wi-day-cloudy'
  if (code === 3) return 'wi-cloudy'
  if (code === 45 || code === 48) return 'wi-fog'
  if (code >= 51 && code <= 67) return 'wi-rain'
  if (code >= 71 && code <= 86) return 'wi-snow'
  if (code >= 95 && code <= 99) return 'wi-thunderstorm'
  return 'wi-na'
}

const degreesToDirection = (degrees: number): string => {
  const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW']
  const index = Math.round(degrees / 22.5) % 16
  return directions[index] ?? 'N'
}

export const useWeather = () => {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const weatherData = ref<WeatherForecast | null>(null)

  const getWeatherForecast = async (lat: number, lng: number): Promise<WeatherForecast | null> => {
    loading.value = true
    error.value = null
    try {
      const url = `http://localhost:8000/api/weather?latitude=${lat}&longitude=${lng}`

      const r = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json', // Dwing Laravel om JSON-fouten te spugen
          'Content-Type': 'application/json'
        }
      })
      if (!r.ok) {
        const errData = await r.json().catch(() => ({}))
        throw new Error(errData.error || `Backend fout ${r.status}`)
      }
      
      const backendData = await r.json()

      // Voeg de frontend-specifieke tekstvertalingen toe aan de opgeschoonde data
      weatherData.value = {
        current: {
          ...backendData.current,
          weatherText: weatherCodeToText(backendData.current.weatherCode),
        },
        forecast: backendData.forecast.map((day: any) => ({
          ...day,
          weatherText: weatherCodeToText(day.weatherCode),
        })),
      }

      return weatherData.value
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Onbekende fout'
      return null
    } finally {
      loading.value = false
    }
  }

  return { 
    loading, 
    error, 
    weatherData, 
    getWeatherForecast, 
    weatherCodeToIcon, 
    degreesToDirection 
  }
}