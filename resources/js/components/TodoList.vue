<template>
  <div class="container-fluid min-vh-100 bg-soft-primary">
    <div class="row justify-content-center py-5">
      <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-soft">
          <div class="card-body p-4">
            <div class="row mb-4">
              <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                  <div class="progress flex-grow-1 me-3" style="height: 20px;">
                    <div 
                      class="progress-bar bg-success" 
                      :style="{ width: progressPercentage + '%' }"
                      role="progressbar" 
                      :aria-valuenow="progressPercentage" 
                      aria-valuemin="0" 
                      aria-valuemax="100"
                    ></div>
                  </div>
                  <span class="text-muted">{{ Math.round(progressPercentage) }}% Complete</span>
                </div>
              </div>
            </div>

            <div class="text-center mb-5">
              <h1 class="h2 text-muted font-weight-light">📋 Todo Manager</h1>
              <p class="text-muted">Organize your tasks peacefully</p>
            </div>

            <div class="create-section mb-5 px-3 py-4 bg-white rounded-lg shadow-sm">
              <div class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                  <input
                    v-model="newTodo"
                    type="text"
                    class="form-control form-control-soft"
                    placeholder="What needs to be done?"
                  >
                </div>
                
                <div class="col-6 col-md-3">
                  <select v-model="selectedCategory" class="form-control form-control-soft">
                    <option disabled value="">Category</option>
                    <option v-for="category in categories" :key="category">
                      {{ category }}
                    </option>
                  </select>
                </div>

                <div class="col-6 col-md-2">
                  <input
                    v-model="dueDate"
                    type="date"
                    class="form-control form-control-soft"
                    :min="new Date().toISOString().split('T')[0]"
                  >
                </div>

                <div class="col-12 col-md-2">
                  <button 
                    @click="addTodo"
                    class="btn btn-soft-primary w-100"
                  >
                    <i class="bi bi-plus-circle me-2"></i>Add
                  </button>
                </div>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-12 col-md-6">
                <div class="todo-column bg-light p-4 rounded-lg shadow-sm h-100 border border-primary">
                  <h4 class="text-primary mb-4 d-flex align-items-center">
                    ⏳ Pending 
                    <span class="badge bg-primary text-white ms-2">{{ pendingTodos.length }}</span>
                  </h4>

                  <div class="list-group">
                    <div 
                      v-for="todo in sortedPendingTodos" 
                      :key="todo.id" 
                      class="todo-item bg-white p-3 mb-3 rounded shadow-sm d-flex justify-content-between align-items-center"
                      :class="dueDateStatus(todo.due_date)"
                    >
                      <div>
                        <input 
                          type="checkbox"
                          :checked="todo.completed"
                          @change="updateTodo(todo, $event.target.checked)"
                          :disabled="todo.isUpdating"
                          class="form-check-input me-2"
                        >
                        <h5 class="mb-1">{{ todo.title }}</h5>
                        <p class="mb-0 text-muted">Category: {{ todo.category || 'No Category' }}</p>
                        <p class="mb-0 text-muted">Due Date: {{ formatDate(todo.due_date) }}</p>
                      </div>
                      <button @click.prevent.stop="deleteTodo(todo.id)" class="btn btn-danger btn-sm">Delete</button>
                    </div>
                  </div>

                  <div v-if="!pendingTodos.length" class="text-center text-muted mt-4">
                    No pending tasks. 🎉
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="todo-column bg-light p-4 rounded-lg shadow-sm h-100 border border-success">
                  <h4 class="text-success mb-4 d-flex align-items-center">
                    ✅ Completed 
                    <span class="badge bg-success text-white ms-2">{{ completedTodos.length }}</span>
                  </h4>

                  <div class="list-group">
                    <div 
                      v-for="todo in completedTodos" 
                      :key="todo.id" 
                      class="todo-item bg-light p-3 mb-3 rounded shadow-sm d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h5 class="mb-1">{{ todo.title }}</h5>
                        <p class="mb-0 text-muted">Category: {{ todo.category || 'No Category' }}</p>
                        <p class="mb-0 text-muted">Due Date: {{ formatDate(todo.due_date) }}</p>
                      </div>
                      <button @click.prevent.stop="deleteTodo(todo.id)" class="btn btn-danger btn-sm">Delete</button>
                    </div>
                  </div>

                  <div v-if="!completedTodos.length" class="text-center text-muted mt-4">
                    No completed tasks yet.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../axios';

export default {
  data() {
    return {
      newTodo: '',
      selectedCategory: '',
      dueDate: '',
      categories: ['Work', 'Personal', 'Shopping', 'Health'],
      todos: [],
      loading: true,
      error: null
    }
  },
  computed: {
    pendingTodos() {
      return this.todos.filter(t => !t.completed)
    },
    completedTodos() {
      return this.todos.filter(t => t.completed)
    },
    progressPercentage() {
      const completed = this.todos.filter(t => t.completed).length
      return this.todos.length ? (completed / this.todos.length * 100) : 0
    },
    sortedPendingTodos() {
      return [...this.pendingTodos].sort((a, b) => {
        const aDate = a.due_date ? new Date(a.due_date) : new Date(8640000000000000)
        const bDate = b.due_date ? new Date(b.due_date) : new Date(8640000000000000)
        return aDate - bDate
      })
    }
  },
  async mounted() {
    await this.fetchTodos()
  },
  methods: {
    async fetchTodos() {
      try {
        this.loading = true
        const { data } = await axios.get('/todos')
        this.todos = data
      } catch (error) {
        this.error = 'Failed to load todos. Please try again later.'
        console.error(error)
      } finally {
        this.loading = false
      }
    },
    async addTodo() {
      if (!this.newTodo.trim()) return
      try {
        const { data } = await axios.post('/todos', {
          title: this.newTodo,
          category: this.selectedCategory,
          due_date: this.dueDate,
          completed: false
        })
        this.todos.push(data)
        this.resetForm()
      } catch (error) {
        this.error = 'Failed to create todo. Please try again.'
        console.error(error)
      }
    },
    async updateTodo(todo, newCompletedState) {
      const originalState = todo.completed
      todo.completed = newCompletedState
      try {
        await axios.put(`/todos/${todo.id}`, { completed: newCompletedState })
      } catch (error) {
        todo.completed = originalState
        console.error("Update failed:", error)
        this.error = "Failed to update todo status"
      }
    },
    async deleteTodo(id) {
      if (!confirm('Are you sure you want to delete this todo?')) return
      try {
        await axios.delete(`/todos/${id}`)
        this.todos = this.todos.filter(t => t.id !== id)
      } catch (error) {
        this.error = 'Failed to delete todo. Please try again.'
        console.error(error)
      }
    },
    resetForm() {
      this.newTodo = ''
      this.selectedCategory = ''
      this.dueDate = ''
    },
    formatDate(dateString) {
      if (!dateString) return 'No due date'
      const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }
      return new Date(dateString).toLocaleDateString(undefined, options)
    },
    dueDateStatus(dueDate) {
      if (!dueDate) return 'due-unknown'
      const today = new Date()
      const due = new Date(dueDate)
      const timeDiff = due.getTime() - today.getTime()
      const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24))
      if (daysDiff <= 3) return 'due-soon'
      if (daysDiff <= 7) return 'due-later'
      return 'due-far'
    }
  }
}
</script>

<style scoped>
.bg-soft-primary {
  background-color: #e7f5ff;
}


.shadow-soft {
  box-shadow: 0 0.5rem 1rem rgba(148, 163, 184, 0.03);
}


.form-control-soft {
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.form-control-soft:focus {
  border-color: #93c5fd;
  box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.15);
}


.btn-soft-primary {
  background-color: #eff6ff;
  color: #3b82f6;
  border: 1px solid #bfdbfe;
  transition: all 0.3s ease;
}


.btn-soft-primary:hover {
  background-color: #3b82f6;
  color: white;
}


.todo-column {
  min-height: 500px;
}


.todo-item {
  transition: all 0.2s ease;
}

.todo-item:hover {
  transform: translateY(-2px);
}

.due-soon {
  border-left: 4px solid #dc3545;
}

.due-later {
  border-left: 4px solid #ffc107;
}

.due-far {
  border-left: 4px solid #28a745;
}

.due-unknown {
  border-left: 4px solid #6c757d;
}

.progress-bar {
  transition: width 0.5s ease;
}
</style>
