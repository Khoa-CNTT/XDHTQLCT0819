<template>
  <div class="recurring-transactions">
    <!-- Main card container -->
    <div class="card">
      <div class="card-header">
        <h1 class="header-title">Quản lý giao dịch định kỳ</h1>
        <button 
          class="btn btn-primary add-transaction-btn"
          @click="openAddTransactionModal"
        >
          <i class="fas fa-plus"></i> Thêm giao dịch
        </button>
      </div>
      
      <div class="card-body">
        <!-- Loading indicator -->
        <div v-if="loading" class="loading-container">
          <div class="spinner"></div>
          <p>Đang tải dữ liệu...</p>
        </div>

        <div v-else>
          <!-- Table header -->
          <div class="transaction-header">
            <div class="transaction-row header-row">
              <div class="col col-description">
                <i class="fas fa-file-alt icon-header"></i> Mô tả
              </div>
              <div class="col col-amount">
                <i class="fas fa-coins icon-header"></i> Số tiền
              </div>
              <div class="col col-category">
                <i class="fas fa-folder icon-header"></i> Danh mục
              </div>
              <div class="col col-goal">
                <i class="fas fa-bullseye icon-header"></i> Mục tiêu
              </div>
              <div class="col col-frequency">
                <i class="fas fa-sync-alt icon-header"></i> Chu kỳ
              </div>
              <div class="col col-date">
                <i class="fas fa-calendar-alt icon-header"></i> Ngày bắt đầu
              </div>
              <div class="col col-actions">
                <i class="fas fa-cog icon-header"></i> Hành động
              </div>
            </div>
          </div>
          
          <!-- Transaction list -->
          <div class="transaction-list">
            <div 
              v-for="(transaction, index) in recurringTransactions" 
              :key="index"
              class="transaction-row transaction-item"
            >
              <div class="col col-description" data-label="Mô tả">{{ transaction.description }}</div>
              <div class="col col-amount" data-label="Số tiền">{{ formatCurrency(transaction.amount) }}</div>
              <div class="col col-category" data-label="Danh mục">
                <span class="category-badge" :class="getCategoryClass(transaction.category_id)" v-if="transaction.category_id">
                  <i :class="getCategoryIcon(transaction.category_id)"></i>
                  {{ getCategoryName(transaction.category_id) }}
                </span>
                <span v-else>—</span>
              </div>
              <div class="col col-goal" data-label="Mục tiêu">
                <span class="goal-badge" v-if="transaction.savingoal_id">
                  <i class="fas fa-bullseye"></i>
                  {{ getGoalName(transaction.savingoal_id) }}
                </span>
                <span v-else>—</span>
              </div>
              <div class="col col-frequency" data-label="Chu kỳ">{{ translatePeriod(transaction.period) }}</div>
              <div class="col col-date" data-label="Ngày bắt đầu">{{ formatDate(transaction.date) }}</div>
              <div class="col col-actions" data-label="Hành động">
                <div class="action-buttons">
                  <button class="btn-icon btn-edit" @click="editTransaction(transaction)" title="Chỉnh sửa">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn-icon btn-delete" @click="showDeleteConfirm(transaction.id)" title="Xoá">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Empty state -->
            <div v-if="recurringTransactions.length === 0" class="empty-state">
              <div class="empty-icon">
                <i class="far fa-calendar-alt"></i>
              </div>
              <p>Không có giao dịch định kỳ nào</p>
              <button 
                class="btn btn-primary"
                @click="openAddTransactionModal"
              >
                Thêm giao dịch định kỳ
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Add/Edit Transaction Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-content">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Chỉnh sửa giao dịch' : 'Thêm giao dịch định kỳ' }}</h2>
            <button @click="closeModal" class="close-button">&times;</button>
          </div>
          <div class="modal-body">
            <!-- Form errors alert -->
            <div v-if="formErrors.length > 0" class="alert alert-danger">
              <ul class="error-list">
                <li v-for="(error, index) in formErrors" :key="index">{{ error }}</li>
              </ul>
            </div>
            
            <div class="form-group">
              <label for="description">
                <i class="fas fa-file-alt form-icon"></i> Mô tả
              </label>
              <input 
                id="description"
                v-model="formData.description" 
                type="text" 
                class="form-control"
                placeholder="Nhập mô tả giao dịch"
              />
            </div>
            
            <div class="form-group">
              <label for="amount">
                <i class="fas fa-coins form-icon"></i> Số tiền
              </label>
              <div class="input-with-icon">
                <input 
                  id="amount"
                  v-model="formData.amount" 
                  type="number" 
                  class="form-control" 
                  placeholder="0"
                />
                <span class="currency-suffix">VND</span>
              </div>
            </div>
            
            <div class="form-group">
              <label for="category">
                <i class="fas fa-folder form-icon"></i> Danh mục
              </label>
              <select 
                id="category"
                v-model="formData.category_id" 
                class="form-control"
              >
                <option value="">-- Không chọn --</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="savingoal">
                <i class="fas fa-bullseye form-icon"></i> Mục tiêu
              </label>
              <select 
                id="savingoal"
                v-model="formData.savingoal_id" 
                class="form-control"
              >
                <option value="">-- Không chọn --</option>
                <option v-for="goal in savingGoals" :key="goal.id" :value="goal.id">
                  {{ goal.name }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="period">
                <i class="fas fa-sync-alt form-icon"></i> Chu kỳ
              </label>
              <select 
                id="period"
                v-model="formData.period" 
                class="form-control"
              >
                <option value="daily">Hàng ngày</option>
                <option value="weekly">Hàng tuần</option>
                <option value="monthly">Hàng tháng</option>
                <option value="yearly">Hàng năm</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="date">
                <i class="fas fa-calendar-alt form-icon"></i> Ngày bắt đầu
              </label>
              <input 
                id="date"
                v-model="formData.date" 
                type="date" 
                class="form-control"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closeModal" class="btn btn-secondary" :disabled="formSubmitting">
              <i class="fas fa-times"></i> Huỷ
            </button>
            <button @click="saveTransaction" class="btn btn-primary" :disabled="formSubmitting">
              <i class="fas" :class="formSubmitting ? 'fa-spinner fa-spin' : 'fa-save'"></i> 
              {{ isEditing ? 'Cập nhật' : 'Thêm mới' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-content delete-modal">
          <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Xác nhận xoá</h2>
            <button @click="closeDeleteModal" class="close-button">&times;</button>
          </div>
          <div class="modal-body">
            <div class="delete-warning">
              <i class="fas fa-trash-alt delete-icon"></i>
              <p>Bạn có chắc chắn muốn xoá giao dịch định kỳ này?</p>
              <p class="delete-note">Hành động này không thể hoàn tác.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closeDeleteModal" class="btn btn-secondary" :disabled="deleteSubmitting">
              <i class="fas fa-times"></i> Huỷ
            </button>
            <button @click="deleteTransaction" class="btn btn-danger" :disabled="deleteSubmitting">
              <i class="fas" :class="deleteSubmitting ? 'fa-spinner fa-spin' : 'fa-trash-alt'"></i> 
              Xoá
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useToast } from "vue-toastification";

export default {
  name: 'RecurringTransactions',
  setup() {
    // Sử dụng composable để lấy thông báo toast
    const toast = useToast();
    return { toast }
  },
  data() {
    return {
      recurringTransactions: [],
      categories: [],
      savingGoals: [],
      loading: true,
      showModal: false,
      showDeleteModal: false,
      deleteId: null,
      isEditing: false,
      editingId: null,
      formSubmitting: false,
      deleteSubmitting: false,
      formErrors: [],
      formData: {
        description: '',
        amount: '',
        category_id: '',
        savingoal_id: '',
        period: 'monthly',
        date: ''
      }
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      try {
        this.loading = true
        
        // Fetch transactions data
        const transactionsResponse = await axios.get('/api/recurringtransaction', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`
          }
        })
        this.recurringTransactions = Array.isArray(transactionsResponse.data) ? 
          transactionsResponse.data : []
        
        // Fetch categories data
        const categoriesResponse = await axios.get('/api/categories', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`
          }
        })
        this.categories = Array.isArray(categoriesResponse.data) ? 
          categoriesResponse.data : []
        
        // Fetch saving goals data
        const goalsResponse = await axios.get('/api/saving-goals', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`
          }
        })
        
        // Lưu ý: Kiểm tra cấu trúc dữ liệu sau khi nhận từ backend
        if (Array.isArray(goalsResponse.data)) {
          this.savingGoals = goalsResponse.data
        } else if (goalsResponse.data && Array.isArray(goalsResponse.data.data)) {
          // Nếu response có cấu trúc { data: [...] }
          this.savingGoals = goalsResponse.data.data
        } else {
          this.savingGoals = []
          console.error('Dữ liệu mục tiêu không có định dạng mảng:', goalsResponse.data)
        }
        
      } catch (error) {
        console.error('Lỗi khi tải dữ liệu:', error)
        
        // Improved error handling
        let errorMessage = 'Không thể tải dữ liệu. Vui lòng thử lại sau.'
        
        if (error.response && error.response.data) {
          if (typeof error.response.data === 'string') {
            errorMessage = error.response.data
          } else if (error.response.data.error) {
            errorMessage = error.response.data.error
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message
          }
        }
        
        // Sử dụng toast thay vì this.$toast
        this.toast.error(errorMessage)
      } finally {
        this.loading = false
      }
    },
    formatCurrency(amount) {
      if (amount === undefined || amount === null) return '0 ₫'
      return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫'
    },
    formatDate(dateStr) {
      if (!dateStr) return ''
      try {
        const date = new Date(dateStr)
        if (isNaN(date.getTime())) return dateStr // Return original if invalid date
        
        return new Intl.DateTimeFormat('vi-VN', { 
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        }).format(date)
      } catch (e) {
        console.error('Lỗi khi định dạng ngày:', e)
        return dateStr // Return original on error
      }
    },
    getCategoryName(categoryId) {
      if (!categoryId) return ''
      const category = this.categories.find(c => c.id === categoryId)
      return category ? category.name : ''
    },
    getCategoryIcon(categoryId) {
      if (!categoryId) return 'fas fa-folder'
      const category = this.categories.find(c => c.id === categoryId)
      return category && category.icon ? `fas ${category.icon}` : 'fas fa-folder'
    },
    getCategoryClass(categoryId) {
      if (!categoryId) return ''
      const category = this.categories.find(c => c.id === categoryId)
      return category && category.color ? `category-${category.color}` : ''
    },
    getGoalName(goalId) {
      if (!goalId) return ''
      const goal = this.savingGoals.find(g => g.id === goalId)
      return goal ? goal.name : ''
    },
    translatePeriod(period) {
      if (!period) return ''
      const translations = {
        daily: 'Hàng ngày',
        weekly: 'Hàng tuần',
        monthly: 'Hàng tháng',
        yearly: 'Hàng năm'
      }
      return translations[period] || period
    },
    openAddTransactionModal() {
      this.isEditing = false
      this.editingId = null
      this.formErrors = []
      this.formData = {
        description: '',
        amount: '',
        category_id: '',
        savingoal_id: '',
        period: 'monthly',
        date: new Date().toISOString().split('T')[0] // Today's date
      }
      this.showModal = true
    },
    editTransaction(transaction) {
      if (!transaction) return
      this.isEditing = true
      this.editingId = transaction.id
      this.formErrors = []
      
      // Safety checks for each property
      this.formData = { 
        description: transaction.description || '',
        amount: transaction.amount || '',
        category_id: transaction.category_id || '',
        savingoal_id: transaction.savingoal_id || '',
        period: transaction.period || 'monthly',
        date: transaction.date || new Date().toISOString().split('T')[0]
      }
      this.showModal = true
    },
    closeModal() {
      this.showModal = false
      this.formErrors = []
    },
    validateForm() {
      this.formErrors = []
      
      if (!this.formData.description.trim()) {
        this.formErrors.push('Vui lòng nhập mô tả')
      }
      
      if (!this.formData.amount) {
        this.formErrors.push('Vui lòng nhập số tiền')
      } else if (isNaN(this.formData.amount) || parseFloat(this.formData.amount) <= 0) {
        this.formErrors.push('Số tiền phải lớn hơn 0')
      }
      
      if (!this.formData.period) {
        this.formErrors.push('Vui lòng chọn kỳ hạn')
      }
      
      if (!this.formData.date) {
        this.formErrors.push('Vui lòng chọn ngày bắt đầu')
      }
      
      return this.formErrors.length === 0
    },
    async saveTransaction() {
      if (!this.validateForm()) return
      
      try {
        this.formSubmitting = true
        
        // Process form data for proper storage
        const processedData = {
          description: this.formData.description.trim(),
          period: this.formData.period,
          date: this.formData.date,
          amount: parseFloat(this.formData.amount),
          category_id: this.formData.category_id || null,
          savingoal_id: this.formData.savingoal_id || null
        }
        
        let response
        
        if (this.isEditing && this.editingId) {
          // Update existing transaction
          response = await axios.put(`/api/recurringtransaction/${this.editingId}`, processedData, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`
            }
          })
          
          this.toast.success('Giao dịch định kỳ đã được cập nhật thành công')
        } else {
          // Add new transaction
          response = await axios.post('/api/recurringtransaction', processedData, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`
            }
          })
          
          this.toast.success('Giao dịch định kỳ mới đã được tạo thành công')
        }
        
        // Refresh data
        await this.fetchData()
        this.closeModal()
      } catch (error) {
        console.error('Lỗi khi lưu giao dịch:', error)
        
        // Handle validation errors from server
        if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
          this.formErrors = Object.values(error.response.data.errors).flat()
        } else {
          let errorMessage = 'Có lỗi xảy ra khi lưu giao dịch. Vui lòng thử lại.'
          
          if (error.response && error.response.data) {
            if (typeof error.response.data === 'string') {
              errorMessage = error.response.data
            } else if (error.response.data.error) {
              errorMessage = error.response.data.error
            } else if (error.response.data.message) {
              errorMessage = error.response.data.message
            }
          }
          
          this.toast.error(errorMessage)
        }
      } finally {
        this.formSubmitting = false
      }
    },
    showDeleteConfirm(id) {
      if (!id) return
      this.deleteId = id
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
      this.deleteId = null
    },
    async deleteTransaction() {
      if (!this.deleteId) return
      
      try {
        this.deleteSubmitting = true
        await axios.delete(`/api/recurringtransaction/${this.deleteId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`
          }
        })
        
        // Refresh data
        await this.fetchData()
        
        this.toast.success('Giao dịch định kỳ đã được xoá thành công')
        
        this.closeDeleteModal()
      } catch (error) {
        console.error('Lỗi khi xoá giao dịch:', error)
        
        let errorMessage = 'Có lỗi xảy ra khi xoá giao dịch. Vui lòng thử lại.'
        
        if (error.response && error.response.data) {
          if (typeof error.response.data === 'string') {
            errorMessage = error.response.data
          } else if (error.response.data.error) {
            errorMessage = error.response.data.error
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message
          }
        }
        
        this.toast.error(errorMessage)
      } finally {
        this.deleteSubmitting = false
      }
    }
  }
}
</script>




<style scoped>
/* Main styles */
.recurring-transactions {
  font-family: 'Inter', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #333;
  background-color: #f8f9fa;
  padding: 70px;
  min-height: 100vh;
  box-sizing: border-box;
}

/* Card styles */
.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-bottom: 20px;
  border: 1px solid #eaeaea;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid #eaeaea;
  background-color: #fff;
  flex-wrap: wrap;
  gap: 10px;
}

.header-title {
  font-size: 32px;
  font-weight: 600;
  color: #1a202c;
  margin: 0;
}

/* Button styles */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  padding: 10px 18px;
  border: none;
  white-space: nowrap;
  text-decoration: none;
  user-select: none;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover {
  background-color: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
  background-color: #f3f4f6;
  color: #4b5563;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
  transform: translateY(-1px);
}

.btn-danger {
  background-color: #dc2626;
  color: white;
}

.btn-danger:hover {
  background-color: #b91c1c;
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(220, 38, 38, 0.2);
}

.add-transaction-btn i {
  margin-right: 8px;
}

.btn i {
  margin-right: 6px;
}

/* Icon buttons */
.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  background-color: transparent;
  color: #64748b;
}

.btn-icon:hover {
  background-color: #f1f5f9;
}

.btn-edit {
  color: #2563eb;
}

.btn-edit:hover {
  background-color: rgba(37, 99, 235, 0.1);
}

.btn-delete {
  color: #dc2626;
}

.btn-delete:hover {
  background-color: rgba(220, 38, 38, 0.1);
}

.action-buttons {
  display: flex;
  gap: 8px;
}

/* Header icons */
.icon-header {
  color: #64748b;
  margin-right: 8px;
}

/* Transaction table styles */
.card-body {
  padding: 0;
  overflow-x: auto;
}

.transaction-header {
  background-color: #f8fafc;
  border-bottom: 1px solid #eaeaea;
  position: sticky;
  top: 0;
  z-index: 10;
}

.transaction-row {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 1fr 0.8fr;
  align-items: center;
  gap: 12px;
  min-width: 800px;
}

.header-row {
  padding: 14px 24px;
  font-weight: 600;
  color: #64748b;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.transaction-list .transaction-row {
  padding: 16px 24px;
  border-bottom: 1px solid #eaeaea;
  transition: background-color 0.2s ease;
}

.transaction-list .transaction-row:last-child {
  border-bottom: none;
}

.transaction-list .transaction-row:hover {
  background-color: #f8fafc;
}

.col {
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 8px;
}

.col-amount {
  text-align: right;
  font-weight: 600;
}

/* Category badges */
.category-badge,
.goal-badge {
  display: inline-flex;
  align-items: center;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  background-color: #f1f5f9;
  color: #64748b;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.category-badge i,
.goal-badge i {
  margin-right: 6px;
  font-size: 12px;
  flex-shrink: 0;
}

.category-blue {
  background-color: #dbeafe;
  color: #2563eb;
}

.category-green {
  background-color: #dcfce7;
  color: #16a34a;
}

.category-orange {
  background-color: #ffedd5;
  color: #ea580c;
}

.category-purple {
  background-color: #f3e8ff;
  color: #9333ea;
}

.category-teal {
  background-color: #ccfbf1;
  color: #0d9488;
}

.goal-badge {
  background-color: #fae8ff;
  color: #a21caf;
}

/* Empty state styles */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 24px;
  text-align: center;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  color: #94a3b8;
  background-color: #f1f5f9;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.empty-state p {
  margin-bottom: 20px;
  color: #64748b;
  font-size: 15px;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.modal-container {
  width: 100%;
  max-width: 500px;
  margin: 0 24px;
  max-height: 90vh;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  border: 1px solid #eaeaea;
  animation: modalSlideIn 0.3s ease forwards;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.delete-modal {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid #eaeaea;
  flex-shrink: 0;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-header h2 i {
  color: #dc2626;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
  height: 36px;
  width: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  padding: 0;
  margin: -6px;
}

.close-button:hover {
  background-color: #f1f5f9;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 18px 24px;
  border-top: 1px solid #eaeaea;
  gap: 12px;
  flex-shrink: 0;
}

/* Delete warning styles */
.delete-warning {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 10px 0;
}

.delete-icon {
  font-size: 42px;
  color: #dc2626;
  margin-bottom: 20px;
  background-color: #fee2e2;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.delete-warning p {
  margin: 0;
  font-size: 15px;
  line-height: 1.5;
  color: #1a202c;
}

.delete-note {
  color: #64748b !important;
  font-size: 13px !important;
  margin-top: 8px !important;
}

/* Form styles */
.form-group {
  margin-bottom: 20px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #4b5563;
  display: flex;
  align-items: center;
}

.form-icon {
  margin-right: 8px;
  color: #64748b;
  width: 16px;
  text-align: center;
}

.form-control {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
  background-color: #fff;
  box-sizing: border-box;
}

.form-control:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.input-with-icon {
  position: relative;
}

.currency-suffix {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 14px;
  pointer-events: none;
}

/* Responsive styles */
@media (max-width: 900px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .add-transaction-btn {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .card {
    border-radius: 8px;
    margin-left: -16px;
    margin-right: -16px;
    width: calc(100% + 32px);
  }
  
  .transaction-row {
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 16px;
    min-width: auto;
  }
  
  .transaction-header {
    display: none;
  }
  
  .col {
    padding: 8px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
  }
  
  .col:last-child {
    border-bottom: none;
  }
  
  .col::before {
    content: attr(data-label);
    font-weight: 500;
    color: #64748b;
    margin-right: 12px;
    font-size: 13px;
    white-space: nowrap;
  }
  
  .col-actions {
    justify-content: flex-end;
  }
  
  .modal-container {
    width: 95%;
    margin: 0;
    max-height: 85vh;
  }
  
  .btn {
    font-size: 13px;
    padding: 10px 14px;
  }
  
  .modal-header, .modal-footer {
    padding: 16px;
  }
  
  .modal-body {
    padding: 16px;
  }
  
  .card-header {
    padding: 16px;
  }
  
  .header-title {
    font-size: 18px;
  }
}

/* Animation effects */
.transaction-item {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.transaction-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  z-index: 1;
}

.btn, .btn-icon {
  position: relative;
  overflow: hidden;
}

.btn::after, .btn-icon::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background-color: #fff;
  opacity: 0;
  transition: opacity 0.3s;
  pointer-events: none;
}

.btn:active::after, .btn-icon:active::after {
  opacity: 0.2;
}

/* Fix for when the budget is small and negative */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}

/* Scrollbar styling */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Print styles */
@media print {
  .recurring-transactions {
    background-color: white;
    padding: 0;
  }
  
  .card {
    box-shadow: none;
    border: none;
  }
  
  .add-transaction-btn,
  .action-buttons,
  .btn-icon {
    display: none;
  }
  
  .modal-overlay {
    display: none;
  }
}
</style>
