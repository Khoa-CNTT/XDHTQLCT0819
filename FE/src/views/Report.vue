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
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const value = parseFloat(context.parsed);
                const total = context.dataset.data.reduce(
                  (acc, val) => acc + parseFloat(val),
                  0
                );
                const percentage =
                  total > 0 ? Math.round((value / total) * 100) : 0;
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
  background: linear-gradient(145deg, #f8fafc 0%, #e0eafc 100%);
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
  color: #1e293b;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.page-title i {
  color: #0ea5e9;
  font-size: 2.2rem;
  filter: drop-shadow(0 2px 4px rgba(14, 165, 233, 0.3));
}

/* Lọc thời gian */
.time-filter {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  background: rgba(255, 255, 255, 0.7);
  padding: 1.5rem;
  border-radius: 1.25rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.8);
}

.time-tabs {
  display: flex;
  gap: 0.75rem;
}

.time-tabs button {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 1.25rem;
  background-color: #f1f5f9;
  color: #475569;
  font-size: 1.15rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.time-tabs button::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(255, 255, 255, 0.2), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.time-tabs button:hover::before {
  opacity: 1;
}

.time-tabs button.active {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  color: white;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.time-tabs button:hover:not(.active) {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.date-picker {
  padding: 0.8rem;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  background-color: white;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.date-picker:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Loading indicator */
.loading-indicator {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  font-size: 1.2rem;
  color: #64748b;
  height: 200px;
  animation: fadeIn 0.5s ease;
}

.loading-indicator i {
  color: #0ea5e9;
  font-size: 1.5rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Nội dung báo cáo */
.reports-content {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  animation: slideUp 0.5s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Biểu đồ cột */
.bar-chart-container {
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 1.25rem;
  padding: 2rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(226, 232, 240, 0.8);
  height: 480px;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
}

.bar-chart-container::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, #0ea5e9, #3b82f6, #8b5cf6);
  opacity: 0.7;
  border-radius: 0 0 1.25rem 1.25rem;
}

.bar-chart-container:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

/* Biểu đồ tròn */
.pie-charts {
  display: flex;
  justify-content: space-between;
  gap: 2.5rem;
}

.pie-chart-container {
  flex: 1;
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 1.25rem;
  padding: 2rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(226, 232, 240, 0.8);
  text-align: center;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
}

.pie-chart-container:first-child::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, #ef4444, #f97316, #f59e0b);
  opacity: 0.7;
  border-radius: 0 0 1.25rem 1.25rem;
}

.pie-chart-container:last-child::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, #10b981, #14b8a6, #0ea5e9);
  opacity: 0.7;
  border-radius: 0 0 1.25rem 1.25rem;
}

.pie-chart-container:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.pie-chart-container h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  position: relative;
  display: inline-block;
}

.pie-chart-container h3::after {
  content: "";
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 40px;
  height: 3px;
  background-color: #cbd5e1;
  border-radius: 3px;
}

.pie-chart-container canvas {
  height: 240px !important;
  width: 240px !important;
  margin: 0 auto;
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
  animation: rotateIn 1s ease;
}

@keyframes rotateIn {
  from {
    opacity: 0;
    transform: rotate(-10deg) scale(0.9);
  }
  to {
    opacity: 1;
    transform: rotate(0) scale(1);
  }
}

.legend {
  margin-top: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 1.25rem;
  padding: 0.75rem;
  background-color: rgba(248, 250, 252, 0.5);
  border-radius: 0.75rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: transform 0.2s ease;
}

.legend-item:hover {
  transform: translateY(-2px);
}

.legend-item .color-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.legend-item span {
  font-size: 1rem;
  color: #475569;
  font-weight: 500;
}

/* Thống kê tổng quan */
.summary {
  display: flex;
  justify-content: space-between;
  gap: 2rem;
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 1.25rem;
  padding: 2.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(226, 232, 240, 0.8);
  position: relative;
  overflow: hidden;
}

.summary::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(
    circle at top right,
    rgba(255, 255, 255, 0.8),
    transparent 70%
  );
  pointer-events: none;
}

.summary-item {
  flex: 1;
  text-align: center;
  position: relative;
  padding: 1.5rem;
  border-radius: 1rem;
  background-color: rgba(255, 255, 255, 0.5);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.summary-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
}

.summary-item .label {
  font-size: 1.15rem;
  color: #64748b;
  margin-bottom: 0.75rem;
  display: block;
  font-weight: 500;
}

.summary-item .value {
  font-size: 2rem;
  font-weight: 800;
  display: block;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  letter-spacing: -0.5px;
}

.summary-item .income {
  color: #10b981;
  background: linear-gradient(135deg, #10b981, #059669);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.summary-item .expense {
  color: #ef4444;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.summary-item .balance {
  color: #3b82f6;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.summary-item .balance.negative {
  background: linear-gradient(135deg, #f43f5e, #e11d48);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Responsive Mobile */
@media (max-width: 768px) {
  .reports-page {
    padding: 1.5rem;
    gap: 2rem;
  }

  .page-title {
    font-size: 1.8rem;
  }

  .time-filter {
    flex-direction: column;
    gap: 1.5rem;
    padding: 1.25rem;
  }

  .time-tabs {
    justify-content: center;
    flex-wrap: wrap;
    width: 100%;
  }

  .time-tabs button {
    flex: 1;
    min-width: 80px;
    padding: 0.7rem 1rem;
    font-size: 1rem;
  }

  .date-picker {
    width: 100%;
  }

  .bar-chart-container {
    height: 350px;
    padding: 1.5rem;
  }

  .pie-charts {
    flex-direction: column;
    gap: 1.5rem;
  }

  .pie-chart-container {
    padding: 1.5rem;
  }

  .pie-chart-container h3 {
    font-size: 1.3rem;
  }

  .pie-chart-container canvas {
    height: 200px !important;
    width: 200px !important;
  }

  .legend {
    padding: 0.5rem;
    gap: 0.75rem;
  }

  .legend-item span {
    font-size: 0.9rem;
  }

  .summary {
    flex-direction: column;
    gap: 1rem;
    padding: 1.5rem;
  }

  .summary-item {
    padding: 1rem;
  }

  .summary-item .label {
    font-size: 1rem;
  }

  .summary-item .value {
    font-size: 1.6rem;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .reports-page {
    padding: 2rem;
  }

  .pie-charts {
    flex-direction: row;
  }

  .pie-chart-container canvas {
    height: 220px !important;
    width: 220px !important;
  }
}
</style>
