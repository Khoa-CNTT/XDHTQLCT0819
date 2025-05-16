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
  background-color: #ffffff;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  flex-wrap: wrap;
}

.logo-img {
  width: 100px;
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
  width: 25px;
  height: 3px;
  background-color: #000;
  border-radius: 2px;
}

/* Menu mặc định trên desktop */
.nav-menu {
  display: block;
}

.nav-list {
  display: flex;
  gap: 2rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-link {
  color: #000;
  text-decoration: none;
  position: relative;
  font-size: 1rem;
}

.nav-link::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background-color: #000;
  transition: all 0.2s ease;
}

.nav-link:hover::after {
  width: 100%;
}

/* --- Responsive --- */
@media (max-width: 768px) {
  .nav-toggle {
    display: flex;
  }

  .nav-menu {
    display: none;
    width: 100%;
  }

  .nav-menu.nav-menu--active {
    display: block;
  }

  .nav-list {
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    padding-top: 1rem;
  }

  .nav-link {
    font-size: 1.1rem;
  }

  .logo-img {
    width: 80px;
  }
}

@media (max-width: 480px) {
  .nav {
    padding: 0.5rem;
  }

  .nav-link {
    font-size: 1rem;
  }

  .logo-img {
    width: 70px;
  }
}
</style>
