import { createApp } from "vue";
import App from "./App.vue";
import router from "./router"; // Import Vue Router
import axios from "axios";

// Cấu hình Axios (Kết nối Laravel API)
axios.defaults.baseURL = "http://127.0.0.1:8000/api";
axios.defaults.headers.common["Authorization"] = `Bearer ${localStorage.getItem("token")}`;

const app = createApp(App);
app.use(router);
app.mount("#app");
