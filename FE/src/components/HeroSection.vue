<template>
  <div id="home" class="hero">
    <div class="row">
      <div class="hero-left">
        <!-- Hero Content (Left Section) -->
        <div class="hero-content">
          <h1 class="hero-title">QUẢN LÝ CHI TIÊU CÙNG SAVEUP</h1>
          <p class="hero-text">
            Quản lý chi tiêu thông minh, tiết kiệm, hiệu quả
          </p>
          <router-link to="/sign-up" class="btn-primary register-btn">
            Đăng Ký
          </router-link>
        </div>
      </div>

      <div class="hero-right">
        <!-- Hero Illustration (Center Section) -->
        <div class="illustration-wrapper">
          <div class="center-image">
            <img
              src="https://png.pngtree.com/png-vector/20221108/ourmid/pngtree-cartoon-house-illustration-png-image_6434928.png"
              alt="User illustration"
              class="user-img"
              loading="lazy"
            />
          </div>

          <div class="circular-icons">
            <div class="icon-item" style="--delay: 0s; --top: 0%; --left: 50%">
              🏠
            </div>
            <div
              class="icon-item"
              style="--delay: 0.2s; --top: 25%; --right: 0%"
            >
              🍔
            </div>
            <div
              class="icon-item"
              style="--delay: 0.4s; --bottom: 25%; --right: 0%"
            >
              🎬
            </div>
            <div
              class="icon-item"
              style="--delay: 0.6s; --bottom: 0%; --left: 50%"
            >
              🚗
            </div>
            <div
              class="icon-item"
              style="--delay: 0.8s; --bottom: 25%; --left: 0%"
            >
              🧮
            </div>
            <div class="icon-item" style="--delay: 1s; --top: 25%; --left: 0%">
              🐷
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Call to Action (Bottom Section) -->
    <div class="cta-section">
      <h2 class="cta-title">Tiết kiệm thông minh - Tương lai vững vàng</h2>
      <router-link to="/sign-up" class="btn-primary cta-btn">
        Bắt đầu ngay 
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: "HeroSection",
  methods: {
    register() {
      console.log("Register button clicked");
    },
  },
};
</script>

<style scoped>
.hero {
  display: flex;
  flex-direction: column;
  padding: 2rem 3rem 2rem 3rem;
  background-color: #f0f9ff;
  min-height: 100vh;
  align-items: center;
  justify-content: center;
  gap: 2rem;
}

.row {
  display: flex;
  width: 100%;
  max-width: 1200px;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.hero-left {
  flex: 1 1 400px;
  text-align: left;
  display: flex;
  flex-direction: column;
  justify-content: center;
  height: 400px;
}

.hero-right {
  flex: 1 1 400px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 400px;
}

.hero-content {
  opacity: 0;
  animation: fadeIn 1s ease forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.hero-title {
  white-space: nowrap;
  font-size: 2.5rem;
  font-weight: bold;
  color: #0ea5e9;
  margin-bottom: 1rem;
}

.hero-text {
  font-size: 1.25rem;
  color: #6b7280;
  margin-bottom: 2rem;
}

.register-btn {
  background-color: #0ea5e9;
  color: white;
  padding: 1rem 2rem;
  font-size: 1.125rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.register-btn:hover {
  background-color: rgb(114, 151, 184);
}

.illustration-wrapper {
  position: relative;
  width: 350px;
  height: 350px;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.3s ease-in-out;
  margin: 0 auto;
}

.center-image {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  background-color: #ffffff;
  padding: 1rem;
  border-radius: 50%;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 2;
}

.center-image img {
  height: 150px;
  width: 150px;
}

.circular-icons {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: center;
  align-items: center;
}

.icon-item {
  position: absolute;
  width: 48px;
  height: 48px;
  background-color: rgb(252, 252, 252);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  opacity: 1;
  animation: iconAppear 0.5s ease forwards;
  transition: transform 0.3s ease;
}

@keyframes iconAppear {
  0% {
    opacity: 0;
    transform: scale(0);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.icon-item:nth-child(1) {
  top: 0;
  left: 50%;
  transform: translate(-50%, 0);
  animation-delay: 0s;
}

.icon-item:nth-child(2) {
  top: 25%;
  right: 0;
  animation-delay: 0.2s;
}

.icon-item:nth-child(3) {
  bottom: 25%;
  right: 0;
  animation-delay: 0.4s;
}

.icon-item:nth-child(4) {
  bottom: 0;
  left: 50%;
  animation-delay: 0.6s;
}

.icon-item:nth-child(5) {
  bottom: 25%;
  left: 0;
  animation-delay: 0.8s;
}

.icon-item:nth-child(6) {
  top: 25%;
  left: 0;
  animation-delay: 1s;
}

.icon-item:hover {
  transform: scale(1.1);
}

.cta-section {
  width: 100%;
  max-width: 1200px;
  text-align: center;
  background-color: #0ea5e9;
  padding: 2rem 1rem;
  border-radius: 1rem;
  margin-top: 3rem;
}

.cta-title {
  font-size: 2rem;
  color: white;
  margin-bottom: 2rem;
}

.cta-btn {
  background-color: white;
  color: #0ea5e9;
  font-weight: bold;
  padding: 1rem 2rem;
  font-size: 1.125rem;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.cta-btn:hover {
  background-color: #f0f9ff;
}

/* Responsive Styles */
@media (max-width: 900px) {
  .hero-left, .hero-right {
    flex: 1 1 100%;
    height: auto;
  }

  .hero-title {
    white-space: normal;
    font-size: 2rem;
  }

  .hero-text {
    font-size: 1.1rem;
  }

  .illustration-wrapper {
    width: 250px;
    height: 250px;
  }

  .center-image img {
    height: 120px;
    width: 120px;
  }
}

@media (max-width: 600px) {
  .hero {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .hero-title {
    font-size: 1.5rem;
  }

  .hero-text {
    font-size: 1rem;
  }

  .register-btn {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
  }

  .illustration-wrapper {
    width: 200px;
    height: 200px;
  }

  .center-image img {
    height: 100px;
    width: 100px;
  }

  .icon-item {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }

  .cta-title {
    font-size: 1.5rem;
  }

  .cta-btn {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
  }
}
</style>
