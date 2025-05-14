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

      <!-- Toggle Button -->
      <button class="nav-toggle" @click="toggleMenu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
      </button>

      <div class="nav-menu" :class="{ open: isMenuOpen }">
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
              <router-link to="/budget" class="nav-link">Ngân sách</router-link>
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
      isMenuOpen: false,
      user: null,
    };
  },
  methods: {
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
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
  padding: 1rem;
  flex-wrap: wrap;
}

.logo-img {
  width: 100px;
}

.nav-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  height: 22px;
  width: 28px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.nav-toggle .bar {
  height: 3px;
  width: 100%;
  background-color: #333;
  border-radius: 2px;
}

.nav-menu {
  display: flex;
  align-items: center;
}

.nav-list {
  display: flex;
  gap: 2rem;
  list-style: none;
  margin: 0;
  padding: 0;
  align-items: center;
  flex-wrap: wrap;
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

/* 🔽 Responsive */
@media (max-width: 768px) {
  .nav-toggle {
    display: flex;
  }

  .nav-menu {
    width: 100%;
    flex-direction: column;
    display: none;
  }

  .nav-menu.open {
    display: flex;
  }

  .nav-list {
    flex-direction: column;
    width: 100%;
    gap: 1rem;
    margin-top: 1rem;
  }

  .nav-link {
    font-size: 1.1rem;
    width: 100%;
  }

  .profile-dropdown {
    align-self: flex-end;
    margin-top: 1rem;
  }

  .dropdown-menu {
    right: 10px;
    left: auto;
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

  .avatar {
    width: 32px;
    height: 32px;
  }

  .dropdown-menu {
    min-width: 150px;
    top: 42px;
  }
}
</style>
