// In dev, the Vue app (5173) and Flask (8000) run on different ports, so we need
// an absolute URL. In production they're served from the same origin by Flask,
// so an empty string (same-origin) is correct. Override with VITE_BACKEND_ORIGIN
// if the backend ever needs to live on its own domain.
export const BACKEND_ORIGIN =
  import.meta.env.VITE_BACKEND_ORIGIN || (import.meta.env.DEV ? 'http://localhost:8000' : '')
