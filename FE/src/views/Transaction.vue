<template>
  <div class="transaction-page">
    <!-- Bộ lọc thời gian -->
    <div class="filter-section">
      <div class="calendar-wrapper">
        <button @click="prevMonth" class="nav-arrow"><</button>
        <span class="month-year"
          >Tháng {{ selectedMonth }} năm {{ selectedYear }}</span
        >
        <button @click="nextMonth" class="nav-arrow">></button>
      </div>
      <div class="calendar">
        <div class="calendar-header">
          <div class="day-header" v-for="day in weekDays" :key="day">
            {{ day }}
          </div>
        </div>
        <div class="calendar-body">
          <div
            v-for="(day, index) in calendarDays"
            :key="index"
            class="calendar-day"
            :class="{
              today: isToday(day.date),
              selected: isSelectedDay(day.date),
              'no-date': !day.date,
            }"
            @click="day.date && selectDay(day.date)"
          >
            {{ day.label }}
          </div>
        </div>
      </div>
      <div class="filter-buttons">
        <button
          :class="{ active: filterType === 'day' }"
          @click="setFilter('day')"
        >
          Hôm nay
        </button>
        <button
          :class="{ active: filterType === 'week' }"
          @click="setFilter('week')"
        >
          Tuần này
        </button>
        <button
          :class="{ active: filterType === 'month' }"
          @click="setFilter('month')"
        >
          Tháng này
        </button>
        <button class="btn-fetch" @click="fetchMBTransactions">
          Tải giao dịch MB Bank hôm nay
        </button>
      </div>
    </div>

    <!-- Thông tin tài chính -->
    <div class="finance-info">
      <div class="info-card income-card">
        <span class="label">Tổng thu nhập:</span>
        <span class="value income">{{ formatNumber(totalIncome) }} VND</span>
      </div>
      <div class="info-card expense-card">
        <span class="label">Tổng chi tiêu:</span>
        <span class="value expense">{{ formatNumber(totalExpense) }} VND</span>
      </div>
    </div>

    <!-- Debug info -->
    <div class="debug-info" v-if="debugMode">
      <p>FilterType: {{ filterType }}</p>
      <p>SelectedDate: {{ selectedDate.toLocaleDateString("vi-VN") }}</p>
      <p>TransactionsCount: {{ transactions.length }}</p>
    </div>

     <div class="row">
      <div class="col-6">
        <!-- Danh sách giao dịch chi tiêu -->
    <div class="transaction-list">
      <h3 class="list-title">Danh sách chi tiêu</h3>
      <div v-if="isLoading" class="loading">Đang tải...</div>
      <div v-else-if="filteredExpenses.length === 0" class="no-data">
        Không có dữ liệu chi tiêu trong thời gian đã chọn
      </div>
      <div
        v-else
        v-for="(txn, index) in filteredExpenses"
        :key="'expense-' + index"
        class="transaction-item"
      >
        <div class="item-details">
          <div>
            <span class="category">{{
              txn.description || "Không có mô tả"
            }}</span>
            <div class="note">{{ formatDate(txn.transaction_date) }}</div>
          </div>
          <div class="amount negative">-{{ formatNumber(txn.amount) }} VND</div>
        </div>
      </div>
    </div>
     </div>
      <div class="col-6">
        <!-- Danh sách giao dịch thu nhập -->
        <div class="transaction-list">
          <h3 class="list-title">Danh sách thu nhập</h3>
          <div v-if="isLoading" class="loading">Đang tải...</div>
          <div v-else-if="filteredIncomes.length === 0" class="no-data">
            Không có dữ liệu thu nhập trong thời gian đã chọn
          </div>
          <div
            v-else
            v-for="(txn, index) in filteredIncomes"
            :key="'income-' + index"
            class="transaction-item"
          >
            <div class="item-details">
              <div>
                <span class="category">{{
                  txn.description || "Không có mô tả"
                }}</span>
                <div class="note">{{ formatDate(txn.transaction_date) }}</div>
              </div>
              <div class="amount income">+{{ formatNumber(txn.amount) }} VND</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

export default {
  name: "TransactionPage",
  data() {
    return {
      transactions: [],
      selectedDate: new Date(),
      selectedMonth: new Date().getMonth() + 1,
      selectedYear: new Date().getFullYear(),
      filterType: "month",
      isLoading: false,
      debugMode: false, // Bật để debug nếu cần
    };
  },
  computed: {
    weekDays() {
      return ["THỨ 2", "THỨ 3", "THỨ 4", "THỨ 5", "THỨ 6", "THỨ 7", "CHỦ NHẬT"];
    },
    calendarDays() {
      const days = [];
      const year = this.selectedYear;
      const month = this.selectedMonth - 1;
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);

      const startDayOfWeek = (firstDay.getDay() + 6) % 7;
      for (let i = 0; i < startDayOfWeek; i++) {
        days.push({ label: "", date: null });
      }

      for (let d = 1; d <= lastDay.getDate(); d++) {
        const date = new Date(year, month, d);
        days.push({ label: d, date });
      }

      return days;
    },
    filteredIncomes() {
      return this.transactions.filter(
        (t) =>
          t.transaction_type === "income" &&
          this.matchFilter(new Date(t.transaction_date))
      );
    },
    filteredExpenses() {
      return this.transactions.filter(
        (t) =>
          t.transaction_type === "expense" &&
          this.matchFilter(new Date(t.transaction_date))
      );
    },
    totalIncome() {
      return this.filteredIncomes.reduce(
        (sum, t) => sum + parseFloat(t.amount),
        0
      );
    },
    totalExpense() {
      return this.filteredExpenses.reduce(
        (sum, t) => sum + parseFloat(t.amount),
        0
      );
    },
  },
  methods: {
    matchFilter(date) {
      if (!date || isNaN(date)) return false;

      const d = new Date(date);
      if (this.filterType === "day") {
        return this.isSameDay(d, this.selectedDate);
      } else if (this.filterType === "week") {
        const start = new Date(this.selectedDate);
        start.setDate(start.getDate() - ((start.getDay() + 6) % 7));
        const end = new Date(start);
        end.setDate(start.getDate() + 6);
        return d >= start && d <= end;
      } else if (this.filterType === "month") {
        return (
          d.getMonth() + 1 === this.selectedMonth &&
          d.getFullYear() === this.selectedYear
        );
      }
      return true;
    },

    isSameDay(date1, date2) {
      return (
        date1.getDate() === date2.getDate() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getFullYear() === date2.getFullYear()
      );
    },

    isToday(date) {
      if (!date) return false;
      const today = new Date();
      return this.isSameDay(new Date(date), today);
    },

    isSelectedDay(date) {
      if (!date) return false;
      return this.isSameDay(new Date(date), this.selectedDate);
    },

    selectDay(date) {
      if (!date) return;

      this.selectedDate = new Date(date);
      this.filterType = "day";
      this.fetchTransactions();
    },

    async fetchTransactions() {
      try {
        this.isLoading = true;
        const params = this.getApiParams();

        const response = await axios.get("/api/transaction", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
          params: params,
        });

        if (response.data.success) {
          let transactions = response.data.data;

          // Nếu là filter tuần, lọc thêm ở client
          if (this.filterType === "week") {
            transactions = transactions.filter((t) =>
              this.matchFilter(new Date(t.transaction_date))
            );
          }

          // Cập nhật transactions trong một lần để tránh nhiều lần re-render
          this.transactions = transactions;
        } else {
          console.error("Lỗi tải giao dịch:", response.data.error);
          this.transactions = [];
        }
      } catch (err) {
        console.error("❌ Lỗi tải giao dịch", err);
        this.transactions = [];
      } finally {
        this.isLoading = false;
      }
    },

    getApiParams() {
      const params = {};

      if (this.filterType === "day") {
        params.date = this.formatDateForAPI(this.selectedDate);
      } else if (this.filterType === "month") {
        params.month = this.selectedMonth.toString().padStart(2, "0");
        params.year = this.selectedYear;
      } else if (this.filterType === "week") {
        // Với filter tuần, lấy dữ liệu của cả tháng và lọc ở frontend
        params.month = this.selectedMonth.toString().padStart(2, "0");
        params.year = this.selectedYear;
      }

      return params;
    },

    async fetchMBTransactions() {
      const toast = useToast();
      try {
        this.isLoading = true;
        const res = await axios.get("/api/ai/get-mbank", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        toast.success(res.data.message);
        const user = JSON.parse(localStorage.getItem("user"));
        if (user) {
          user.monthly_income = res.data.monthly_income;
          user.monthly_customer_spending = res.data.monthly_customer_spending;
          localStorage.setItem("user", JSON.stringify(user));
        }
        this.fetchTransactions();
      } catch (error) {
        toast.error(error.response.data.error);
      } finally {
        this.isLoading = false;
      }
    },

    formatDateForAPI(date) {
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const day = String(d.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },

    prevMonth() {
      if (this.selectedMonth === 1) {
        this.selectedMonth = 12;
        this.selectedYear--;
      } else {
        this.selectedMonth--;
      }
      this.selectedDate = new Date(
        this.selectedYear,
        this.selectedMonth - 1,
        1
      );
      this.filterType = "month";
      this.fetchTransactions();
    },

    nextMonth() {
      if (this.selectedMonth === 12) {
        this.selectedMonth = 1;
        this.selectedYear++;
      } else {
        this.selectedMonth++;
      }
      this.selectedDate = new Date(
        this.selectedYear,
        this.selectedMonth - 1,
        1
      );
      this.filterType = "month";
      this.fetchTransactions();
    },

    setFilter(type) {
      this.filterType = type;
      const today = new Date();
      this.selectedDate = today;
      this.selectedMonth = today.getMonth() + 1;
      this.selectedYear = today.getFullYear();
      this.fetchTransactions();
    },

    formatNumber(n) {
      return parseFloat(n).toLocaleString("vi-VN");
    },

    formatDate(dateStr) {
      if (!dateStr) return "Không xác định";
      const d = new Date(dateStr);
      return isNaN(d) ? "Không hợp lệ" : d.toLocaleDateString("vi-VN");
    },
  },
  mounted() {
    this.fetchTransactions();
  },
};
</script>

<style scoped>
/* Keep styles unchanged from your existing FE */
</style>

<style scoped>
/* Reset cơ bản */
html,
body {
  margin: 0;
  padding: 0;
  width: 100%;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background-color: #f0f2f5;
}

/* Container chính */
.transaction-page {
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - 60px);
}

/* Bộ lọc thời gian */
.filter-section {
  padding: 1rem 2rem;
  background: white;
  border-radius: 0.5rem;
  margin: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.calendar-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.nav-arrow {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #374151;
}

.nav-arrow:hover {
  color: #2b6cb0;
}

.month-year {
  font-size: 1.2rem;
  font-weight: 600;
  color: #374151;
}

.calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  text-align: center;
}

.calendar-header {
  display: contents;
}

.day-header {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

.calendar-body {
  display: contents;
}

.calendar-day {
  padding: 0.5rem;
  font-size: 0.9rem;
  color: #374151;
  cursor: pointer;
  border-radius: 8px; /* Bo góc nhẹ cho tất cả các ngày */
  transition: background-color 0.3s ease;
}

.calendar-day:hover {
  background-color: #e5e7eb;
}

.calendar-day.selected {
  background-color: #facc15;
  color: white;
  border-radius: 8px; /* Bo góc nhẹ thay vì 50% (hình elip) */
}

.calendar-day.today {
  border: 2px solid #2b6cb0;
  border-radius: 8px; /* Đảm bảo ngày hôm nay cũng bo góc đồng bộ */
}

.filter-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
}

.filter-buttons button {
  padding: 0.5rem 1rem;
  background-color: #e5e7eb;
  color: #374151;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.filter-buttons button:hover {
  background-color: #d1d5db;
}

.filter-buttons button.active {
  background-color: #2b6cb0;
  color: white;
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

.expense-card {
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

.expense {
  color: #2b6cb0;
}

/* Danh sách giao dịch */
.transaction-list {
  flex: 1;
  padding: 1.5rem 2rem;
  overflow-y: auto;
  background: linear-gradient(135deg, #f0f2f5 0%, #e6f0fa 100%);
}

.list-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.subtitle {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.transaction-item {
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

.transaction-item:hover {
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

.no-data {
  text-align: center;
  color: #6b7280;
  font-size: 1rem;
}

/* Modal chi tiết danh mục */
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
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 2100;
  border: 2px solid #a1c4fd;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2b6cb0;
  margin-bottom: 1rem;
  text-align: center;
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

.modal-actions {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
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

/* Responsive */
@media (max-width: 768px) {
  .filter-section {
    margin: 1rem;
    padding: 1rem;
  }

  .calendar {
    gap: 0.25rem;
  }

  .calendar-day {
    padding: 0.25rem;
    font-size: 0.8rem;
  }

  .filter-buttons {
    flex-direction: column;
    gap: 0.5rem;
  }

  .finance-info {
    flex-direction: column;
    gap: 1rem;
    margin: 1rem;
    padding: 1rem;
  }

  .transaction-list {
    padding: 1rem;
  }

  .open-modal-button {
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
}
</style>
