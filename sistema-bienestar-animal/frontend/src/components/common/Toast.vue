<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup name="toast">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="['toast', `toast-${notification.type}`]"
          @click="removeNotification(notification.id)"
        >
          <div class="toast-icon">
            <svg v-if="notification.type === 'success'" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else-if="notification.type === 'error'" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else-if="notification.type === 'warning'" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="toast-content">
            <p class="toast-title">{{ notification.title }}</p>
            <p v-if="notification.message" class="toast-message">{{ notification.message }}</p>
          </div>
          <button class="toast-close" @click.stop="removeNotification(notification.id)">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const notifications = ref([]);
let nextId = 0;

const addNotification = ({ type = 'info', title, message, duration = 5000 }) => {
  const id = nextId++;

  notifications.value.push({
    id,
    type,
    title,
    message,
  });

  if (duration > 0) {
    setTimeout(() => {
      removeNotification(id);
    }, duration);
  }
};

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index > -1) {
    notifications.value.splice(index, 1);
  }
};

// Metodos publicos para facilitar el uso
const success = (title, message, duration) => {
  addNotification({ type: 'success', title, message, duration });
};

const error = (title, message, duration) => {
  addNotification({ type: 'error', title, message, duration });
};

const warning = (title, message, duration) => {
  addNotification({ type: 'warning', title, message, duration });
};

const info = (title, message, duration) => {
  addNotification({ type: 'info', title, message, duration });
};

// Exponer metodos
defineExpose({
  addNotification,
  removeNotification,
  success,
  error,
  warning,
  info,
});
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-width: 320px;
  max-width: 480px;
  padding: 16px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  pointer-events: auto;
  cursor: pointer;
  transition: all 0.3s ease;
}

.toast:hover {
  transform: translateX(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.toast-success {
  border-left: 4px solid #069169;
}

.toast-error {
  border-left: 4px solid #A80521;
}

.toast-warning {
  border-left: 4px solid #FFAB00;
}

.toast-info {
  border-left: 4px solid #3366CC;
}

.toast-icon {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
}

.toast-success .toast-icon {
  color: #069169;
}

.toast-error .toast-icon {
  color: #A80521;
}

.toast-warning .toast-icon {
  color: #FFAB00;
}

.toast-info .toast-icon {
  color: #3366CC;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.4;
}

.toast-message {
  margin: 4px 0 0;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  padding: 0;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  transition: color 0.2s;
}

.toast-close:hover {
  color: #4b5563;
}

/* Animaciones */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

/* Responsive */
@media (max-width: 576px) {
  .toast-container {
    top: auto;
    bottom: 20px;
    left: 10px;
    right: 10px;
  }

  .toast {
    min-width: auto;
    max-width: none;
  }
}
</style>
