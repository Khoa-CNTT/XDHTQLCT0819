<template>
  <div class="wrapper">
    <!-- HEADER -->
    <header v-if="!isAuthPage && !isLoggedIn">
      <NavbarBeforeLogin />
    </header>
    <header v-if="!isAuthPage && isLoggedIn">
      <NavbarAfterLogin @logged-out="handleLogout" />
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- FOOTER -->
    <footer v-if="!isAuthPage">
      <FooterBeforeLogin v-if="!isLoggedIn" />
      <FooterAfterLogin v-else />
    </footer>
  </div>
</template>

<script>
import NavbarBeforeLogin from "@/components/NavbarBeforeLogin.vue";
import NavbarAfterLogin from "@/components/NavbarAfterLogin.vue";
import FooterBeforeLogin from "@/components/FooterBeforeLogin.vue";
import FooterAfterLogin from "@/components/FooterAfterLogin.vue";

export default {
  name: "App",
  components: {
    NavbarBeforeLogin,
    NavbarAfterLogin,
    FooterBeforeLogin,
    FooterAfterLogin,
  },
  computed: {
    isAuthPage() {
      const authRoutes = ["/login", "/sign-up", "/forgot-password"];
      return authRoutes.includes(this.$route.path);
    },
    isLoggedIn() {
      return !!localStorage.getItem("auth_token");
    },
  },
  methods: {
    handleLogout() {
      // XÓA token hoặc dữ liệu login
      localStorage.removeItem("auth_token");

      // CHUYỂN VỀ TRANG CHỦ & RELOAD TOÀN BỘ TRANG
      window.location.href = "/";
    },
  },
};
</script>

<style>
/* CẤU HÌNH CƠ BẢN */
* {
  font-family: 'Roboto', sans-serif;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html {
  scroll-behavior: smooth;
}

body {
  background-color: #f9fafb;
  color: #111827;
}

/* WRAPPER */
.wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* MAIN */
.main-content {
  flex: 1;
  margin-top: 80px;
  padding: 1.5rem;
}

/* FOOTER CHUNG */
footer {
  background-color: #0ea5e9;
  color: #ffffff;
  padding: 2rem 1rem 1rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }
}
</style>
