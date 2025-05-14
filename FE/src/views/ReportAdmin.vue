<template>
  <div class="dashboard-container">
    <!-- User Stats Cards -->
    <div class="stats-grid">
      <div v-for="(stat, index) in userStats" :key="index" class="stat-card">
        <div class="stat-content">
          <div class="stat-header">
            <h3 class="stat-title">{{ stat.title }}</h3>
          </div>
          <p class="stat-value">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="charts-grid">
      <!-- Visitor Chart -->
      <div class="chart-card">
        <div class="card-header">
          <h3 class="card-title">Số lượng người dùng truy cập</h3>
        </div>
        <div class="card-body">
          <div class="chart-controls">
            <div class="tab-controls">
              <button 
                v-for="(tab, index) in tabs" 
                :key="index"
                @click="activeTab = tab.value"
                class="tab-button"
                :class="{ 'active': activeTab === tab.value }"
              >
                {{ tab.label }}
              </button>
            </div>
            <div class="date-picker">
              <CalendarIcon class="icon" />
              <span>23/03/2025 - 01/04/2025</span>
            </div>
          </div>

          <div class="chart-container">
            <canvas ref="chartRef"></canvas>
          </div>
        </div>
      </div>

      <!-- User Management -->
      <div class="chart-card">
        <div class="card-header">
          <h3 class="card-title">Quản lý người dùng</h3>
        </div>
        <div class="card-body">
          <div class="user-icon-container">
            <div class="user-icon">
              <UsersIcon />
            </div>
          </div>

          <div class="user-stats">
            <div 
              v-for="(stat, index) in userManagement" 
              :key="index" 
              class="user-stat-item"
            >
              <div class="user-stat-label">
                <div class="user-stat-icon">
                  <component :is="stat.icon" />
                </div>
                <span>{{ stat.label }}</span>
              </div>
              <div class="user-stat-value">
                <div class="value">{{ stat.value }}</div>
                <div class="period">{{ stat.period }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { 
  Calendar as CalendarIcon, 
  Users as UsersIcon,
  UserPlus as UserPlusIcon,
  UserCheck as UserCheckIcon,
  UserX as UserXIcon
} from 'lucide-vue-next';
import Chart from 'chart.js/auto';

// Stats Data
const userStats = [
  { title: "Tổng người dùng", value: "100" },
  { title: "Lượng truy cập hôm qua", value: "100" },
  { title: "Lượng truy cập tuần trước", value: "220" },
  { title: "Lượng truy cập tháng trước", value: "300" }
];

const tabs = [
  { label: 'Tuần', value: 'tuần' },
  { label: 'Tháng', value: 'tháng' },
  { label: 'Năm', value: 'năm' }
];

const activeTab = ref('tuần');

// Quản lý người dùng
const userManagement = [
  { icon: UserPlusIcon, label: 'Người dùng mới', value: '12', period: 'tuần này' },
  { icon: UserCheckIcon, label: 'Người dùng hoạt động', value: '78', period: 'tháng này' },
  { icon: UserXIcon, label: 'Người dùng không hoạt động', value: '22', period: 'tháng này' }
];

// Chart setup
const chartRef = ref(null);
let chart = null;

const getChartData = () => {
  if (activeTab.value === 'tuần') {
    return {
      labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'],
      data: [15, 35, 30, 45, 35, 25, 30]
    };
  } else if (activeTab.value === 'tháng') {
    return {
      labels: Array.from({ length: 30 }, (_, i) => `${i + 1}`),
      data: Array.from({ length: 30 }, () => Math.floor(Math.random() * 50) + 10)
    };
  } else {
    const months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
    return {
      labels: months,
      data: Array.from({ length: 12 }, () => Math.floor(Math.random() * 500) + 100)
    };
  }
};

const renderChart = () => {
  const ctx = chartRef.value.getContext('2d');
  const { labels, data } = getChartData();

  if (chart) chart.destroy();

  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Lượt truy cập',
        data,
        backgroundColor: 'rgba(59, 130, 246, 0.85)',
        borderRadius: 6,
        barThickness: activeTab.value === 'tháng' ? 8 : 'flex',
        maxBarThickness: 40
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 500,
        easing: 'easeOutQuart'
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          }
        },
        x: {
          grid: {
            display: false
          }
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#334155',
          bodyColor: '#334155',
          borderColor: '#e2e8f0',
          borderWidth: 1,
          padding: 10,
          displayColors: false,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          callbacks: {
            title: items => `${items[0].label}`,
            label: ctx => `Lượt truy cập: ${ctx.parsed.y}`
          }
        }
      }
    }
  });
};

// Initialize and update chart when tab changes
onMounted(() => {
  renderChart();
});

watch(activeTab, () => {
  renderChart();
});
</script>

<style scoped>
.dashboard-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.stat-card {
  background-color: #eff6ff;
  border: 1px solid #dbeafe;
  border-radius: 0.75rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.stat-content {
  padding: 1.25rem;
}

.stat-header {
  margin-bottom: 0.5rem;
}

.stat-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #4b5563;
  margin: 0;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2563eb;
  margin: 0;
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .charts-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.chart-card {
  background-color: #ffffff;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f3f4f6;
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.card-body {
  padding: 1.25rem;
}

/* Chart Controls */
.chart-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.tab-controls {
  display: flex;
  background-color: #f3f4f6;
  border-radius: 0.375rem;
  overflow: hidden;
}

.tab-button {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  color: #4b5563;
  font-weight: 500;
}

.tab-button:hover:not(.active) {
  background-color: #e5e7eb;
}

.tab-button.active {
  background-color: #ffffff;
  color: #2563eb;
  font-weight: 600;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.date-picker {
  display: flex;
  align-items: center;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #4b5563;
  gap: 0.5rem;
  cursor: pointer;
  transition: border-color 0.2s;
}

.date-picker:hover {
  border-color: #d1d5db;
}

.date-picker .icon {
  width: 1rem;
  height: 1rem;
  color: #6b7280;
}

/* Chart Container */
.chart-container {
  height: 280px;
  position: relative;
}

/* User Management Section */
.user-icon-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.user-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 3.5rem;
  background-color: #dbeafe;
  border-radius: 9999px;
  color: #2563eb;
}

.user-icon svg {
  width: 2rem;
  height: 2rem;
}

.user-stats {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.user-stat-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.user-stat-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.user-stat-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #4b5563;
}

.user-stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
}

.user-stat-icon svg {
  width: 1.25rem;
  height: 1.25rem;
}

.user-stat-value {
  text-align: right;
}

.user-stat-value .value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2563eb;
  line-height: 1.2;
}

.user-stat-value .period {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.125rem;
}
</style>