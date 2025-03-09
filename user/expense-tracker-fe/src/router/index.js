import { createRouter, createWebHistory } from "vue-router";
import Home from "@/views/Home.vue";
import Login from "@/views/Login.vue";
import Dashboard from "@/views/Dashboard.vue";

// Danh sách các routes
const routes = [
  { path: "/", name: "Home", component: Home },
  { path: "/login", name: "Login", component: Login },
  { path: "/dashboard", name: "Dashboard", component: Dashboard },
];

// Khởi tạo Router
const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
