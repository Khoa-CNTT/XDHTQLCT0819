import { createRouter, createWebHistory } from "vue-router";
import HomeView from "@/views/HomeView.vue";
import Login from "@/views/Login.vue";
import SignUp from "@/views/SignUp.vue";
import ForgotPass from "@/views/ForgotPass.vue";
import ExpenseManagement from "@/views/ExpenseManagement.vue";
import Transaction from "@/views/Transaction.vue";
import Target from "@/views/Target.vue";
import Report from "@/views/Report.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomeView,
  },
  {
    path: "/login",
    name: "login",
    component: Login,
  },
  {
    path: "/sign-up",
    name: "sign-up",
    component: SignUp,
  },
  {
    path: "/forgot-password",
    name: "forgot-password",
    component: ForgotPass,
  },
  {
    path: "/quan-li-chi-tieu",
    name: "quan-li-chi-tieu",
    component: ExpenseManagement,
    meta: { requiresAuth: true },
  },
  {
    path: "/nhat-ki-chi-tieu",
    name: "nhat-ki-chi-tieu",
    component: Transaction,
    meta: { requiresAuth: true },
  },
  {
    path: "/muc-tieu",
    name: "muc-tieu",
    component: Target,
    meta: { requiresAuth: true },
  },
  {
    path: "/bao-cao",
    name: "bao-cao",
    component: Report,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Middleware check login
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem("auth_token");
  if (to.meta.requiresAuth && !isAuthenticated) {
    next("/login");
  } else {
    next();
  }
});

export default router;
