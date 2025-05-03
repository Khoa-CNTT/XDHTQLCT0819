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
  padding: 2rem 1rem;
  font-family: 'Arial', sans-serif;
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
  gap: 2rem;
}

.footer-column {
  flex: 1;
  min-width: 250px;
  margin-bottom: 1.5rem;
}

.footer-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: #ffffff;
}

.footer-links {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.footer-link {
  color: #ffffff;
  text-decoration: none;
  margin-bottom: 0.5rem;
  display: block;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #a1c4fd;
  text-decoration: underline;
}

/* Social Icons Styling */
.social-icons {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.social-icon {
  font-size: 1.5rem;
  color: #ffffff;
  transition: transform 0.3s ease, color 0.3s ease;
}

.social-icon:hover {
  transform: scale(1.2);
  color: #a1c4fd;
}

/* Newsletter Form Styling */
.newsletter-form {
  display: flex;
  flex-direction: row;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.newsletter-form input[type="email"] {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: none;
  outline: none;
  font-size: 1rem;
  width: 70%;
  max-width: 250px;
}

.newsletter-form button {
  padding: 0.5rem 1rem;
  background-color: #ffffff;
  color: #0ea5e9;
  font-weight: bold;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.newsletter-form button:hover {
  background-color: #f0f9ff;
  color: #0284c7;
}

/* Footer Bottom Section */
.footer-bottom {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(245, 245, 245, 0.2);
}

.footer-bottom p {
  font-size: 0.9rem;
  color: #e0f2fe;
}

/* Responsive Design */
@media (max-width: 768px) {
  .footer-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .newsletter-form {
    flex-direction: column;
    align-items: center;
  }

  .newsletter-form input[type="email"] {
    width: 90%;
  }

  .newsletter-form button {
    width: 90%;
  }
}

  </style>
