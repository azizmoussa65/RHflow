import { io } from 'socket.io-client'
import { BACKEND_ORIGIN } from '@/utils/env.js'

let socket = null

export function getSocket() {
  if (socket) return socket

  const token = localStorage.getItem('hrflow_token')
  socket = io(BACKEND_ORIGIN || undefined, {
    auth: { token },
    autoConnect: false,
    transports: ['polling', 'websocket']
  })
  return socket
}

export function disconnectSocket() {
  if (socket) {
    socket.disconnect()
    socket = null
  }
}
