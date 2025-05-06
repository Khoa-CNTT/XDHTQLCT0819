<template>
    <div class="transaction-page">
      <!-- Bộ lọc thời gian -->
      <div class="filter-section">
        <div class="calendar-wrapper">
          <button @click="prevMonth" class="nav-arrow"><</button>
          <span class="month-year">Tháng {{ selectedMonth }} năm {{ selectedYear }}</span>
          <button @click="nextMonth" class="nav-arrow">></button>
        </div>
        <div class="calendar">
          <div class="calendar-header">
            <span v-for="day in daysOfWeek" :key="day" class="day-header">{{ day }}</span>
          </div>
          <div class="calendar-body">
            <span
              v-for="day in calendarDays"
              :key="day.date"
              class="calendar-day"
              :class="{ 'selected': isSelected(day.date), 'today': isToday(day.date) }"
              @click="selectDay(day.date)"
            >
              {{ day.day }}
            </span>
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
        </div>
      </div>
  
      <!-- Thông tin tài chính -->
      <div class="finance-info">
        <div class="info-card income-card">
          <span class="label">Thu Nhập:</span>
          <span class="value income">{{ formatNumber(totalIncome) }}</span>
        </div>
        <div class="info-card expense-card">
          <span class="label">Chi tiêu:</span>
          <span class="value expense">{{ formatNumber(totalExpense) }}</span>
        </div>
      </div>
  
      <!-- Danh sách giao dịch -->
      <div class="transaction-list">
        <h3 class="list-title">Theo dõi chi tiêu</h3>
        <p class="subtitle">Xem chi tiêu của bạn theo biểu đồ</p>
        <div
          class="transaction-item"
          v-for="(category, index) in filteredCategories"
          :key="index"
          @click="showDetailModal(category)"
        >
          <div class="item-icon">
            <i :class="category.icon"></i>
          </div>
          <div class="item-details">
            <span class="category">{{ category.name }}</span>
            <span class="amount negative">{{ category.total }}</span>
          </div>
          <div class="item-action">
            <i class="fas fa-chevron-right"></i>
          </div>
        </div>
        <p v-if="filteredCategories.length === 0" class="no-data">
          Không có giao dịch nào trong khoảng thời gian này.
        </p>
      </div>
  
      <!-- Modal chi tiết danh mục -->
      <div v-if="showDetail" class="modal-overlay">
        <div class="modal detail-modal">
          <h2 class="modal-title">{{ selectedCategory.name }}</h2>
          <div class="detail-list">
            <div
              class="detail-item"
              v-for="(expense, index) in selectedCategory.expenses"
              :key="index"
            >
              <div class="detail-date">
                <span class="day">{{ expense.day }}</span>
                <span class="month-year">{{ expense.monthYear }}</span>
              </div>
              <div class="detail-info">
                <span class="detail-purpose">{{ expense.purpose }}</span>
                <span class="detail-amount negative">{{ expense.amount }}</span>
                <span class="detail-note">{{ expense.note }}</span>
              </div>
            </div>
          </div>
          <div class="modal-actions">
            <button class="cancel-button" @click="showDetail = false">Đóng</button>
          </div>
        </div>
      </div>
  
      <!-- Nút mở modal thêm chi tiêu -->
      <button class="open-modal-button" @click="$router.push('/quan-li-chi-tieu')">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Transaction',
    data() {
      return {
        showDetail: false,
        selectedCategory: null,
        selectedDate: new Date(2025, 2, 19), // Đặt mặc định là 19/3/2025 để kiểm tra
        selectedMonth: 3, // Tháng 3
        selectedYear: 2025,
        filterType: 'day', // Mặc định là 'day' để kiểm tra ngày 19/3/2025
        daysOfWeek: ['THỨ 2', 'THỨ 3', 'THỨ 4', 'THỨ 5', 'THỨ 6', 'THỨ 7', 'CHỦ NHẬT'],
        categories: [
          {
            name: 'Ăn uống',
            icon: 'fa-solid fa-utensils',
            total: '-200,000',
            expenses: [
              {
                day: '2',
                monthYear: 'Thứ Sáu tháng 3 năm 2025',
                purpose: 'Ăn uống',
                amount: '-50,000 VND',
                note: 'Mua đồ ăn sáng',
                date: new Date(2025, 2, 2), // Tháng 3 là 2
              },
              {
                day: '19',
                monthYear: 'Thứ Bảy tháng 3 năm 2025',
                purpose: 'Ăn uống',
                amount: '-65,000 VND',
                note: 'Ăn trưa với bạn',
                date: new Date(2025, 2, 19), // Tháng 3 là 2
              },
              {
                day: '11',
                monthYear: 'Thứ Sáu tháng 3 năm 2025',
                purpose: 'Ăn uống',
                amount: '-10,000 VND',
                note: 'Mua nước uống',
                date: new Date(2025, 2, 11), // Tháng 3 là 2
              },
            ],
          },
          {
            name: 'Di chuyển',
            icon: 'fa-solid fa-car-side',
            total: '-50,000',
            expenses: [
              {
                day: '19',
                monthYear: 'Thứ Bảy tháng 3 năm 2025',
                purpose: 'Di chuyển',
                amount: '-15,000 VND',
                note: 'Đi xe buýt',
                date: new Date(2025, 2, 19), // Tháng 3 là 2
              },
              {
                day: '11',
                monthYear: 'Thứ Sáu tháng 3 năm 2025',
                purpose: 'Di chuyển',
                amount: '-10,000 VND',
                note: 'Đi xe ôm',
                date: new Date(2025, 2, 11), // Tháng 3 là 2
              },
              {
                day: '12',
                monthYear: 'Thứ Tư tháng 3 năm 2025',
                purpose: 'Di chuyển',
                amount: '-100,000 VND',
                note: 'Đổ xăng xe máy',
                date: new Date(2025, 2, 12), // Tháng 3 là 2
              },
            ],
          },
          {
            name: 'Mua sắm',
            icon: 'fa-solid fa-cart-shopping',
            total: '-540,000',
            expenses: [
              {
                day: '12',
                monthYear: 'Thứ Tư tháng 3 năm 2025',
                purpose: 'Mua sắm',
                amount: '-100,000 VND',
                note: 'Mua quần áo',
                date: new Date(2025, 2, 12), // Tháng 3 là 2
              },
            ],
          },
        ],
      };
    },
    computed: {
      calendarDays() {
        const firstDayOfMonth = new Date(this.selectedYear, this.selectedMonth - 1, 1);
        const lastDayOfMonth = new Date(this.selectedYear, this.selectedMonth, 0);
        const daysInMonth = lastDayOfMonth.getDate();
        const firstDayIndex = (firstDayOfMonth.getDay() + 6) % 7; // Điều chỉnh để thứ 2 là ngày đầu tuần
        const days = [];
  
        // Thêm các ngày trống trước ngày đầu tiên của tháng
        for (let i = 0; i < firstDayIndex; i++) {
          days.push({ day: '', date: null });
        }
  
        // Thêm các ngày trong tháng
        for (let day = 1; day <= daysInMonth; day++) {
          days.push({
            day,
            date: new Date(this.selectedYear, this.selectedMonth - 1, day),
          });
        }
  
        return days;
      },
      filteredCategories() {
        return this.categories.map(category => {
          const filteredExpenses = category.expenses.filter(expense => {
            const expenseDate = new Date(expense.date);
            console.log(`Filter Type: ${this.filterType}`); // Debug filterType
            console.log(`Expense Date: ${expenseDate.toISOString()}, Selected Date: ${this.selectedDate.toISOString()}`); // Debug
  
            if (this.filterType === 'day') {
              const isSameDay =
                expenseDate.getDate() === this.selectedDate.getDate() &&
                expenseDate.getMonth() === this.selectedDate.getMonth() &&
                expenseDate.getFullYear() === this.selectedDate.getFullYear();
              console.log(`Filter Day - Is Same Day: ${isSameDay}`); // Debug
              return isSameDay;
            } else if (this.filterType === 'week') {
              const startOfWeek = new Date(this.selectedDate);
              startOfWeek.setDate(this.selectedDate.getDate() - ((this.selectedDate.getDay() + 6) % 7)); // Thứ 2 là ngày đầu tuần
              const endOfWeek = new Date(startOfWeek);
              endOfWeek.setDate(startOfWeek.getDate() + 6); // Chủ nhật là ngày cuối tuần
              console.log(`Filter Week - Start: ${startOfWeek.toISOString()}, End: ${endOfWeek.toISOString()}`); // Debug
              return expenseDate >= startOfWeek && expenseDate <= endOfWeek;
            } else if (this.filterType === 'month') {
              return (
                expenseDate.getMonth() === this.selectedDate.getMonth() &&
                expenseDate.getFullYear() === this.selectedDate.getFullYear()
              );
            }
            return true;
          });
  
          const total = filteredExpenses.reduce((sum, expense) => {
            const amount = parseInt(expense.amount.replace(/[^0-9-]/g, ''), 10);
            return sum + amount;
          }, 0);
  
          return {
            ...category,
            expenses: filteredExpenses,
            total: this.formatNumber(total),
          };
        }).filter(category => category.expenses.length > 0);
      },
      totalIncome() {
        return 0; // Giả lập thu nhập, bạn có thể thay đổi logic nếu có dữ liệu thu nhập
      },
      totalExpense() {
        return this.filteredCategories.reduce((sum, category) => {
          const amount = parseInt(category.total.replace(/[^0-9-]/g, ''), 10);
          return sum + amount;
        }, 0);
      },
    },
    methods: {
      prevMonth() {
        if (this.selectedMonth === 1) {
          this.selectedMonth = 12;
          this.selectedYear--;
        } else {
          this.selectedMonth--;
        }
        this.selectedDate = new Date(this.selectedYear, this.selectedMonth - 1, 1);
      },
      nextMonth() {
        if (this.selectedMonth === 12) {
          this.selectedMonth = 1;
          this.selectedYear++;
        } else {
          this.selectedMonth++;
        }
        this.selectedDate = new Date(this.selectedYear, this.selectedMonth - 1, 1);
      },
      selectDay(date) {
        if (date) {
          this.selectedDate = date;
          this.filterType = 'day';
          console.log(`Selected Date: ${this.selectedDate.toISOString()}`); // Debug
        }
      },
      isSelected(date) {
        if (!date) return false;
        return (
          date.getDate() === this.selectedDate.getDate() &&
          date.getMonth() === this.selectedDate.getMonth() &&
          date.getFullYear() === this.selectedDate.getFullYear()
        );
      },
      isToday(date) {
        if (!date) return false;
        const today = new Date();
        return (
          date.getDate() === today.getDate() &&
          date.getMonth() === today.getMonth() &&
          date.getFullYear() === today.getFullYear()
        );
      },
      setFilter(type) {
        this.filterType = type;
        const today = new Date(2025, 2, 23); // Giả lập ngày hiện tại là 23/3/2025 để kiểm tra
        if (type === 'day') {
          this.selectedDate = today;
        } else if (type === 'week') {
          this.selectedDate = today;
          // Đảm bảo tuần bắt đầu từ thứ 2 và kết thúc vào chủ nhật
          const startOfWeek = new Date(today);
          startOfWeek.setDate(today.getDate() - ((today.getDay() + 6) % 7)); // Thứ 2
          this.selectedDate = startOfWeek;
        } else if (type === 'month') {
          this.selectedDate = today;
          this.selectedMonth = today.getMonth() + 1;
          this.selectedYear = today.getFullYear();
        }
        console.log(`Set Filter: ${type}, Selected Date: ${this.selectedDate.toISOString()}`); // Debug
      },
      formatNumber(number) {
        return number.toLocaleString('vi-VN');
      },
      showDetailModal(category) {
        this.selectedCategory = category;
        this.showDetail = true;
      },
    },
  };
  </script>
  
  <style scoped>
  /* Reset cơ bản */
  html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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