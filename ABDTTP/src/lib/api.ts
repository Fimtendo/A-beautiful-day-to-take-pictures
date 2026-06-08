const BASE = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api'

function authHeaders(): Record<string, string> {
  const token = localStorage.getItem('api_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function request(path: string, method = 'GET', body?: any) {
  const headers: Record<string, string> = { Accept: 'application/json', ...authHeaders() }
  if (body && !(body instanceof FormData)) headers['Content-Type'] = 'application/json'

  const res = await fetch(`${BASE}${path}`, {
    method,
    headers,
    body: body && !(body instanceof FormData) ? JSON.stringify(body) : body,
  })

  const text = await res.text()
  let data: any = null
  try { data = text ? JSON.parse(text) : null } catch { data = text }

  if (!res.ok) {
    const message = data?.message || res.statusText || 'Request failed'
    throw { status: res.status, data, message, errors: data?.errors }
  }

  return data
}

export const api = {
  get: (p: string) => request(p, 'GET'),
  post: (p: string, b?: any) => request(p, 'POST', b),
  put: (p: string, b?: any) => request(p, 'PUT', b),
  del: (p: string) => request(p, 'DELETE'),
}

export default api
