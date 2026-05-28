import L from 'leaflet'

// Leaflet helper composable for map initialization and marker management
export function useMapMarkers() {
  let map: L.Map | null = null
  const markerMap = new Map<number, L.Marker>()

  // pre-create colored icons; images should reside in public folder
  const markerIcons: Record<number, L.Icon> = {
    1: L.icon({
      iconUrl: '/leaf-green.png',
      shadowUrl: '/leaf-shadow.png',
      iconSize: [65, 95],
      shadowSize: [60, 84],
      iconAnchor: [13, 85],
      shadowAnchor: [11, 75],
      popupAnchor: [22, -76],
    }),
    2: L.icon({
      iconUrl: '/leaf-orange.png',
      shadowUrl: '/leaf-shadow.png',
      iconSize: [65, 95],
      shadowSize: [60, 84],
      iconAnchor: [13, 85],
      shadowAnchor: [11, 75],
      popupAnchor: [22, -76],
    }),
    3: L.icon({
      iconUrl: '/leaf-red.png',
      shadowUrl: '/leaf-shadow.png',
      iconSize: [65, 95],
      shadowSize: [60, 84],
      iconAnchor: [13, 85],
      shadowAnchor: [11, 75],
      popupAnchor: [22, -76],
    }),
  }

  const getIconByType = (type: number) => {
    return markerIcons[type] || markerIcons[1]
  }

  const initializeMap = (mapElementId: string, lat = 52.1326, lng = 5.2913, zoom = 7) => {
    if (map) return map

    map = L.map(mapElementId).setView([lat, lng], zoom)
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map)

    return map
  }

  const addMarker = (
    latitude: number,
    longitude: number,
    popupContent?: string,
    markerType: number = 1,
    id?: number
  ) => {
    if (!map) return

    const options: L.MarkerOptions = {}
    if (markerType) {
      options.icon = getIconByType(markerType)
    }

    const marker = L.marker([latitude, longitude], options).addTo(map)
    if (popupContent) marker.bindPopup(popupContent)

    if (id != null) {
      markerMap.set(id, marker)
    }

    return marker
  }

  const removeMarker = (id: number) => {
    const marker = markerMap.get(id)
    if (!marker) return
    map?.removeLayer(marker)
    markerMap.delete(id)
  }

  const clearMarkers = () => {
    markerMap.forEach((marker) => {
      map?.removeLayer(marker)
    })
    markerMap.clear()
  }

  const getMap = () => map

  return {
    initializeMap,
    addMarker,
    removeMarker,
    clearMarkers,
    getMap,
    getIconByType, // exported if future logic needs icons
  }
}