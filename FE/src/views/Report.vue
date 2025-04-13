<template>
    <div class="reports-page">
      <!-- Tiêu đề -->
      <h1 class="page-title">
        <i class="fas fa-chart-line"></i> Báo cáo tài chính
      </h1>
  
      <!-- Lựa chọn khoảng thời gian -->
      <div class="time-filter">
        <div class="time-tabs">
          <button
            :class="{ active: timeRange === 'week' }"
            @click="setTimeRange('week')"
          >
            Tuần
          </button>
          <button
            :class="{ active: timeRange === 'month' }"
            @click="setTimeRange('month')"
          >
            Tháng
          </button>
          <button
            :class="{ active: timeRange === 'year' }"
            @click="setTimeRange('year')"
          >
            Năm
          </button>
        </div>
        <div class="date-picker">
          <VueDatePicker v-model="dateRange" range />
        </div>
      </div>
  
      <!-- Biểu đồ và số liệu -->
      <div class="reports-content">
        <!-- Biểu đồ cột -->
        <div class="bar-chart-container">
          <Bar :data="barChartData" :options="barChartOptions" />
        </div>
  
        <!-- Biểu đồ tròn -->
        <div class="pie-charts">
          <div class="pie-chart-container">
            <h3>Chi tiêu</h3>
            <Pie :data="expensePieChartData" :options="pieChartOptions" />
            <div class="legend">
              <div v-for="(item, index) in expenseCategories" :key="index" class="legend-item">
                <i :class="item.icon" :style="{ color: expensePieChartData.datasets[0].backgroundColor[index] }"></i>
                <span>{{ item.label }}</span>
              </div>
            </div>
          </div>
          <div class="pie-chart-container">
            <h3>Thu nhập</h3>
            <Pie :data="incomePieChartData" :options="pieChartOptions" />
            <div class="legend">
              <div v-for="(item, index) in incomeCategories" :key="index" class="legend-item">
                <i :class="item.icon" :style="{ color: incomePieChartData.datasets[0].backgroundColor[index] }"></i>
                <span>{{ item.label }}</span>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Số liệu tổng quan -->
        <div class="summary">
          <div class="summary-item">
            <span class="label">Thu nhập:</span>
            <span class="value income">{{ formatNumber(summary.income) }} VND</span>
          </div>
          <div class="summary-item">
            <span class="label">Chi tiêu:</span>
            <span class="value expense">{{ formatNumber(summary.expense) }} VND</span>
          </div>
          <div class="summary-item">
            <span class="label">Số dư:</span>
            <span class="value balance">{{ formatNumber(summary.balance) }} VND</span>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Reports',
    data() {
      return {
        timeRange: 'month', // Mặc định là tháng
        dateRange: [
          new Date('2025-03-01'),
          new Date('2025-04-01'),
        ],
        // Dữ liệu giả lập
        barChartData: {
          labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
          datasets: [
            {
              label: 'Chi tiêu',
              backgroundColor: '#ef4444',
              data: [800000, 1200000, 600000, 900000],
            },
            {
              label: 'Thu nhập',
              backgroundColor: '#10b981',
              data: [2000000, 1500000, 500000, 1800000],
            },
          ],
        },
        barChartOptions: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Số tiền (triệu VND)',
              },
              ticks: {
                callback: (value) => value / 1000000, // Chia cho 1 triệu để hiển thị đơn vị triệu
              },
            },
            x: {
              title: {
                display: true,
                text: 'Thời gian',
              },
            },
          },
        },
        expenseCategories: [
          { label: 'Ăn uống', icon: 'fas fa-utensils' },
          { label: 'Đi chuyển', icon: 'fas fa-motorcycle' },
          { label: 'Mua sắm', icon: 'fas fa-shopping-cart' },
        ],
        expensePieChartData: {
          labels: ['Ăn uống', 'Đi chuyển', 'Mua sắm'],
          datasets: [
            {
              data: [2000000, 1500000, 1500000],
              backgroundColor: ['#3b82f6', '#ef4444', '#10b981'],
            },
          ],
        },
        incomeCategories: [
          { label: 'Tiền lương', icon: 'fas fa-money-bill' },
          { label: 'Tiền thưởng', icon: 'fas fa-trophy' },
          { label: 'Kinh doanh', icon: 'fas fa-briefcase' },
          { label: 'Đầu tư', icon: 'fas fa-chart-line' },
        ],
        incomePieChartData: {
          labels: ['Tiền lương', 'Tiền thưởng', 'Kinh doanh', 'Đầu tư'],
          datasets: [
            {
              data: [5000000, 2000000, 2000000, 1000000],
              backgroundColor: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b'],
            },
          ],
        },
        pieChartOptions: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false, // Ẩn legend mặc định, sử dụng legend tùy chỉnh
            },
          },
        },
        summary: {
          income: 10000000,
          expense: 5000000,
          balance: 5000000,
        },
      };
    },
    methods: {
      formatNumber(number) {
        return number.toLocaleString('vi-VN');
      },
      setTimeRange(range) {
        this.timeRange = range;
        // Cập nhật dữ liệu biểu đồ dựa trên khoảng thời gian (tuần, tháng, năm)
        if (range === 'week') {
          this.barChartData.labels = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'];
        } else if (range === 'month') {
          this.barChartData.labels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4'];
        } else if (range === 'year') {
          this.barChartData.labels = ['Năm 2023', 'Năm 2024', 'Năm 2025', 'Năm 2026'];
        }
      },
    },
    watch: {
      dateRange(newRange) {
        console.log('Date range changed:', newRange);
        // Gọi API hoặc cập nhật dữ liệu biểu đồ tại đây
      },
    },
  };
  </script>
  
  <style scoped>
  /* Container chính */
  .reports-page {
    padding: 2rem;
    background: linear-gradient(135deg, #f0f2f5 0%, #e6f0fa 100%);
    min-height: calc(100vh - 80px);
    display: flex;
    flex-direction: column;
    gap: 2rem;
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
  }
  
  .page-title i {
    color: #0ea5e9;
    font-size: 1.5rem;
  }
  
  /* Lựa chọn khoảng thời gian */
  .time-filter {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .time-tabs {
    display: flex;
    gap: 0.5rem;
  }
  
  .time-tabs button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    background-color: #e5e7eb;
    color: #374151;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  
  .time-tabs button.active {
    background-color: #f59e0b;
    color: white;
  }
  
  .time-tabs button:hover {
    background-color: #d1d5db;
  }
  
  .date-picker {
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background-color: white;
  }
  
  /* Nội dung báo cáo */
  .reports-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
  }
  
  /* Biểu đồ cột */
  .bar-chart-container {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #a1c4fd;
    height: 400px;
  }
  
  /* Biểu đồ tròn */
  .pie-charts {
    display: flex;
    justify-content: space-between;
    gap: 2rem;
  }
  
  .pie-chart-container {
    flex: 1;
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #a1c4fd;
    text-align: center;
  }
  
  .pie-chart-container h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
  }
  
  .pie-chart-container canvas {
    height: 200px !important;
    width: 200px !important;
    margin: 0 auto;
  }
  
  .legend {
    margin-top: 1rem;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
  }
  
  .legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .legend-item i {
    font-size: 1rem;
  }
  
  .legend-item span {
    font-size: 0.9rem;
    color: #374151;
  }
  
  /* Số liệu tổng quan */
  .summary {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #a1c4fd;
  }
  
  .summary-item {
    flex: 1;
    text-align: center;
  }
  
  .summary-item .label {
    display: block;
    font-size: 1rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
  }
  
  .summary-item .value {
    font-size: 1.2rem;
    font-weight: 600;
  }
  
  .summary-item .income {
    color: #10b981;
  }
  
  .summary-item .expense {
    color: #ef4444;
  }
  
  .summary-item .balance {
    color: #3b82f6;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .reports-page {
      padding: 1rem;
    }
  
    .page-title {
      font-size: 1.2rem;
    }
  
    .time-filter {
      flex-direction: column;
      gap: 1rem;
    }
  
    .time-tabs {
      justify-content: center;
    }
  
    .bar-chart-container {
      height: 300px;
    }
  
    .pie-charts {
      flex-direction: column;
      gap: 1rem;
    }
  
    .pie-chart-container canvas {
      height: 150px !important;
      width: 150px !important;
    }
  
    .summary {
      flex-direction: column;
      gap: 1rem;
    }
  
    .summary-item .value {
      font-size: 1rem;
    }
  }
  </style>