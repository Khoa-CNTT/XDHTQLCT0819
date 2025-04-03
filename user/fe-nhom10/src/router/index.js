import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
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
  },
  {
    path: "/nhat-ki-chi-tieu",
    name: "nhat-ki-chi-tieu",
    component: Transaction,
  },
  {
    path: "/muc-tieu",
    name: "muc-tieu",
    component: Target,
  },
  {
    path: "/bao-cao",
    name: "bao-cao",
    component: Report,
  },
];
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
