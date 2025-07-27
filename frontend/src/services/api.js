import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const authStore = useAuthStore()
  console.log('🔑 Making request with token:', !!authStore.token)
  console.log('🔗 Request URL:', config.url)
  console.log('🔗 Request method:', config.method)
  
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }
  return config
}, (error) => {
  console.error('❌ Request interceptor error:', error)
  return Promise.reject(error)
})

api.interceptors.response.use(
  (response) => {
    console.log('✅ Response received:', response.status, response.config.url)
    return response
  },
  (error) => {
    console.error('❌ Response error:', error.response?.status, error.config?.url)
    console.error('❌ Error details:', error.response?.data)
    
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      console.log('🔒 401 Unauthorized - logging out')
      authStore.logout()
    }
    return Promise.reject(error)
  }
)

export default api

export const authAPI = {
  register: (data) => api.post('/register', data),
  login: (data) => api.post('/login', data),
  logout: () => api.post('/logout'),
  getUser: () => api.get('/user'),
}

export const tasksAPI = {
  getTasks: (filters = {}) => api.get('/tasks', { params: filters }),
  createTask: (data) => api.post('/tasks', data),
  updateTask: (id, data) => api.put(`/tasks/${id}`, data),
  deleteTask: (id) => api.delete(`/tasks/${id}`),
  reorderTasks: (tasks) => api.post('/tasks/reorder', { tasks }),
}

// Admin API
//export const adminAPI = {
//  getDashboard: () => api.get('/admin/dashboard'),
//  getUserTasks: (userId) => api.get(`/admin/users/${userId}/tasks`),
//  getAllTasks: (params = {}) => api.get('/admin/tasks', { params }),
//}
// Enhanced Admin API
export const adminAPI = {
  async getDashboard() {
    try {
      console.log('📊 Fetching admin dashboard...')
      const response = await api.get('/admin/dashboard')
      console.log('✅ Dashboard data received:', response.data)
      return response
    } catch (error) {
      console.error('❌ Dashboard fetch failed:', {
        status: error.response?.status,
        message: error.response?.data?.message || error.message,
        details: error.response?.data
      })
      throw error
    }
  },

  async getUserTasks(userId) {
    try {
      console.log(`👤 Fetching tasks for user ${userId}...`)
      const response = await api.get(`/admin/users/${userId}/tasks`)
      return response
    } catch (error) {
      console.error(`❌ Failed to fetch tasks for user ${userId}:`, error.response?.data)
      throw error
    }
  },

  async getAllTasks(params = {}) {
    try {
      console.log('📋 Fetching all tasks...', params)
      const response = await api.get('/admin/tasks', { params })
      return response
    } catch (error) {
      console.error('❌ Failed to fetch all tasks:', error.response?.data)
      throw error
    }
  }
}