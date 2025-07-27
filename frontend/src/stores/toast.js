import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: []
  }),

  actions: {
    addToast(toast) {
      const id = Date.now() + Math.random()
      const newToast = {
        id,
        type: toast.type || 'info', 
        title: toast.title,
        message: toast.message,
        duration: toast.duration || 5000,
        persistent: toast.persistent || false
      }

      this.toasts.push(newToast)

      if (!newToast.persistent) {
        setTimeout(() => {
          this.removeToast(id)
        }, newToast.duration)
      }

      return id
    },

    removeToast(id) {
      const index = this.toasts.findIndex(toast => toast.id === id)
      if (index > -1) {
        this.toasts.splice(index, 1)
      }
    },

    clearAllToasts() {
      this.toasts = []
    },

    success(title, message, options = {}) {
      return this.addToast({
        type: 'success',
        title,
        message,
        ...options
      })
    },

    error(title, message, options = {}) {
      return this.addToast({
        type: 'error',
        title,
        message,
        duration: 6000, 
        ...options
      })
    },

    warning(title, message, options = {}) {
      return this.addToast({
        type: 'warning',
        title,
        message,
        ...options
      })
    },

    info(title, message, options = {}) {
      return this.addToast({
        type: 'info',
        title,
        message,
        ...options
      })
    }
  }
})