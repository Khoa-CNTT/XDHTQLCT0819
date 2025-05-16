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
            <li>
              <router-link to="/quan-li-lien-he" class="nav-link text-dark"
                >Quản lí liên hệ</router-link
              >
            </li>
            <li>
              <router-link to="/report-admin" class="nav-link text-dark"
                >Thống kê lượng người dùng</router-link
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
              <router-link to="/nhat-ky-hoat-dong" class="dropdown-link"
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
  background: #fff;
}

.logo-img {
  width: 120px;
  height: auto;
  object-fit: contain;
  margin-right: 18px;
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
  height: 3.5px;
  width: 100%;
  background-color: #333;
  border-radius: 2px;
  transition: all 0.3s;
}

.nav-menu {
  display: flex;
  align-items: center;
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
  outline: none;
  transition: box-shadow 0.2s;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2.5px solid #54a0ff;
  box-shadow: 0 2px 8px rgba(84,160,255,0.10);
  transition: border 0.2s, box-shadow 0.2s;
}

.profile-btn:hover .avatar,
.profile-btn:focus .avatar {
  border-color: #00c6ff;
  box-shadow: 0 4px 16px rgba(84,160,255,0.18);
}

.caret {
  margin-left: 8px;
  font-size: 1.1rem;
  color: #54a0ff;
  transition: color 0.2s;
}

.profile-btn:hover .caret,
.profile-btn:focus .caret {
  color: #00c6ff;
}

.dropdown-menu {
  position: absolute;
  top: 48px;
  right: 0;
  background-color: #fff;
  min-width: 190px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.13);
  border-radius: 10px;
  padding: 10px 0;
  z-index: 1001;
  display: flex;
  flex-direction: column;
  animation: fadeIn 0.25s;
}

.dropdown-link {
  padding: 11px 22px;
  text-decoration: none;
  color: #333;
  transition: background-color 0.2s, color 0.2s;
  font-size: 1.01rem;
  border-radius: 6px;
}

.dropdown-link:hover {
  background: linear-gradient(90deg, #e3f2fd 0%, #e6f0fa 100%);
  color: #0077ff;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

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
    width: 100vw;
    flex-direction: column;
    display: none;
    position: absolute;
    top: 68px;
    left: 0;
    background: #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,0.10);
    border-radius: 0 0 18px 18px;
    animation: slideDown 0.3s;
    z-index: 1002;
  }
  .nav-menu.open {
    display: flex;
  }
  .nav-list {
    flex-direction: column;
    width: 100%;
    gap: 0.5rem;
    margin-top: 1rem;
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
  .profile-dropdown {
    align-self: flex-end;
    margin-top: 1rem;
  }
  .dropdown-menu {
    right: 10px;
    left: auto;
    min-width: 160px;
    top: 46px;
    border-radius: 10px;
  }
  .logo-img {
    width: 80px;
  }
  .avatar {
    width: 36px;
    height: 36px;
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
  .avatar {
    width: 32px;
    height: 32px;
  }
  .dropdown-menu {
    min-width: 120px;
    top: 42px;
    border-radius: 8px;
  }
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
