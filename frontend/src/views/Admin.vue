<template>
  <div class="min-h-screen bg-gray-50">
     <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        
        <div class="flex items-center space-x-4">
          <!-- Mobile Avatar Dropdown (visible on mobile only) -->
          <div class="md:hidden relative" ref="mobileAvatarRef">
            <button
              @click="showMobileDropdown = !showMobileDropdown"
              class="relative focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
            >
              <!-- Profile Avatar -->
              <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-medium text-sm">
                {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
              </div>
              <!-- Online indicator -->
              <div class="absolute -bottom-0 -right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
            </button>

            <!-- Mobile Dropdown Menu -->
            <div
              v-if="showMobileDropdown"
              class="absolute right-0 mt-3 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50"
              role="menu"
              aria-orientation="vertical"
            >
              <!-- User Info Section -->
              <div class="py-3 px-4">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-medium">
                    {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ authStore.user?.name }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ authStore.user?.email }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Action Buttons Section -->
              <div class="py-2" role="none">
                <!-- Back to Tasks Link -->
                <router-link
                  to="/dashboard"
                  @click="showMobileDropdown = false"
                  class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                  role="menuitem"
                >
                  <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                  Back to Tasks
                </router-link>
                
                <!-- Logout Button -->
                <button
                  @click="handleMobileLogout"
                  class="group flex w-full items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                  role="menuitem"
                >
                  <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Action Buttons (visible on desktop only) -->
          <div class="hidden md:flex items-center space-x-4">
            <router-link to="/dashboard" class="btn btn-secondary text-sm">
              Back to Tasks
            </router-link>
            <button @click="authStore.logout" class="btn btn-secondary text-sm">
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
    <!--<header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
          <div class="flex items-center space-x-4">
            <router-link to="/dashboard" class="btn btn-secondary text-sm">
              Back to Tasks
            </router-link>
            <button @click="authStore.logout" class="btn btn-secondary text-sm">
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>-->

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8">
          <div class="card">
            <div class="text-center">
              <p class="text-2xl font-bold text-blue-600">{{ adminStore.globalStatistics.total || 0 }}</p>
              <p class="text-sm text-gray-600">Total Tasks</p>
            </div>
          </div>
          <div class="card">
            <div class="text-center">
              <p class="text-2xl font-bold text-green-600">{{ adminStore.globalStatistics.completed || 0 }}</p>
              <p class="text-sm text-gray-600">Completed</p>
            </div>
          </div>
          <div class="card">
            <div class="text-center">
              <p class="text-2xl font-bold text-yellow-600">{{ adminStore.globalStatistics.pending || 0 }}</p>
              <p class="text-sm text-gray-600">Pending</p>
            </div>
          </div>
          <div class="card">
            <div class="text-center">
              <p class="text-2xl font-bold text-red-600">{{ adminStore.globalStatistics.high_priority || 0 }}</p>
              <p class="text-sm text-gray-600">High Priority</p>
            </div>
          </div>
          <div class="card">
            <div class="text-center">
              <p class="text-2xl font-bold text-orange-600">{{ adminStore.globalStatistics.medium_priority || 0 }}</p>
              <p class="text-sm text-gray-600">Medium Priority</p>
            </div>
          </div>
          <div class="card">
            <div class="text-center">
              <p class="text-2xl font-bold text-green-600">{{ adminStore.globalStatistics.low_priority || 0 }}</p>
              <p class="text-sm text-gray-600">Low Priority</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Users & Their Tasks</h3>
            <div class="text-sm text-gray-500">
              {{ adminStore.pagination.total || 0 }} users total
            </div>
          </div>

          <div v-if="adminStore.isLoading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-gray-500">Loading users...</p>
          </div>

          <div v-else-if="adminStore.users.length === 0" class="text-center py-8">
            <p class="text-gray-500">No users found.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tasks</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in adminStore.users" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ user.tasks_count || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                    {{ user.completed_tasks_count || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                    {{ user.pending_tasks_count || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="user.is_admin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'">
                      {{ user.is_admin ? 'Admin' : 'User' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click="viewUserTasks(user)"
                      class="text-blue-600 hover:text-blue-900 mr-3"
                    >
                      View Tasks
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="adminStore.pagination.last_page > 1" class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Showing {{ (adminStore.pagination.current_page - 1) * adminStore.pagination.per_page + 1 }} to 
              {{ Math.min(adminStore.pagination.current_page * adminStore.pagination.per_page, adminStore.pagination.total) }} 
              of {{ adminStore.pagination.total }} results
            </div>
            <div class="flex space-x-2">
              <button
                @click="loadPage(adminStore.pagination.current_page - 1)"
                :disabled="adminStore.pagination.current_page === 1"
                class="btn btn-secondary text-sm"
                :class="{ 'opacity-50 cursor-not-allowed': adminStore.pagination.current_page === 1 }"
              >
                Previous
              </button>
              <button
                @click="loadPage(adminStore.pagination.current_page + 1)"
                :disabled="adminStore.pagination.current_page === adminStore.pagination.last_page"
                class="btn btn-secondary text-sm"
                :class="{ 'opacity-50 cursor-not-allowed': adminStore.pagination.current_page === adminStore.pagination.last_page }"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <div v-if="selectedUser" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div class="bg-white rounded-lg max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                  Tasks for {{ selectedUser.name }}
                </h3>
                <button @click="closeUserTasksModal" class="text-gray-400 hover:text-gray-600">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[60vh]">
              <div v-if="adminStore.isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-gray-500">Loading tasks...</p>
              </div>
              
              <div v-else-if="adminStore.selectedUserTasks.length === 0" class="text-center py-8">
                <p class="text-gray-500">No tasks found for this user.</p>
              </div>
              
              <div v-else class="space-y-3">
                <div
                  v-for="task in adminStore.selectedUserTasks"
                  :key="task.id"
                  class="bg-gray-50 border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center space-x-2 mb-2">
                        <h4 class="text-sm font-medium text-gray-900">{{ task.title }}</h4>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border"
                              :class="getPriorityClass(task.priority)">
                          {{ task.priority }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                              :class="getStatusClass(task.status)">
                          {{ task.status }}
                        </span>
                      </div>
                      <p v-if="task.description" class="text-sm text-gray-600 mb-2">
                        {{ task.description }}
                      </p>
                      <p class="text-xs text-gray-400">
                        Created {{ formatDate(task.created_at) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted, ref, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAdminStore } from '@/stores/admin'

const authStore = useAuthStore()
const adminStore = useAdminStore()
const selectedUser = ref(null)

const showMobileDropdown = ref(false)
const mobileAvatarRef = ref(null)

const handleMobileLogout = () => {
  showMobileDropdown.value = false
  authStore.logout()
}

const handleClickOutside = (event) => {
  if (mobileAvatarRef.value && !mobileAvatarRef.value.contains(event.target)) {
    showMobileDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onMounted(() => {
  adminStore.fetchDashboard()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const viewUserTasks = async (user) => {
  selectedUser.value = user
  await adminStore.fetchUserTasks(user.id)
}

const closeUserTasksModal = () => {
  selectedUser.value = null
  adminStore.selectedUserTasks = []
}

const loadPage = (page) => {
  adminStore.fetchDashboard({ page })
}

const getPriorityClass = (priority) => {
  const classes = {
    low: 'priority-low',
    medium: 'priority-medium',
    high: 'priority-high'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'status-pending',
    completed: 'status-completed'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}
</script>