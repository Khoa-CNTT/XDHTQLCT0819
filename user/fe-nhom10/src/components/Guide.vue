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
    background-color: aliceblue; /* Background color from the first image */
  }
  
  /* Phần .section */
  .section {
    padding-top: 8rem;
    padding-left: 6rem;
    padding-right: 6rem;
    background-color: aliceblue;
    min-height: 100vh;
  }
  
  /* Title styling */
  .section-title {
    font-size: 2rem;
    font-weight: bold;
    color: #0ea5e9; /* Blue title as requested */
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
    display: flex; /* Use flex to align items horizontally */
    flex-direction: row; /* Align items in a row */
    align-items: center; /* Vertically center the items */
    gap: 1rem; /* Space between the number, title, and description */
  }
  
  /* Hover effect */
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
    flex-shrink: 0; /* Prevent the circle from shrinking */
  }
  
  /* Container for title and description */
  .guide-step > div {
    display: flex;
    flex-direction: column; /* Stack title and description vertically within this container */
    flex: 1; /* Allow this container to take up remaining space */
  }
  
  /* Step title */
  .guide-step h3 {
    font-size: 1.25rem;
    color: #0ea5e9;
    font-weight: bold;
    margin-bottom: 0.5rem;
  }
  
  /* Description text */
  .guide-step p {
    color: #6b7280;
    margin: 0;
    font-size: 1rem;
  }
  </style>