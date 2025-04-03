<template>
  <div class="wrapper">
    <!-- Hiển thị header chỉ khi không phải là các trang đăng nhập, đăng ký, quên mật khẩu -->
    <header v-if="!isAuthPage" class="header">
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
            <li><a href="#home" class="nav-link text-dark">Trang chủ</a></li>
            <li><a href="#features" class="nav-link text-dark">Tính năng</a></li>
            <li><a href="#guide" class="nav-link text-dark">Hướng dẫn</a></li>
            <li><a href="#terms" class="nav-link text-dark">Điều khoản</a></li>
            <li><a href="#contact" class="nav-link text-dark">Liên hệ</a></li>
            <li><a href="/bao-cao" class="nav-link signup">Báo cáo</a></li>
            <li><a href="/muc-tieu" class="nav-link signup">Mục tiêu</a></li>
            <li><a href="/nhat-ki-chi-tieu" class="nav-link signup">Nhật ký</a></li>
            <li><a href="/login" class="nav-link login">Đăng nhập</a></li>
            <li><a href="/sign-up" class="nav-link signup">Đăng ký</a></li>
          </ul>
        </div>
      </nav>
    </header>

    <!-- Main content -->
    <main>
      <router-view />
    </main>

    <!-- Hiển thị footer chỉ khi không phải là các trang đăng nhập, đăng ký, quên mật khẩu -->
    <footer v-if="!isAuthPage" class="footer">
      <div class="container footer-content">
        <div class="footer-column">
          <h3 class="footer-title">SaveUp</h3>
          <p>Quản lý chi tiêu thông minh, tiết kiệm hiệu quả</p>
          <div class="social-icons">
            <a href="#" class="social-icon">📘</a>
            <a href="#" class="social-icon">🐦</a>
            <a href="#" class="social-icon">📸</a>
            <a href="#" class="social-icon">🔗</a>
          </div>
        </div>
        <div class="footer-column">
          <h3 class="footer-title">Liên kết</h3>
          <ul class="footer-links">
            <li><a href="#" class="footer-link">Trang chủ</a></li>
            <li><a href="#" class="footer-link">Tính năng</a></li>
            <li><a href="#" class="footer-link">Hướng dẫn</a></li>
            <li><a href="#" class="footer-link">Liên hệ</a></li>
            <li><a href="#" class="footer-link">Điều khoản</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h3 class="footer-title">Đăng ký nhận tin</h3>
          <form class="newsletter-form">
            <input type="email" placeholder="Nhập email của bạn" required />
            <button type="submit" class="btn-primary">Đăng ký</button>
          </form>
          <p>Nhận thông báo về các tính năng mới và ưu đãi đặc biệt.</p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2025 SaveUp. Bảo lưu mọi quyền.</p>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: "App",
  computed: {
    isAuthPage() {
      // Kiểm tra nếu route hiện tại là /login, /register, hoặc /forgot-password
      const authRoutes = ['/login', '/sign-up', '/forgot-password'];
      return authRoutes.includes(this.$route.path);
    },
  },
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

/* Wrapper chính */
.wrapper {
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
  font-size: 1.5rem;
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

/* Main content */
main {
  margin-top: 80px; /* Sẽ được điều chỉnh động bằng JavaScript */
  flex: 1;
}

/* Footer */
.footer {
  background-color: #0ea5e9;
  color: #ffffff;
  padding: 2rem 1rem;
}

.footer-content {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

.footer-column {
  flex: 1;
  min-width: 250px;
  margin-bottom: 2rem;
}

.footer-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
}

.footer-links {
  list-style-type: none;
  padding: 0;
}

.footer-link {
  color: #ffffff;
  text-decoration: none;
  margin-bottom: 0.75rem;
  display: inline-block;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #f0f9ff;
  text-decoration: underline;
}

.social-icons {
  display: flex;
  gap: 1rem;
}

.social-icon {
  font-size: 1.75rem;
  color: #ffffff;
  text-decoration: none;
  transition: transform 0.3s ease;
}

.social-icon:hover {
  transform: translateY(-3px);
  color: #f0f9ff;
}

.newsletter-form {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.newsletter-form input {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
}

.newsletter-form button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  background-color: #ffffff;
  color: #0ea5e9;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.newsletter-form button:hover {
  background-color: #f0f9ff;
}

.footer-bottom {
  text-align: center;
  margin-top: 1rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(245, 245, 245, 0.1);
}

.footer-bottom p {
  font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .nav {
    flex-direction: column;
    align-items: flex-start;
  }

  .nav-list {
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    text-align: center;
  }

  .section {
    min-height: 80vh;
    padding: 1rem;
  }

  .footer-content {
    flex-direction: column;
    gap: 1.5rem;
  }
}
</style>