<template>
  <header class="header">
    <nav class="nav container">
      <router-link to="/" class="logo">
        <img
          src="/logo.png"
          alt="SaveUp Logo"
          class="logo-img"
          loading="lazy"
        />
      </router-link>

      <div class="nav-menu">
        <ul class="nav-list">
          <template v-if="!isAdmin">
            <li>
              <router-link to="/quan-li-chi-tieu" class="nav-link"
                >Trang chủ</router-link
              >
            </li>
            <li>
              <router-link to="/nhat-ki-chi-tieu" class="nav-link"
                >Lịch</router-link
              >
            </li>
            <li>
              <router-link to="/muc-tieu" class="nav-link"
                >Mục tiêu</router-link
              >
            </li>
            <li>
              <router-link to="/bao-cao" class="nav-link">Thống kê</router-link>
            </li>
            <li>
              <router-link to="/quan-ly-danh-muc" class="nav-link"
                >Danh mục</router-link
              >
            </li>
             <li>
              <router-link to="/budget" class="nav-link"
                >Ngân sách</router-link
              >
            </li>
            <li>
              <router-link to="/quan-ly-tai-khoan" class="nav-link"
                >Tài khoản</router-link
              >
            </li>
            <li>
              <router-link to="/quan-ly-giao-dich-dinh-ky" class="nav-link"
                >Quản lý giao dịch định kỳ</router-link
              >
            </li>
          </template>

          <template v-if="isAdmin">
            <li>
              <router-link to="/quan-ly-nguoi-dung" class="nav-link text-dark"
                >Quản lý người dùng</router-link
              >
            </li>
          </template>

          <li class="profile-dropdown">
            <button type="button" class="profile-btn" @click="toggleDropdown">
              <template v-if="user?.avatar">
                <img
                  :src="apiImage(user.avatar)"
                  alt="Profile"
                  class="avatar"
                />
              </template>
              <div v-else class="avatar-placeholder">
                <img
                  src="https://i.pinimg.com/736x/bc/43/98/bc439871417621836a0eeea768d60944.jpg"
                  alt="Profile"
                  class="avatar"
                />
              </div>
              <span class="caret">&#9662;</span>
            </button>

            <div v-if="isDropdownOpen" class="dropdown-menu">
              <router-link to="/profile" class="dropdown-link"
                >Thông tin tài khoản</router-link
              >
              <!-- nhật ký hoạt động của người dùng  -->
              <router-link to="/nhat-ki-hoat-dong" class="dropdown-link"
                >Nhật ký hoạt động</router-link
              >
              <a href="#" class="dropdown-link" @click.prevent="handleLogout"
                >Đăng xuất</a
              >
            </div>
          </li>
        </ul>
      </div>
    </nav>
  </header>
</template>

<script>
import { useToast } from "vue-toastification";
import Swal from "sweetalert2";
import axios from "axios";

export default {
  name: "NavbarAfterLogin",
  emits: ["logged-out"],
  data() {
    return {
      isAdmin: false,
      isDropdownOpen: false,
      user: null,
      defaultAvatar: "/default-avatar.png",
    };
  },
  methods: {
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    apiImage(filename) {
      return `/api/get-image/${filename}`;
    },
    handleLogout() {
      Swal.fire({
        title: "Bạn có chắc chắn muốn đăng xuất?",
        text: "Hành động này sẽ đăng xuất khỏi tài khoản hiện tại.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#0ea5e9",
        cancelButtonColor: "#d33",
        confirmButtonText: "Đăng xuất",
        cancelButtonText: "Hủy",
      }).then((result) => {
        if (result.isConfirmed) {
          axios
            .post(
              "/api/logout",
              {},
              {
                headers: {
                  Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                },
              }
            )
            .then(() => {
              localStorage.removeItem("auth_token");
              localStorage.removeItem("user");

              const toast = useToast();
              toast.success("Tài khoản đã đăng xuất thành công!", {
                timeout: 3000,
                position: "top-right",
              });

              this.$emit("logged-out");
            })
            .catch(() => {
              const toast = useToast();
              toast.error("Đã có lỗi xảy ra khi đăng xuất!", {
                timeout: 3000,
                position: "top-right",
              });
            });
        }
      });
    },
    closeDropdown(event) {
      const profileEl = this.$el.querySelector(".profile-dropdown");
      if (profileEl && !profileEl.contains(event.target)) {
        this.isDropdownOpen = false;
      }
    },
    
  },
  mounted() {
    try {
      const userData = localStorage.getItem("user");
      if (userData) {
        const parsedUser = JSON.parse(userData);
        this.user = parsedUser;
        if (parsedUser.role === "admin") {
          this.isAdmin = true;
        }
      }
    } catch (e) {
      console.error("Error parsing user data", e);
    }
    document.addEventListener("click", this.closeDropdown);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.closeDropdown);
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
  align-items: center;
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

/* Profile styles */
.profile-dropdown {
  position: relative;
}

.profile-btn {
  display: flex;
  align-items: center;
  cursor: pointer;
  background: none;
  border: none;
  padding: 0;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #0ea5e9;
}

.caret {
  margin-left: 6px;
  font-size: 0.8rem;
}

.dropdown-menu {
  position: absolute;
  top: 45px;
  right: 0;
  background-color: #fff;
  min-width: 180px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  border-radius: 4px;
  padding: 8px 0;
  z-index: 1001;
  display: flex;
  flex-direction: column;
}

.dropdown-link {
  padding: 8px 16px;
  text-decoration: none;
  color: #333;
  transition: background-color 0.2s, color 0.2s;
  font-size: 0.95rem;
}

.dropdown-link:hover {
  background-color: #f3f4f6;
  color: #0ea5e9;
}
</style>
