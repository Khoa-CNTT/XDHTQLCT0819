<template>
    <footer class="footer-before-login">
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
  </template>
  
  <script>
  export default {
    name: 'FooterBeforeLogin',
  };
  </script>
  
  <style scoped>
  .footer-before-login {
    background-color: #0ea5e9;
    color: #ffffff;
    padding: 1.5rem 1rem;
  }
  
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
  }
  
  .footer-content {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }
  
  .footer-column {
    flex: 1;
    min-width: 200px;
    margin-bottom: 1.5rem;
  }
  
  .footer-title {
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 1rem;
  }
  
  .footer-links {
    list-style-type: none;
    padding: 0;
  }
  
  .footer-link {
    color: #ffffff;
    text-decoration: none;
    margin-bottom: 0.5rem;
    display: inline-block;
    transition: color 0.3s ease;
  }
  
  .footer-link:hover {
    color: #a1c4fd;
    text-decoration: underline;
  }
  
  .footer-bottom {
    text-align: center;
    margin-top: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(245, 245, 245, 0.1);
  }
  
  .footer-bottom p {
    font-size: 0.9rem;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .footer-content {
      flex-direction: column;
      gap: 1rem;
    }
  }
  </style>