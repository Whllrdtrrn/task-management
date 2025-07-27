<template>
  <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 cursor-default"
    :class="{
      'opacity-75': task.status === 'completed'
    }">
    <div class="flex items-start justify-between">
      <div class="flex items-start space-x-3 flex-1">
        <div class="drag-handle flex-shrink-0 cursor-move p-1 text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zM6 4a1 1 0 011-1h6a1 1 0 011 1v12a1 1 0 01-1 1H7a1 1 0 01-1-1V4zm2 2a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm0 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm0 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z">
            </path>
          </svg>
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-center space-x-2 mb-2">
            <h4 class="text-sm font-medium text-gray-900 truncate"
              :class="{ 'line-through': task.status === 'completed' }">
              {{ task.title }}
            </h4>

            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border"
              :class="priorityClasses[task.priority]">
              {{ task.priority }}
            </span>

            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
              :class="statusClasses[task.status]">
              {{ task.status }}
            </span>
          </div>

          <p v-if="task.description" class="text-sm text-gray-600 mb-2"
            :class="{ 'line-through': task.status === 'completed' }">
            {{ task.description }}
          </p>

          <p class="text-xs text-gray-400">
            Created {{ formatDate(task.created_at) }}
          </p>
        </div>
      </div>

      <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
        <button @click="handleToggleStatus" :disabled="isUpdating"
          class="p-1 rounded text-gray-400 hover:text-gray-600 disabled:opacity-50 transition-colors"
          :title="task.status === 'completed' ? 'Mark as pending' : 'Mark as completed'">
          <div v-if="isUpdating"
            class="animate-spin h-4 w-4 border-2 border-blue-600 border-t-transparent rounded-full"></div>
          <svg v-else-if="task.status === 'completed'" class="w-4 h-4 text-green-600" fill="currentColor"
            viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </button>

        <button @click="$emit('edit',task)" class="p-1 rounded text-gray-400 hover:text-blue-600 transition-colors"
          title="Edit task">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
            </path>
          </svg>
        </button>

        <button v-if="canDelete" @click="$emit('delete',task)"
          class="p-1 rounded text-gray-400 hover:text-red-600 transition-colors" title="Delete task">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
            </path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed,ref} from 'vue'
import {useAuthStore} from '@/stores/auth'
import {useTasksStore} from '@/stores/tasks'

const props=defineProps({
  task: {
    type: Object,
    required: true
  }
})

defineEmits(['edit','delete'])

const authStore=useAuthStore()
const tasksStore=useTasksStore()
const isUpdating=ref(false)

const priorityClasses={
  low: 'bg-green-100 text-green-800 border-green-200',
  medium: 'bg-yellow-100 text-yellow-800 border-yellow-200',
  high: 'bg-red-100 text-red-800 border-red-200'
}

const statusClasses={
  pending: 'bg-gray-100 text-gray-800',
  completed: 'bg-green-100 text-green-800'
}

const canDelete=computed(() => {
  return authStore.isAdmin||authStore.user?.id===props.task.user_id
})

const handleToggleStatus=async () => {
  if(isUpdating.value) return

  isUpdating.value=true
  const newStatus=props.task.status==='completed'? 'pending':'completed'

  try {
    const updateData={
      title: props.task.title,
      description: props.task.description||'',
      priority: props.task.priority,
      status: newStatus
    }

    await tasksStore.updateTask(props.task.id,updateData)
    console.log('✅ Task status updated:',props.task.id)
  } catch(error) {
    console.error('❌ Failed to update task status:',error)
    alert('Failed to update task status')
  } finally {
    isUpdating.value=false
  }
}

const formatDate=(dateString) => {
  return new Date(dateString).toLocaleDateString()
}

</script>

<style scoped>
.drag-handle:hover {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}
</style>