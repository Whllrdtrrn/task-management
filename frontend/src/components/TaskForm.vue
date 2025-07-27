<template>
  <div class="card">
    <h3 class="text-lg font-medium text-gray-900 mb-4">
      {{ editingTask ? 'Edit Task' : 'Create New Task' }}
    </h3>
    
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded-md text-sm">
        {{ error }}
      </div>
      
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          required
          class="input"
          placeholder="Enter task title"
        />
      </div>
      
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea
          id="description"
          v-model="form.description"
          rows="3"
          class="input"
          placeholder="Enter task description"
        />
      </div>
      
      <div>
        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
        <select id="priority" v-model="form.priority" class="input">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>
      
      <div v-if="editingTask">
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" v-model="form.status" class="input">
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
        </select>
      </div>
      
      <div class="flex space-x-2">
        <button
          type="submit"
          :disabled="isLoading"
          class="flex-1 btn btn-primary"
        >
          <span v-if="isLoading">{{ editingTask ? 'Updating...' : 'Creating...' }}</span>
          <span v-else>{{ editingTask ? 'Update Task' : 'Create Task' }}</span>
        </button>
        
        <button
          v-if="editingTask"
          type="button"
          @click="cancelEdit"
          class="btn btn-secondary"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  editingTask: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['taskUpdated', 'cancelEdit'])

const tasksStore = useTasksStore()
const toastStore = useToastStore()
const isLoading = ref(false)
const error = ref('')

const form = reactive({
  title: '',
  description: '',
  priority: 'medium',
  status: 'pending'
})

const resetForm = () => {
  form.title = ''
  form.description = ''
  form.priority = 'medium'
  form.status = 'pending'
  error.value = ''
}

const handleSubmit = async () => {
  isLoading.value = true
  error.value = ''
     
  try {
    if (props.editingTask) {
      console.log('📝 Updating task:', props.editingTask.id, form)
      await tasksStore.updateTask(props.editingTask.id, form)
      emit('taskUpdated')
      // Show success toast for update
      //toastStore.success(
      //  'Task Updated!',
      //  `"${form.title}" has been updated successfully.`
      //)
      setTimeout(() => {
          toastStore.success(
            'High Priority Task',
            'Don\'t forget to tackle this important task!'
          )
        },50000000)
    } else {
      console.log('📝 Creating new task:', form)
      await tasksStore.createTask(form)
      toastStore.success(
        'Task Created!',
        `"${form.title}" has been added to your tasks.`
      )
      resetForm()
       if (form.priority === 'high') {
        setTimeout(() => {
          toastStore.info(
            'High Priority Task',
            'Don\'t forget to tackle this important task!'
          )
        },500)
      }
    }
  } catch (err) {
    console.error('Form submission error:', err)
    error.value = err.response?.data?.message || err.message || 'An error occurred'
  } finally {
    isLoading.value = false
  }
}

const cancelEdit = () => {
  resetForm()
  emit('cancelEdit')
}

watch(() => props.editingTask, (newTask) => {
  console.log('📝 TaskForm received editingTask:', newTask)
  
  if (newTask) {
    console.log('📝 Populating form with task data:', {
      title: newTask.title,
      description: newTask.description,
      priority: newTask.priority,
      status: newTask.status
    })
    
    form.title = newTask.title
    form.description = newTask.description || ''
    form.priority = newTask.priority
    form.status = newTask.status
  } else {
    console.log('📝 Clearing form (no editing task)')
    resetForm()
  }
}, { immediate: true })
</script>