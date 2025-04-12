<template>
  <section id="features" class="section">
    <h2 class="section-title">Tính năng nổi bật</h2>
    <div class="features-list">
      <!-- Hàng 1 -->
      <div class="feature-item">
        <div class="feature-icon">📊</div>
        <h3 class="feature-title">Theo dõi chi tiêu</h3>
        <p class="feature-description">Ghi chép và phân loại các khoản chi tiêu hàng ngày.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🎯</div>
        <h3 class="feature-title">Đặt mục tiêu tài chính</h3>
        <p class="feature-description">Thiết lập và theo dõi các mục tiêu tiết kiệm.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📅</div>
        <h3 class="feature-title">Lập kế hoạch ngân sách</h3>
        <p class="feature-description">Tạo và quản lý ngân sách cho các danh mục chi tiêu.</p>
      </div>

      <!-- Hàng 2 -->
      <div class="feature-item">
        <div class="feature-icon">📈</div>
        <h3 class="feature-title">Báo cáo và phân tích</h3>
        <p class="feature-description">Xem báo cáo chi tiết và biểu đồ phân tích chi tiêu.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🔔</div>
        <h3 class="feature-title">Thông báo</h3>
        <p class="feature-description">Nhận thông báo về chi tiêu vượt ngân sách hoặc nhắc nhở tiết kiệm.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🤖</div>
        <h3 class="feature-title">AI hỗ trợ</h3>
        <p class="feature-description">AI gợi ý kế hoạch chi tiêu và trả lời câu hỏi tài chính.</p>
      </div>
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
  min-height: 100vh;
  background-color: aliceblue;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.section-title {
  font-size: 2rem;
  font-weight: bold;
  color: #0ea5e9; /* Blue title as requested */
  margin-bottom: 4rem;
  text-align: center;
}

/* Danh sách tính năng */
.features-list {
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* 3 cột bằng nhau */
  grid-template-rows: repeat(2, auto); /* 2 hàng */
  gap: 2.5rem; /* Tăng khoảng cách giữa các thẻ */
  width: 100%;
  max-width: 1200px; /* Giới hạn chiều rộng tối đa */
}

/* Thẻ tính năng */
.feature-item {
  background-color: #ffffff;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.feature-item:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

/* Biểu tượng */
.feature-icon {
  font-size: 2.5rem;
  margin-bottom: 1.5rem;
  background-color: #e0f2fe; /* Nền màu xanh nhạt */
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #0ea5e9;
}

/* Tiêu đề tính năng */
.feature-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: #1f2937;
}

/* Mô tả tính năng */
.feature-description {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 1024px) {
  .features-list {
    grid-template-columns: repeat(2, 1fr); /* 2 cột trên tablet */
    grid-template-rows: repeat(3, auto); /* 3 hàng */
  }

  .section-title {
    font-size: 2rem;
  }
}

@media (max-width: 768px) {
  .section {
    padding-top: 6rem;
    min-height: 80vh;
  }

  .section-title {
    font-size: 1.75rem;
    margin-bottom: 3rem;
  }

  .features-list {
    grid-template-columns: 1fr; /* 1 cột trên mobile */
    grid-template-rows: repeat(6, auto); /* 6 hàng */
    gap: 1.5rem;
  }

  .feature-item {
    padding: 1.5rem;
  }

  .feature-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
  }

  .feature-title {
    font-size: 1.25rem;
  }

  .feature-description {
    font-size: 0.9rem;
  }
}
</style>
