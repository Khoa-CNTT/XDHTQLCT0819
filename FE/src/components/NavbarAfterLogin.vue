<template>
  <header class="header">
    <nav class="nav container">
      <!-- Logo -->
      <router-link to="/" class="logo">
        <img src="/logo.png" alt="SaveUp Logo" class="logo-img" loading="lazy" />
      </router-link>

      <!-- Menu -->
      <div class="nav-menu">
        <ul class="nav-list">
          <li><router-link to="/quan-li-chi-tieu" class="nav-link">Trang chủ</router-link></li>
          <li><router-link to="/nhat-ki-chi-tieu" class="nav-link">Lịch</router-link></li>
          <li><router-link to="/muc-tieu" class="nav-link">Mục tiêu</router-link></li>
          <li><router-link to="/bao-cao" class="nav-link">Thống kê</router-link></li>
          <li><router-link to="/quan-ly-danh-muc" class="nav-link">Danh mục</router-link></li>
          <li><router-link to="/quan-ly-nguoi-dung" class="nav-link">Người dùng</router-link></li>
          <li><router-link to="/quan-ly-tai-khoan" class="nav-link">Tài khoản</router-link></li>
          <li><router-link to="/tai-khoan-ca-nhan" class="dropdown-item">Trang cá nhân</router-link></li>
          <li><a href="#" class="dropdown-item" @click.prevent="handleLogout">Đăng xuất</a></li>
        </ul>
      </div>
    </nav>
  </header>
</template>

<script>
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

export default {
  name: "NavbarAfterLogin",
  setup(props, { emit }) {
    const toast = useToast();

    const handleLogout = () => {
      Swal.fire({
        title: 'Bạn có chắc chắn muốn đăng xuất?',
        text: 'Hành động này sẽ đăng xuất khỏi tài khoản hiện tại.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đăng xuất',
        cancelButtonText: 'Hủy',
      }).then((result) => {
        if (result.isConfirmed) {
          // Xoá token
          localStorage.removeItem('auth_token');

          // Thông báo bằng toast
          toast.success('Tài khoản đã đăng xuất thành công!', {
            timeout: 3000,
            position: 'top-right',
            closeOnClick: true,
            pauseOnHover: true,
          });

          // Gửi sự kiện logout lên App.vue để xử lý
          emit('logged-out');
        }
      });
    };

    return { handleLogout };
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
  padding: 1rem 0;
}

.logo-img {
  width: 100px;
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
  font-size: 1rem;
  position: relative;
  cursor: pointer;
  transition: color 0.2s ease;
}

.nav-link:hover {
  color: #0ea5e9;
}

.nav-link::after {
  content: "";
  position: absolute;
  bottom: -4px;
  left: 0;
  width: 0;
  height: 2px;
  background-color: #0ea5e9;
  transition: all 0.3s ease;
}

.nav-link:hover::after {
  width: 100%;
}

.dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  border: 1px solid #ddd;
  list-style: none;
  padding: 0;
  margin: 0;
  min-width: 150px;
  z-index: 100;
}

.dropdown-item {
  display: block;
  padding: 10px;
  color: #333;
  text-decoration: none;
}

.dropdown-item:hover {
  background: #f0f0f0;
}
</style>
