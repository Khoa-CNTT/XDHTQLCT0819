<template>
  <section id="contact" class="section">
    <h2 class="section-title">Liên hệ với chúng tôi</h2>
    <div class="contact-wrapper">
      <form class="contact-form" @submit.prevent="submitContact">
        <div class="form-group">
          <label for="contact-name">Họ và tên</label>
          <input
            type="text"
            id="contact-name"
            v-model="form.name"
            required
            placeholder="Nhập họ và tên"
          />
        </div>
        <div class="form-group">
          <label for="contact-email">Email</label>
          <input
            type="email"
            id="contact-email"
            v-model="form.email"
            required
            placeholder="Nhập email"
          />
        </div>
        <div class="form-group">
          <label for="contact-message">Nội dung</label>
          <textarea
            id="contact-message"
            rows="5"
            v-model="form.description"
            required
            placeholder="Nhập nội dung liên hệ"
          ></textarea>
        </div>
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? "Đang gửi..." : "Gửi liên hệ" }}
        </button>
      </form>

      <div class="contact-info">
        <!-- ... phần thông tin liên hệ và mạng xã hội giữ nguyên ... -->
        <h3>Thông tin liên hệ</h3>
        <p>Nếu bạn có bất kỳ câu hỏi nào, hãy liên hệ với chúng tôi qua:</p>
        <ul>
          <li>
            <span class="contact-icon">📧</span>
            Email: <a href="mailto:support@saveup.com">support@saveup.com</a>
          </li>
          <li>
            <span class="contact-icon">📞</span>
            Hotline: <a href="tel:0983057130">0983057130</a>
          </li>
          <li>
            <span class="contact-icon">🏠</span>
            Địa chỉ: 117 Đường Phạm Nhữ Tăng, Quận Thanh Khê, TP.Đà Nẵng
          </li>
        </ul>
        <!-- Liên kết mạng xã hội -->
        <div class="social-links">
          <h4>Kết nối với chúng tôi</h4>
          <div class="social-icons">
            <a
              href="https://www.facebook.com/profile.php?id=100075416786651"
              class="social-icon"
              target="_blank"
              rel="noopener"
              ><i class="fab fa-facebook-f"></i
            ></a>
            <a
              href="https://x.com/home?lang=vi"
              class="social-icon"
              target="_blank"
              rel="noopener"
              ><i class="fab fa-twitter"></i
            ></a>
            <a
              href="https://www.instagram.com/vanmanhit.03/"
              class="social-icon"
              target="_blank"
              rel="noopener"
              ><i class="fab fa-instagram"></i
            ></a>
            <a
              href="https://www.instagram.com/vanmanhit.03/"
              class="social-icon"
              target="_blank"
              rel="noopener"
              ><i class="fab fa-linkedin-in"></i
            ></a>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA -->
    <div class="cta-section">
      <h3>Khám phá thêm về SaveUp</h3>
      <p>Quay lại trang chủ để tìm hiểu các tính năng nổi bật của chúng tôi.</p>
      <a href="#home" class="btn-primary">Về trang chủ</a>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";
export default {
  name: "ContactSection",
  data() {
    return {
      form: {
        name: "",
        email: "",
        description: "",
      },
      loading: false,
    };
  },
  methods: {
    async submitContact() {
      const toast = useToast();
      this.loading = true;
      try {
        const response = await axios.post("/api/contact", this.form);
        toast.success(response.data.message);
        this.form.name = "";
        this.form.email = "";
        this.form.description = "";
      } catch (error) {
        toast.error(error.response.data.message);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
<style scoped>
/* Đảm bảo phần tử cha không bị ảnh hưởng bởi style toàn cục */
html,
body {
  margin: 0;
  padding: 0;
  width: 100%;
  background-color: aliceblue;
}

/* Phần .section */
.section {
  padding-top: 8rem;
  background-color: aliceblue;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-bottom: 4rem;
}

.section-title {
  font-size: 2rem;
  font-weight: bold;
  color: #0ea5e9; /* Blue title as requested */
  margin-bottom: 4rem;
  text-align: center;
}

/* Wrapper liên hệ */
.contact-wrapper {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  background-color: #ffffff;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 1400px;
  margin-bottom: 4rem;
}

/* Form liên hệ */
.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.contact-form .form-group {
  margin-bottom: 1rem;
}

.contact-form label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #374151;
}

.contact-form input,
.contact-form textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.contact-form input:focus,
.contact-form textarea:focus {
  outline: none;
  border-color: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
}

.contact-form textarea {
  resize: vertical;
  min-height: 120px;
}

.contact-form button {
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

.contact-form button:hover {
  background-color: #0284c7;
  transform: scale(1.05);
}

/* Thông tin liên hệ */
.contact-info {
  background-color: #f9fafb;
  padding: 1.5rem;
  border-radius: 0.75rem;
}

.contact-info h3 {
  font-size: 1.5rem;
  color: #0ea5e9;
  margin-bottom: 1rem;
}

.contact-info p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.contact-info ul {
  list-style-type: none;
  padding: 0;
}

.contact-info li {
  margin-bottom: 1rem;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.contact-icon {
  font-size: 1.25rem;
  color: #0ea5e9;
}

.contact-info a {
  color: #0ea5e9;
  text-decoration: none;
  font-weight: bold;
}

.contact-info a:hover {
  text-decoration: underline;
}

/* Liên kết mạng xã hội */
.social-links {
  margin-top: 2rem;
  text-align: center;
}

.social-links h4 {
  font-size: 1.25rem;
  color: #1f2937;
  margin-bottom: 1rem;
}

.social-icons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.social-icon {
  font-size: 1.5rem;
  color: #0ea5e9;
  transition: all 0.3s ease;
}

.social-icon:hover {
  color: #0284c7;
  transform: translateY(-3px);
}

/* Phần CTA */
.cta-section {
  text-align: center;
  margin-bottom: 4rem;
  background-color: #e0f2fe;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 1500px;
}

.cta-section h3 {
  font-size: 1.75rem;
  color: #1f2937;
  margin-bottom: 1rem;
}

.cta-section p {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.btn-primary {
  padding: 0.75rem 2rem;
  background-color: #0ea5e9;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}

.btn-primary:hover {
  background-color: #0284c7;
  transform: scale(1.05);
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

  .contact-wrapper {
    grid-template-columns: 1fr;
    padding: 1.5rem;
  }

  .contact-form input,
  .contact-form textarea {
    font-size: 0.9rem;
  }

  .contact-form button {
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
  }

  .contact-info h3 {
    font-size: 1.25rem;
  }

  .contact-info p,
  .contact-info li {
    font-size: 0.9rem;
  }

  .cta-section {
    padding: 1.5rem;
  }

  .cta-section h3 {
    font-size: 1.5rem;
  }

  .cta-section p {
    font-size: 0.9rem;
  }
}
</style>
