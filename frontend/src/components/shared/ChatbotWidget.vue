<template>
  <!-- Bouton flottant -->
  <button class="chat-fab" @click="open = !open" :title="open ? 'Fermer' : 'Assistant IA'">
    <Transition name="icon-switch" mode="out-in">
      <i v-if="open"  key="close" class="fa-solid fa-xmark"        style="font-size:20px"></i>
      <i v-else       key="bot"   class="fa-solid fa-robot"         style="font-size:20px"></i>
    </Transition>
    <span v-if="!open && unread > 0" class="fab-badge">{{ unread }}</span>
  </button>

  <!-- Fenêtre chat -->
  <Transition name="chat-slide">
    <div v-if="open" class="chat-window">

      <!-- Header -->
      <div class="chat-header">
        <div class="flex items-center gap-3">
          <div class="bot-avatar"><i class="fa-solid fa-robot"></i></div>
          <div>
            <div style="font-size:13px;font-weight:700;color:#fff">Assistant HRFlow IA</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.7);display:flex;align-items:center;gap:5px">
              <span class="online-dot"></span> En ligne · Groq llama3
            </div>
          </div>
        </div>
        <button @click="clearChat" title="Effacer" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.6);font-size:14px">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>

      <!-- Messages -->
      <div class="chat-messages" ref="msgBox">
        <!-- Message de bienvenue -->
        <div class="msg bot-msg">
          <div class="bot-icon"><i class="fa-solid fa-robot"></i></div>
          <div class="bubble">
            Bonjour ! Je suis votre assistant RH. Posez-moi des questions sur les employés, les congés, les projets ou vos propres informations.
          </div>
        </div>

        <div v-for="(m, i) in messages" :key="i"
          :class="m.role === 'user' ? 'msg user-msg' : 'msg bot-msg'">
          <div v-if="m.role === 'bot'" class="bot-icon"><i class="fa-solid fa-robot"></i></div>
          <div class="bubble" v-html="formatMsg(m.content)"></div>
        </div>

        <!-- Typing indicator -->
        <div v-if="loading" class="msg bot-msg">
          <div class="bot-icon"><i class="fa-solid fa-robot"></i></div>
          <div class="bubble typing">
            <span></span><span></span><span></span>
          </div>
        </div>
      </div>

      <!-- Suggestions rapides -->
      <div v-if="messages.length === 0" class="suggestions">
        <button v-for="s in suggestions" :key="s" class="suggestion-btn" @click="sendSuggestion(s)">
          {{ s }}
        </button>
      </div>

      <!-- Input -->
      <div class="chat-input">
        <input
          ref="inputRef"
          v-model="inputText"
          type="text"
          placeholder="Posez votre question..."
          @keydown.enter="sendMessage"
          :disabled="loading"
        />
        <button @click="sendMessage" :disabled="loading || !inputText.trim()" class="send-btn">
          <i v-if="loading" class="fa-solid fa-spinner fa-spin"></i>
          <i v-else          class="fa-solid fa-paper-plane"></i>
        </button>
      </div>

    </div>
  </Transition>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import api from '@/services/api.js'

const auth     = useAuthStore()
const open     = ref(false)
const loading  = ref(false)
const inputText = ref('')
const messages  = ref([])
const msgBox    = ref(null)
const inputRef  = ref(null)
const unread    = ref(0)

const suggestions = [
  'Combien y a-t-il d\'employés ?',
  'Qui sont les employés du département RH ?',
  'Quels congés sont en attente ?',
  'Quels sont les projets en cours ?',
  'Donne-moi mes informations',
]

function formatMsg(text) {
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>')
}

async function sendSuggestion(s) {
  inputText.value = s
  await sendMessage()
}

async function sendMessage() {
  const q = inputText.value.trim()
  if (!q || loading.value) return

  messages.value.push({ role: 'user', content: q })
  inputText.value = ''
  loading.value   = true
  unread.value    = 0

  await scrollBottom()

  try {
    const res = await api.post('/chatbot/chat', {
      question: q,
      userId: auth.user?.id ?? null,
    })
    messages.value.push({ role: 'bot', content: res.data.answer })
    if (!open.value) unread.value++
  } catch (e) {
    messages.value.push({
      role: 'bot',
      content: '⚠️ Impossible de joindre le serveur. Vérifiez que le backend est lancé.',
    })
  }

  loading.value = false
  await scrollBottom()
  inputRef.value?.focus()
}

function clearChat() {
  messages.value = []
  unread.value   = 0
}

async function scrollBottom() {
  await nextTick()
  if (msgBox.value) msgBox.value.scrollTop = msgBox.value.scrollHeight
}
</script>

<style scoped>
/* ── Bouton flottant ─────────────────────── */
.chat-fab {
  position: fixed; bottom: 28px; right: 28px; z-index: 1000;
  width: 56px; height: 56px; border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border: none; cursor: pointer; color: #fff;
  box-shadow: 0 4px 20px rgba(99,102,241,0.5);
  display: flex; align-items: center; justify-content: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.chat-fab:hover { transform: scale(1.1); box-shadow: 0 6px 28px rgba(99,102,241,0.65); }

.fab-badge {
  position: absolute; top: -2px; right: -2px;
  background: #ef4444; color: #fff;
  font-size: 10px; font-weight: 700;
  width: 18px; height: 18px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--bg-base);
}

/* ── Fenêtre chat ────────────────────────── */
.chat-window {
  position: fixed; bottom: 96px; right: 28px; z-index: 999;
  width: 360px; height: 520px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 12px 48px rgba(0,0,0,0.25);
  display: flex; flex-direction: column;
  overflow: hidden;
}

/* ── Header ──────────────────────────────── */
.chat-header {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  padding: 14px 16px;
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
}
.bot-avatar {
  width: 36px; height: 36px; border-radius: 10px;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 16px;
}
.online-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: #4ade80;
  display: inline-block;
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.4; } }

/* ── Messages ────────────────────────────── */
.chat-messages {
  flex: 1; overflow-y: auto; padding: 14px;
  display: flex; flex-direction: column; gap: 10px;
  scroll-behavior: smooth;
}

.msg { display: flex; align-items: flex-end; gap: 8px; }
.user-msg { flex-direction: row-reverse; }

.bot-icon {
  width: 28px; height: 28px; border-radius: 8px;
  background: linear-gradient(135deg,#3b82f6,#6366f1);
  color: #fff; font-size: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.bubble {
  max-width: 76%; padding: 10px 14px;
  border-radius: 16px; font-size: 12.5px; line-height: 1.55;
  word-break: break-word;
}
.bot-msg  .bubble { background: var(--bg-elevated); color: var(--text-primary); border-bottom-left-radius: 4px; }
.user-msg .bubble { background: linear-gradient(135deg,#3b82f6,#6366f1); color: #fff; border-bottom-right-radius: 4px; }

/* Typing dots */
.typing { display: flex; align-items: center; gap: 4px; padding: 12px 16px; }
.typing span {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--text-muted);
  animation: bounce 1.2s infinite;
}
.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { 0%,80%,100% { transform:scale(0.7); opacity:0.5; } 40% { transform:scale(1); opacity:1; } }

/* ── Suggestions ─────────────────────────── */
.suggestions {
  padding: 6px 12px 4px;
  display: flex; flex-wrap: wrap; gap: 6px;
  flex-shrink: 0;
}
.suggestion-btn {
  font-size: 11px; padding: 5px 10px; border-radius: 20px;
  background: var(--bg-elevated); border: 1px solid var(--border);
  color: var(--text-secondary); cursor: pointer; transition: all 0.15s;
  white-space: nowrap;
}
.suggestion-btn:hover { background: rgba(59,130,246,0.1); border-color: #3b82f6; color: #3b82f6; }

/* ── Input ───────────────────────────────── */
.chat-input {
  padding: 12px 14px;
  border-top: 1px solid var(--border);
  display: flex; gap: 8px; flex-shrink: 0;
}
.chat-input input {
  flex: 1; padding: 9px 14px; border-radius: 12px;
  border: 1px solid var(--border); background: var(--bg-elevated);
  color: var(--text-primary); font-size: 13px;
  outline: none; transition: border 0.2s;
}
.chat-input input:focus { border-color: #3b82f6; }
.chat-input input:disabled { opacity: 0.6; }

.send-btn {
  width: 38px; height: 38px; border-radius: 12px;
  background: linear-gradient(135deg,#3b82f6,#6366f1);
  border: none; cursor: pointer; color: #fff; font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  transition: opacity 0.2s; flex-shrink: 0;
}
.send-btn:disabled { opacity: 0.4; cursor: default; }

/* ── Animations ──────────────────────────── */
.chat-slide-enter-active { animation: slideUp 0.3s ease; }
.chat-slide-leave-active { animation: slideUp 0.2s ease reverse; }
@keyframes slideUp {
  from { opacity:0; transform:translateY(20px) scale(0.95); }
  to   { opacity:1; transform:none; }
}
.icon-switch-enter-active, .icon-switch-leave-active { transition: all 0.15s ease; }
.icon-switch-enter-from { opacity:0; transform:rotate(90deg) scale(0.6); }
.icon-switch-leave-to   { opacity:0; transform:rotate(-90deg) scale(0.6); }
</style>
