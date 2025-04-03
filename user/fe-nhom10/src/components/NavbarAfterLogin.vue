<template>
  <div class="navbar-after-login">
      <header class="header">
        <nav class="nav container">
          <a href="/" class="logo">
            <img
              src="/logo.png"
              alt="SaveUp Logo"
              class="logo-img"
              loading="lazy"
            />
          </a>
  
          <div class="nav-menu">
            <ul class="nav-list">
              <router-link to="/quan-li-chi-tieu" class="nav-link text-dark">Trang chủ</router-link>
              <router-link to="/nhat-ki-chi-tieu" class="nav-link text-dark">Nhật kí</router-link>
              <router-link to="/muc-tieu" class="nav-link text-dark">Mục tiêu</router-link>
              <router-link to="/thong-ke" class="nav-link text-dark">Thống kê</router-link>
              <router-link to="/tai-khoan" class="nav-link">Tài khoản</router-link>
            </ul>
          </div>
        </nav>
      </header>
    </div>
    </template>
  
  <script>
  export default {
    name: 'NavbarAfterLogin',
  };
  </script>
  
  <style>
  /* Cấu hình chung */
  * {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  
  html {
    scroll-behavior: smooth;
  }
  
  .navbar-after-login {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  
  /* Container */
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
  }
  
  /* Header & Navigation */
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
    flex-wrap: wrap;
    padding: 1rem 0;
  }
  
  .logo {
    display: flex;
    align-items: center;
    justify-content: flex-start;
  }
  
  .logo-img {
    width: 100px;
    height: auto;
  }
  
  .nav-menu {
    flex: 1;
    color: #ffffff;
  }
  
  .nav-list {
    display: flex;
    list-style: none;
    gap: 2rem;
    justify-content: flex-end;
    margin: 0;
    padding: 0;
  }
  
  .nav-link {
    color: #000000;
    text-decoration: none;
    position: relative;
    font-size: 1rem;
    transition: all 0.3s ease;
  }
  
  /* Hiệu ứng cho liên kết */
  .nav-link::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #000000;
    transition: all 0.2s ease;
  }
  
  .nav-link:hover {
    color: #000000;
  }
  
  .nav-link:hover::after {
    width: 100%;
  }
  </style>