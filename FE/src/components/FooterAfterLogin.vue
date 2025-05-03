<template>
    <footer class="footer-after-login">
      <div class="footer-bottom">
        <p>© 2025 SaveUp. Bảo lưu mọi quyền.</p>
      </div>
    </footer>
  </template>
  
  <script>
  export default {
    name: 'FooterAfterLogin',
    data() {
      return {
        email: '',
      };
    },
    methods: {
      subscribe() {
        console.log(`Đăng ký nhận tin với email: ${this.email}`);
        this.email = ''; // Reset form sau khi submit
      },
    },
  };
  </script>
  
  <style scoped>
  /* Footer after login */
  .footer-after-login {
    width: 100%;
    background-color: #0ea5e9; /* Màu xanh nổi bật */
    padding: 1px 0; /* Padding đều cho phần Footer */
    text-align: center; /* Căn giữa văn bản */
    position: relative;
    z-index: 1;
  }
  
  .footer-bottom p {
    color: #ffffff; /* Màu chữ trắng */
    font-size: 1.1rem; /* Cỡ chữ vừa phải */
    font-weight: 400; /* Độ đậm chữ trung bình */
    margin: 0;
    padding: 10px 0; /* Padding nhẹ để văn bản dễ đọc */
  }
  
  /* Cải tiến hiệu ứng khi hover nếu có các liên kết trong footer sau này */
  .footer-after-login a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
  }
  
  .footer-after-login a:hover {
    color: #ffed61; /* Màu vàng khi hover để tạo sự tương phản */
  }
  
  /* Responsive cho Footer */
  @media (max-width: 768px) {
    .footer-after-login {
      padding: 15px 0; /* Giảm padding cho các thiết bị di động */
    }
  
    .footer-bottom p {
      font-size: 1.2rem; /* Giảm cỡ chữ trên các thiết bị di động */
    }
  }
  </style>
  
