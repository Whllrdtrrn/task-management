<template>
  <div class="card">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-medium text-gray-900">Tasks</h3>
      <div class="text-sm text-gray-500">
        {{ filteredTasks.length }} task{{ filteredTasks.length !== 1 ? 's' : '' }}
      </div>
    </div>
    
    <div v-if="tasksStore.isLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-gray-500">Loading tasks...</p>
    </div>
    
    <div v-else-if="filteredTasks.length === 0" class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new task.</p>
    </div>
    
    <div v-else ref="sortableContainer" class="space-y-3">
      <TaskItem
        v-for="task in sortableTasks"
        :key="`task-${task.id}-${task.updated_at || task.created_at}`"
        :task="task"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch, nextTick, onUnmounted } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import TaskItem from './TaskItem.vue'
import Sortable from 'sortablejs'

const tasksStore = useTasksStore()
const toastStore = useToastStore()
const authStore = useAuthStore()
const sortableContainer = ref(null)
const sortableTasks = ref([])
let sortableInstance = null
let isReordering = ref(false)

const filteredTasks = computed(() => tasksStore.filteredTasks)

const initializeSortable = () => {
  if (sortableContainer.value && !sortableInstance) {
    sortableInstance = new Sortable(sortableContainer.value, {
      animation: 200,
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      dragClass: 'sortable-drag',
      handle: '.drag-handle',
      onStart: () => {
        isReordering.value = true
      },
      onEnd: async (event) => {
        const { oldIndex, newIndex } = event
        
        if (oldIndex !== newIndex) {
          console.log(`🔄 Reordering: moved from ${oldIndex} to ${newIndex}`)
          
          const movedTask = sortableTasks.value.splice(oldIndex, 1)[0]
          sortableTasks.value.splice(newIndex, 0, movedTask)
          
          try {
            await tasksStore.reorderTasks(sortableTasks.value)
            console.log('✅ Tasks reordered successfully')
          } catch (error) {
            console.error('❌ Failed to reorder tasks:', error)
            const revertedTask = sortableTasks.value.splice(newIndex, 1)[0]
            sortableTasks.value.splice(oldIndex, 0, revertedTask)
            alert('Failed to reorder tasks. Please try again.')
          }
        }
        
        isReordering.value = false
      }
    })
    
    console.log('✅ Sortable initialized')
  }
}

const destroySortable = () => {
  if (sortableInstance) {
    sortableInstance.destroy()
    sortableInstance = null
    console.log('🔌 Sortable destroyed')
  }
}

const updateSortableTasks = () => {
  if (!isReordering.value) {
    console.log('📝 Updating sortable tasks:', filteredTasks.value.length)
    sortableTasks.value = [...filteredTasks.value]
  }
}

onMounted(async () => {
  await nextTick()
  updateSortableTasks()
  initializeSortable()
})

watch(filteredTasks, (newTasks, oldTasks) => {
  console.log('🔄 Filtered tasks changed:', newTasks.length, 'tasks')
  
  updateSortableTasks()
  
  nextTick(() => {
    destroySortable()
    if (newTasks.length > 0) {
      initializeSortable()
    }
  })
}, { immediate: true, deep: true })

watch(() => tasksStore.tasks, (newTasks) => {
  console.log('📝 Store tasks changed:', newTasks.length, 'tasks')
  updateSortableTasks()
}, { deep: true })

onUnmounted(() => {
  destroySortable()
})

const handleEdit = (task) => {
  console.log('📝 Edit task requested:', task.id, task)
  emit('edit', task)
}

const handleDelete = async (task) => {
  if (isDeleting.value) {
    console.log('⚠️ Already deleting, ignoring request')
    return
  }
  
  if (confirm('Are you sure you want to delete this task?')) {
    isDeleting.value = true
    try {
      await tasksStore.deleteTask(task.id)
      console.log('✅ Task deleted:', task.id)
      toastStore.success('Success!', 'Task deleted successfully')
    } catch (error) {
      console.error('❌ Failed to delete task:', error)
      toastStore.success('Success!', 'Failed to delete task')
    } finally {
      isDeleting.value = false
    }
  }
}

const isDeleting = ref(false)

const emit = defineEmits(['edit'])
</script>

<style scoped>
.sortable-ghost {
  opacity: 0.4;
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.sortable-chosen {
  transform: scale(1.02);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  z-index: 999;
}

.sortable-drag {
  transform: rotate(2deg);
  opacity: 0.8;
}
</style>