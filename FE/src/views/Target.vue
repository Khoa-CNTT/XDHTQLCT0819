<template>
  <div class="goals-page">
    <!-- Tiêu đề -->
    <h1 class="page-title"><i class="fas fa-bullseye"></i> Mục tiêu của bạn</h1>

    <!-- Hình ảnh -->
    <div class="goals-image-container">
      <img
        src="https://vinuni.edu.vn/wp-content/uploads/2024/11/tim-hieu-muc-tieu-marketing-la-gi-va-cac-loai-muc-tieu-thuong-gap-hinh-1.jpg"
        alt="Hình minh họa mục tiêu"
        class="goals-image"
      />
    </div>

    <!-- Danh sách mục tiêu -->
    <div class="goals-list">
      <div
        v-for="(goal, index) in goals"
        :key="index"
        class="goal-card"
        :class="`goal-card--${index % 3}`"
      >
        <div class="goal-header">
          <div class="goal-icon">
            <i :class="goal.icon"></i>
          </div>
          <span class="goal-name">{{ goal.name }}</span>
          <i
            class="fas fa-edit"
            v-if="getGoalStatus(goal) !== 'completed'"
            @click.stop="showEditModal(goal, index)"
          >
          </i>
          <i
            class="fas fa-trash"
            v-if="getGoalStatus(goal) !== 'completed'"
            @click.stop="deleteGoal(index)"
          >
          </i>
        </div>
        <div class="goal-details">
          <span class="goal-target"
            >Mục tiêu: {{ formatVND(goal.target) }}</span
          >
          <span class="goal-saved"
            >Đã tiết kiệm: {{ formatVND(goal.save_money) }}</span
          >
          <span class="goal-start-date"
            >Bắt đầu: {{ formatDate(goal.start_day) }}</span
          >
          <span class="goal-date">Hạn: {{ formatDate(goal.end_day) }}</span>
          <span class="goal-remaining" :class="getGoalStatusClass(goal)">
            {{ goal.status }}
          </span>
          <span
            class="goal-remaining"
            :class="getGoalStatusClass(getGoalStatus(goal))"
          >
            {{
              getGoalStatus(goal) === "completed"
                ? "🎉 Mục tiêu hoàn thành!"
                : getGoalStatus(goal) === "failed"
                ? "❌ Mục tiêu thất bại"
                : "⏳ Đang tiến hành"
            }}
          </span>
          <p v-if="getGoalStatus(goal) === 'failed'" class="goal-hint">
            🕒 Mục tiêu thất bại. Bạn có muốn thêm thời gian? <br />
            👉 Hãy nhấn nút chỉnh sửa để gia hạn ngày hoàn thành.
          </p>
        </div>
        <div class="progress-bar">
          <div
            class="progress-fill"
            :style="{ width: `${progress(goal.save_money, goal.target)}%` }"
          >
            <span class="progress-text"
              >{{ progress(goal.save_money, goal.target) }}%</span
            >
          </div>
        </div>
        <!-- Thêm số tiền hôm nay tiết kiệm -->
        <div class="add-savings-today">
          <label for="savings-today">Số tiền hôm nay tiết kiệm (VND):</label>
          <div class="savings-input-group">
            <input
              type="text"
              v-model="goal.savingsToday"
              @input="formatCurrency($event, index)"
              placeholder="Ví dụ: 100000"
              min="0"
              class="savings-input"
            />
            <button @click="addSavingsToday(index)" class="add-savings-button">
              Thêm
            </button>
          </div>
        </div>
      </div>
      <p v-if="goals.length === 0" class="no-data">
        Bạn chưa có mục tiêu nào. Hãy thêm mục tiêu mới!
      </p>
    </div>

    <!-- Modal thêm/sửa mục tiêu -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <h2 class="modal-title">
          {{ isEditing ? "Chỉnh sửa mục tiêu" : "Thêm mục tiêu mới" }}
        </h2>
        <form @submit.prevent="saveGoal" class="goal-form">
          <div class="form-group">
            <label for="goal-name">Tên mục tiêu</label>
            <input
              id="goal-name"
              v-model="currentGoal.name"
              type="text"
              placeholder="Ví dụ: Mua xe máy"
              required
            />
          </div>
          <div class="form-group">
            <label for="goal-target">Số tiền mục tiêu (VND)</label>
            <input
              id="goal-target"
              v-model.number="currentGoal.target"
              type="number"
              placeholder="Ví dụ: 30000000"
              required
            />
          </div>
          <div class="form-group">
            <label for="goal-saved">Số tiền đã tiết kiệm (VND)</label>
            <input
              id="goal-saved"
              v-model.number="currentGoal.save_money"
              type="number"
              placeholder="Ví dụ: 5000000"
              required
            />
          </div>
          <div class="form-group">
            <label for="goal-start-date">Ngày bắt đầu</label>
            <input
              id="goal-start-date"
              v-model="currentGoal.start_day"
              type="date"
              required
            />
          </div>
          <div class="form-group">
            <label for="goal-deadline">Ngày hoàn thành</label>
            <input
              id="goal-deadline"
              v-model="currentGoal.end_day"
              type="date"
              required
            />
          </div>
          <div class="modal-actions">
            <button type="submit" class="save-button">Lưu</button>
            <button type="button" class="cancel-button" @click="closeModal">
              Hủy
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Nút thêm mục tiêu -->
    <button class="add-goal-button" @click="showAddModal">
      <i class="fas fa-plus"></i>
    </button>
  </div>
</template>

<script>
import { useToast } from "vue-toastification";
import axios from "axios";

export default {
  name: "Goals",
  data() {
    return {
      goals: [],
      showModal: false,
      isEditing: false,
      currentGoal: {
        name: "",
        target: 0,
        save_money: 0,
        start_day: "",
        end_day: "",
        note: "",
        icon: "fas fa-bullseye",
      },
      editingIndex: null,
      feedbackMessage: "",
      toast: useToast(),
    };
  },
  mounted() {
    this.fetchGoals();
  },
  methods: {
    formatVND(number) {
      if (typeof number === "string") {
        number = number.replace(/\./g, "");
      }
      const value = Number(number) || 0;
      return value.toLocaleString("vi-VN") + " VND";
    },

    formatCurrency(event, index) {
      let value = event.target.value.replace(/\D/g, "");
      value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      this.goals[index].savingsToday = value;
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("vi-VN");
    },

    progress(saved, target) {
      return Math.min(Math.round((saved / target) * 100), 100);
    },

    showAddModal() {
      this.currentGoal = {
        name: "",
        target: 0,
        save_money: 0,
        start_day: "",
        end_day: "",
        note: "",
        icon: "fas fa-bullseye",
      };
      this.isEditing = false;
      this.showModal = true;
    },

    showEditModal(goal, index) {
      this.currentGoal = { ...goal };
      this.editingIndex = index;
      this.isEditing = true;
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.currentGoal = {
        name: "",
        target: 0,
        save_money: 0,
        start_day: "",
        end_day: "",
        note: "",
        icon: "",
      };
      this.editingIndex = null;
    },

    saveGoal() {
      if (this.isEditing) {
        axios
          .put(`/api/saving-goals/${this.currentGoal.id}`, this.currentGoal, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          })
          .then((response) => {
            this.goals[this.editingIndex] = response.data.data;
            this.toast.success("✅ Cập nhật mục tiêu thành công");
            this.closeModal();
          })
          .catch((error) => {
            this.toast.error("❌ Có lỗi khi cập nhật mục tiêu");
            console.error("Có lỗi khi cập nhật mục tiêu:", error);
          });
      } else {
        axios
          .post("/api/saving-goals", this.currentGoal, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          })
          .then((response) => {
            this.goals.push(response.data.data);
            this.closeModal();
            this.toast.success("✅ Thêm mục tiêu thành công");
          })
          .catch((error) => {
            this.toast.error("❌ Có lỗi khi thêm mục tiêu");
            console.error("Có lỗi khi thêm mục tiêu:", error);
          });
      }
    },

    deleteGoal(index) {
      const goal = this.goals[index];
      axios
        .delete(`/api/saving-goals/${goal.id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        })
        .then((response) => {
          this.goals.splice(index, 1);
          this.toast.success(response.data.data.message);
        })
        .catch((error) => {
          this.toast.error("❌ Có lỗi khi xóa mục tiêu");
          console.error("Có lỗi khi xóa mục tiêu:", error);
        });
    },

    addSavingsToday(index) {
      const goal = this.goals[index];
      const savingsToday = goal.savingsToday.replace(/\./g, "") || 0;
      if (savingsToday < 0) {
        this.toast.error("❌ Số tiền không thể âm!");
        return;
      }
      goal.savingsToday = "";
      axios
        .put(
          `/api/saving-goals/${goal.id}/add-money`,
          { amount: savingsToday },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          }
        )
        .then((response) => {
          this.goals[index] = response.data.data;
          this.toast.success(response.data.message);
          const user = JSON.parse(localStorage.getItem("user"));
          if (user) {
            user.monthly_customer_spending =
              response.data.data.monthly_customer_spending;
            localStorage.setItem("user", JSON.stringify(user));
          }
          this.fetchGoals();
        })
        .catch((error) => {
          this.toast.error(error.response.data.message);
          console.error("Có lỗi khi cập nhật số tiền:", error);
        });
    },

    fetchGoals() {
      axios
        .get("/api/saving-goals", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        })
        .then((response) => {
          this.goals = response.data.data;
        })
        .catch((error) => {
          this.toast.error("❌ Có lỗi khi tải mục tiêu");
          console.error("Có lỗi khi tải mục tiêu:", error);
        });
    },
    getGoalStatus(goal) {
      const today = new Date();
      const endDate = new Date(goal.end_day);

      if (goal.savings_percentage >= 100 && today <= endDate) {
        return "completed";
      } else if (today > endDate && goal.savings_percentage < 100) {
        return "failed";
      } else {
        return "in_progress";
      }
    },

    getGoalStatusClass(status) {
      if (status === "completed") return "goal-status-completed";
      if (status === "failed") return "goal-status-failed";
      return "goal-status-active";
    },
  },
};
</script>

<style scoped>
/* Container chính */
.goals-page {
  padding: 2rem;
  background: linear-gradient(135deg, #f0f2f5 0%, #e6f0fa 100%);
  min-height: calc(100vh - 80px);
  display: flex;
  flex-direction: column;
  gap: 2rem;
  overflow: hidden;
}

/* Tiêu đề */
.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
}

.page-title i {
  color: #0ea5e9;
  font-size: 1.5rem;
}

/* Hình ảnh */
.goals-image-container {
  width: 100%;
  max-width: 700px;
  margin: 0 auto;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.goals-image {
  width: 100%;
  height: 300px;
  object-fit: cover;
  display: block;
}

/* Danh sách mục tiêu */
.goals-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

/* Card mục tiêu */
.goal-card {
  background-color: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #a1c4fd;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  animation: fadeInUp 0.5s ease forwards;
}

.goal-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Màu nền khác nhau cho từng card */
.goal-card--0 {
  background-color: #f0f9ff;
}

.goal-card--1 {
  background-color: #f0fdf4;
}

.goal-card--2 {
  background-color: #fefce8;
}

.goal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.goal-icon i {
  font-size: 1.5rem;
  color: #0ea5e9;
}

.goal-name {
  font-size: 1.3rem;
  font-weight: 600;
  color: #374151;
  flex: 1;
}

.goal-actions {
  display: flex;
  gap: 0.75rem;
}

.goal-actions i {
  font-size: 1rem;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.3s ease;
}

.goal-actions i:hover {
  color: #0ea5e9;
}

.goal-details {
  margin-bottom: 1rem;
}

.goal-target,
.goal-saved,
.goal-start-date,
.goal-date,
.goal-remaining {
  display: block;
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.goal-target,
.goal-saved {
  color: #374151;
}

/* Thanh tiến độ */
.progress-bar {
  background-color: #e5e7eb;
  border-radius: 0.5rem;
  height: 1.5rem;
  overflow: hidden;
  position: relative;
  margin-bottom: 1rem;
}

.progress-fill {
  background: linear-gradient(90deg, #0ea5e9, #2b6cb0);
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.9rem;
  font-weight: 500;
  transition: width 1s ease;
}

.progress-text {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Thêm số tiền hôm nay tiết kiệm */
.add-savings-today {
  margin-top: 1rem;
}

.add-savings-today label {
  font-size: 1rem;
  color: #374151;
  font-weight: 500;
  display: block;
  margin-bottom: 0.5rem;
}

.savings-input-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.savings-input {
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 1rem;
  width: 100%;
  flex: 1;
}

.add-savings-button {
  padding: 0.5rem 1rem;
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.add-savings-button:hover {
  background-color: #059669;
}

/* Thông báo không có dữ liệu */
.no-data {
  text-align: center;
  color: #6b7280;
  font-size: 1rem;
  margin-top: 2rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 80%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

.modal {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 10001;
  border: 2px solid #0ea5e9;
  position: relative;
  display: block;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #0ea5e9;
  margin-bottom: 1rem;
  text-align: center;
}

.goal-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 1rem;
  color: #374151;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 1rem;
  width: 100%;
  box-sizing: border-box;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
}

.save-button {
  padding: 0.6rem 1.2rem;
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.save-button:hover {
  background-color: #059669;
}

.cancel-button {
  padding: 0.6rem 1.2rem;
  background-color: #e5e7eb;
  color: #374151;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cancel-button:hover {
  background-color: #d1d5db;
}

/* Nút thêm mục tiêu */
.add-goal-button {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 50px;
  height: 50px;
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: background-color 0.3s ease, transform 0.3s ease;
  z-index: 1000;
}

.add-goal-button:hover {
  background-color: #059669;
  transform: scale(1.1);
}

.add-goal-button i {
  transition: transform 0.3s ease;
}

.add-goal-button:hover i {
  transform: rotate(90deg);
}

/* Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .goals-page {
    padding: 1rem;
  }

  .page-title {
    font-size: 1.2rem;
  }

  .goals-image-container {
    max-width: 100%;
  }

  .goals-image {
    height: 200px;
  }

  .goals-list {
    grid-template-columns: 1fr;
  }

  .goal-card {
    padding: 1rem;
  }

  .goal-name {
    font-size: 1.1rem;
  }

  .goal-target,
  .goal-saved,
  .goal-start-date,
  .goal-date,
  .goal-remaining {
    font-size: 0.9rem;
  }

  .progress-bar {
    height: 1.2rem;
  }

  .progress-text {
    font-size: 0.8rem;
  }

  .add-savings-today label {
    font-size: 0.9rem;
  }

  .savings-input {
    font-size: 0.9rem;
  }

  .add-savings-button {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
  }

  .add-goal-button {
    bottom: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }

  .modal {
    padding: 1rem;
    max-width: 90%;
  }

  .goal-status-completed {
    color: #10b981;
    font-weight: bold;
  }

  .goal-status-failed {
    color: #ef4444;
    font-weight: bold;
  }

  .goal-status-active {
    color: #f59e0b;
    font-weight: bold;
  }
}
</style>
