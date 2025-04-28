<template>
    <section id="forgot-password" class="section">
      <div class="auth-wrapper">
        <div class="modal-header">
        <img
          src="https://cdn-resize-img.vietcetera.com/_next/image?url=https%3A%2F%2Fimg.vietcetera.com%2Fuploads%2Fimages%2F28-dec-2021%2Finfina-feature-1640679969588.jpg&q=80&w=1536"
          alt="Logo"
          class="modal-logo"
        />
        <h2>Quên mật khẩu</h2>
      </div>
        <form class="auth-form" @submit.prevent="handleForgotPassword">
          <div class="form-group">
            <label for="forgot-email">Email</label>
            <input
              type="email"
              id="forgot-email"
              v-model="email"
              required
              placeholder="Nhập email của bạn"
            />
          </div>
          <button type="submit" class="btn-primary">Gửi yêu cầu</button>
        </form>
        <div class="auth-links">
          <p>
            Quay lại <router-link to="/login">Đăng nhập ngay</router-link>
          </p>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  import axios from "axios";
  import { useToast } from "vue-toastification";
  
  export default {
    name: "ForgotPasswordPage",
    data() {
      return {
        email: "",
      };
    },
    methods: {
      async handleForgotPassword() {
        const toast = useToast();  // ✅ Gọi ở đây, không cần this.$toast

        try {
          const response = await axios.post("http://localhost:8000/api/forgot-password", {
            email: this.email,
          });
  
          toast.success("🎉 Yêu cầu đã được gửi! Vui lòng kiểm tra email của bạn.");
          // this.$router.push('/login');
        } catch (error) {
          const errorMsg = error.response?.data?.message || "❌ Gửi yêu cầu thất bại!";
          toast.error(errorMsg);
        }
      },
    },
  };
  </script>
  
  
  <style scoped>
  /* Đảm bảo phần tử cha không bị ảnh hưởng bởi style toàn cục */
  html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  background-color: #f0f2f5;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Phần section */
.section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f0f2f5;
  background-image: url('/src/assets/dn.jpg');
}
.section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 249, 249, 0.3);
    backdrop-filter: blur(12px);
    z-index: 1;
  }
  .section > * {
    position: relative;
    z-index: 2;
  }
/* Wrapper chứa form */
.auth-wrapper {
  background-color: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  margin-bottom: 2rem;
}

/* Modal header với logo và tiêu đề */
.modal-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h2 {
  color: #0ea5e9;
  font-size: 1.75rem;
  font-weight: 600;
  margin-top: 0.5rem;
  text-transform: uppercase;
}

.modal-logo {
  width: 120px;
  height: auto;
  margin-bottom: 0.5rem;
}
  
  /* Form quên mật khẩu */
  .auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .auth-form .form-group {
    margin-bottom: 1rem;
  }
  
  .auth-form label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
  }
  
  .auth-form input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
  }
  
  .auth-form input:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
  }
  
  .auth-form button {
    width: auto;
    padding: 0.75rem 2rem;
    margin: 0 auto;
    background-color: #0ea5e9;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .auth-form button:hover {
    background-color: #0284c7;
    transform: scale(1.05);
  }
  
  /* Liên kết */
  .auth-links {
    text-align: center;
    margin-top: 1.5rem;
  }
  
  .auth-links p {
    color: #6b7280;
    margin-bottom: 0.5rem;
  }
  
  .auth-links a {
    color: #0ea5e9;
    text-decoration: none;
    font-weight: bold;
  }
  
  .auth-links a:hover {
    text-decoration: underline;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .section {
      padding-top: 6rem;
      min-height: 80vh;
    }
  
    .section-title {
      font-size: 2rem;
    }
  
    .auth-wrapper {
      padding: 1.5rem;
      max-width: 90%;
    }
  
    .auth-form input {
      font-size: 0.9rem;
    }
  
    .auth-form button {
      padding: 0.5rem 1.5rem;
      font-size: 0.9rem;
    }
  }
  </style>
