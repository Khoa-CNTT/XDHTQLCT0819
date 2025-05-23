import { createRouter, createWebHistory } from "vue-router";
import HomeView from "@/views/HomeView.vue";
import Login from "@/views/Login.vue";
import SignUp from "@/views/SignUp.vue";
import ForgotPass from "@/views/ForgotPass.vue";
import ExpenseManagement from "@/views/ExpenseManagement.vue";
import Transaction from "@/views/Transaction.vue";
import Target from "@/views/Target.vue";
import Report from "@/views/Report.vue";
import EditUser from "@/views/EditUser.vue";
import Category from "@/views/Category.vue";
import Account from "@/views/Account.vue";
import Profile from "@/views/Profile.vue";
import Budget from "@/views/Budget.vue";
import RecurringTransactions from "@/views/RecurringTransactions.vue";
import ReportAdmin from "@/views/ReportAdmin.vue";
import ActivityLogs from "@/views/ActivityLogs.vue";
import ContactAdmin from "@/views/ContactAdmin.vue";
import VerifyOpt from "@/views/VerifyOpt.vue";
import ResetPassword from "@/views/ResetPassword.vue";
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
  {
    path: "/quan-ly-nguoi-dung",
    name: "quan-ly-nguoi-dung",
    component: EditUser,
    meta: { requiresAuth: true },
  },
  {
    path: "/quan-ly-danh-muc",
    name: "quan-ly-danh-muc",
    component: Category,
    meta: { requiresAuth: true },
  },
  {
    path: "/quan-ly-tai-khoan",
    name: "quan-ly-tai-khoan",
    component: Account,
    meta: { requiresAuth: true },
  },
  {
    path: "/profile",
    name: "profile",
    component: Profile,
    meta: { requiresAuth: true },
  },
  {
    path: "/budget",
    name: "budget",
    component: Budget,
    meta: { requiresAuth: true },
  },
  {
    path: "/quan-ly-giao-dich-dinh-ky",
    name: "quan-ly-giao-dich-dinh-ky",
    component: RecurringTransactions,
    meta: { requiresAuth: true },
  },
  {
    path: "/report-admin",
    name: "report-admin",
    component: ReportAdmin,
    meta: { requiresAuth: true },
  },
  {
    path: "/nhat-ky-hoat-dong",
    name: "nhat-ky-hoat-dong",
    component: ActivityLogs,
  },
  {
    path: "/quan-li-lien-he",
    name: "quan-li-lien-he",
    component: ContactAdmin,
  },
  {
    path: "/verify-otp",
    name: "verify-otp",
    component: VerifyOpt,
  },
  {
    path: "/reset-password",
    name: "reset-password",
    component: ResetPassword,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authToken = localStorage.getItem("auth_token");

  if (to.meta.requiresAuth && !authToken) {
    return next("/login");
  }

  if (to.path === "/login" && authToken) {
    return next("/quan-li-chi-tieu");
  }

  return next();
});

export default router;
