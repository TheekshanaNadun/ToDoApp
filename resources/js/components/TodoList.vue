<template>
  <div class="container-fluid min-vh-100 bg-soft-primary">
    <div class="row justify-content-center py-5">
      <div class="col-12 col-xl-10">
        <!-- Main Content Card -->
        <div class="card border-0 shadow-soft">
          <div class="card-body p-4">
            <!-- Header -->
            <div class="text-center mb-5">
              <h1 class="h2 text-muted font-weight-light">📋 Todo Manager</h1>
              <p class="text-muted">Organize your tasks peacefully</p>
            </div>

            <!-- Create Todo Section -->
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
  <!-- Pending Tasks Column -->
  <div class="col-12 col-md-6">
    <div class="todo-column bg-light p-4 rounded-lg shadow-sm h-100 border border-primary">
      <h4 class="text-primary mb-4 d-flex align-items-center">
        ⏳ Pending 
        <span class="badge bg-primary text-white ms-2">{{ pendingTodos.length }}</span>
      </h4>

      <!-- Pending Todo Items -->
      <div v-for="todo in pendingTodos" :key="todo.id" class="todo-item bg-white p-3 mb-3 rounded shadow-sm d-flex justify-content-between align-items-center">
        <!-- Todo Details -->
        <div>
          <h5 class="mb-1">{{ todo.title }}</h5>
          <p class="mb-0 text-muted">Category: {{ todo.category || 'No Category' }}</p>
          <p class="mb-0 text-muted">Due Date: {{ formatDate(todo.due_date) }}</p>
        </div>

        <!-- Actions -->
        <div>
          <input type="checkbox" v-model="todo.completed" @change="updateTodo(todo)" class="form-check-input me-2">
          <button @click.prevent.stop="deleteTodo(todo.id)" class="btn btn-danger btn-sm">Delete</button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!pendingTodos.length" class="text-center text-muted mt-4">
        No pending tasks. 🎉
      </div>
    </div>
  </div>

  <!-- Completed Tasks Column -->
  <div class="col-12 col-md-6">
    <div class="todo-column bg-light p-4 rounded-lg shadow-sm h-100 border border-success">
      <h4 class="text-success mb-4 d-flex align-items-center">
        ✅ Completed 
        <span class="badge bg-success text-white ms-2">{{ completedTodos.length }}</span>
      </h4>

      <!-- Completed Todo Items -->
      <div v-for="todo in completedTodos" :key="todo.id" class="todo-item bg-light p-3 mb-3 rounded shadow-sm d-flex justify-content-between align-items-center">
        <!-- Todo Details -->
        <div>
          <h5 class="mb-1">{{ todo.title }}</h5>
          <p class="mb-0 text-muted">Category: {{ todo.category || 'No Category' }}</p>
          <p v-if="todo.due_date" class="mb-0 text-muted">Completed on: {{ formatDate(todo.due_date) }}</p>
        </div>

        <!-- Actions -->
        <button @click.prevent.stop="deleteTodo(todo.id)" class="btn btn-danger btn-sm">Delete</button>
      </div>

      <!-- Empty State -->
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
import { VueDraggableNext } from 'vue-draggable-next'

export default {
  components: {       draggable: VueDraggableNext  },
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

        async updateTodo(todo) {
            try {
                const originalState = todo.completed
                todo.completed = !originalState // Optimistic update
                
                await axios.put(`/todos/${todo.id}`, {
                    title: todo.title,
                    completed: todo.completed,
                    category: todo.category,
                    due_date: todo.due_date
                })
            } catch (error) {
                this.error = 'Failed to update todo. Please try again.'
                console.error(error)
                todo.completed = !todo.completed // Revert on error
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

        async onDragEnd(evt) {
            try {
                const todo = this.todos.find(t => t.id === evt.item.id)
                const newCompletedState = evt.to.classList.contains('completed-column')
                
                if (todo.completed !== newCompletedState) {
                    await axios.put(`/todos/${todo.id}/position`, {
                        completed: newCompletedState
                    })
                    todo.completed = newCompletedState
                }
            } catch (error) {
                this.error = 'Failed to save changes. Please try again.'
                console.error(error)
            }
        },

        resetForm() {
            this.newTodo = ''
            this.selectedCategory = ''
            this.dueDate = ''
        },

        formatDate(dateString) {
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }
            return new Date(dateString).toLocaleDateString(undefined, options)
        }
    }
}
</script>

<style scoped>
.bg-soft-primary {
  background-color: #ffffff;
}

.shadow-soft {
  box-shadow: 0 0.5rem 1rem rgba(148, 163, 184, 0.03);
}

.form-control-soft {
  border: 1px solid hwb(0 100% 0%);
  transition: all 0.3s ease;
}

.form-control-soft:focus {
  border-color: #93c5fd;
  box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.15);
}
.card-body {
  background-color: #ecfff0;
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
  background-color: rgba(241, 245, 249, 0.4);
  border: 1px solid #f1f5f9;
}

.todo-item {
  transition: all 0.2s ease;
  cursor: grab;
}

.todo-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(148, 163, 184, 0.1);
}

.bg-soft {
  background-color: #f8fafc;
}
</style>
