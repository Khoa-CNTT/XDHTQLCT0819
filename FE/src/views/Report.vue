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
        <VueDatePicker
          v-model="dateRange"
          range
          @update:model-value="onDateRangeChange"
        />
      </div>
    </div>

    <!-- Loading indicator -->
    <div v-if="loading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Đang tải dữ liệu...
    </div>

    <!-- Biểu đồ và số liệu -->
    <div v-else class="reports-content">
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
            <div
              v-for="(label, index) in expensePieChartData.labels"
              :key="index"
              class="legend-item"
            >
              <span
                class="color-dot"
                :style="{
                  backgroundColor:
                    expensePieChartData.datasets[0].backgroundColor[index],
                }"
              ></span>
              <span>{{ label }}</span>
            </div>
          </div>
        </div>
        <div class="pie-chart-container">
          <h3>Thu nhập</h3>
          <Pie :data="incomePieChartData" :options="pieChartOptions" />
          <div class="legend">
            <div
              v-for="(label, index) in incomePieChartData.labels"
              :key="index"
              class="legend-item"
            >
              <span
                class="color-dot"
                :style="{
                  backgroundColor:
                    incomePieChartData.datasets[0].backgroundColor[index],
                }"
              ></span>
              <span>{{ label }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Số liệu tổng quan -->
      <div class="summary">
        <div class="summary-item">
          <span class="label">Thu nhập:</span>
          <span class="value income"
            >{{ formatNumber(summary.income) }} VND</span
          >
        </div>
        <div class="summary-item">
          <span class="label">Chi tiêu:</span>
          <span class="value expense"
            >{{ formatNumber(summary.expense) }} VND</span
          >
        </div>
        <div class="summary-item">
          <span class="label">Số dư:</span>
          <span
            class="value balance"
            :class="{ negative: summary.balance < 0 }"
          >
            <small v-if="summary.balance < 0">-</small>
            {{ formatNumber(Math.abs(summary.balance)) }} VND
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { Bar, Pie } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
} from "chart.js";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

export default {
  name: "Reports",
  components: {
    Bar,
    Pie,
  },
  data() {
    return {
      loading: true,
      timeRange: "month",
      dateRange: [
        new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0),
      ],
      barChartData: {
        labels: [],
        datasets: [
          {
            label: "Chi tiêu",
            backgroundColor: "#ef4444",
            data: [],
          },
          {
            label: "Thu nhập",
            backgroundColor: "#10b981",
            data: [],
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
              text: "Số tiền (triệu VND)",
            },
            ticks: {
              callback: (value) => {
                const val = value / 1000000;
                return val % 1 === 0 ? val.toFixed(0) : val.toFixed(1);
              },
            },
          },
          x: {
            title: {
              display: true,
              text: "Thời gian",
            },
          },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                let label = context.dataset.label || "";
                if (label) {
                  label += ": ";
                }
                if (context.parsed.y !== null) {
                  label += new Intl.NumberFormat("vi-VN", {
                    style: "currency",
                    currency: "VND",
                  }).format(context.parsed.y);
                }
                return label;
              },
            },
          },
        },
      },
      expensePieChartData: {
        labels: [],
        datasets: [
          {
            data: [],
            backgroundColor: [],
          },
        ],
      },
      incomePieChartData: {
        labels: [],
        datasets: [
          {
            data: [],
            backgroundColor: [],
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
          tooltip: {
            callbacks: {
              label: function (context) {
                const value = context.parsed;
                const total = context.dataset.data.reduce(
                  (acc, val) => acc + val,
                  0
                );
                const percentage = Math.round((value / total) * 100);
                return `${context.label}: ${new Intl.NumberFormat("vi-VN", {
                  style: "currency",
                  currency: "VND",
                }).format(value)} (${percentage}%)`;
              },
            },
          },
        },
      },
      summary: {
        income: 0,
        expense: 0,
        balance: 0,
      },
    };
  },
  computed: {
    apiParams() {
      const params = { range: this.timeRange };

      if (this.dateRange && this.dateRange.length === 2) {
        params.dateRange = this.dateRange.map((date) => {
          return date.toISOString().split("T")[0];
        });
      }

      return params;
    },
  },
  methods: {
    formatNumber(number) {
      return new Intl.NumberFormat("vi-VN").format(number);
    },

    setTimeRange(range) {
      this.timeRange = range;

      const now = new Date();

      switch (range) {
        case "week":
          const startOfWeek = new Date(now);
          startOfWeek.setDate(now.getDate() - now.getDay());

          const endOfWeek = new Date(startOfWeek);
          endOfWeek.setDate(startOfWeek.getDate() + 6);

          this.dateRange = [startOfWeek, endOfWeek];
          break;

        case "month":
          const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);

          const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);

          this.dateRange = [startOfMonth, endOfMonth];
          break;

        case "year":
          const startOfYear = new Date(now.getFullYear(), 0, 1);

          const endOfYear = new Date(now.getFullYear(), 11, 31);

          this.dateRange = [startOfYear, endOfYear];
          break;
      }

      this.fetchData();
    },

    onDateRangeChange() {
      this.fetchData();
    },

    async fetchData() {
      this.loading = true;

      try {
        const [barData, expensePieData, incomePieData, summaryData] =
          await Promise.all([
            this.fetchBarChartData(),
            this.fetchExpensePieData(),
            this.fetchIncomePieData(),
            this.fetchSummaryData(),
          ]);

        this.updateBarChartData(barData);
        this.updateExpensePieData(expensePieData);
        this.updateIncomePieData(incomePieData);

        this.summary = summaryData;
      } catch (error) {
        console.error("Error fetching report data:", error);
        this.$toast.error("Đã xảy ra lỗi khi tải dữ liệu báo cáo");
      } finally {
        this.loading = false;
      }
    },

    async fetchBarChartData() {
      const response = await axios.get("/api/reports/bar-chart", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
        params: this.apiParams,
      });
      return response.data;
    },

    async fetchExpensePieData() {
      const response = await axios.get("/api/reports/expense-pie", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
        params: this.apiParams,
      });
      return response.data;
    },

    async fetchIncomePieData() {
      const response = await axios.get("/api/reports/income-pie", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
        params: this.apiParams,
      });
      return response.data;
    },

    async fetchSummaryData() {
      const response = await axios.get("/api/reports/summary", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
        params: this.apiParams,
      });
      return response.data;
    },

    updateBarChartData(data) {
      this.barChartData = {
        labels: data.labels,
        datasets: data.datasets.map((dataset, index) => ({
          ...dataset,
          backgroundColor: index === 0 ? "#ef4444" : "#10b981",
        })),
      };
    },

    updateExpensePieData(data) {
      this.expensePieChartData = {
        labels: data.labels,
        datasets: [
          {
            data: data.values,
            backgroundColor: data.colors,
          },
        ],
      };
    },

    updateIncomePieData(data) {
      this.incomePieChartData = {
        labels: data.labels,
        datasets: [
          {
            data: data.values,
            backgroundColor: data.colors,
          },
        ],
      };
    },
  },
  mounted() {
    // Fetch data when component is mounted
    this.fetchData();
  },
};
</script>

<style scoped>
.reports-page {
  padding: 3rem 4rem;
  background: linear-gradient(135deg, #f0f4f8 0%, #dbe5f7 100%);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  transition: all 0.3s ease;
}

/* Tiêu đề */
.page-title {
  font-size: 2.4rem;
  font-weight: 700;
  color: #333;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  transition: all 0.3s ease;
}

.page-title i {
  color: #0ea5e9;
  font-size: 2.2rem;
}

/* Lọc thời gian */
.time-filter {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

.time-tabs {
  display: flex;
  gap: 0.75rem;
}

.time-tabs button {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 1.25rem;
  background-color: #f3f4f6;
  color: #2d3748;
  font-size: 1.15rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.time-tabs button.active {
  background-color: #f59e0b;
  color: white;
  font-weight: 700;
}

.time-tabs button:hover {
  background-color: #e5e7eb;
  transform: scale(1.05);
}

.date-picker {
  padding: 0.8rem;
  border: 1px solid #d1d5db;
  border-radius: 1rem;
  background-color: white;
  font-size: 1.1rem;
  transition: border-color 0.3s ease;
}

.date-picker:focus {
  border-color: #0ea5e9;
}

/* Nội dung báo cáo */
.reports-content {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

/* Biểu đồ cột */
.bar-chart-container {
  background-color: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  border: 1px solid #d1e9ff;
  height: 480px;
  transition: all 0.3s ease;
  animation: scaleUp 0.3s ease;
}

.bar-chart-container:hover {
  transform: translateY(-8px);
}

@keyframes scaleUp {
  0% {
    opacity: 0.5;
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Biểu đồ tròn */
.pie-charts {
  display: flex;
  justify-content: space-between;
  gap: 2.5rem;
}

.pie-chart-container {
  flex: 1;
  background-color: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  border: 1px solid #d1e9ff;
  text-align: center;
  transition: transform 0.3s ease;
}

.pie-chart-container:hover {
  transform: translateY(-8px);
}

.pie-chart-container h3 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 1.5rem;
}

.pie-chart-container canvas {
  height: 240px !important;
  width: 240px !important;
  margin: 0 auto;
}

.legend {
  margin-top: 1.25rem;
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

.legend-item span {
  font-size: 1rem;
  color: #4b5563;
}

/* Thống kê tổng quan */
.summary {
  display: flex;
  justify-content: space-between;
  gap: 2rem;
  background-color: white;
  border-radius: 1rem;
  padding: 2.5rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  border: 1px solid #d1e9ff;
}

.summary-item {
  flex: 1;
  text-align: center;
}

.summary-item .label {
  font-size: 1.15rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.summary-item .value {
  font-size: 1.9rem;
  font-weight: 700;
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

/* Responsive Mobile */
@media (max-width: 768px) {
  .reports-page {
    padding: 1.5rem;
  }

  .page-title {
    font-size: 1.8rem;
  }

  .time-filter {
    flex-direction: column;
    gap: 1.5rem;
  }

  .time-tabs {
    justify-content: center;
    flex-wrap: wrap;
  }

  .bar-chart-container {
    height: 350px;
  }

  .pie-charts {
    flex-direction: column;
    gap: 1.5rem;
  }

  .pie-chart-container canvas {
    height: 200px !important;
    width: 200px !important;
  }

  .summary {
    flex-direction: column;
    gap: 1rem;
  }

  .summary-item .value {
    font-size: 1.5rem;
  }
}
</style>
