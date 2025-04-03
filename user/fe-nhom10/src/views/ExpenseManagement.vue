<template>
    <div class="expense-management">
      <div class="row">
        <div class="hero-section">
          <div class="hero-content">
            <h1>XIN CHÀO</h1>
            <p>Hôm nay bạn đã chi tiêu những gì?</p>
            <form class="search-form" action="/search" method="get">
              <input type="text" name="query" placeholder="Tìm kiếm danh mục chi tiêu..." />
              <button type="submit">Search</button>
            </form>
          </div>
        </div>
      </div>
  
      <!-- Thông tin tài chính -->
      <div class="finance-info">
        <div class="info-card income-card">
          <span class="label">Thu Nhập:</span>
          <span class="value income">10,000,000</span>
        </div>
        <div class="info-card balance-card">
          <span class="label">Số dư hiện tại:</span>
          <span class="value balance">1,200,000</span>
        </div>
      </div>
  
      <!-- Danh sách danh mục chi tiêu -->
      <div class="expense-list">
        <h3 class="list-title">Danh sách danh mục chi tiêu</h3>
        <div
          class="expense-item"
          v-for="(category, index) in categories"
          :key="index"
          @click="showDetailModal(category)"
        >
          <div class="item-icon">
            <i :class="category.icon"></i>
          </div>
          <div class="item-details ms-3">
            <span class="category">{{ category.name }}</span>
            <span class="amount negative">{{ category.total }}</span>
          </div>
          <div class="item-action ms-3">
            <i class="fas fa-chevron-right"></i>
          </div>
        </div>
      </div>
  
      <!-- Modal thêm danh mục -->
      <div v-if="showModal" class="modal-overlay">
        <div class="modal">
          <h2 class="modal-title">Thêm chi tiêu</h2>
          <form @submit.prevent="addExpense" class="modal-form">
            <div class="form-group">
              <label for="amount">Nhập số tiền</label>
              <input
                type="number"
                id="amount"
                v-model="newExpense.amount"
                placeholder="Nhập số tiền..."
                required
              />
            </div>
            <div class="form-group">
              <label for="category">Danh mục</label>
              <select id="category" v-model="newExpense.category" required>
                <option value="" disabled>Chọn danh mục</option>
                <option value="Ăn uống">Ăn uống</option>
                <option value="Di chuyển">Di chuyển</option>
                <option value="Mua sắm">Mua sắm</option>
                <option value="Học tập">Học tập</option>
              </select>
            </div>
            <div class="form-group">
              <label for="date">Ngày</label>
              <input
                type="date"
                id="date"
                v-model="newExpense.date"
                required
              />
            </div>
            <div class="form-group">
              <label for="time">Giờ</label>
              <input
                type="time"
                id="time"
                v-model="newExpense.time"
                required
              />
            </div>
            <div class="form-group">
              <label for="note">Ghi chú</label>
              <textarea
                id="note"
                v-model="newExpense.note"
                placeholder="Ghi chú..."
              ></textarea>
            </div>
            <div class="modal-actions">
              <button type="button" class="cancel-button" @click="showModal = false">Hủy</button>
              <button type="submit" class="add-button">Thêm</button>
            </div>
          </form>
        </div>
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
      <button class="open-modal-button" @click="showModal = true">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </template>
  
  <script>
  export default {
    name: "ExpenseManagement",
    data() {
      return {
        searchQuery: "",
        showModal: false,
        showDetail: false,
        selectedCategory: null,
        newExpense: {
          amount: "",
          category: "",
          date: "",
          time: "",
          note: "",
        },
        categories: [
          {
            name: "Ăn uống",
            icon: "fa-solid fa-utensils",
            total: "-300,000",
            expenses: [
              {
                day: "2",
                monthYear: "Thứ Sáu tháng 12 năm 2025",
                purpose: "Ăn uống",
                amount: "-50,000 VND",
                note: "Mua đồ ăn sáng",
              },
              {
                day: "19",
                monthYear: "Thứ Bảy tháng 11 năm 2025",
                purpose: "Ăn uống",
                amount: "-65,000 VND",
                note: "Ăn trưa với bạn",
              },
              {
                day: "11",
                monthYear: "Thứ Sáu tháng 11 năm 2025",
                purpose: "Ăn uống",
                amount: "-10,000 VND",
                note: "Mua nước uống",
              },
            ],
          },
          {
            name: "Di chuyển",
            icon: "fa-solid fa-car-side",
            total: "-100,000",
            expenses: [
              {
                day: "19",
                monthYear: "Thứ Bảy tháng 11 năm 2025",
                purpose: "Di chuyển",
                amount: "-15,000 VND",
                note: "Đi xe buýt",
              },
              {
                day: "11",
                monthYear: "Thứ Sáu tháng 11 năm 2025",
                purpose: "Di chuyển",
                amount: "-10,000 VND",
                note: "Đi xe ôm",
              },
              {
                day: "12",
                monthYear: "Thứ Tư tháng 10 năm 2025",
                purpose: "Di chuyển",
                amount: "-100,000 VND",
                note: "Đổ xăng xe máy",
              },
            ],
          },
          {
            name: "Mua sắm",
            icon: "fa-solid fa-cart-shopping",
            total: "-300,000",
            expenses: [
              {
                day: "12",
                monthYear: "Thứ Tư tháng 10 năm 2025",
                purpose: "Mua sắm",
                amount: "-100,000 VND",
                note: "Mua quần áo",
              },
            ],
          },
        ],
      };
    },
    methods: {
      addExpense() {
        // Logic để thêm chi tiêu mới
        console.log("Thêm chi tiêu:", this.newExpense);
        // Sau khi thêm, đóng modal và reset form
        this.showModal = false;
        this.newExpense = {
          amount: "",
          category: "",
          date: "",
          time: "",
          note: "",
        };
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
    height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f0f2f5;
  }
  
  /* Container chính */
  .expense-management {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px); /* Trừ chiều cao của header (giả sử header cao 60px) */
  }
  
  /* Thanh tìm kiếm */
  .hero-section {
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.6),
        rgba(0, 0, 0, 0.6)
      ),
      url('/src/assets/ba.webp');
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
  </style>