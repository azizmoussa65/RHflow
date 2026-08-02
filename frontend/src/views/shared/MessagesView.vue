<template>
  <div class="page" style="height:calc(100vh - 100px);display:flex;flex-direction:column">
    <div class="mb-4">
      <h1>Messagerie</h1>
      <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Discutez avec vos collègues</p>
    </div>

    <div class="card" style="flex:1;display:flex;padding:0;overflow:hidden;min-height:0">
      <!-- Conversation list -->
      <div style="width:300px;flex-shrink:0;border-right:1px solid var(--border);display:flex;flex-direction:column">
        <div style="padding:14px;border-bottom:1px solid var(--border)">
          <button class="btn-primary w-full" style="justify-content:center" @click="showPicker = true">
            <i class="fa-solid fa-pen-to-square"></i> Nouvelle conversation
          </button>
        </div>
        <div style="flex:1;overflow-y:auto">
          <div v-if="conversations.length === 0" style="text-align:center;padding:32px 16px;color:var(--text-muted);font-size:12px">
            Aucune conversation. Cliquez sur "Nouvelle conversation" pour commencer.
          </div>
          <div v-for="c in conversations" :key="c.id"
            @click="openConversation(c)"
            :style="{ padding:'12px 14px', display:'flex', alignItems:'center', gap:'10px', cursor:'pointer', background: active?.id === c.id ? 'var(--bg-hover)' : 'transparent', borderBottom:'1px solid var(--border)' }">
            <div class="avatar" :style="{ background: avatarColor(c.otherUser?.id) }">{{ c.otherUser?.initials }}</div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;justify-content:space-between;align-items:baseline">
                <span style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ c.otherUser?.prenom }} {{ c.otherUser?.nom }}</span>
                <span style="font-size:10px;color:var(--text-muted)">{{ timeAgo(c.lastMessageAt) }}</span>
              </div>
              <div class="truncate" style="font-size:12px;color:var(--text-muted);max-width:180px">{{ c.lastMessageText || 'Aucun message' }}</div>
            </div>
            <span v-if="c.unreadCount > 0" class="badge badge-blue" style="border-radius:999px">{{ c.unreadCount }}</span>
          </div>
        </div>
      </div>

      <!-- Chat panel -->
      <div style="flex:1;display:flex;flex-direction:column;min-width:0">
        <template v-if="active">
          <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
            <div class="avatar" :style="{ background: avatarColor(active.otherUser?.id) }">{{ active.otherUser?.initials }}</div>
            <div>
              <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ active.otherUser?.prenom }} {{ active.otherUser?.nom }}</div>
              <div style="font-size:11px;color:var(--text-muted)">{{ active.otherUser?.poste || active.otherUser?.role }}</div>
            </div>
          </div>

          <div ref="threadEl" style="flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:10px">
            <div v-for="m in messages" :key="m.id"
              :style="{ display:'flex', justifyContent: m.senderId === myId ? 'flex-end' : 'flex-start' }">
              <div :style="{
                maxWidth:'60%', padding:'10px 14px', borderRadius:'14px', fontSize:'13px',
                background: m.senderId === myId ? 'var(--brand-primary)' : 'var(--bg-elevated)',
                color: m.senderId === myId ? '#fff' : 'var(--text-primary)'
              }">
                <template v-if="m.type === 'image'">
                  <img :src="fileUrl(m.attachmentPath)"
                    :style="{ maxWidth:'240px', borderRadius:'8px', display:'block', marginBottom: m.content ? '6px' : '0' }" />
                </template>
                <template v-else-if="m.type === 'video'">
                  <video :src="fileUrl(m.attachmentPath)" controls
                    :style="{ maxWidth:'240px', borderRadius:'8px', display:'block', marginBottom: m.content ? '6px' : '0' }"></video>
                </template>
                <template v-else-if="m.type === 'file'">
                  <a :href="fileUrl(m.attachmentPath)" target="_blank" rel="noopener"
                    :style="{ color: m.senderId === myId ? '#fff' : 'var(--brand-primary)', display:'flex', alignItems:'center', gap:'6px', marginBottom: m.content ? '6px' : '0' }">
                    <i class="fa-solid fa-paperclip"></i> {{ m.attachmentName || 'Fichier' }}
                  </a>
                </template>
                <span v-if="m.content" style="white-space:pre-wrap;word-break:break-word">
                  <template v-for="(part, i) in linkify(m.content)" :key="i">
                    <a v-if="part.type === 'link'" :href="part.value" target="_blank" rel="noopener"
                      :style="{ color: m.senderId === myId ? '#fff' : 'var(--brand-primary)', textDecoration:'underline' }">{{ part.value }}</a>
                    <template v-else>{{ part.value }}</template>
                  </template>
                </span>
              </div>
            </div>
          </div>

          <div style="padding:14px;border-top:1px solid var(--border);display:flex;gap:8px;align-items:center">
            <input type="file" ref="fileInput" style="display:none" @change="onFileSelected" accept="image/*,video/*,.pdf,.doc,.docx" />
            <button class="btn-ghost" style="padding:10px 12px" @click="$refs.fileInput.click()" :disabled="uploading">
              <i class="fa-solid fa-paperclip"></i>
            </button>
            <input class="form-input" style="flex:1" placeholder="Écrire un message..." v-model="draft"
              @keydown.enter.prevent="sendText" />
            <button class="btn-primary" style="padding:10px 16px" @click="sendText" :disabled="!draft.trim()">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </div>
        </template>
        <div v-else style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:13px">
          Sélectionnez une conversation pour commencer à discuter.
        </div>
      </div>
    </div>

    <!-- New conversation picker -->
    <ModalBase v-model="showPicker" title="Nouvelle conversation">
      <div class="form-group">
        <input class="form-input" placeholder="Rechercher un collègue..." v-model="pickerSearch" />
      </div>
      <div style="max-height:320px;overflow-y:auto">
        <div v-for="u in filteredDirectory" :key="u.id"
          @click="startWith(u)"
          style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:10px;cursor:pointer"
          class="picker-row">
          <div class="avatar" :style="{ background: avatarColor(u.id) }">{{ u.initials }}</div>
          <div>
            <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ u.prenom }} {{ u.nom }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ u.poste || u.role }}</div>
          </div>
        </div>
        <div v-if="filteredDirectory.length === 0" style="text-align:center;padding:24px;color:var(--text-muted);font-size:12px">
          Aucun résultat.
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, inject } from 'vue'
import { useRoute } from 'vue-router'
import ModalBase from '@/components/shared/ModalBase.vue'
import messageService from '@/services/messageService.js'
import { getSocket } from '@/services/socket.js'
import { BACKEND_ORIGIN } from '@/utils/env.js'
import { useAuthStore } from '@/stores/auth.js'
import { useMessagingStore } from '@/stores/messaging.js'

const auth  = useAuthStore()
const toast = inject('toast')
const myId  = auth.user?.id
const messaging = useMessagingStore()

const conversations = computed(() => messaging.conversations)
const active = ref(null)
const messages = ref([])
const draft = ref('')
const uploading = ref(false)
const threadEl = ref(null)

const showPicker = ref(false)
const pickerSearch = ref('')
const directory = ref([])
const filteredDirectory = computed(() => {
  const q = pickerSearch.value.toLowerCase()
  return directory.value.filter(u => !q || `${u.prenom} ${u.nom}`.toLowerCase().includes(q))
})

const AVATAR_COLORS = [
  'linear-gradient(135deg,#3b82f6,#06b6d4)', 'linear-gradient(135deg,#8b5cf6,#a78bfa)',
  'linear-gradient(135deg,#10b981,#34d399)', 'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#ef4444,#f87171)', 'linear-gradient(135deg,#06b6d4,#67e8f9)',
]
function avatarColor(id) {
  if (!id) return AVATAR_COLORS[0]
  let sum = 0
  for (const ch of String(id)) sum += ch.charCodeAt(0)
  return AVATAR_COLORS[sum % AVATAR_COLORS.length]
}

function fileUrl(path) {
  return path ? `${BACKEND_ORIGIN}/${path}` : ''
}

function linkify(text) {
  const re = /(https?:\/\/[^\s]+)/g
  const parts = []
  let lastIndex = 0
  let match
  while ((match = re.exec(text)) !== null) {
    if (match.index > lastIndex) parts.push({ type: 'text', value: text.slice(lastIndex, match.index) })
    parts.push({ type: 'link', value: match[0] })
    lastIndex = match.index + match[0].length
  }
  if (lastIndex < text.length) parts.push({ type: 'text', value: text.slice(lastIndex) })
  return parts
}

function timeAgo(iso) {
  if (!iso) return ''
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 60) return 'à l\'instant'
  if (diff < 3600) return Math.floor(diff / 60) + ' min'
  if (diff < 86400) return Math.floor(diff / 3600) + ' h'
  return Math.floor(diff / 86400) + ' j'
}

async function scrollToBottom() {
  await nextTick()
  if (threadEl.value) threadEl.value.scrollTop = threadEl.value.scrollHeight
}

async function openConversation(c) {
  active.value = c
  try {
    messages.value = await messageService.getMessages(c.id)
  } catch (_) {
    messages.value = []
  }
  messaging.markConversationRead(c.id)
  socket.emit('join_conversation', { conversationId: c.id })
  scrollToBottom()
}

async function startWith(u) {
  try {
    const convo = await messageService.startConversation(u.id)
    showPicker.value = false
    pickerSearch.value = ''
    const existing = conversations.value.find(c => c.id === convo.id)
    if (!existing) await messaging.refresh()
    await openConversation(conversations.value.find(c => c.id === convo.id) || convo)
  } catch (_) {
    toast.error('Erreur', 'Impossible de démarrer la conversation.')
  }
}

function sendText() {
  if (!draft.value.trim() || !active.value) return
  socket.emit('send_message', { conversationId: active.value.id, type: 'text', content: draft.value.trim() })
  draft.value = ''
}

async function onFileSelected(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file || !active.value) return
  uploading.value = true
  try {
    const uploaded = await messageService.upload(file)
    socket.emit('send_message', {
      conversationId: active.value.id,
      type: uploaded.type,
      attachmentPath: uploaded.path,
      attachmentName: uploaded.name,
      content: ''
    })
  } catch (_) {
    toast.error('Erreur', "Impossible d'envoyer la pièce jointe.")
  }
  uploading.value = false
}

const route = useRoute()
let socket

function onNewMessage(m) {
  if (active.value && m.conversationId === active.value.id) {
    messages.value.push(m)
    scrollToBottom()
  }
}

onMounted(async () => {
  socket = getSocket()
  socket.on('new_message', onNewMessage)
  messaging.listen()

  try { directory.value = await messageService.directory() } catch (_) {}
  await messaging.refresh()

  const wantedId = route.query.conversationId
  if (wantedId) {
    const found = conversations.value.find(c => c.id === wantedId)
    if (found) await openConversation(found)
  }
})

onBeforeUnmount(() => {
  socket.off('new_message', onNewMessage)
})
</script>

<style scoped>
.picker-row:hover { background: var(--bg-hover); }
.w-full { width: 100%; }
</style>
