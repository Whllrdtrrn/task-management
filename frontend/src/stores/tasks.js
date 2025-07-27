import {defineStore} from "pinia";
import {tasksAPI} from "@/services/api";
import {createEcho, destroyEcho} from "@/services/echo"; // Add this import

export const useTasksStore = defineStore("tasks", {
  state: () => ({
    tasks: [],
    statistics: {},
    filters: {
      status: "",
      priority: "",
      search: "",
    },
    isLoading: false,
    error: null,
    
    realTimeEnabled: false,
    connectionStatus: "disconnected", 
  }),

  getters: {
    filteredTasks: state => {
      let filtered = [...state.tasks];

      if (state.filters.status) {
        filtered = filtered.filter(
          task => task.status === state.filters.status
        );
      }

      if (state.filters.priority) {
        filtered = filtered.filter(
          task => task.priority === state.filters.priority
        );
      }

      if (state.filters.search) {
        const search = state.filters.search.toLowerCase();
        filtered = filtered.filter(
          task =>
            task.title.toLowerCase().includes(search) ||
            task.description?.toLowerCase().includes(search)
        );
      }

      return filtered.sort((a, b) => {
        const orderA = a.order ?? 999999; 
        const orderB = b.order ?? 999999;
        return orderA - orderB;
      });
    },
  },

  actions: {
    async fetchTasks() {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await tasksAPI.getTasks(this.filters);
        this.tasks = response.data.tasks || [];
        this.statistics = response.data.statistics || {};

        this.ensureTaskOrder();

        console.log("📋 Tasks fetched:", this.tasks.length);
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch tasks";
        console.error("❌ Fetch tasks error:", error);
      } finally {
        this.isLoading = false;
      }
    },

    ensureTaskOrder() {
      let hasTasksWithoutOrder = false;

      this.tasks.forEach((task, index) => {
        if (!task.order && task.order !== 0) {
          task.order = index + 1;
          hasTasksWithoutOrder = true;
        }
      });

      if (hasTasksWithoutOrder) {
        console.log("🔧 Fixed tasks without order values");
      }
    },

    async createTask(taskData) {
      try {
        console.log("📝 Creating task:", taskData);
        const response = await tasksAPI.createTask(taskData);
        const newTask = response.data.task;

        if (!newTask.order) {
          const maxOrder = Math.max(...this.tasks.map(t => t.order || 0), 0);
          newTask.order = maxOrder + 1;
        }

        this.tasks.push(newTask);
        this.updateStatistics();

        console.log(
          "✅ Task created:",
          newTask.id,
          "with order:",
          newTask.order
        );
        return newTask;
      } catch (error) {
        console.error("❌ Create task error:", error);
        this.error = error.response?.data?.message || "Failed to create task";
        throw error;
      }
    },

    async updateTask(id, taskData) {
      try {
        console.log("🔄 Updating task:", id, taskData);
        const response = await tasksAPI.updateTask(id, taskData);
        const updatedTask = response.data.task;

        const index = this.tasks.findIndex(task => task.id === id);
        if (index !== -1) {
          this.tasks.splice(index, 1, {...updatedTask});
          console.log("✅ Task updated locally:", updatedTask.id);
        } else {
          this.tasks.push(updatedTask);
        }

        this.updateStatistics();
        return updatedTask;
      } catch (error) {
        console.error("❌ Update task error:", error);
        this.error = error.response?.data?.message || "Failed to update task";
        throw error;
      }
    },

    async deleteTask(id) {
      try {
        console.log("🗑️ Deleting task:", id);
        await tasksAPI.deleteTask(id);

        const initialLength = this.tasks.length;
        this.tasks = this.tasks.filter(task => task.id !== id);

        if (this.tasks.length < initialLength) {
          this.updateStatistics();
          console.log("✅ Task deleted locally:", id);
        } else {
          console.log("⚠️ Task not found locally for deletion:", id);
        }

        return true;
      } catch (error) {
        console.error("❌ Delete task error:", error);
        this.error = error.response?.data?.message || "Failed to delete task";
        throw error;
      }
    },

    async deleteTask(id) {
      try {
        console.log("🗑️ Deleting task:", id);
        await tasksAPI.deleteTask(id);

        if (!this.realTimeEnabled) {
          this.tasks = this.tasks.filter(task => task.id !== id);
          this.updateStatistics();
        }

        console.log("✅ Task deleted:", id);
        return true;
      } catch (error) {
        console.error("❌ Delete task error:", error);
        this.error = error.response?.data?.message || "Failed to delete task";
        throw error;
      }
    },

    async deleteTask(id) {
      try {
        console.log("🗑️ Deleting task:", id);
        await tasksAPI.deleteTask(id);

        const initialLength = this.tasks.length;
        this.tasks = this.tasks.filter(task => task.id !== id);

        if (this.tasks.length < initialLength) {
          this.updateStatistics();
          console.log("✅ Task deleted locally:", id);
        } else {
          console.log("⚠️ Task not found locally for deletion:", id);
        }

        return true;
      } catch (error) {
        console.error("❌ Delete task error:", error);
        this.error = error.response?.data?.message || "Failed to delete task";
        throw error;
      }
    },

    async reorderTasks(newOrder) {
      const tasksToUpdate = newOrder.map((task, index) => ({
        id: task.id,
        order: index + 1,
        user_id: task.user_id,
      }));

      try {
        await tasksAPI.reorderTasks(tasksToUpdate);
        console.log("✅ Tasks reordered on backend");

        if (!this.realTimeEnabled) {
          tasksToUpdate.forEach(({id, order}) => {
            const task = this.tasks.find(t => t.id === id);
            if (task) task.order = order;
          });
        }
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to reorder tasks";
        throw error;
      }
    },

    initializeRealTime(userId, token) {
      console.log("🔌 Initializing WebSocket for user:", userId);
      this.connectionStatus = "connecting";

      try {
        const echo = createEcho(token);
        if (!echo) {
          console.log("❌ Failed to create Echo instance");
          this.connectionStatus = "disconnected";
          return;
        }

        console.log(
          "✅ Echo instance created, setting up PUBLIC channels (avoiding auth issues)..."
        );

        echo
          .channel("tasks")
          .listen(".task.updated", e => {
            console.log("🔄 Real-time: Task updated", e.task);
            if (e.user_id === userId) {
              this.handleTaskUpdated(e.task);
            }
          })
          .listen(".task.created", e => {
            console.log("➕ Real-time: Task created", e.task);
            if (e.user_id === userId) {
              this.handleTaskCreated(e.task);
            }
          })
          .listen(".task.deleted", e => {
            console.log("🗑️ Real-time: Task deleted", e.task_id);
            if (e.user_id === userId) {
              this.handleTaskDeleted(e.task_id);
            }
          });

        this.realTimeEnabled = true;
        this.connectionStatus = "connected";
        console.log(
          "✅ WebSocket initialized successfully with PUBLIC channels"
        );
      } catch (error) {
        console.error("❌ Failed to initialize WebSocket:", error);
        this.connectionStatus = "disconnected";
      }
    },

    destroyRealTime() {
      console.log("🔌 Destroying WebSocket connection");
      this.realTimeEnabled = false;
      this.connectionStatus = "disconnected";
      destroyEcho();
    },

    handleTaskCreated(task) {
      const exists = this.tasks.find(t => t.id === task.id);
      if (!exists) {
        if (!task.order) {
          const maxOrder = Math.max(...this.tasks.map(t => t.order || 0), 0);
          task.order = maxOrder + 1;
        }
        this.tasks.push(task);
        this.updateStatistics();
        console.log("➕ Task added via WebSocket:", task.id);
      } else {
        console.log(
          "⚠️ Task already exists, skipping WebSocket create:",
          task.id
        );
      }
    },

    handleTaskUpdated(task) {
      const index = this.tasks.findIndex(t => t.id === task.id);
      if (index !== -1) {
        const currentTask = this.tasks[index];
        const hasChanges = JSON.stringify(currentTask) !== JSON.stringify(task);

        if (hasChanges) {
          this.tasks.splice(index, 1, {...task});
          this.updateStatistics();
          console.log("🔄 Task updated via WebSocket:", task.id);
        } else {
          console.log("⚠️ Task unchanged, skipping WebSocket update:", task.id);
        }
      } else {
        this.tasks.push(task);
        this.updateStatistics();
        console.log("➕ Task not found locally, added via WebSocket:", task.id);
      }
    },

    handleTaskDeleted(taskId) {
      const initialLength = this.tasks.length;
      this.tasks = this.tasks.filter(t => t.id !== taskId);

      if (this.tasks.length < initialLength) {
        this.updateStatistics();
        console.log("🗑️ Task deleted via WebSocket:", taskId);
      } else {
        console.log("⚠️ Task not found for deletion:", taskId);
      }
    },

    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchTasks();
    },

    clearFilters() {
      this.filters = {
        status: "",
        priority: "",
        search: "",
      };
      this.fetchTasks();
    },

    updateStatistics() {
      const stats = {
        total: this.tasks.length,
        completed: this.tasks.filter(t => t.status === "completed").length,
        pending: this.tasks.filter(t => t.status === "pending").length,
        high_priority: this.tasks.filter(t => t.priority === "high").length,
        medium_priority: this.tasks.filter(t => t.priority === "medium").length,
        low_priority: this.tasks.filter(t => t.priority === "low").length,
      };

      this.statistics = {...stats};
      console.log("📊 Statistics updated:", stats);
    },
  },
});
