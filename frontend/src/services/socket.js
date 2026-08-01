import { io } from 'socket.io-client'

const SOCKET_URL = 'http://localhost:8000'

let socket = null

export function getSocket() {
  if (socket) return socket

  const token = localStorage.getItem('hrflow_token')
  socket = io(SOCKET_URL, {
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
