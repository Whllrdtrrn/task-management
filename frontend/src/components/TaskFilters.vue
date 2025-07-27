<template>
  <div class="card">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
    
    <div class="space-y-4">
      <div>
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <input
          id="search"
          v-model="localFilters.search"
          type="text"
          class="input"
          placeholder="Search tasks..."
          @input="debounceSearch"
        />
      </div>
      
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select
          id="status"
          v-model="localFilters.status"
          class="input"
          @change="updateFilter('status', $event.target.value)"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
        </select>
      </div>
      
      <div>
        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
        <select
          id="priority"
          v-model="localFilters.priority"
          class="input"
          @change="updateFilter('priority', $event.target.value)"
        >
          <option value="">All Priorities</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>
      
      <button
        @click="clearAllFilters"
        class="w-full btn btn-secondary text-sm"
      >
        Clear Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { debounce } from 'lodash-es'

const tasksStore = useTasksStore()

const localFilters = reactive({
  search: tasksStore.filters.search,
  status: tasksStore.filters.status,
  priority: tasksStore.filters.priority,
})

const updateFilter = (key, value) => {
  tasksStore.setFilter(key, value)
}

const debounceSearch = debounce(() => {
  updateFilter('search', localFilters.search)
}, 300)

const clearAllFilters = () => {
  localFilters.search = ''
  localFilters.status = ''
  localFilters.priority = ''
  tasksStore.clearFilters()
}
</script>