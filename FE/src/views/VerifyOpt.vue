<template>
  <section id="verify-otp" class="section">
    <div class="auth-wrapper">
      <div class="modal-header">
        <img
          src="https://cdn-resize-img.vietcetera.com/_next/image?url=https%3A%2F%2Fimg.vietcetera.com%2Fuploads%2Fimages%2F28-dec-2021%2Finfina-feature-1640679969588.jpg&q=80&w=1536"
          alt="Logo"
          class="modal-logo"
        />
        <h2>Nhập mã xác thực</h2>
      </div>
      <form class="auth-form" @submit.prevent="handleVerifyOtp">
        <input type="hidden" v-model="email" />

        <div class="form-group">
          <div class="otp-inputs">
            <input
              v-for="(digit, index) in otpDigits"
              :key="index"
              ref="otpBoxes"
              type="text"
              inputmode="numeric"
              maxlength="1"
              class="otp-box"
              v-model="otpDigits[index]"
              @input="focusNext(index, $event)"
              @keydown.backspace="focusPrev(index, $event)"
            />
          </div>
        </div>

        <button type="submit" class="btn-primary">Xác nhận</button>
      </form>
      <div class="auth-links">
        <p>
          Chưa nhận được mã?
          <router-link to="/forgot-password">Gửi lại</router-link>
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

export default {
  name: "VerifyOtpPage",
  data() {
    return {
      email: "",
      otpDigits: Array(6).fill(""),
    };
  },
  mounted() {
    const storedEmail = sessionStorage.getItem("forgotEmail");
    if (!storedEmail) {
      this.$router.push("/forgot-password");
    } else {
      this.email = storedEmail;
    }
  },
  methods: {
    focusNext(index, event) {
      const val = event.target.value;
      if (val && index < 5) {
        this.$refs.otpBoxes[index + 1]?.focus();
      }
    },
    focusPrev(index, event) {
      if (!event.target.value && index > 0) {
        this.$refs.otpBoxes[index - 1]?.focus();
      }
    },
    async handleVerifyOtp() {
      const toast = useToast();
      const otp = this.otpDigits.join("");

      if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
        toast.error("Mã OTP không hợp lệ. Vui lòng nhập đủ 6 chữ số.");
        return;
      }

      try {
        const res = await axios.post("/api/verify-otp", {
          email: this.email,
          otp: otp,
        });
        toast.success(res.data.message);
        sessionStorage.setItem("resetEmail", this.email);
        sessionStorage.setItem("resetToken", otp);
        this.$router.push({
          path: "/reset-password",
        });
      } catch (error) {
        toast.error(error.response?.data?.message || "Xác thực thất bại.");
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
}

.section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f0f2f5;
  background-image: url("/src/assets/dn.jpg");
  position: relative;
  min-height: 100vh;
}

.section::before {
  content: "";
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

.auth-wrapper {
  background-color: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  margin-bottom: 2rem;
}

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

.otp-inputs {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.otp-box {
  width: 48px;
  height: 48px;
  font-size: 1.5rem;
  text-align: center;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
}

.otp-box:focus {
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

@media (max-width: 768px) {
  .section {
    padding-top: 6rem;
    min-height: 80vh;
  }

  .auth-wrapper {
    padding: 1.5rem;
    max-width: 90%;
  }

  .otp-box {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
  }

  .auth-form button {
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
  }
}
</style>
