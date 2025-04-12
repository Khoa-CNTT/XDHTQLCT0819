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
  .footer-bottom p {
    background-color: #0ea5e9;
    color: #ffffff;
    padding: 1rem;
  }
  </style>