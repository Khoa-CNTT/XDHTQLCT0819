<template>
    <section id="register" class="section">
      <div class="auth-wrapper">
        <div class="modal-header">
        <img
          src="https://cdn-resize-img.vietcetera.com/_next/image?url=https%3A%2F%2Fimg.vietcetera.com%2Fuploads%2Fimages%2F28-dec-2021%2Finfina-feature-1640679969588.jpg&q=80&w=1536"
          alt="Logo"
          class="modal-logo"
        />
        <h2>Đăng kí</h2>
    </div>
        <form class="auth-form" @submit.prevent="handleRegister">
          <div class="form-group">
            <label for="register-name">Họ và tên</label>
            <input
              type="text"
              id="register-name"
              v-model="name"
              required
              placeholder="Nhập họ và tên"
            />
          </div>
          <div class="form-group">
            <label for="register-email">Email</label>
            <input
              type="email"
              id="register-email"
              v-model="email"
              required
              placeholder="Nhập email"
            />
          </div>
          <div class="form-group password-group">
            <label for="register-password">Mật khẩu</label>
            <div class="password-wrapper">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="register-password"
                v-model="password"
                required
                placeholder="Nhập mật khẩu"
              />
              <span class="toggle-password" @click="togglePassword">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </span>
            </div>
          </div>
          <div class="form-group password-group">
            <label for="register-confirm-password">Xác nhận mật khẩu</label>
            <div class="password-wrapper">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                id="register-confirm-password"
                v-model="confirmPassword"
                required
                placeholder="Xác nhận mật khẩu"
              />
              <span class="toggle-password" @click="toggleConfirmPassword">
                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </span>
            </div>
          </div>
          <button type="submit" class="btn-primary">Đăng ký</button>
        </form>
        <div class="auth-links">
          <p>
            Bạn đã có tài khoản? <router-link to="/login">Đăng nhập ngay</router-link>
          </p>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  export default {
    name: "RegisterPage",
    data() {
      return {
        name: "",
        email: "",
        password: "",
        confirmPassword: "",
        showPassword: false, // Trạng thái ẩn/hiện mật khẩu
        showConfirmPassword: false, // Trạng thái ẩn/hiện xác nhận mật khẩu
      };
    },
    methods: {
      handleRegister() {
        if (this.password !== this.confirmPassword) {
          alert("Mật khẩu xác nhận không khớp!");
          return;
        }
        console.log("Đăng ký với:", this.name, this.email, this.password);
      },
      togglePassword() {
        this.showPassword = !this.showPassword;
      },
      toggleConfirmPassword() {
        this.showConfirmPassword = !this.showConfirmPassword;
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
 

.auth-wrapper-image {
  width: 50px; /* Điều chỉnh kích thước hình ảnh nhỏ */
  height: 50px;
  object-fit: contain;
  margin-bottom: 1rem; /* Khoảng cách dưới hình ảnh */
}
  
  /* Form đăng ký */
  .auth-form {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
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
  
  /* Nút ẩn/hiện mật khẩu */
  .password-group {
    position: relative;
  }
  
  .password-wrapper {
    position: relative;
  }
  
  .toggle-password {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6b7280;
    font-size: 1rem;
  }
  
  .toggle-password:hover {
    color: #0ea5e9;
  }
  
  /* Nút đăng ký */
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