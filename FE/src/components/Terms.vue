<template>
    <section id="terms" class="section">
      <h2 class="section-title">Điều khoản sử dụng</h2>
      <div class="terms-content">
        <!-- Mục 1 -->
        <div class="terms-section">
          <div class="terms-header">
            <span class="terms-icon">📜</span>
            <h3>1. Quyền sở hữu và sử dụng</h3>
          </div>
          <ul>
            <li>Chỉ sử dụng ứng dụng cho mục đích cá nhân, không sử dụng cho mục đích thương mại.</li>
            <li>Tuân thủ các quy định pháp luật hiện hành khi sử dụng ứng dụng.</li>
            <li>Không sử dụng ứng dụng để thực hiện các hành vi bất hợp pháp hoặc gây hại cho bên thứ ba.</li>
          </ul>
        </div>
  
        <!-- Mục 2 -->
        <div class="terms-section">
          <div class="terms-header">
            <span class="terms-icon">🔒</span>
            <h3>2. Bảo mật thông tin</h3>
          </div>
          <ul>
            <li>Thông tin cá nhân được mã hóa và lưu trữ trên các máy chủ an toàn.</li>
            <li>Người dùng có quyền yêu cầu xem, chỉnh sửa hoặc xóa dữ liệu cá nhân của mình.</li>
            <li>Chúng tôi không chịu trách nhiệm nếu thông tin bị rò rỉ do lỗi từ phía người dùng (ví dụ: chia sẻ mật khẩu).</li>
          </ul>
        </div>
  
        <!-- Mục 3 -->
        <div class="terms-section">
          <div class="terms-header">
            <span class="terms-icon">👤</span>
            <h3>3. Trách nhiệm người dùng</h3>
          </div>
          <ul>
            <li>Không sử dụng ứng dụng để thực hiện các hành vi vi phạm pháp luật.</li>
            <li>Thông báo ngay cho chúng tôi nếu phát hiện lỗi hoặc vấn đề bảo mật trong ứng dụng.</li>
            <li>Người dùng chịu trách nhiệm bảo mật tài khoản và mật khẩu của mình.</li>
          </ul>
        </div>
      </div>
  
      <!-- Phần FAQ -->
      <div class="faq-section">
        <h3 class="faq-title">Câu hỏi thường gặp</h3>
        <div class="faq-item">
          <h4>Tôi có thể xóa tài khoản của mình không?</h4>
          <p>Có, bạn có thể xóa tài khoản bất kỳ lúc nào bằng cách liên hệ với chúng tôi qua email <a href="mailto:support@saveup.com">support@saveup.com</a>.</p>
        </div>
        <div class="faq-item">
          <h4>Thông tin của tôi có được bảo mật không?</h4>
          <p>Chúng tôi sử dụng công nghệ mã hóa tiên tiến để bảo vệ thông tin của bạn. Xem chi tiết trong phần "Bảo mật thông tin".</p>
        </div>
        <div class="faq-item">
          <h4>Tôi có thể sử dụng SaveUp cho mục đích thương mại không?</h4>
          <p>Không, SaveUp chỉ được sử dụng cho mục đích cá nhân. Vui lòng liên hệ với chúng tôi nếu bạn có nhu cầu sử dụng thương mại.</p>
        </div>
      </div>
  
      <!-- Phần CTA -->
      <div class="cta-section">
        <h3>Có thắc mắc về điều khoản?</h3>
        <p>Liên hệ với chúng tôi để được giải đáp chi tiết.</p>
        <a href="#contact" class="btn-primary">Liên hệ ngay</a>
      </div>
  
      <!-- Phần ngày cập nhật -->
      <div class="update-date">
        <p>Điều khoản được cập nhật lần cuối: 20/03/2025</p>
      </div>
    </section>
  </template>

<script>
export default {
  name: "HelloWorld",
  props: {
    msg: String,
  },
};
</script>

<style scoped>
/* Đảm bảo phần tử cha không bị ảnh hưởng bởi style toàn cục */
html, body {
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

/* Nội dung điều khoản */
.terms-content {
  background-color: #ffffff;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 1400px;
  margin-bottom: 4rem;
}

.terms-section {
  margin-bottom: 2rem;
}

.terms-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.terms-icon {
  font-size: 2rem;
  margin-right: 1rem;
  color: #0ea5e9;
}

.terms-section h3 {
  font-size: 1.5rem;
  color: #0ea5e9;
  font-weight: bold;
}

.terms-section p {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.terms-section ul {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1rem;
}

.terms-section ul li {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

/* Phần FAQ */
.faq-section {
  width: 100%;
  max-width: 1400px;
  margin-bottom: 4rem;
}

.faq-title {
  font-size: 2rem;
  color: #0ea5e9;
  margin-bottom: 2rem;
  text-align: center;
  font-weight: bold;
}

.faq-item {
  background-color: #ffffff;
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.faq-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.faq-item h4 {
  font-size: 1.25rem;
  color: #1f2937;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.faq-item p {
  font-size: 1rem;
  color: #6b7280;
  line-height: 1.6;
}

.faq-item a {
  color: #0ea5e9;
  text-decoration: none;
  font-weight: bold;
}

.faq-item a:hover {
  text-decoration: underline;
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

/* Phần ngày cập nhật */
.update-date {
  text-align: center;
  margin-top: 2rem;
}

.update-date p {
  font-size: 0.9rem;
  color: #6b7280;
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

  .terms-content {
    padding: 1.5rem;
  }

  .terms-section h3 {
    font-size: 1.25rem;
  }

  .terms-section p,
  .terms-section ul li {
    font-size: 0.9rem;
  }

  .faq-title {
    font-size: 1.75rem;
  }

  .faq-item {
    padding: 1rem;
  }

  .faq-item h4 {
    font-size: 1.1rem;
  }

  .faq-item p {
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

  .btn-primary {
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
  }
}
</style>
