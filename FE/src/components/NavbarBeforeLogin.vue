<template>
  <header class="header">
    <nav class="nav container">
      <a href="/" class="logo">
        <img src="/logo.png" alt="SaveUp Logo" class="logo-img" loading="lazy" />
      </a>
      
      <button class="nav-toggle" @click="toggleMenu" aria-label="Toggle menu" :aria-expanded="isMenuOpen">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
      </button>

      <div :class="['nav-menu', { 'nav-menu--active': isMenuOpen }]">
        <ul class="nav-list">
          <li><a href="#home" class="nav-link text-dark">Trang chủ</a></li>
          <li><a href="#features" class="nav-link text-dark">Tính năng</a></li>
          <li><a href="#guide" class="nav-link text-dark">Hướng dẫn</a></li>
          <li><a href="#terms" class="nav-link text-dark">Điều khoản</a></li>
          <li><a href="#contact" class="nav-link text-dark">Liên hệ</a></li>
          <li><router-link to="/login" class="nav-link login">Đăng nhập</router-link></li>
        </ul>
      </div>
    </nav>
  </header>
</template>

<script>
export default {
  name: "NavbarBeforeLogin",
  data() {
    return {
      isMenuOpen: false,
    };
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
    },
  },
};
</script>

<style scoped>
.header {
  background-color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  height: 68px;
  display: flex;
  align-items: center;
}

.nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2.5rem;
  height: 68px;
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
}

.logo-img {
  width: 120px;
  height: auto;
  object-fit: contain;
  margin-right: 18px;
}

.nav-list {
  display: flex;
  align-items: center;
  gap: 2.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-link {
  color: #222;
  text-decoration: none;
  position: relative;
  font-size: 1.08rem;
  font-weight: 500;
  padding: 8px 0;
  transition: color 0.18s;
  letter-spacing: 0.01em;
}

.nav-link::after {
  content: "";
  position: absolute;
  left: 50%;
  bottom: 0;
  width: 0;
  height: 2.5px;
  background: linear-gradient(90deg, #54a0ff 0%, #00c6ff 100%);
  border-radius: 2px;
  transition: all 0.3s cubic-bezier(.4,0,.2,1);
  transform: translateX(-50%);
}

.nav-link:hover,
.nav-link:focus {
  color: #0077ff;
}

.nav-link:hover::after,
.nav-link:focus::after {
  width: 100%;
}

.login {
  background: linear-gradient(90deg, #54a0ff 0%, #00c6ff 100%);
  color: #fff !important;
  border-radius: 22px;
  padding: 8px 26px !important;
  margin-left: 10px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(84,160,255,0.10);
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
  border: none;
  outline: none;
  display: inline-block;
}
.login:hover, .login:focus {
  background: linear-gradient(90deg, #00c6ff 0%, #54a0ff 100%);
  color: #fff !important;
  box-shadow: 0 4px 16px rgba(84,160,255,0.18);
}

/* Ẩn nút toggle khi desktop */
.nav-toggle {
  display: none;
  flex-direction: column;
  gap: 5px;
  cursor: pointer;
  border: none;
  background: transparent;
  padding: 0;
}

.nav-toggle .bar {
  width: 28px;
  height: 3.5px;
  background-color: #222;
  border-radius: 2px;
  transition: all 0.3s;
}

/* Menu mặc định trên desktop */
.nav-menu {
  display: block;
}

/* --- Responsive --- */
@media (max-width: 1024px) {
  .nav {
    padding: 0 1rem;
  }
  .nav-list {
    gap: 1.3rem;
  }
  .logo-img {
    width: 90px;
  }
}
@media (max-width: 768px) {
  .nav-toggle {
    display: flex;
    margin-left: auto;
  }
  .nav-menu {
    display: none;
    width: 100vw;
    position: absolute;
    top: 68px;
    left: 0;
    background: #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,0.10);
    border-radius: 0 0 18px 18px;
    animation: slideDown 0.3s;
  }
  .nav-menu.nav-menu--active {
    display: block;
  }
  .nav-list {
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
    padding: 1.2rem 0.5rem 1rem 0.5rem;
    align-items: flex-start;
  }
  .nav-link {
    font-size: 1.13rem;
    width: 100%;
    padding: 12px 0 12px 10px;
    border-radius: 8px;
  }
  .nav-link::after {
    left: 10px;
    transform: none;
  }
  .login {
    width: 100%;
    margin: 0;
    text-align: center;
    padding: 12px 0 !important;
    border-radius: 12px;
    font-size: 1.13rem;
  }
}
@media (max-width: 480px) {
  .nav {
    padding: 0 0.3rem;
  }
  .logo-img {
    width: 70px;
  }
  .nav-link {
    font-size: 1rem;
    padding: 10px 0 10px 8px;
  }
  .login {
    font-size: 1rem;
    padding: 10px 0 !important;
  }
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
