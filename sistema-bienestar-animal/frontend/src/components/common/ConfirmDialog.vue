<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="modal-overlay" @click="handleCancel">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <div class="modal-icon" :class="`icon-${mode}`">
              <svg v-if="mode === 'danger'" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3 class="modal-title">{{ title }}</h3>
          </div>

          <div class="modal-body">
            <p>{{ message }}</p>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn-govco btn-govco-outline"
              @click="handleCancel"
            >
              {{ cancelText }}
            </button>
            <button
              type="button"
              :class="['btn-govco', mode === 'danger' ? 'btn-govco-danger' : 'btn-govco-primary']"
              @click="handleConfirm"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const title = ref('');
const message = ref('');
const confirmText = ref('Confirmar');
const cancelText = ref('Cancelar');
const mode = ref('default');
let resolvePromise = null;

const show = ({
  title: t = '¿Esta seguro?',
  message: m = '',
  confirmText: ct = 'Confirmar',
  cancelText: clt = 'Cancelar',
  danger = false
}) => {
  title.value = t;
  message.value = m;
  confirmText.value = ct;
  cancelText.value = clt;
  mode.value = danger ? 'danger' : 'default';
  isOpen.value = true;

  return new Promise((resolve) => {
    resolvePromise = resolve;
  });
};

const handleConfirm = () => {
  isOpen.value = false;
  if (resolvePromise) {
    resolvePromise(true);
    resolvePromise = null;
  }
};

const handleCancel = () => {
  isOpen.value = false;
  if (resolvePromise) {
    resolvePromise(false);
    resolvePromise = null;
  }
};

defineExpose({ show });
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-container {
  background: white;
  border-radius: 12px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-header {
  padding: 24px 24px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.modal-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.modal-icon.icon-default {
  background: #E8F0FE;
  color: #3366CC;
}

.modal-icon.icon-danger {
  background: #FFEBEE;
  color: #A80521;
}

.modal-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
}

.modal-body {
  padding: 16px 24px 24px;
  text-align: center;
}

.modal-body p {
  margin: 0;
  color: #6b7280;
  line-height: 1.6;
  font-size: 14px;
}

.modal-footer {
  padding: 16px 24px 24px;
  display: flex;
  justify-content: center;
  gap: 12px;
}

.btn-govco {
  padding: 10px 24px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-govco-outline {
  background: white;
  color: #4B4B4B;
  border: 1px solid #D0D0D0;
}

.btn-govco-outline:hover {
  background: #f5f5f5;
  border-color: #BABABA;
}

.btn-govco-primary {
  background: #3366CC;
  color: white;
}

.btn-govco-primary:hover {
  background: #2851a3;
}

.btn-govco-danger {
  background: #A80521;
  color: white;
}

.btn-govco-danger:hover {
  background: #8a041b;
}

/* Animaciones */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
  transition: transform 0.3s ease;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(0.9);
}

/* Responsive */
@media (max-width: 576px) {
  .modal-container {
    margin: 0 10px;
  }

  .modal-footer {
    flex-direction: column;
  }

  .btn-govco {
    width: 100%;
  }
}
</style>
