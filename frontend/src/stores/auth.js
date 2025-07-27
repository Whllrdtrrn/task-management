import { defineStore } from 'pinia'
import { authAPI } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    isLoading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.is_admin || false,
  },

  actions: {
    async register(userData) {
      this.isLoading = true
      this.error = null
      
      try {
        const response = await authAPI.register(userData)
        this.setAuth(response.data.user, response.data.token)
        router.push('/dashboard')
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async login(credentials) {
      this.isLoading = true
      this.error = null
      
      try {
        const response = await authAPI.login(credentials)
        this.setAuth(response.data.user, response.data.token)
        router.push('/dashboard')
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        await authAPI.logout()
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
        router.push('/login')
      }
    },

    async fetchUser() {
      if (!this.token) return
      
      try {
        const response = await authAPI.getUser()
        this.user = response.data.user
      } catch (error) {
        this.clearAuth()
      }
    },

    setAuth(user, token) {
      this.user = user
      this.token = token
      localStorage.setItem('token', token)
    },

    clearAuth() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
    },
  },
})