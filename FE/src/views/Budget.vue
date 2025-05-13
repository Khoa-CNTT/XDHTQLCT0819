<template>
  <div class="budget-container">
    <!-- Budget Header -->
    <div class="budget-header">
      <h1 class="budget-title">Quản lý ngân sách</h1>
      <button class="add-btn" @click="openBudgetModal">
        <span class="add-btn-icon">+</span>
        Thêm ngân sách mới
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card">
        <div class="card-title">Tổng ngân sách</div>
        <div class="card-value">{{ budgetSummary.totalBudget }}</div>
        <div class="progress-container">
          <div
            class="progress-bar"
            :class="getProgressClass(budgetSummary.totalSpentPercentage)"
            :style="{ width: getClampedPercentage(budgetSummary.totalSpentPercentage) }"
          ></div>
        </div>
        <div class="card-info">
          <div>Đã chi: {{ budgetSummary.totalSpent }}</div>
          <div>{{ budgetSummary.totalSpentPercentage }}%</div>
        </div>
      </div>

      <div
        class="summary-card"
        v-for="(budget, index) in budgetSummary.topOverThreshold"
        :key="'top-' + index"
      >
        <div class="card-title">{{ budget.category_name }}</div>
        <div class="card-value">{{ budget.budget_limit }}</div>
        <div class="progress-container">
          <div
            class="progress-bar"
            :class="getProgressClass(budget.percent)"
            :style="{ width: getClampedPercentage(budget.percent) }"
          ></div>
        </div>
        <div class="card-info">
          <div>Đã chi: {{ budget.spent }}</div>
          <div>{{ budget.percent }}%</div>
        </div>
      </div>
    </div>

    <!-- Alerts Section -->
    <div class="alerts-section" v-if="budgetAlerts.length > 0">
      <div
        v-for="(alert, index) in budgetAlerts"
        :key="index"
        class="alert-box alert-warning"
      >
        <div class="alert-icon">⚠️</div>
        <div class="alert-content" v-html="alert"></div>
        <button class="alert-dismiss" @click="dismissAlert(index)">&times;</button>
      </div>
    </div>

    <!-- Budget Details Section -->
    <div class="budget-section">
      <h2 class="section-header">Chi tiết ngân sách</h2>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Đang tải dữ liệu...</p>
      </div>

      <div
        v-else-if="
          budgetSummary.detailed && budgetSummary.detailed.length === 0
        "
        class="empty-state"
      >
        <p>
          Chưa có ngân sách nào được thiết lập. Vui lòng thêm ngân sách mới.
        </p>
      </div>

      <table v-else class="budget-table">
        <thead>
          <tr>
            <th>Danh mục</th>
            <th>Ngân sách</th>
            <th>Đã chi</th>
            <th class="hide-mobile">Ngưỡng cảnh báo</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(budget, index) in budgetSummary.detailed" :key="index">
            <td>
              <div class="category-cell">
                <div
                  class="category-icon"
                  :style="{ backgroundColor: getCategoryColor(budget.status) }"
                >
                  <i :class="getCategoryIcon(budget.icon)"></i>
                </div>
                <div>{{ budget.category_name }}</div>
              </div>
            </td>
            <td>{{ budget.budget_limit }}</td>
            <td>{{ budget.spent }}</td>
            <td class="hide-mobile">{{ budget.warning_threshold }}</td>
            <td>
              <span
                class="badge"
                :class="'badge-' + getStatusType(budget.status)"
              >
                {{ budget.status }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-edit" @click="editBudget(budget.id)">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-delete" @click="deleteBudget(budget.id)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal for adding/editing budget -->
    <div class="modal" v-if="showModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>
            {{ isEditing ? "Chỉnh sửa ngân sách" : "Thêm ngân sách mới" }}
          </h2>
          <span class="close-btn" @click="closeModal">&times;</span>
        </div>
        <div class="modal-body">
          <div v-if="errors.length > 0" class="form-errors">
            <div
              v-for="(error, index) in errors"
              :key="index"
              class="error-message"
            >
              {{ error }}
            </div>
          </div>

          <div class="form-group">
            <label for="categoryId">Danh mục</label>
            <select
              id="categoryId"
              v-model="currentBudget.category_id"
              required
            >
              <option value="" disabled>Chọn danh mục</option>
              <option
                v-for="category in expenseCategories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="budgetLimit">Hạn mức ngân sách</label>
            <input
              type="number"
              id="budgetLimit"
              v-model.number="currentBudget.budget_limit"
              required
              min="0"
            />
          </div>

          <div class="form-group">
            <label for="warningThreshold">Ngưỡng cảnh báo</label>
            <input
              type="number"
              id="warningThreshold"
              v-model.number="currentBudget.warning_threshold"
              required
              min="0"
            />
            <small class="form-help"
              >Số tiền khi đạt đến sẽ hiển thị cảnh báo</small
            >
          </div>

          <div class="form-actions">
            <button class="cancel-btn" @click="closeModal">Hủy</button>
            <button
              class="save-btn"
              @click="saveBudget"
              :disabled="formSubmitting"
            >
              <span v-if="formSubmitting">Đang lưu...</span>
              <span v-else>Lưu</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";

export default {
  name: "BudgetManager",
  data() {
    return {
      loading: true,
      budgetSummary: {
        id: 1,
        totalBudget: "0 ₫",
        totalSpent: "0 ₫",
        totalSpentPercentage: 0,
        topOverThreshold: [],
        detailed: [],
      },
      budgetAlerts: [],
      expenseCategories: [],
      showModal: false,
      isEditing: false,
      currentBudgetId: null,
      formSubmitting: false,
      errors: [],
      currentBudget: {
        category_id: "",
        budget_limit: 0,
        warning_threshold: 0,
      },
      budgetMap: {},
      dismissedAlerts: [], // Track dismissed alerts by index
    };
  },
  created() {
    this.fetchData();
    // Restore dismissed alerts from localStorage if available
    const savedDismissed = localStorage.getItem("dismissed_budget_alerts");
    if (savedDismissed) {
      try {
        this.dismissedAlerts = JSON.parse(savedDismissed);
      } catch (e) {
        console.error("Error parsing dismissed alerts:", e);
        this.dismissedAlerts = [];
      }
    }
  },
  methods: {
    getClampedPercentage(percentage) {
      if (!percentage && percentage !== 0) return "0%";
      
      const numericPercentage = parseFloat(percentage);
      if (isNaN(numericPercentage)) return "0%";
      
      return Math.min(100, numericPercentage) + "%";
    },
    
    // New method to dismiss alerts
    dismissAlert(index) {
      if (index >= 0 && index < this.budgetAlerts.length) {
        // Add alert index to dismissed list
        this.dismissedAlerts.push(index);
        // Save to localStorage for persistence
        localStorage.setItem("dismissed_budget_alerts", JSON.stringify(this.dismissedAlerts));
        // Remove from displayed alerts
        this.budgetAlerts.splice(index, 1);
      }
    },

    async fetchData() {
      this.loading = true;

      try {
        const categoriesResponse = await axios.get("/api/categories", {
          params: {
            type: "expense",
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });

        this.expenseCategories = categoriesResponse.data;

        const budgetSummaryResponse = await axios.get("/api/budget", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });

        if (budgetSummaryResponse.data) {
          this.budgetSummary = budgetSummaryResponse.data;

          if (!this.budgetSummary.topOverThreshold)
            this.budgetSummary.topOverThreshold = [];
          if (!this.budgetSummary.detailed) this.budgetSummary.detailed = [];
        }

        // Fetch budget alerts
        const budgetAlertsResponse = await axios.get("/api/budget/alerts", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });

        if (budgetAlertsResponse.data && budgetAlertsResponse.data.alerts) {
          this.budgetAlerts = budgetAlertsResponse.data.alerts;
        } else {
          this.budgetAlerts = [];
        }

        this.buildBudgetMap();
      } catch (error) {
        console.error("Error fetching data:", error);
        this.$toast?.error("Không thể tải dữ liệu. Vui lòng thử lại sau.") ||
          alert("Không thể tải dữ liệu. Vui lòng thử lại sau.");
      } finally {
        this.loading = false;
      }
    },

    buildBudgetMap() {
      // Reset the map
      this.budgetMap = {};

      // If the API response contains budgets with IDs
      if (
        this.budgetSummary.detailed &&
        this.budgetSummary.detailed.length > 0
      ) {
        this.budgetSummary.detailed.forEach((budget) => {
          // Store by category_id if available
          if (budget.category_id) {
            this.budgetMap[budget.category_id] = budget.id;
          }
          // Also store by category_name for lookup
          if (budget.category_name) {
            this.budgetMap[budget.category_name] = budget.id;
          }
        });
      }
    },

    getProgressClass(percentage) {
      if (!percentage && percentage !== 0) return "progress-normal";

      const numericPercentage = parseFloat(percentage);
      if (isNaN(numericPercentage)) return "progress-normal";

      if (numericPercentage >= 100) return "progress-danger";
      if (numericPercentage >= 75) return "progress-warning";
      return "progress-normal";
    },

    // Status styling
    getStatusType(status) {
      if (!status) return "success";

      switch (status) {
        case "Vượt ngưỡng":
          return "danger";
        case "Gần ngưỡng":
          return "warning";
        default:
          return "success";
      }
    },

    getCategoryIcon(icon) {
      if (!icon) return "fas fa-money-bill-wave";

      if (icon.includes("fa-")) return icon;

      return `fas fa-${icon}`;
    },

    getCategoryColor(status) {
      if (!status) return "#3b82f6";

      switch (status) {
        case "Vượt ngưỡng":
          return "#ef4444";
        case "Gần ngưỡng":
          return "#f59e0b";
        default:
          return "#3b82f6";
      }
    },

    openBudgetModal() {
      this.isEditing = false;
      this.currentBudgetId = null;
      this.errors = [];
      this.currentBudget = {
        category_id: "",
        budget_limit: 0,
        warning_threshold: 0,
      };
      this.showModal = true;
    },

    async editBudget(id) {
      this.isEditing = true;
      this.currentBudgetId = id;
      try {
        const response = await axios.get(`/api/budget/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });

        if (response.data && response.data.budget) {
          const budget = response.data.budget;
          this.currentBudget = {
            id: budget.id,
            category_id: budget.category_id,
            budget_limit: budget.budget_limit || budget.limit_amount || 0,
            warning_threshold: budget.warning_threshold || 0,
          };

          this.showModal = true;
        }
      } catch (error) {
        console.error(error);
      }
    },
    closeModal() {
      this.showModal = false;
      this.errors = [];
    },

    async saveBudget() {
      const toast = useToast();
      this.errors = [];
      this.formSubmitting = true;

      if (!this.currentBudget.category_id) {
        this.errors.push("Vui lòng chọn danh mục.");
      }

      if (
        !this.currentBudget.budget_limit ||
        this.currentBudget.budget_limit <= 0
      ) {
        this.errors.push("Vui lòng nhập hạn mức ngân sách hợp lệ.");
      }

      if (
        !this.currentBudget.warning_threshold ||
        this.currentBudget.warning_threshold <= 0
      ) {
        this.errors.push("Vui lòng nhập ngưỡng cảnh báo hợp lệ.");
      }

      if (this.errors.length > 0) {
        this.formSubmitting = false;
        return;
      }

      try {
        if (this.isEditing) {
          const response = await axios.put(
            `/api/budget/${this.currentBudgetId}`,
            this.currentBudget,
            {
              headers: {
                Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
              },
            }
          );
          toast.success(response.data.message);
        } else {
          const response = await axios.post("/api/budget", this.currentBudget, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });

          toast.success(response.data.message);
        }

        this.fetchData();
        this.closeModal();
      } catch (error) {
        toast.error(error.response.data.message);
      } finally {
        this.formSubmitting = false;
      }
    },

    async deleteBudget(id) {
      const toast = useToast();
      const result = await Swal.fire({
        title: "Xác nhận xoá",
        text: "⚠️ Bạn có chắc chắn muốn xoá danh mục này không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Xoá",
        cancelButtonText: "Huỷ",
      });
      if (result.isConfirmed) {
        try {
          const response = await axios.delete(`/api/budget/${id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          toast.success(response.data.message);
          this.fetchData();
        } catch (error) {
          console.log(error);
        }
      }
    },
  },
};
</script>

<style scoped>
:root {
  --primary-color: #3b82f6;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-700: #374151;
  --gray-800: #1f2937;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
    Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
}

.budget-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
  background-color: #ffffff;
  color: #1f2937;
  line-height: 1.5;
}

.budget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.budget-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
}

.add-btn {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-btn:hover {
  background-color: #2563eb;
}

.add-btn-icon {
  font-size: 18px;
  line-height: 0;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.summary-card {
  background-color: white;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.card-title {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 8px;
}

.card-value {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.progress-container {
  height: 8px;
  background-color: #e5e7eb;
  border-radius: 4px;
  margin-bottom: 12px;
  overflow: hidden; /* Add this to prevent overflow */
}

.progress-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-normal {
  background-color: #3b82f6;
}

.progress-warning {
  background-color: #f59e0b;
}

.progress-danger {
  background-color: #ef4444;
}

.card-info {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #6b7280;
}

.alerts-section {
  margin-bottom: 24px;
}

.alert-box {
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 8px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  position: relative; /* Added for positioning dismiss button */
}

.alert-warning {
  background-color: #fee2e2;
  border-left: 4px solid #ef4444;
  color: #b91c1c;
}

.alert-success {
  background-color: #d1fae5;
  border-left: 4px solid #10b981;
  color: #065f46;
}

.alert-icon {
  font-size: 20px;
  line-height: 1;
}

.alert-content {
  flex: 1;
}

/* New style for alert dismiss button */
.alert-dismiss {
  background: none;
  border: none;
  font-size: 20px;
  line-height: 1;
  color: #b91c1c;
  cursor: pointer;
  padding: 0 4px;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.alert-dismiss:hover {
  opacity: 1;
}

.budget-section {
  margin-bottom: 24px;
}

.section-header {
  margin-bottom: 16px;
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px 0;
  text-align: center;
  color: #6b7280;
  background-color: #f9fafb;
  border-radius: 8px;
  border: 1px dashed #d1d5db;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-radius: 50%;
  border-top-color: #3b82f6;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}

.budget-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.budget-table thead {
  background-color: #f3f4f6;
}

.budget-table th {
  text-align: left;
  padding: 12px 16px;
  font-weight: 500;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.budget-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}

.budget-table tr:last-child td {
  border-bottom: none;
}

.budget-table tr:hover {
  background-color: #f3f4f6;
}

.category-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.badge-warning {
  background-color: #fff7ed;
  color: #c2410c;
}

.badge-danger {
  background-color: #fee2e2;
  color: #b91c1c;
}

.badge-success {
  background-color: #d1fae5;
  color: #065f46;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn {
  background: none;
  border: none;
  cursor: pointer;
  border-radius: 4px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-edit {
  background-color: #dbeafe;
  color: #3b82f6;
}

.btn-edit:hover {
  background-color: #3b82f6;
  color: white;
}

.btn-delete {
  background-color: #fee2e2;
  color: #ef4444;
}

.btn-delete:hover {
  background-color: #ef4444;
  color: white;
}

.add-category-btn {
  background-color: #10b981;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-category-btn:hover {
  background-color: #059669;
}

/* Modal styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 600;
}

.close-btn {
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
}

.modal-body {
  padding: 16px;
}

.form-errors {
  margin-bottom: 16px;
  padding: 12px;
  background-color: #fee2e2;
  border-radius: 6px;
  border-left: 4px solid #ef4444;
}

.error-message {
  color: #b91c1c;
  font-size: 14px;
  margin-bottom: 4px;
}

.error-message:last-child {
  margin-bottom: 0;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.form-help {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: #6b7280;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 16px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.cancel-btn {
  padding: 8px 16px;
  border: 1px solid #d1d5db;
  background-color: white;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.save-btn {
  padding: 8px 16px;
  border: none;
  background-color: #3b82f6;
  color: white;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.save-btn:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

/* Responsive styles */
@media (max-width: 768px) {
  .summary-cards {
    grid-template-columns: 1fr;
  }

  .hide-mobile {
    display: none;
  }
}
</style>