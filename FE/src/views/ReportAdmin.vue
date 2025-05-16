<template>
  <div class="dashboard-container">
    <!-- User Stats Cards -->
    <div class="stats-grid">
      <div v-for="(stat, index) in userStats" :key="index" class="stat-card">
        <component
          :is="stat.title === 'Tổng người dùng' ? 'router-link' : 'div'"
          v-bind="
            stat.title === 'Tổng người dùng'
              ? { to: '/quan-ly-nguoi-dung', class: 'stat-content-link' }
              : {}
          "
        >
          <div class="stat-content">
            <div class="stat-header">
              <h3 class="stat-title">{{ stat.title }}</h3>
            </div>
            <p class="stat-value">{{ stat.value }}</p>
          </div>
        </component>
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
                :class="{ active: activeTab === tab.value }"
              >
                {{ tab.label }}
              </button>
            </div>
            <div class="date-picker">
              <CalendarIcon class="icon" />
              <span>{{ dateRangeText }}</span>
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
import { ref, onMounted, watch, computed } from "vue";
import {
  Calendar as CalendarIcon,
  Users as UsersIcon,
  UserPlus as UserPlusIcon,
  UserCheck as UserCheckIcon,
  UserX as UserXIcon,
} from "lucide-vue-next";
import Chart from "chart.js/auto";
import axios from "axios";

// State
const loading = ref(true);
const statsData = ref(null);
const activeTab = ref("tuần");
const chartRef = ref(null);
let chart = null;

// Tabs
const tabs = [
  { label: "Tuần", value: "tuần" },
  { label: "Tháng", value: "tháng" },
  { label: "Năm", value: "năm" },
];

// Format date range text for tabs
const dateRangeText = computed(() => {
  const now = new Date();
  let start, end;

  if (activeTab.value === "tuần") {
    // Bắt đầu từ Thứ Hai (T2) đến Chủ Nhật (CN)
    const day = now.getDay(); // 0 = CN, 1 = T2, ..., 6 = T7
    const diffToMonday = day === 0 ? -6 : 1 - day;
    start = new Date(now);
    start.setDate(now.getDate() + diffToMonday);
    end = new Date(start);
    end.setDate(start.getDate() + 6);
  } else if (activeTab.value === "tháng") {
    start = new Date(now.getFullYear(), now.getMonth(), 1);
    end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  } else {
    start = new Date(now.getFullYear(), 0, 1);
    end = new Date(now.getFullYear(), 11, 31);
  }

  const formatDate = (date) => {
    return `${date.getDate().toString().padStart(2, "0")}/${(
      date.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")}/${date.getFullYear()}`;
  };

  return `${formatDate(start)} - ${formatDate(end)}`;
});

// Computed user stats
const userStats = computed(() => {
  if (!statsData.value)
    return Array(4).fill({ title: "Loading...", value: "..." });

  return [
    { title: "Tổng người dùng", value: statsData.value.total_users.toString() },
    {
      title: "Lượng truy cập hôm qua",
      value: statsData.value.login_yesterday.toString(),
    },
    {
      title: "Lượng truy cập tuần trước",
      value: statsData.value.login_last_week.toString(),
    },
    {
      title: "Lượng truy cập tháng trước",
      value: statsData.value.login_last_month.toString(),
    },
  ];
});

// Computed user management stats
const userManagement = computed(() => {
  if (!statsData.value)
    return Array(3).fill({
      icon: UsersIcon,
      label: "Loading...",
      value: "...",
      period: "",
    });

  return [
    {
      icon: UserPlusIcon,
      label: "Người dùng mới",
      value: statsData.value.new_users_this_week.toString(),
      period: "tuần này",
    },
    {
      icon: UserCheckIcon,
      label: "Người dùng hoạt động",
      value: statsData.value.active_users.toString(),
      period: "tháng này",
    },
    {
      icon: UserXIcon,
      label: "Người dùng không hoạt động",
      value: statsData.value.inactive_users.toString(),
      period: "tháng này",
    },
  ];
});

// Chart data based on tab
const getChartData = () => {
  if (!statsData.value) {
    return { labels: [], data: [] };
  }

  if (activeTab.value === "tuần") {
    const weekData = statsData.value.weekly_stats;
    return {
      labels: weekData.map((item) => item.day),
      data: weekData.map((item) => item.count),
    };
  } else if (activeTab.value === "tháng") {
    const monthData = statsData.value.monthly_stats;
    return {
      labels: monthData.map((item) => item.day.toString()),
      data: monthData.map((item) => item.count),
    };
  } else {
    const months = [
      "T1",
      "T2",
      "T3",
      "T4",
      "T5",
      "T6",
      "T7",
      "T8",
      "T9",
      "T10",
      "T11",
      "T12",
    ];
    const yearData = statsData.value.yearly_stats;
    return {
      labels: months,
      data: yearData.map((item) => item.count),
    };
  }
};

// Render chart
const renderChart = () => {
  if (!chartRef.value) return;

  const ctx = chartRef.value.getContext("2d");
  const { labels, data } = getChartData();

  if (chart) chart.destroy();

  chart = new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [
        {
          label: "Lượt truy cập",
          data,
          backgroundColor: "rgba(59, 130, 246, 0.85)",
          borderRadius: 6,
          barThickness: activeTab.value === "tháng" ? 8 : "flex",
          maxBarThickness: 40,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 500,
        easing: "easeOutQuart",
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "rgba(0, 0, 0, 0.05)",
          },
        },
        x: {
          grid: {
            display: false,
          },
        },
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#fff",
          titleColor: "#334155",
          bodyColor: "#334155",
          borderColor: "#e2e8f0",
          borderWidth: 1,
          padding: 10,
          displayColors: false,
          titleFont: {
            size: 14,
            weight: "bold",
          },
          bodyFont: {
            size: 13,
          },
          callbacks: {
            title: (items) => `${items[0].label}`,
            label: (ctx) => `Lượt truy cập: ${ctx.parsed.y}`,
          },
        },
      },
    },
  });
};

// Fetch stats from API
const fetchStats = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/dashboard-stats", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
      },
    });
    if (response.data.status === "success") {
      statsData.value = response.data.data;
    } else {
      console.error("Failed to fetch stats");
    }
  } catch (error) {
    console.error("Error fetching stats:", error);
  } finally {
    loading.value = false;
    renderChart();
  }
};

// Init on mount
onMounted(() => {
  fetchStats();
});

// Re-render chart when tab changes
watch(activeTab, () => {
  renderChart();
});
</script>

<style scoped>
.dashboard-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica,
    Arial, sans-serif;
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
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
.stat-content-link {
  text-decoration: none;
  color: inherit;
  display: block;
}
</style>
