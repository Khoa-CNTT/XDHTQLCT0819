<template>
  <div class="activity-logs">
    <div class="card shadow">
      <div class="card-header">
        <h1 class="header-title">Nhật ký hoạt động</h1>
      </div>
      
      <div class="card-body">
        <div class="logs-container">
          <!-- Multi-select control -->
          <div v-if="selectedLogs.length > 0" class="multi-select-controls">
            <div class="selected-count">
              <span>{{ selectedLogs.length }} mục đã chọn</span>
            </div>
            <div class="action-buttons">
              <button class="btn-delete" @click="showDeleteConfirm(selectedLogs)">
                <i class="fas fa-trash-alt"></i> Xoá tất cả
              </button>
              <button class="btn-cancel" @click="clearSelection">
                <i class="fas fa-times"></i> Huỷ chọn
              </button>
            </div>
          </div>
          
          <!-- Activity logs list -->
          <div 
            v-for="(log, index) in activityLogs" 
            :key="log.id"
            class="log-item"
            :class="{ 'selected': isSelected(log.id) }"
            @click="toggleSelection(log.id, $event)"
          >
            <div class="log-date-group">
              <div class="log-date">
                <span class="date">{{ formatDate(log.created_at) }}</span>
                <span class="time">{{ formatTime(log.created_at) }}</span>
              </div>
            </div>
            
            <div class="log-avatar">
              <div class="avatar-circle">
                <i class="fas fa-history"></i>
              </div>
            </div>
            
            <div class="log-content">
              <div class="log-action">{{ log.action }}</div>
            </div>
            
            <div class="log-actions">
              <div class="checkbox-wrapper">
                <input 
                  type="checkbox" 
                  :id="`log-${log.id}`" 
                  :checked="isSelected(log.id)"
                  @click.stop="toggleSelection(log.id, $event)"
                />
                <label :for="`log-${log.id}`"></label>
              </div>
              <button class="btn-single-delete" @click.stop="showDeleteConfirm([log.id])">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
          
          <!-- Empty state -->
          <div v-if="activityLogs.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="far fa-list-alt"></i>
            </div>
            <p>Không có hoạt động nào được ghi lại</p>
          </div>
          
          <!-- Loading state -->
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Đang tải...</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal-content delete-modal">
        <div class="modal-header">
          <h2>Xác nhận xoá</h2>
          <button @click="closeDeleteModal" class="close-button">&times;</button>
        </div>
        <div class="modal-body">
          <p>
            {{ deleteIds.length > 1 
               ? `Bạn có chắc chắn muốn xoá ${deleteIds.length} hoạt động đã chọn?` 
               : 'Bạn có chắc chắn muốn xoá hoạt động này?' }}
          </p>
        </div>
        <div class="modal-footer">
          <button @click="closeDeleteModal" class="btn-cancel">
            <i class="fas fa-times"></i> Huỷ
          </button>
          <button @click="deleteLogs" class="btn-delete" :disabled="isDeleting">
            <i class="fas fa-trash-alt"></i> 
            {{ isDeleting ? 'Đang xoá...' : 'Xoá' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ActivityLogs',
  data() {
    return {
      activityLogs: [],
      selectedLogs: [],
      showDeleteModal: false,
      deleteIds: [],
      loading: true,
      isDeleting: false
    }
  },
  created() {
    this.fetchActivityLogs();
  },
  methods: {
    async fetchActivityLogs() {
      this.loading = true;
      try {
        const response = await axios.get('/api/stories', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`
          }
        });
        this.activityLogs = response.data;
      } catch (error) {
        console.error('Error fetching activity logs:', error);
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      const weekdays = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
      const weekday = weekdays[date.getDay()];
      return `${weekday}, ngày ${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes} ${hours >= 12 ? 'PM' : 'AM'}`;
    },
    isSelected(id) {
      return this.selectedLogs.includes(id);
    },
    toggleSelection(id, event) {
      if (event.target.closest('.btn-single-delete')) return;
      if (this.isSelected(id)) {
        this.selectedLogs = this.selectedLogs.filter(logId => logId !== id);
      } else {
        this.selectedLogs.push(id);
      }
    },
    clearSelection() {
      this.selectedLogs = [];
    },
    showDeleteConfirm(ids) {
      this.deleteIds = ids;
      this.showDeleteModal = true;
    },
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.deleteIds = [];
    },
    async deleteLogs() {
      if (this.deleteIds.length === 0) return;
      this.isDeleting = true;
      try {
        await axios.delete('/api/stories', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`
          },
          data: { ids: this.deleteIds }
        });
        this.activityLogs = this.activityLogs.filter(log => !this.deleteIds.includes(log.id));
        this.selectedLogs = this.selectedLogs.filter(id => !this.deleteIds.includes(id));
      } catch (error) {
        console.error('Error deleting logs:', error);
      } finally {
        this.isDeleting = false;
        this.closeDeleteModal();
      }
    }
  }
}
</script>


<style scoped>
.activity-logs {
  font-family: 'Roboto', sans-serif;
  color: #1e293b;
}

.card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
}

.shadow {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.header-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.card-body {
  padding: 0;
}

.logs-container {
  max-height: 700px;
  overflow-y: auto;
  position: relative;
}

.multi-select-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background-color: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
}

.selected-count {
  font-weight: 500;
  color: #475569;
}

.action-buttons {
  display: flex;
  gap: 12px;
}

.log-item {
  position: relative;
  padding: 16px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  grid-template-areas: "content avatar date actions";
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.log-item:hover {
  background-color: #f8fafc;
}

.log-item.selected {
  background-color: #f1f5f9;
}

.log-date-group {
  grid-area: date;
  text-align: right;
}

.log-date {
  display: flex;
  flex-direction: column;
}

.date {
  font-weight: 500;
  font-size: 14px;
  color: #475569;
}

.time {
  font-size: 13px;
  color: #64748b;
  margin-top: 4px;
}

.log-avatar {
  grid-area: avatar;
  display: flex;
  justify-content: center;
  align-items: center;
}

.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #f3f4f6;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #64748b;
}

.log-content {
  grid-area: content;
}

.log-action {
  font-size: 15px;
  color: #334155;
  font-weight: 500;
}

.log-actions {
  grid-area: actions;
  display: flex;
  gap: 10px;
  align-items: center;
}

.checkbox-wrapper {
  position: relative;
}

.checkbox-wrapper input[type="checkbox"] {
  opacity: 0;
  position: absolute;
}

.checkbox-wrapper label {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid #cbd5e1;
  border-radius: 4px;
  position: relative;
  cursor: pointer;
  transition: all 0.2s ease;
}

.checkbox-wrapper input[type="checkbox"]:checked + label {
  background-color: #0ea5e9;
  border-color: #0ea5e9;
}

.checkbox-wrapper input[type="checkbox"]:checked + label::after {
  content: '';
  position: absolute;
  left: 6px;
  top: 2px;
  width: 6px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.btn-single-delete {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 14px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.btn-single-delete:hover {
  color: #ef4444;
  background-color: rgba(239, 68, 68, 0.1);
}

.btn-delete, .btn-cancel {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: none;
  border-radius: 6px;
  padding: 8px 14px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
}

.btn-delete:hover {
  background-color: #dc2626;
}

.btn-delete:disabled {
  background-color: #fca5a5;
  cursor: not-allowed;
}

.btn-cancel {
  background-color: #f8fafc;
  color: #64748b;
  border: 1px solid #cbd5e1;
}

.btn-cancel:hover {
  background-color: #f1f5f9;
  color: #475569;
}

.empty-state, .loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  text-align: center;
}

.empty-icon, .spinner {
  margin-bottom: 16px;
  color: #94a3b8;
  font-size: 48px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(203, 213, 225, 0.3);
  border-radius: 50%;
  border-top-color: #0ea5e9;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

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
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.1rem;
  color: #1e293b;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  color: #64748b;
  cursor: pointer;
}

.modal-body {
  padding: 24px;
}

.modal-footer {
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
  .log-item {
    grid-template-columns: 40px 1fr auto;
    grid-template-areas:
      "avatar content actions"
      "... date actions";
    gap: 12px;
  }
  
  .log-date-group {
    grid-area: date;
    text-align: left;
    margin-top: 8px;
  }
  
  .log-date {
    flex-direction: row;
    align-items: center;
    gap: 10px;
  }
  
  .date, .time {
    font-size: 12px;
  }
}
</style>

