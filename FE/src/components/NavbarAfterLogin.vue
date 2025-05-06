<template>
    <header class="header">
      <nav class="nav container">
        <a href="/" class="logo">
          <img src="/logo.png" alt="SaveUp Logo" class="logo-img" loading="lazy" />
        </a>

        <div class="nav-menu">
          <ul class="nav-list">
            <li><router-link to="/quan-li-chi-tieu" class="nav-link">Trang chủ</router-link></li>
            <li><router-link to="/nhat-ki-chi-tieu" class="nav-link">Lịch</router-link></li>
            <li><router-link to="/muc-tieu" class="nav-link">Mục tiêu</router-link></li>
            <li><router-link to="/bao-cao" class="nav-link">Thống kê</router-link></li>
            <li><router-link to="/quan-ly-danh-muc" class="nav-link">Danh mục</router-link></li>
            <li><router-link to="/quan-ly-nguoi-dung" class="nav-link">Người dùng</router-link></li>
            <li><router-link to="/quan-ly-tai-khoan" class="nav-link">Tài khoản</router-link></li>
            <li><a href="#" class="nav-link" @click.prevent="logout">Đăng xuất</a></li>
          </ul>
        </div>
      </nav>
    </header>
  </template>

  <script>
  export default {
    name: "NavbarAfterLogin",
    methods: {
      logout() {
        localStorage.removeItem("auth_token");
        alert("Tài khoản đã đăng xuất thành công!");
        this.$emit("logged-out"); // Gửi lên App.vue
      },
    },
  };
  </script>

  <style scoped>
  .header {
    background-color: #ffffff;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  .nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
  }
  .logo-img {
    width: 100px;
  }
  .nav-list {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .nav-link {
    color: #000;
    text-decoration: none;
    font-size: 1rem;
    position: relative;
    cursor: pointer;
  }
  .nav-link::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #000;
    transition: all 0.2s ease;
  }
  .nav-link:hover::after {
    width: 100%;
  }
  </style>
