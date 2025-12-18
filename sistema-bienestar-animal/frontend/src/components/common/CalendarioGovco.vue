<template>
  <div class="ui-field" :style="{ width }">
    <label :for="inputId" class="ui-label">
      {{ label }}<span v-if="required" class="ui-required">*</span>
    </label>

    <div class="ui-control ui-date" :class="{ 'has-error': error, 'is-disabled': disabled }" ref="root">
      <input
        class="ui-input"
        :id="inputId"
        type="text"
        :value="displayValue"
        :placeholder="placeholder"
        :disabled="disabled"
        readonly
        @click="toggle(true)"
        @keydown.enter.prevent="toggle()"
      />

      <button class="ui-date__icon" type="button" aria-label="Abrir calendario" :disabled="disabled" @click="toggle()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M7 3v3M17 3v3M4 8h16M6 6h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"
                stroke="#004884" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <div v-if="open" class="ui-popover" role="dialog" aria-label="Calendario">
        <div class="ui-popover__header">
          <button type="button" class="nav" @click="prevMonth" aria-label="Mes anterior">‹</button>
          <div class="title">{{ monthLabel }}</div>
          <button type="button" class="nav" @click="nextMonth" aria-label="Mes siguiente">›</button>
        </div>

        <div class="ui-popover__grid">
          <div class="dow" v-for="d in dows" :key="d">{{ d }}</div>

          <button
            v-for="cell in cells"
            :key="cell.key"
            type="button"
            class="day"
            :class="{
              'is-blank': cell.blank,
              'is-today': cell.isToday,
              'is-selected': cell.isSelected,
              'is-disabled': cell.isDisabled
            }"
            :disabled="cell.blank || cell.isDisabled"
            @click="select(cell.date)"
          >
            {{ cell.label }}
          </button>
        </div>

        <div class="ui-popover__footer">
          <button type="button" class="link" @click="setToday" :disabled="disabled">Hoy</button>
          <button type="button" class="link danger" @click="clear" :disabled="disabled">Limpiar</button>
        </div>
      </div>
    </div>

    <p v-if="helpText" class="ui-help">{{ helpText }}</p>
    <p v-if="alertText" class="ui-alert" :class="{ 'is-error': error }">{{ alertText }}</p>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  id: { type: String, required: true }, // por compatibilidad
  inputId: { type: String, required: true },

  modelValue: { type: String, default: '' }, // ISO: YYYY-MM-DD
  label: { type: String, required: true },
  placeholder: { type: String, default: 'Seleccione una fecha' },

  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },

  min: { type: String, default: '' }, // ISO
  max: { type: String, default: '' }, // ISO

  width: { type: String, default: '100%' },

  helpText: { type: String, default: '' },
  alertText: { type: String, default: '' },
  error: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const open = ref(false)
const root = ref(null)

function toggle(force) {
  if (props.disabled) return
  open.value = typeof force === 'boolean' ? force : !open.value
}

function close() { open.value = false }

function onDocClick(e) {
  if (!open.value) return
  if (!root.value) return
  if (!root.value.contains(e.target)) close()
}

onMounted(() => document.addEventListener('mousedown', onDocClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocClick))

function parseISO(iso) {
  if (!iso) return null
  const [y, m, d] = iso.split('-').map(Number)
  if (!y || !m || !d) return null
  return new Date(y, m - 1, d)
}
function toISO(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}
function toDMY(iso) {
  const dt = parseISO(iso)
  if (!dt) return ''
  const d = String(dt.getDate()).padStart(2, '0')
  const m = String(dt.getMonth() + 1).padStart(2, '0')
  const y = dt.getFullYear()
  return `${d}/${m}/${y}`
}

const displayValue = computed(() => toDMY(props.modelValue))

const viewDate = ref(parseISO(props.modelValue) || new Date())

watch(
  () => props.modelValue,
  (v) => {
    const dt = parseISO(v)
    if (dt) viewDate.value = dt
  }
)

const dows = ['L', 'M', 'M', 'J', 'V', 'S', 'D']

const monthLabel = computed(() => {
  const dt = viewDate.value
  return dt.toLocaleDateString('es-CO', { month: 'long', year: 'numeric' })
})

function prevMonth() {
  const dt = new Date(viewDate.value)
  dt.setMonth(dt.getMonth() - 1)
  viewDate.value = dt
}
function nextMonth() {
  const dt = new Date(viewDate.value)
  dt.setMonth(dt.getMonth() + 1)
  viewDate.value = dt
}

const cells = computed(() => {
  const dt = viewDate.value
  const year = dt.getFullYear()
  const month = dt.getMonth()

  const first = new Date(year, month, 1)
  const last = new Date(year, month + 1, 0)

  // Convertir domingo=0 a domingo=7 y luego ajustar para semana iniciando lunes
  const firstDow = ((first.getDay() || 7) - 1) // 0..6 (lunes..domingo)
  const blanks = Array.from({ length: firstDow }, (_, i) => ({
    key: `b-${i}`,
    blank: true,
    label: '',
  }))

  const minDt = parseISO(props.min)
  const maxDt = parseISO(props.max)
  const todayISO = toISO(new Date())
  const selectedISO = props.modelValue

  const days = Array.from({ length: last.getDate() }, (_, i) => {
    const date = new Date(year, month, i + 1)
    const iso = toISO(date)

    const isDisabled =
      (minDt && date < minDt) ||
      (maxDt && date > maxDt)

    return {
      key: iso,
      blank: false,
      label: String(i + 1),
      date,
      isToday: iso === todayISO,
      isSelected: iso === selectedISO,
      isDisabled,
    }
  })

  return [...blanks, ...days]
})

function select(date) {
  const iso = toISO(date)
  emit('update:modelValue', iso)
  emit('change', iso)
  close()
}

function setToday() {
  const iso = toISO(new Date())
  emit('update:modelValue', iso)
  emit('change', iso)
  close()
}

function clear() {
  emit('update:modelValue', '')
  emit('change', '')
  close()
}
</script>

<style scoped>
.ui-field { font-family: "Work Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }

.ui-label {
  display: inline-block;
  margin: 0 0 6px;
  font-size: 13px;
  font-weight: 600;
  color: #333;
}
.ui-required { color: #b00020; margin-left: 4px; }

.ui-control {
  position: relative;
  border: 1px solid #D0D0D0;
  border-radius: 6px;
  background: #FFFFFF;
  transition: box-shadow .15s ease, border-color .15s ease;
}
.ui-control:focus-within {
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, .22);
}
.ui-control.has-error {
  border-color: #d32f2f;
  box-shadow: 0 0 0 3px rgba(211, 47, 47, .14);
}
.ui-control.is-disabled { opacity: .7; }

.ui-input {
  width: 100%;
  height: 40px;
  border: none;
  outline: none;
  padding: 0 42px 0 12px;
  font-size: 14px;
  color: #333;
  background: transparent;
}
.ui-input::placeholder { color: #737373; }

.ui-date__icon {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
}
.ui-date__icon:hover:not(:disabled) { background: #f5f5f5; }
.ui-date__icon:disabled { cursor: not-allowed; }

.ui-popover {
  position: absolute;
  z-index: 1500;
  top: calc(100% + 8px);
  left: 0;
  width: min(360px, 100%);
  background: #fff;
  border: 1px solid #D0D0D0;
  border-radius: 10px;
  box-shadow: 0 10px 24px rgba(0,0,0,.12);
  padding: 10px;
}

.ui-popover__header {
  display: grid;
  grid-template-columns: 40px 1fr 40px;
  align-items: center;
  gap: 6px;
  padding: 2px 2px 10px;
}
.ui-popover__header .title {
  text-align: center;
  font-weight: 700;
  color: #004884;
  text-transform: capitalize;
}
.ui-popover__header .nav {
  height: 32px;
  border: 1px solid #D0D0D0;
  background: #fff;
  border-radius: 8px;
  cursor: pointer;
}
.ui-popover__header .nav:hover { background: #f5f5f5; }

.ui-popover__grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
  padding: 6px 2px 10px;
}
.dow {
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  color: #666;
  padding: 4px 0;
}

.day {
  height: 34px;
  border-radius: 8px;
  border: 1px solid transparent;
  background: #fff;
  cursor: pointer;
  font-weight: 600;
  color: #333;
}
.day:hover:not(:disabled) { background: #f5f7ff; border-color: rgba(51,102,204,.25); }
.day.is-today { border-color: rgba(51,102,204,.65); }
.day.is-selected { background: #3366CC; color: #fff; }
.day.is-disabled { opacity: .45; cursor: not-allowed; }
.day.is-blank { background: transparent; border-color: transparent; cursor: default; }

.ui-popover__footer {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 2px 2px;
}
.link {
  border: none;
  background: transparent;
  color: #004884;
  font-weight: 700;
  cursor: pointer;
  padding: 6px 8px;
  border-radius: 8px;
}
.link:hover:not(:disabled) { background: #f5f5f5; }
.link.danger { color: #b00020; }

.ui-help { margin: 6px 0 0; font-size: 12px; color: #666; }
.ui-alert { margin: 6px 0 0; font-size: 12px; color: #4B4B4B; }
.ui-alert.is-error { color: #b00020; }
</style>
