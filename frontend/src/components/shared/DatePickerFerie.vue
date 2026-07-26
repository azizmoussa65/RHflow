<template>
  <div class="dp-wrapper" ref="wrapperRef">
    <!-- Input affiché -->
    <div class="dp-input" @click="toggle" :class="{ 'dp-open': show }">
      <i class="fa-solid fa-calendar" style="font-size:13px;color:var(--text-muted)"></i>
      <span :style="{ color: modelValue ? 'var(--text-primary)' : 'var(--text-muted)' }">
        {{ modelValue ? formatDisplay(modelValue) : placeholder }}
      </span>
      <i class="fa-solid fa-chevron-down" style="font-size:11px;color:var(--text-muted);margin-left:auto;transition:transform 0.2s"
        :style="{ transform: show ? 'rotate(180deg)' : 'none' }"></i>
    </div>

    <!-- Calendrier -->
    <Transition name="cal-drop">
      <div v-if="show" class="dp-calendar" @click.stop>
        <!-- Navigation mois -->
        <div class="dp-nav">
          <button class="dp-nav-btn" @click="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
          <span class="dp-month-label">{{ moisNoms[mois] }} {{ annee }}</span>
          <button class="dp-nav-btn" @click="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
        </div>

        <!-- Jours de la semaine -->
        <div class="dp-grid dp-header">
          <div v-for="j in ['Lu','Ma','Me','Je','Ve','Sa','Di']" :key="j">{{ j }}</div>
        </div>

        <!-- Cellules -->
        <div class="dp-grid">
          <div v-for="(cell, i) in cellules" :key="i"
            class="dp-cell"
            :class="{
              'dp-empty':    !cell.day,
              'dp-today':    cell.today,
              'dp-selected': cell.selected,
              'dp-ferie':    cell.ferie && !cell.selected,
              'dp-weekend':  cell.weekend && !cell.ferie && !cell.selected,
            }"
            @click="cell.day && select(cell)">

            <span v-if="cell.day">{{ cell.day }}</span>

            <!-- Point rouge jour férié -->
            <span v-if="cell.ferie" class="dp-dot" :title="cell.ferie"></span>
          </div>
        </div>

        <!-- Légende -->
        <div class="dp-legend">
          <div class="dp-leg-item"><span class="dp-leg-dot" style="background:#ef4444"></span> Jour férié</div>
          <div class="dp-leg-item"><span class="dp-leg-dot" style="background:#3b82f6"></span> Aujourd'hui</div>
          <button class="dp-clear" @click="clear">Effacer</button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'jj/mm/aaaa' },
})
const emit = defineEmits(['update:modelValue', 'change'])

const show       = ref(false)
const wrapperRef = ref(null)
const today      = new Date()

const mois  = ref(today.getMonth())
const annee = ref(today.getFullYear())

const moisNoms = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']

const JOURS_FERIES = {
  '01-01':'Nouvel An',        '03-20':"Fête de l'Indépendance",
  '04-09':'Fête des Martyrs', '05-01':'Fête du Travail',
  '07-25':'Fête de la République', '08-13':'Fête de la Femme',
  '10-15':"Fête de l'Évacuation", '11-07':'Fête du 7 Novembre',
}

function ferieKey(day, month) {
  return `${String(month + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`
}

const cellules = computed(() => {
  const premier   = new Date(annee.value, mois.value, 1)
  const dernierJour = new Date(annee.value, mois.value + 1, 0).getDate()
  let debut = premier.getDay() - 1
  if (debut < 0) debut = 6

  const cells = []
  for (let i = 0; i < debut; i++) cells.push({ day: null })

  const selectedDate = props.modelValue ? new Date(props.modelValue) : null

  for (let d = 1; d <= dernierJour; d++) {
    const date    = new Date(annee.value, mois.value, d)
    const dow     = date.getDay()
    const key     = ferieKey(d, mois.value)
    const isToday = d === today.getDate() && mois.value === today.getMonth() && annee.value === today.getFullYear()
    const isSel   = selectedDate
      ? d === selectedDate.getDate() && mois.value === selectedDate.getMonth() && annee.value === selectedDate.getFullYear()
      : false

    cells.push({
      day:      d,
      weekend:  dow === 0 || dow === 6,
      ferie:    JOURS_FERIES[key] || null,
      today:    isToday,
      selected: isSel,
      iso:      `${annee.value}-${String(mois.value+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`,
    })
  }
  return cells
})

function select(cell) {
  emit('update:modelValue', cell.iso)
  emit('change', cell.iso)
  show.value = false
}

function clear() {
  emit('update:modelValue', '')
  emit('change', '')
  show.value = false
}

function toggle() { show.value = !show.value }

function prevMonth() {
  if (mois.value === 0) { mois.value = 11; annee.value-- }
  else mois.value--
}
function nextMonth() {
  if (mois.value === 11) { mois.value = 0; annee.value++ }
  else mois.value++
}

function formatDisplay(iso) {
  if (!iso) return ''
  const [y, m, d] = iso.split('-')
  return `${d}/${m}/${y}`
}

// Sync mois/année quand la valeur change de l'extérieur
watch(() => props.modelValue, (val) => {
  if (val) {
    const d = new Date(val)
    mois.value  = d.getMonth()
    annee.value = d.getFullYear()
  }
})

// Fermer si clic extérieur
function onClickOutside(e) {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
    show.value = false
  }
}
onMounted(()  => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))
</script>

<style scoped>
.dp-wrapper { position: relative; }

/* Input */
.dp-input {
  display: flex; align-items: center; gap: 8px;
  padding: 9px 12px; border-radius: 10px;
  border: 1px solid var(--border); background: var(--bg-card);
  cursor: pointer; transition: border 0.2s; font-size: 13px;
  min-height: 40px;
}
.dp-input:hover, .dp-open { border-color: #3b82f6; }

/* Calendrier dropdown */
.dp-calendar {
  position: absolute; top: calc(100% + 6px); left: 0; z-index: 300;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 14px; box-shadow: 0 8px 32px rgba(0,0,0,0.18);
  padding: 12px; width: 270px;
}

/* Navigation */
.dp-nav {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 10px;
}
.dp-nav-btn {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: 8px; width: 28px; height: 28px;
  cursor: pointer; color: var(--text-secondary); font-size: 11px;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s;
}
.dp-nav-btn:hover { background: rgba(59,130,246,0.1); color: #3b82f6; }
.dp-month-label { font-size: 13px; font-weight: 700; color: var(--text-primary); }

/* Grille */
.dp-grid {
  display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;
}
.dp-header > div {
  text-align: center; font-size: 10px; font-weight: 600;
  color: var(--text-muted); padding: 4px 0; text-transform: uppercase;
}

/* Cellule */
.dp-cell {
  position: relative;
  aspect-ratio: 1; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 500; color: var(--text-primary);
  cursor: pointer; transition: background 0.15s;
}
.dp-cell:hover:not(.dp-empty) { background: var(--bg-elevated); }
.dp-empty { cursor: default; }

.dp-today   { background: rgba(59,130,246,0.12); color: #3b82f6; font-weight: 700; }
.dp-selected { background: #3b82f6 !important; color: #fff !important; }
.dp-ferie   { background: rgba(239,68,68,0.08); color: #ef4444; font-weight: 600; }
.dp-weekend { color: var(--text-muted); }

/* Point rouge */
.dp-dot {
  position: absolute; bottom: 3px; left: 50%; transform: translateX(-50%);
  width: 5px; height: 5px; border-radius: 50%; background: #ef4444;
}
.dp-selected .dp-dot { background: #fff; }

/* Légende */
.dp-legend {
  display: flex; align-items: center; gap: 10px; margin-top: 10px;
  padding-top: 10px; border-top: 1px solid var(--border);
  font-size: 11px; color: var(--text-muted);
}
.dp-leg-item { display: flex; align-items: center; gap: 4px; }
.dp-leg-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dp-clear {
  margin-left: auto; background: none; border: none;
  cursor: pointer; color: #3b82f6; font-size: 11px; padding: 0;
}
.dp-clear:hover { text-decoration: underline; }

/* Animation */
.cal-drop-enter-active { animation: calIn 0.2s ease; }
.cal-drop-leave-active { animation: calIn 0.15s ease reverse; }
@keyframes calIn {
  from { opacity: 0; transform: translateY(-8px) scale(0.97); }
  to   { opacity: 1; transform: none; }
}
</style>
