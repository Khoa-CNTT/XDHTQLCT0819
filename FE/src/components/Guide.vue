<template>
  <section id="guide" class="section">
    <h2 class="section-title">Hướng dẫn sử dụng</h2>
    <ol class="guide-steps">
      <li class="guide-step">
        <div>
          <h3>Đăng ký tài khoản</h3>
          <p>Tạo tài khoản mới bằng cách cung cấp thông tin cơ bản.</p>
        </div>
      </li>
      <li class="guide-step">
        <div>
          <h3>Thiết lập ngân sách</h3>
          <p>Xác định các danh mục chi tiêu và đặt giới hạn cho mỗi danh mục.</p>
        </div>
      </li>
      <li class="guide-step">
        <div>
          <h3>Ghi chép chi tiêu hàng ngày</h3>
          <p>Nhập các khoản chi tiêu của bạn và phân loại chúng.</p>
        </div>
      </li>
      <li class="guide-step">
        <div>
          <h3>Theo dõi và điều chỉnh</h3>
          <p>Xem báo cáo, phân tích chi tiêu và điều chỉnh ngân sách nếu cần.</p>
        </div>
      </li>
    </ol>
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
html, body {
  height: 200%;
  margin: 0;
  padding: 0;
  width: 100%;
  background-color: aliceblue;
}

.section {
  padding-top: 8rem;
  padding-left: 6rem;
  padding-right: 6rem;
  background-color: aliceblue;
  min-height: 100vh;
}

.section-title {
  font-size: 2rem;
  font-weight: bold;
  color: #0ea5e9;
  margin-bottom: 4rem;
  text-align: center;
}

.guide-steps {
  counter-reset: step;
  list-style-type: none;
  padding: 0;
  display: grid;
  gap: 1.5rem;
}

.guide-step {
  position: relative;
  padding: 1.5rem;
  background-color: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 1rem;
}

.guide-step:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.guide-step::before {
  counter-increment: step;
  content: counter(step);
  width: 2.5rem;
  height: 2.5rem;
  background-color: #0ea5e9;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.guide-step > div {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.guide-step h3 {
  font-size: 1.25rem;
  color: #0ea5e9;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.guide-step p {
  color: #6b7280;
  margin: 0;
  font-size: 1rem;
}

/* ===== Responsive Styles ===== */
@media (max-width: 768px) {
  .section {
    padding-left: 2rem;
    padding-right: 2rem;
    padding-top: 6rem;
  }

  .guide-step {
    flex-direction: column;
    align-items: flex-start;
  }

  .guide-step::before {
    margin-bottom: 0.5rem;
  }

  .guide-step h3 {
    font-size: 1.1rem;
  }

  .guide-step p {
    font-size: 0.95rem;
  }
}
</style>
