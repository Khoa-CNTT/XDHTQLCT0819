<template>
  <div class="expense-management">
    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-content">
        <h1>XIN CHÀO</h1>
        <p>Hôm nay bạn đã chi tiêu những gì?</p>
        <form class="search-form" @submit.prevent>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Tìm kiếm danh mục chi tiêu..."
          />
          <button @click="searchCategory">Search</button>
        </form>
      </div>
    </div>

    <!-- Tông quan tài chính -->
    <div class="finance-info">
      <div class="info-card income-card">
        <span class="label">Thu Nhập:</span>
        <span class="value income">{{ formatCurrency(totalIncome) }}</span>
      </div>
      <div class="info-card balance-card">
        <span class="label">Số dư hiện tại:</span>
        <span class="value balance">{{ formatCurrency(balance) }}</span>
      </div>
    </div>
    <!-- Danh sách danh mục chi tiêu -->
    <div class="expense-list">
      <h3 class="list-title">Danh sách danh mục chi tiêu</h3>
      <div
        class="expense-item"
        v-for="(group, index) in filteredExpenses"
        :key="index"
        @click="openCategoryDetail(group.id)"
      >
        <div class="item-icon">
          <i v-if="group.category && group.icon" :class="group.icon"></i>
        </div>
        <div class="item-details ms-3">
          <span class="category">{{
            group.name ? group.name : "Không có tên"
          }}</span>
          <span class="amount negative"
            >-{{ formatCurrency(group.total_amount) }}</span
          >
        </div>
        <div class="item-action ms-3"><i class="fas fa-chevron-right"></i></div>
      </div>
    </div>

    <!-- Modal thêm chi tiêu -->
    <div v-if="showAddTransactionModal" class="modal-overlay">
      <div class="modal">
        <h2 class="modal-title">Thêm Giao Dịch</h2>
        <form @submit.prevent="addTransaction" class="modal-form">
          <div class="form-group">
            <label for="description" class="form-label">Nội dung</label>
            <input
              type="text"
              id="description"
              v-model="newTransaction.description"
              class="form-control"
              required
            />
          </div>
          <div class="form-group">
            <label for="amount" class="form-label">Số tiền</label>
            <input
              type="number"
              id="amount"
              v-model="newTransaction.amount"
              class="form-control"
              required
              min="0"
            />
          </div>
          <div class="form-group">
            <label>Danh mục</label>
            <select
              v-model="newTransaction.category_id"
              required
              :class="{ 'text-muted': !newTransaction.category_id }"
            >
              <option value="" disabled hidden>Chọn danh mục</option>
              <option
                v-for="cat in categoryList2"
                :key="cat.id"
                :value="cat.id"
              >
                {{ cat.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <div class="mb-3">
              <label for="transaction_date" class="form-label">Ngày</label>
              <input
                type="date"
                id="transaction_date"
                v-model="newTransaction.transaction_date"
                class="form-control"
                required
              />
            </div>
          </div>

          <div class="modal-actions">
            <button
              type="button"
              class="cancel-button"
              @click="closeExpenseModal"
            >
              Đóng
            </button>
            <button type="submit" class="add-button">Thêm Giao Dịch</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal thêm thu nhập -->
    <div v-if="showIncomeModal" class="modal-overlay">
      <div class="modal">
        <h2 class="modal-title">Thêm thu nhập</h2>
        <form @submit.prevent="addIncome" class="modal-form">
          <div class="form-group">
            <label>Nhập số tiền</label>
            <input
              type="text"
              v-model="income.amount"
              @input="formatIncomeAmount"
              required
            />
          </div>
          <div class="modal-actions">
            <button
              type="button"
              class="cancel-button"
              @click="closeIncomeModal"
            >
              Huỷ
            </button>
            <button type="submit" class="add-button">Thêm</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Nút mở modal -->
    <div class="action-buttons">
      <button class="action-btn" @click="openIncomeModal">
        <i class="fas fa-coins"></i>
        <span class="tooltip-text">Thêm thu nhập</span>
      </button>
      <button class="action-btn" @click="openExpenseModal">
        <i class="fas fa-plus"></i>
        <span class="tooltip-text">Thêm chi tiêu</span>
      </button>
    </div>

    <div v-if="showDetailModal" class="modal-backdrop">
      <div class="modal-content shadow p-4">
        <h5 class="mb-3">Chi tiết danh mục</h5>
        <div>
          <p><strong>Tên danh mục:</strong> {{ categoryDetail.name }}</p>
          <p>
            <strong>Loại:</strong>
            {{ categoryDetail.type === "income" ? "Thu nhập" : "Chi tiêu" }}
          </p>
          <p>
            <strong>Biểu tượng:</strong> <i :class="categoryDetail.icon"></i>
          </p>
          <p>
            <strong>Tổng số tiền:</strong> {{ formatCurrency(totalAmount) }}
          </p>

          <h6>Giao dịch</h6>
          <ul>
            <li
              v-for="transaction in transactions"
              :key="transaction.id"
              class="d-flex justify-content-between align-items-start mb-3 p-3 bg-light rounded shadow-sm"
            >
              <div>
                <strong>Nội Dung: {{ transaction.description }}</strong
                ><br />
                <small>Ngày: {{ transaction.transaction_date }}</small
                ><br />
                <strong>Số tiền:</strong>
                <span
                  :style="{
                    color: categoryDetail.type === 'income' ? 'green' : 'red',
                  }"
                >
                  {{ formatCurrency(transaction.amount) }}
                </span>
              </div>
            </li>
          </ul>
          <!-- Close modal button -->
          <button class="btn btn-secondary" @click="closeDetailModal">
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

export default {
  data() {
    return {
      selectedCategory: null,
      searchQuery: "",
      categoryList: [],
      categoryList2: [],
      totalIncome: 0,
      balance: 0,
      showDetailModal: false,

      categoryDetail: null,

      showAddTransactionModal: false,
      showIncomeModal: false,
      newTransaction: {
        transaction_date: "",
        type: "cash",
        amount: 0,
        description: "",
        address: "",
        category_id: "",
      },
      income: {
        amount: "",
      },
    };
  },
  computed: {
    filteredExpenses() {
      if (!this.searchQuery) return this.categoryList;
      return this.categoryList.filter(
        (group) =>
          group &&
          group.name &&
          group.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    mounted() {
      const user = JSON.parse(localStorage.getItem("user"));
      this.totalIncome = user.monthly_income;
      this.balance = user.monthly_customer_spending;
    },
  },
  methods: {
    formatCurrency(amount) {
      return new Intl.NumberFormat("vi-VN").format(amount) + " VND";
    },
    unformatCurrency(formattedAmount) {
      return formattedAmount.toString().replace(/\./g, "");
    },

    showSuccessNotification(message) {
      const toast = useToast();
      toast.success(message);
    },
    showErrorNotification(message) {
      const toast = useToast();
      toast.error(message);
    },

    formatDay(dateStr) {
      return new Date(dateStr).getDate();
    },
    formatMonthYear(dateStr) {
      const d = new Date(dateStr);
      return `${d.toLocaleDateString("vi-VN", {
        weekday: "long",
      })}, ${d.toLocaleDateString("vi-VN", {
        month: "long",
        year: "numeric",
      })}`;
    },

    searchCategory() {
      this.showSuccessNotification("Đã lọc danh mục thành công");
    },

    async fetchCategoriesHome() {
      try {
        const res = await axios.get("/api/categories/home", {
          params: {
            type: "expense",
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });

        this.categoryList = res.data;
      } catch (error) {
        console.error("Error fetching categories:", error);
        this.showErrorNotification("Lỗi khi tải danh mục chi tiêu!");
      }
    },

    async fetchCategories() {
      try {
        const res = await axios.get("/api/categories", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
          params: {
            type: "expense",
          },
        });

        this.categoryList2 = res.data;
      } catch (error) {
        console.error("Error fetching categories:", error);
        this.showErrorNotification("Lỗi khi tải danh mục chi tiêu!");
      }
    },

    openExpenseModal() {
      this.showAddTransactionModal = true;
    },
    closeExpenseModal() {
      this.showAddTransactionModal = false;
    },
    openIncomeModal() {
      this.showIncomeModal = true;
    },
    closeIncomeModal() {
      this.showIncomeModal = false;
    },
    closeDetailModal() {
      this.showDetailModal = false;
    },
    beforeUnmount() {
      window.removeEventListener("keydown", this.handleKeydown);
    },
    handleKeydown(event) {
      if (event.key === "Escape") {
        this.showAddTransactionModal = false;
        this.showIncomeModal = false;
        this.showDetailModal = false;
      }
    },

    isValidDate(date) {
      const regex = /^\d{4}-\d{2}-\d{2}$/;
      return regex.test(date);
    },

    // Thêm Chi tiêu
    async addTransaction() {
      const toast = useToast();
      const isValidDate = this.isValidDate(
        this.newTransaction.transaction_date
      );
      if (!isValidDate) {
        toast.error("Ngày phải đúng định dạng (YYYY-MM-DD)");
        return;
      }
      try {
        const res = await axios.post("/api/transaction", this.newTransaction, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        this.balance = res.data.monthly_customer_spending;
        const user = JSON.parse(localStorage.getItem("user"));
        if (user) {
          user.monthly_customer_spending = res.data.monthly_customer_spending;
          localStorage.setItem("user", JSON.stringify(user));
        }
        toast.success("Giao dịch đã được thêm thành công!");
        this.closeExpenseModal();
        this.newTransaction = {
          transaction_date: "",
          type: "cash",
          amount: 0,
          description: "",
          address: "",
          category_id: "",
        };
        await this.fetchCategoriesHome();
      } catch (error) {
        toast.error("Error adding transaction:", error);
        toast.error("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    },

    async addIncome() {
      const toast = useToast();
      try {
        const res = await axios.put("/api/user/income", this.income, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        this.balance = res.data.monthly_customer_spending;
        this.totalIncome = res.data.monthly_income;
        const user = JSON.parse(localStorage.getItem("user"));
        if (user) {
          user.monthly_income = res.data.monthly_income;
          user.monthly_customer_spending = res.data.monthly_customer_spending;
          localStorage.setItem("user", JSON.stringify(user));
        }
        toast.success("Thêm thu nhập thành công");
      } catch (error) {
        toast.error("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    },

    async openCategoryDetail(id) {
      try {
        const res = await axios.get(`/api/categories/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        this.categoryDetail = res.data.category;
        this.transactions = res.data.transactions;
        this.totalAmount = res.data.total_amount;
        this.showDetailModal = true;
      } catch (err) {
        useToast().error("Không thể lấy chi tiết danh mục!");
      }
    },
  },
  mounted() {
    this.fetchCategoriesHome();
    this.fetchCategories();
    const user = JSON.parse(localStorage.getItem("user"));
    if (user) {
      this.totalIncome = user.monthly_income;
      this.balance = user.monthly_customer_spending;
    }
    window.addEventListener("keydown", this.handleKeydown);
  },
};
</script>
<style scoped>
/* Thông báo alert */
.alert-box {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #28a745; /* Màu xanh lá */
  color: white;
  padding: 15px;
  border-radius: 8px;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  z-index: 2000; /* Đảm bảo thông báo hiển thị trên cùng */
  opacity: 1;
  transition: opacity 0.5s ease-in-out;
  animation: fadeOut 3s forwards;
}

/* Hiệu ứng fade-out */
@keyframes fadeOut {
  0% {
    opacity: 1;
  }
  80% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

/* Các thông báo lỗi hay cảnh báo khác (nếu cần) */
.alert-box.success {
  background-color: #28a745; /* Màu xanh lá */
}

.alert-box.error {
  background-color: #dc3545; /* Màu đỏ */
}
/* Reset cơ bản */
html,
body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background-color: #f0f2f5;
}

/* Container chính */
.expense-management {
  display: flex;
  flex-direction: column;
  height: calc(
    100vh - 60px
  ); /* Trừ chiều cao của header (giả sử header cao 60px) */
}

/* Thanh tìm kiếm */
.hero-section {
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
    url("/src/assets/ba.webp");
  background-size: cover;
  background-position: center;
  color: white;
  text-align: center;
  padding: 100px 20px;
  position: relative;
  z-index: 1;
}

/* Nội dung trong vùng hero */
.hero-content h1 {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 10px;
}

.hero-content p {
  font-size: 1.2rem;
  margin-bottom: 20px;
}

/* Form tìm kiếm */
.search-form {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.search-form input {
  width: 50%;
  padding: 10px 15px;
  border: 1px solid #ccc;
  border-radius: 30px;
  font-size: 1rem;
}

.search-form button {
  padding: 10px 20px;
  background-color: #1a73e8;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.search-form button:hover {
  background-color: #0056b3;
}

/* Thông tin tài chính */
.finance-info {
  display: flex;
  justify-content: space-between;
  padding: 1rem 2rem;
  margin: 1rem 2rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 1;
}

.info-card {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 1rem;
  border-radius: 0.5rem;
  transition: transform 0.3s ease;
}

.info-card:hover {
  transform: translateY(-3px);
}

.income-card {
  background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
}

.balance-card {
  background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
}

.label {
  font-size: 0.9rem;
  color: #374151;
  font-weight: 500;
}

.value {
  font-size: 1.25rem;
  font-weight: 600;
}

.income {
  color: #2f855a;
}

.balance {
  color: #2b6cb0;
}

/* Danh sách danh mục chi tiêu */
.expense-list {
  flex: 1;
  padding: 1.5rem 2rem;
  overflow-y: auto;
  background: linear-gradient(135deg, #f0f2f5 0%, #e6f0fa 100%);
  position: relative;
  z-index: 1;
}

.list-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
}

.expense-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background-color: white;
  border-radius: 0.5rem;
  margin-bottom: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.expense-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.item-icon i {
  font-size: 24px;
  margin-right: 1rem;
  color: #374151;
}

.item-details {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category {
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
}

.amount {
  font-size: 1rem;
  font-weight: 500;
}

.negative {
  color: #ef4444;
}

.item-action {
  display: flex;
  align-items: center;
  color: #6b7280;
}

.item-action:hover {
  color: #0ea5e9;
}

/* Nút mở modal thêm chi tiêu */
.open-modal-button {
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

.open-modal-button:hover {
  background-color: #059669;
  transform: scale(1.1);
}

/* Modal thêm danh mục */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal {
  background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%);
  padding: 1.5rem;
  border-radius: 1rem;
  width: 100%;
  height: 600px;
  max-width: 500px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 2100;
  position: relative;
  border: 2px solid #10b981;
  display: block; /* chống đè modal */
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2b6cb0;
  margin-bottom: 1rem;
  text-align: center;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.form-group label {
  font-size: 0.95rem;
  font-weight: 500;
  color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  background-color: #f9fafb;
  outline: none;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.form-group textarea {
  resize: none;
  height: 50px;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
  gap: 0.5rem;
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
  flex: 1;
}

.cancel-button:hover {
  background-color: #d1d5db;
}

.modal-actions .add-button {
  padding: 0.6rem 1.2rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: transform 0.3s ease, background 0.3s ease;
  flex: 1;
}

.modal-actions .add-button:hover {
  transform: scale(1.05);
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
}

/* Modal chi tiết danh mục */
.detail-modal {
  max-width: 400px;
  background: white;
  border: 2px solid #a1c4fd;
}

.detail-list {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.detail-date {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-right: 1rem;
}

.detail-date .day {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
}

.detail-date .month-year {
  font-size: 0.85rem;
  color: #6b7280;
  text-align: center;
}

.detail-info {
  flex: 1;
}

.detail-purpose {
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
  display: block;
}

.detail-amount {
  font-size: 0.95rem;
  font-weight: 500;
  color: #ef4444;
  display: block;
  margin-top: 0.25rem;
}

.detail-note {
  font-size: 0.85rem;
  color: #6b7280;
  display: block;
  margin-top: 0.25rem;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-section {
    padding: 50px 10px;
  }

  .hero-content h1 {
    font-size: 2rem;
  }

  .hero-content p {
    font-size: 1rem;
  }

  .search-form input {
    width: 70%;
  }

  .finance-info {
    flex-direction: column;
    gap: 1rem;
    margin: 1rem;
    padding: 1rem;
  }

  .expense-list {
    padding: 1rem;
  }

  .open-modal-button {
    bottom: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }

  .modal,
  .detail-modal {
    padding: 1rem;
    max-width: 90%;
  }

  .modal-form {
    gap: 0.5rem;
  }

  .modal-actions {
    flex-direction: column;
    gap: 0.5rem;
  }

  .cancel-button,
  .modal-actions .add-button {
    padding: 0.5rem;
    font-size: 0.9rem;
  }
}
.action-buttons {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  display: flex;
  flex-direction: column;
  gap: 15px;
  z-index: 1000;
}

.action-btn {
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  position: relative;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.action-btn:hover {
  background-color: #059669;
  transform: scale(1.1);
}

.tooltip-text {
  visibility: hidden;
  opacity: 0;
  width: max-content;
  background-color: #374151;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 10px;
  position: absolute;
  right: 60px;
  top: 50%;
  transform: translateY(-50%);
  transition: opacity 0.3s;
  white-space: nowrap;
  font-size: 0.85rem;
}

.action-btn:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 950px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modal-in 0.3s ease-out;
}

.modal-content h5 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  padding: 1.25rem 1.5rem;
  margin: -1rem -1rem 1rem -1rem;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-content p {
  margin-bottom: 1rem;
  padding: 0.5rem 0;
  font-size: 1rem;
  display: flex;
  align-items: center;
}

.modal-content p strong {
  min-width: 140px;
  display: inline-block;
  font-weight: 600;
  color: #4b5563;
}

.modal-content p i {
  font-size: 1.25rem;
  margin-left: 0.5rem;
}

.modal-content p:nth-child(4) strong + span,
.modal-content p:nth-child(4) {
  color: #e72121;
  font-weight: 700;
}

.modal-content h6 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 1.5rem 0 1rem 0;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  color: #1f2937;
}

.modal-content ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.modal-content li {
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: #f9fafb;
  transition: transform 0.2s, box-shadow 0.2s;
}

.modal-content li:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.modal-content li strong {
  display: block;
  font-size: 1.1rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.modal-content li small {
  display: block;
  color: #6b7280;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.modal-content li strong:last-of-type {
  display: inline-block;
  margin-right: 0.5rem;
  color: #4b5563;
}

/* Close button */
.modal-content .btn-secondary {
  margin-top: 1.5rem;
  padding: 0.6rem 1.5rem;
  background-color: #6b7280;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  float: right;
}

.modal-content .btn-secondary:hover {
  background-color: #4b5563;
}

/* Animation for modal entrance */
@keyframes modal-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Media queries for responsiveness */
@media (max-width: 640px) {
  .modal-content {
    max-width: 100%;
    border-radius: 8px;
  }

  .modal-content p strong {
    min-width: 120px;
  }
}
</style>
