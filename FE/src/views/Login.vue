<template>
  <section id="login" class="section">
    <div class="auth-wrapper">
      <div class="modal-header">
        <img
          src="https://cdn-resize-img.vietcetera.com/_next/image?url=https%3A%2F%2Fimg.vietcetera.com%2Fuploads%2Fimages%2F28-dec-2021%2Finfina-feature-1640679969588.jpg&q=80&w=1536"
          alt="Logo"
          class="modal-logo"
        />
        <h2>Đăng nhập</h2>
      </div>
      <form class="auth-form" @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="login-email">Email</label>
          <input
            type="email"
            id="login-email"
            v-model="email"
            required
            placeholder="Nhập email"
          />
        </div>
        <div class="form-group password-group">
          <label for="login-password">Mật khẩu</label>
          <div class="password-wrapper">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="login-password"
              v-model="password"
              required
              placeholder="Nhập mật khẩu"
            />
            <span class="toggle-password" @click="togglePassword">
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </span>
          </div>
        </div>
        <div class="auth-links">
          <p><router-link to="/forgot-password">Quên mật khẩu?</router-link></p>  
        </div>
        <button type="submit" class="btn-primary">Đăng nhập</button>
        <div class="auth-links">
          <p>Chưa có tài khoản? <router-link to="/sign-up">Đăng ký ngay</router-link></p>   
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
  name: "LoginPage",
  data() {
    return {
      email: "",
      password: "",
      showPassword: false,
    };
  },
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },
    async handleLogin() {
      const toast = useToast();
      if (!this.email || !this.password) {
        toast.warning("⚠️ Vui lòng nhập đầy đủ email và mật khẩu!");
        return;
      }
      try {
        const response = await axios.post("http://localhost:8000/api/login", {
          email: this.email,
          password: this.password,
        });

        const data = response.data;

        localStorage.setItem("auth_token", data.token);
        localStorage.setItem("user", JSON.stringify(data.user));

        toast.success("🎉 Đăng nhập thành công!");
        this.$router.push("/quan-li-chi-tieu");
      } catch (error) {
        const errorMsg = error.response?.data?.error || "❌ Đăng nhập thất bại! Vui lòng kiểm tra lại thông tin.";
        toast.error(errorMsg);
        console.error(error);
      }
    },
  },
};
</script>



<style scoped>
/* Reset cơ bản */
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

/* Form đăng nhập */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.auth-form .form-group {
  margin-bottom: 1rem;
}

.auth-form label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #374151;
  font-size: 0.95rem;
}

.auth-form input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s ease;
  background-color: #f9fafb;
}

.auth-form input:focus {
  outline: none;
  border-color: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
  background-color: white;
}

/* Checkbox "Ghi nhớ mật khẩu" */
.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.remember-me label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #374151;
  cursor: pointer;
}

.remember-me input {
  width: auto;
  accent-color: #0ea5e9;
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

/* Nút đăng nhập */
.auth-form button {
  width: 100%;
  padding: 0.75rem;
  background-color: #0ea5e9;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.auth-form button:hover {
  background-color: #0284c7;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Liên kết */
.auth-links {
  text-align: center;
  margin-top: 1.5rem;
}

.auth-links p {
  color: #6b7280;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.auth-links a {
  color: #0ea5e9;
  text-decoration: none;
  font-weight: 600;
}

.auth-links a:hover {
  text-decoration: underline;
  color: #0284c7;
}

/* Responsive */
@media (max-width: 768px) {
  .section {
    padding: 1.5rem;
  }

  .auth-wrapper {
    padding: 1.5rem;
    max-width: 90%;
  }

  .modal-header h2 {
    font-size: 1.5rem;
  }

  .modal-logo {
    width: 100px;
  }

  .auth-form input {
    font-size: 0.9rem;
  }

  .auth-form button {
    font-size: 0.9rem;
  }
}
</style>
