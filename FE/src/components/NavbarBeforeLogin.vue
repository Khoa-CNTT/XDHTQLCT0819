<template>
    <header class="header">
      <nav class="nav container">
        <a href="/" class="logo">
          <img src="/logo.png" alt="SaveUp Logo" class="logo-img" loading="lazy" />
        </a>

        <div class="nav-menu">
          <ul class="nav-list">
            <li><a href="#home" class="nav-link text-dark">Trang chủ</a></li>
            <li><a href="#features" class="nav-link text-dark">Tính năng</a></li>
            <li><a href="#guide" class="nav-link text-dark">Hướng dẫn</a></li>
            <li><a href="#terms" class="nav-link text-dark">Điều khoản</a></li>
            <li><a href="#contact" class="nav-link text-dark">Liên hệ</a></li>
            <li><router-link to="/login" class="nav-link login">Đăng nhập</router-link></li>
          </ul>
        </div>
      </nav>
    </header>
  </template>

  <script>
  export default {
    name: "NavbarBeforeLogin",
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
  }
  .nav-link {
    color: #000;
    text-decoration: none;
    position: relative;
    font-size: 1rem;
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
