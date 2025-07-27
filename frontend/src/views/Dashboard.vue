<script setup>
import { onMounted, onUnmounted, watch, computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import TaskFilters from '@/components/TaskFilters.vue'
import TaskForm from '@/components/TaskForm.vue'
import TaskList from '@/components/TaskList.vue'

const authStore = useAuthStore()
const tasksStore = useTasksStore()

const editingTask = ref(null)

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

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const connectionStatus = computed(() => {
  return tasksStore.connectionStatus || 'disconnected'
})

const connectionStatusText = computed(() => {
  switch (connectionStatus.value) {
    case 'connected': return 'Live'
    case 'connecting': return 'Connecting...'
    case 'disconnected': return 'Offline'
    default: return 'Unknown'
  }
})

const handleEditTask = (task) => {
  console.log('🔧 Dashboard received edit request for task:', task.id, task)
  editingTask.value = task
  console.log('🔧 editingTask set to:', editingTask.value)
}

const handleTaskUpdated = () => {
  console.log('✅ Task updated, clearing edit state')
  editingTask.value = null
}

const handleCancelEdit = () => {
  console.log('❌ Edit cancelled')
  editingTask.value = null
}

onMounted(async () => {
  console.log('🚀 Dashboard mounted')
  
  console.log('📋 Fetching tasks...')
  await tasksStore.fetchTasks()
  console.log('✅ Tasks fetched:', tasksStore.tasks.length)
  
  if (authStore.user && authStore.token && typeof tasksStore.initializeRealTime === 'function') {
    console.log('🔌 Initializing WebSocket for user:', authStore.user.id)
    try {
      tasksStore.initializeRealTime(authStore.user.id, authStore.token)
    } catch (error) {
      console.error('❌ Failed to initialize WebSocket:', error)
    }
  } else {
    console.log('⚠️ WebSocket not initialized - missing requirements:', {
      hasUser: !!authStore.user,
      hasToken: !!authStore.token,
      hasMethod: typeof tasksStore.initializeRealTime === 'function'
    })
  }
})

onUnmounted(() => {
  console.log('🔌 Component unmounting, destroying WebSocket')
  if (typeof tasksStore.destroyRealTime === 'function') {
    tasksStore.destroyRealTime()
  }
})

watch(() => authStore.user, (newUser, oldUser) => {
  console.log('👤 User changed:', { newUser: newUser?.id, oldUser: oldUser?.id })
  
  if (newUser && authStore.token) {
    tasksStore.fetchTasks()
    if (typeof tasksStore.initializeRealTime === 'function') {
      tasksStore.initializeRealTime(newUser.id, authStore.token)
    }
  } else if (!newUser && oldUser) {
    if (typeof tasksStore.destroyRealTime === 'function') {
      tasksStore.destroyRealTime()
    }
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
     <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center space-x-4">
          <h1 class="text-2xl font-bold text-gray-900">Task Management</h1>
          
          <!-- WebSocket Status Indicator (commented out) -->
          <!--<div class="flex items-center space-x-2">
            <div 
              class="w-2 h-2 rounded-full transition-colors duration-300"
              :class="{
                'bg-green-500 animate-pulse': connectionStatus === 'connected',
                'bg-yellow-500 animate-pulse': connectionStatus === 'connecting',
                'bg-red-500': connectionStatus === 'disconnected'
              }"
            ></div>
            <span class="text-xs text-gray-500">
              {{ connectionStatusText }}
            </span>
          </div>-->
        </div>
        
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-3">
            <div class="md:hidden relative" ref="mobileAvatarRef">
              <button
                @click="showMobileDropdown = !showMobileDropdown"
                class="relative focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
              >
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-medium text-sm">
                  {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div class="absolute -bottom-0 -right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
              </button>

              <div
                v-if="showMobileDropdown"
                class="absolute right-0 mt-3 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50"
                role="menu"
                aria-orientation="vertical"
              >
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
                
                <div class="py-2" role="none">
                  <router-link
                    v-if="authStore.isAdmin"
                    to="/admin"
                    @click="showMobileDropdown = false"
                    class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    role="menuitem"
                  >
                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Admin Panel
                  </router-link>
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

            <div class="hidden md:flex items-center space-x-3">
              <div class="relative">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-medium text-sm">
                  {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div class="absolute -bottom-0 -right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
              </div>
            </div>
          </div>
          
          <div class="hidden md:flex items-center space-x-2">
            <router-link
              v-if="authStore.isAdmin"
              to="/admin"
              class="btn btn-secondary text-sm"
            >
              Admin Panel
            </router-link>
            <button @click="authStore.logout" class="btn btn-secondary text-sm">
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="card">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm80-80h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/></svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                <p class="text-2xl font-bold text-gray-900">{{ tasksStore.statistics.total || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-2xl font-bold text-gray-900">{{ tasksStore.statistics.completed || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-gray-900">{{ tasksStore.statistics.pending || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center">
              <div class="p-2 bg-red-100 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">High Priority</p>
                <p class="text-2xl font-bold text-gray-900">{{ tasksStore.statistics.high_priority || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          <div class="lg:col-span-1">
            <TaskFilters />
            <div class="mt-6">
              <TaskForm 
                :editing-task="editingTask"
                @task-updated="handleTaskUpdated"
                @cancel-edit="handleCancelEdit"
              />
            </div>
          </div>

          <div class="lg:col-span-3">
            <TaskList @edit="handleEditTask" />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>