<template>
  <section id="register" class="section">
    <div class="auth-wrapper">
      <div class="modal-header">
        <img
          src="https://cdn-resize-img.vietcetera.com/_next/image?url=https%3A%2F%2Fimg.vietcetera.com%2Fuploads%2Fimages%2F28-dec-2021%2Finfina-feature-1640679969588.jpg&q=80&w=1536"
          alt="Logo"
          class="modal-logo"
        />
        <h2>Đăng ký</h2>
      </div>

      <form class="auth-form" @submit.prevent="handleRegister">
        <div class="form-row">
          <div class="form-group">
            <label for="username">Tên đăng nhập</label>
            <input
              type="text"
              id="username"
              v-model="form.username"
              required
              placeholder="Tên đăng nhập"
            />
          </div>
          <div class="form-group">
            <label for="fullName">Họ và tên</label>
            <input
              type="text"
              id="fullName"
              v-model="form.fullName"
              required
              placeholder="Họ và tên"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              required
              placeholder="Email"
            />
          </div>
          <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input
              type="text"
              id="phone"
              v-model="form.phone"
              placeholder="Số điện thoại"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="address">Địa chỉ</label>
            <input
              type="text"
              id="address"
              v-model="form.address"
              placeholder="Địa chỉ"
            />
          </div>
          <div class="form-group password-group">
            <label for="password">Mật khẩu</label>
            <div class="password-wrapper">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="form.password"
                required
                placeholder="Mật khẩu"
              />
              <span class="toggle-password" @click="togglePassword">
                <i
                  :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
                ></i>
              </span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group password-group" style="width: 100%">
            <label for="confirmPassword">Xác nhận mật khẩu</label>
            <div class="password-wrapper">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                id="confirmPassword"
                v-model="confirmPassword"
                required
                placeholder="Xác nhận mật khẩu"
              />
              <span class="toggle-password" @click="toggleConfirmPassword">
                <i
                  :class="
                    showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'
                  "
                ></i>
              </span>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-primary">Đăng ký</button>
      </form>

      <div class="auth-links">
        <p>
          Bạn đã có tài khoản?
          <router-link to="/login">Đăng nhập ngay</router-link>
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

export default {
  name: "RegisterPage",
  data() {
    return {
      form: {
        username: "",
        fullName: "",
        email: "",
        password: "",
        phone: "",
        address: "",
      },
      confirmPassword: "",
      showPassword: false,
      showConfirmPassword: false,
      toast: null,
    };
  },
  created() {
    this.toast = useToast();
  },
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },
    toggleConfirmPassword() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },
    async handleRegister() {
      if (this.form.password !== this.confirmPassword) {
        this.toast.error("❌ Mật khẩu xác nhận không khớp!");
        return;
      }

      try {
        const response = await axios.post(
          "http://localhost:8000/api/register",
          this.form,
          {
            headers: {
              "Content-Type": "application/json",
            },
          }
        );

        this.toast.success(
          "🎉 Đăng ký thành công! Vui lòng kiểm tra email để kích hoạt tài khoản."
        );
        this.$router.push("/login");
      } catch (error) {
        if (error.response && error.response.data) {
          const data = error.response.data;

          const errorMsg = data.error
            ? Object.values(data.error).flat().join("\n")
            : data.message || "❌ Đăng ký thất bại!";
          this.toast.error(errorMsg);
        } else {
          this.toast.error("⚠️ Lỗi kết nối máy chủ!");
        }
      }
    },
  },
};
</script>

<style scoped>
html,
body {
  margin: 0;
  padding: 0;
  width: 100%;
  background-color: #f0f2f5;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  font-size: 16px;
}

.section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f0f2f5;
  background-image: url("/src/assets/dn.jpg");
  min-height: 100vh;
  padding: 3rem 1rem;
}
.section::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(255, 249, 249, 0.3);
  backdrop-filter: blur(10px);
  z-index: 1;
}
.section > * {
  position: relative;
  z-index: 2;
}

.auth-wrapper {
  background-color: white;
  padding: 3rem;
  border-radius: 1.5rem;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 650px;
  margin-bottom: 2rem;
}

.modal-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  color: #0ea5e9;
  font-size: 2rem;
  font-weight: 700;
  margin-top: 0.5rem;
  text-transform: uppercase;
}

.modal-logo {
  width: 140px;
  height: auto;
  margin-bottom: 1rem;
}

.auth-wrapper-image {
  width: 60px;
  height: 60px;
  object-fit: contain;
  margin-bottom: 1rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.auth-form .form-group {
  margin-bottom: 1.2rem;
}

.auth-form label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #374151;
  font-size: 1.05rem;
}

.auth-form input {
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  font-size: 1.05rem;
  transition: all 0.3s ease;
}

.auth-form input:focus {
  outline: none;
  border-color: #0ea5e9;
  box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.2);
}

.auth-form .form-row {
  display: flex;
  gap: 1.2rem;
  justify-content: space-between;
  flex-wrap: wrap;
}

.auth-form .form-row .form-group {
  flex: 1;
  min-width: calc(50% - 0.6rem);
}

.password-group {
  position: relative;
}

.password-wrapper {
  position: relative;
}

.toggle-password {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #6b7280;
  font-size: 1.1rem;
}

.toggle-password:hover {
  color: #0ea5e9;
}

.auth-form button {
  width: auto;
  padding: 0.9rem 2.5rem;
  margin: 0 auto;
  background-color: #0ea5e9;
  color: white;
  border: none;
  border-radius: 0.6rem;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.auth-form button:hover {
  background-color: #0284c7;
  transform: scale(1.05);
}

.auth-links {
  text-align: center;
  margin-top: 2rem;
}

.auth-links p {
  color: #6b7280;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.auth-links a {
  color: #0ea5e9;
  text-decoration: none;
  font-weight: bold;
}

.auth-links a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .section {
    padding-top: 5rem;
  }

  .auth-wrapper {
    padding: 2rem;
    max-width: 90%;
  }

  .auth-form input {
    font-size: 1rem;
  }

  .auth-form button {
    padding: 0.7rem 2rem;
    font-size: 1rem;
  }

  .auth-form .form-row {
    flex-direction: column;
  }

  .auth-form .form-row .form-group {
    min-width: 100%;
  }
}
</style>
