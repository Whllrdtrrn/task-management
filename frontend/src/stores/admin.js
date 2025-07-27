import { defineStore } from 'pinia'
import { adminAPI } from '@/services/api'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    users: [],
    selectedUserTasks: [],
    allTasks: [],
    globalStatistics: {},
    pagination: {},
    isLoading: false,
    error: null,
  }),

  actions: {
    async fetchDashboard() {
      this.isLoading = true
      this.error = null
      
      try {
        const response = await adminAPI.getDashboard()
        this.users = response.data.users
        this.globalStatistics = response.data.global_statistics
        this.pagination = response.data.pagination
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch dashboard'
      } finally {
        this.isLoading = false
      }
    },

    async fetchUserTasks(userId) {
      this.isLoading = true
      this.error = null
      
      try {
        const response = await adminAPI.getUserTasks(userId)
        this.selectedUserTasks = response.data.tasks
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user tasks'
      } finally {
        this.isLoading = false
      }
    },

    async fetchAllTasks(params = {}) {
      this.isLoading = true
      this.error = null
      
      try {
        const response = await adminAPI.getAllTasks(params)
        this.allTasks = response.data.tasks
        this.pagination = response.data.pagination
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch all tasks'
      } finally {
        this.isLoading = false
      }
    },
  },
})