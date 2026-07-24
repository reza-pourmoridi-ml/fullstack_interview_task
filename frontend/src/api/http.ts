const API_BASE = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080'

export async function getJSON<T>(path: string, signal?: AbortSignal): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
    },
    signal,
  })

  if (!res.ok) {
    let message = `Request failed with status ${res.status}`
    try {
      const body = await res.json()
      if (body?.message) message = body.message
    } catch {
      // ignore
    }
    throw new Error(message)
  }

  return res.json() as Promise<T>
}
