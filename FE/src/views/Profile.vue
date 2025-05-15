<template>
  <div class="profile-container">
    <div class="left-column">
      <div class="avatar-container">
        <div class="avatar">
          <img
            v-if="userData.avatar"
            :src="apiImage(userData.avatar)"
            alt="Avatar"
          />
          <div v-else class="avatar-placeholder">
            {{ userData.fullName?.substring(0, 2).toUpperCase() }}
          </div>
        </div>
        <input
          type="file"
          id="avatar-upload"
          @change="handleAvatarUpload"
          accept="image/*"
        />
        <label for="avatar-upload" class="file-upload-button">Chọn ảnh</label>

        <h2 class="user-name">{{ userData.fullName }}</h2>
        <span class="badge">{{ translateRole(userData.role) }}</span>
      </div>

      <div class="card" style="margin: 10px 5px; height: 260px">
        <div class="card-content-user">
          <div class="info-item">
            <mail-icon class="info-icon" />
            <div class="info-details">
              <p class="info-title">Email</p>
              <p class="info-value">{{ userData.email }}</p>
            </div>
          </div>

          <div class="info-item">
            <phone-icon class="info-icon" />
            <div class="info-details">
              <p class="info-title">Điện thoại</p>
              <p class="info-value">{{ userData.phone }}</p>
            </div>
          </div>

          <div class="info-item">
            <map-pin-icon class="info-icon" />
            <div class="info-details">
              <p class="info-title">Địa chỉ</p>
              <p class="info-value">{{ userData.address }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="right-column">
      <div class="tabs">
        <button
          :class="{ active: activeTab === 'account' }"
          @click="activeTab = 'account'"
        >
          Thông tin tài khoản
        </button>
      </div>

      <div v-if="activeTab === 'account'" class="info-card-status">
        <h3>Trạng thái tài khoản</h3>
        <div class="card-content">
          <div class="status-grid">
            <div class="status-item">
              <span>Trạng thái</span>
              <button
                class="status-button"
                :class="userData.status ? 'active' : 'inactive'"
              >
                {{ userData.status ? "Hoạt động" : "Không hoạt động" }}
              </button>
            </div>

            <div class="status-item">
              <span>Tình trạng</span>
              <button
                class="status-button"
                :class="!userData.isBlocked ? 'unlocked' : 'locked'"
              >
                {{ userData.isBlocked ? "Đã khóa" : "Đã kích hoạt" }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'account'" class="info-card">
        <h3>Thời gian tài khoản</h3>
        <div class="card-content-time">
          <div class="time-item">
            <calendar-days-icon class="time-icon" />
            <div class="time-details">
              <p class="time-title">Ngày tạo</p>
              <p class="time-value">{{ formatDate(userData.created_at) }}</p>
            </div>
          </div>
          <div class="time-item">
            <calendar-days-icon class="time-icon" />
            <div class="time-details">
              <p class="time-title">Cập nhật lần cuối</p>
              <p class="time-value">{{ formatDate(userData.updated_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <button class="change-password-button" @click="openPasswordChangeModal">
        Đổi mật khẩu
      </button>

      <button class="edit-profile-button" @click="openEditModal">
        Chỉnh sửa hồ sơ
      </button>

      <!-- Edit Profile Modal -->
      <div v-if="isModalOpen" class="modal-overlay">
        <div class="modal-container">
          <div class="modal-header">
            <h3>Chỉnh sửa hồ sơ</h3>
            <button @click="closeEditModal" class="modal-close">×</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveChanges">
              <div class="form-group">
                <label>Họ và tên:</label>
                <input
                  type="text"
                  v-model="editedUser.fullName"
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>Email:</label>
                <input
                  type="email"
                  v-model="editedUser.email"
                  class="form-input"
                  disabled
                />
              </div>
              <div class="form-group">
                <label>Điện thoại:</label>
                <input
                  type="text"
                  v-model="editedUser.phone"
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>Địa chỉ:</label>
                <input
                  type="text"
                  v-model="editedUser.address"
                  class="form-input"
                />
              </div>
              <div class="form-actions">
                <button
                  type="button"
                  @click="closeEditModal"
                  class="btn-cancel"
                >
                  Hủy bỏ
                </button>
                <button type="submit" class="btn-save">Lưu thay đổi</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Change Password Modal -->
      <div v-if="isPasswordModalOpen" class="modal-overlay">
        <div class="modal-container">
          <div class="modal-header">
            <h3>Đổi mật khẩu</h3>
            <button @click="closePasswordChangeModal" class="modal-close">
              ×
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="changePassword">
              <div class="form-group">
                <label>Mật khẩu cũ:</label>
                <input
                  :type="showOldPassword ? 'text' : 'password'"
                  v-model="passwordForm.current_password"
                  class="form-input"
                  required
                />
                <button type="button" @click="toggleOldPassword" class="password-toggle-btn">
                <Eye v-if="!showOldPassword" />
                <EyeOff v-if="showOldPassword" />
              </button>
              </div>
              <div class="form-group">
                <label>Mật khẩu mới:</label>
                <input
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="passwordForm.new_password"
                  class="form-input"
                  required
                />
                <button type="button" @click="toggleNewPassword" class="password-toggle-btn">
                <Eye v-if="!showNewPassword" />
                <EyeOff v-if="showNewPassword" />
              </button>
              </div>
              <div class="form-group">
                <label>Xác nhận mật khẩu mới:</label>
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="passwordForm.new_password_confirmation"
                  class="form-input"
                  required
                />
                <button type="button" @click="toggleConfirmPassword" class="password-toggle-btn">
                <Eye v-if="!showConfirmPassword" />
                <EyeOff v-if="showConfirmPassword" />
              </button>
              </div>
              <div class="form-actions">
                <button
                  type="button"
                  @click="closePasswordChangeModal"
                  class="btn-cancel"
                >
                  Hủy bỏ
                </button>
                <button type="submit" class="btn-save">Lưu thay đổi</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";
import {
  Mail as MailIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  CalendarDays as CalendarDaysIcon,
  Eye,
  EyeOff,
} from "lucide-vue-next";
import { useToast } from "vue-toastification";

axios.defaults.withCredentials = true;

export default {
  name: "UserProfile",
  components: {
    MailIcon,
    PhoneIcon,
    MapPinIcon,
    CalendarDaysIcon,
    Eye,
    EyeOff,
  },
  setup() {
    const activeTab = ref("account");
    const isModalOpen = ref(false);
    const isPasswordModalOpen = ref(false);
    const userData = ref({});
    const editedUser = ref({});
    const passwordForm = ref({
      current_password: "",
      new_password: "",
      new_password_confirmation: "",
    });

    const showOldPassword = ref(false); // Điều khiển việc hiển thị mật khẩu cũ
    const showNewPassword = ref(false); // Điều khiển việc hiển thị mật khẩu mới
    const showConfirmPassword = ref(false); // Điều khiển việc hiển thị mật khẩu xác nhận

    const toggleOldPassword = () => {
      showOldPassword.value = !showOldPassword.value;
    };

    const toggleNewPassword = () => {
      showNewPassword.value = !showNewPassword.value;
    };

    const toggleConfirmPassword = () => {
      showConfirmPassword.value = !showConfirmPassword.value;
    };

    const fetchProfile = async () => {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await axios.get("/api/user/profile", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        userData.value = response.data;
      } catch (error) {
        console.error("Lỗi khi lấy thông tin hồ sơ:", error);
      }
    };

    const handleAvatarUpload = async (event) => {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append("avatar", file);

      try {
        const token = localStorage.getItem("auth_token");
        const response = await axios.post(
          "/api/user/avatar-profile",
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );
        userData.value.avatar = response.data.avatar;
        localStorage.setItem("user", JSON.stringify(response.data.user));
        await fetchProfile();
        location.reload();
      } catch (error) {
        console.error("Lỗi khi cập nhật ảnh đại diện:", error);
      }
    };

    const openEditModal = () => {
      editedUser.value = { ...userData.value };
      isModalOpen.value = true;
    };

    const closeEditModal = () => {
      isModalOpen.value = false;
    };

    const openPasswordChangeModal = () => {
      isPasswordModalOpen.value = true;
    };

    const closePasswordChangeModal = () => {
      isPasswordModalOpen.value = false;
    };

    const saveChanges = async () => {
      const toast = useToast();
      try {
        const token = localStorage.getItem("auth_token");
        const response = await axios.put(
          "/api/user/update-profile",
          editedUser.value,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        userData.value = response.data.user;
        isModalOpen.value = false;
        toast.success(response.data.message);
      } catch (error) {
        toast.error(error.response.data.message);
      }
    };

    const changePassword = async () => {
      const toast = useToast();
      try {
        const token = localStorage.getItem("auth_token");
        const response = await axios.post(
          "/api/user/change-password",
          passwordForm.value,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        toast.success(response.data.message);
        closePasswordChangeModal();
      } catch (error) {
        if (error.response && error.response.data) {
          const errorMessage =
            error.response.data.error ||
            error.response.data.message ||
            "Đã có lỗi xảy ra.";

            
          toast.error(errorMessage);
        } else {
          toast.error("Đã có lỗi xảy ra.");
        }
      }
    };

    const apiImage = (filename) => {
      return `/api/get-image/${filename}`;
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString("vi-VN", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    };

    const translateRole = (role) => {
      const translations = {
        user: "người dùng",
        admin: "quản trị viên",
      };
      return translations[role?.toLowerCase()] || role;
    };

    onMounted(() => {
      fetchProfile();
    });

    return {
      activeTab,
      userData,
      editedUser,
      isModalOpen,
      isPasswordModalOpen,
      openEditModal,
      closeEditModal,
      saveChanges,
      openPasswordChangeModal,
      closePasswordChangeModal,
      changePassword,
      handleAvatarUpload,
      formatDate,
      apiImage,
      translateRole,
      passwordForm,
      passwordForm,
      showOldPassword,
      showNewPassword,
      showConfirmPassword,
      toggleOldPassword,
      toggleNewPassword,
      toggleConfirmPassword,
    };
  },
};
</script>

<!-- <style scoped>
input[type="file"] {
  display: none;
}

.file-upload-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(50, 164, 199, 0.3);
  text-transform: capitalize;
}

.file-upload-button:hover {
  background: linear-gradient(135deg, #00a3e0, #00a3e0);
  box-shadow: 0 6px 14px rgba(89, 184, 207, 0.4);
  transform: translateY(-1px);
}

.file-upload-button:active {
  transform: translateY(0);
  box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
}

.file-upload-button:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.4);
}

.profile-container {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 20px;
  margin-top: 10px;
}

.left-column {
  background-color: #e8f7fc;
  padding: 5px;
  border-radius: 8px;
}

.avatar-container {
  text-align: center;
  margin-bottom: 50px;
  margin-top: 20px;
}

.avatar {
  width: 200px;
  height: 200px;
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
  background-color: #c4c4c4;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  height: 100%;
}

.user-name {
  margin-top: 20px;
  font-size: 2rem;
  font-weight: bold;
  color: #0056b3;
}

.user-username {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 5px;
}

.badge {
  background-color: #00a3e0;
  color: white;
  padding: 7px 10px;
  border-radius: 5px;
}

.card {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 10px;
}
.user-info {
  margin-top: 10px;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 5px;
}

.info-item svg {
  margin-right: 10px;
  color: #00a3e0;
}

.right-column {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.tabs button {
  padding: 10px 20px;
  border: 1px solid #00a3e0;
  background-color: #ffffff;
  cursor: pointer;
  border-radius: 5px;
  color: #00a3e0;
  font-weight: bold;
  width: 100%;
}

.tabs button.active {
  background-color: #00a3e0;
  color: white;
}

.info-card-status {
  background-color: #f1f9ff;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  height: 180px;
}
.info-card-status h3 {
  margin-bottom: 20px;
  font-size: 1.5rem;
  color: #0056b3;
  font-weight: bold;
}

.info-card {
  background-color: #f1f9ff;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  height: 300px;
}

.info-card h3 {
  margin-bottom: 20px;
  font-size: 1.5rem;
  color: #0056b3;
  font-weight: bold;
}

.card-content p {
  margin-bottom: 10px;
  font-size: 1.2rem;
}

.card-content-time p {
  margin-bottom: 10px;
  font-size: 1.2rem;
}

.status-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-top: 10px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #ffffff;
  padding: 10px 15px;
  font-size: 1.2rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.status-button {
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 1rem;
  border: none;
  cursor: default;
}

.active {
  background-color: #00a3e0;
  color: white;
}

.inactive {
  background-color: #a0a0a0;
  color: white;
}

.unlocked {
  background-color: #4caf50;
  color: white;
}

.locked {
  background-color: #f44336;
  color: white;
}

.time-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
  background-color: #ffffff;
  padding: 10px 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.time-icon {
  margin-right: 10px;
  color: #00a3e0;
  width: 24px;
  height: 24px;
}

.time-details {
  display: flex;
  flex-direction: column;
}

.time-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.time-value {
  font-size: 0.8rem;
  color: #666;
}

.edit-profile-button {
  position: fixed;
  bottom: 70px;
  right: 140px;
  background-color: transparent;
  color: #0a0a0a;
  border: 2px solid #0b0b0b;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s, color 0.3s;
  z-index: 999999 !important;
  display: flex;
}

.edit-profile-button:hover {
  background-color: #a0e1f9;
  color: white;
}

.change-password-button {
  position: fixed;
  bottom: 70px;
  right: 5px;
  background-color: transparent;
  color: #0a0a0a;
  border: 2px solid #0b0b0b;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s, color 0.3s;
  z-index: 999999 !important;
  display: flex;
}

.change-password-button:hover {
  background-color: #a0e1f9;
  color: white;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

/* Modal Container */
.modal-container {
  width: 500px;
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  animation: fadeIn 0.3s ease;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-close {
  background-color: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #888;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #d0d0d0;
  border-radius: 5px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-cancel {
  padding: 8px 15px;
  background-color: #f5f5f5;
  border: 1px solid #d0d0d0;
  border-radius: 5px;
  cursor: pointer;
}

.btn-save {
  padding: 8px 15px;
  background-color: #00a3e0;
  border: 1px solid #00a3e0;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

.password-input {
  display: flex;
  align-items: center;
  position: relative;
}

.password-toggle-btn {
  background: transparent;
  border: none;
  position: absolute;
  right: 30px;
  cursor: pointer;
  color: #00a3e0;
}

.password-toggle-btn svg {
  width: 20px;
  height: 20px;
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style> -->

<style scoped>
/* Base styles */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  line-height: 1.6;
  color: #333;
}

input[type="file"] {
  display: none;
}

/* File upload button */
.file-upload-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(50, 164, 199, 0.3);
  text-transform: capitalize;
  width: fit-content;
  margin: 0 auto;
}

.file-upload-button:hover {
  background: linear-gradient(135deg, #00a3e0, #00a3e0);
  box-shadow: 0 6px 14px rgba(89, 184, 207, 0.4);
  transform: translateY(-1px);
}

.file-upload-button:active {
  transform: translateY(0);
  box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
}

.file-upload-button:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.4);
}

/* Profile container */
.profile-container {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 20px;
  margin: 10px auto;
  max-width: 1200px;
  padding: 0 15px;
}

/* Left column */
.left-column {
  background-color: #e8f7fc;
  padding: 15px;
  border-radius: 8px;
  height: fit-content;
}

.avatar-container {
  text-align: center;
  margin-bottom: 30px;
  margin-top: 20px;
}

.avatar {
  width: 200px;
  height: 200px;
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
  background-color: #c4c4c4;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  height: 100%;
}

.user-name {
  margin-top: 20px;
  font-size: 2rem;
  font-weight: bold;
  color: #0056b3;
}

.user-username {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 5px;
}

.badge {
  background-color: #00a3e0;
  color: white;
  padding: 7px 10px;
  border-radius: 5px;
  display: inline-block;
  margin-top: 5px;
}

.card {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 10px;
}

.user-info {
  margin-top: 10px;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.info-item svg {
  margin-right: 10px;
  color: #00a3e0;
  min-width: 24px;
}

/* Right column */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  flex-wrap: wrap;
}

.tabs button {
  padding: 10px 20px;
  border: 1px solid #00a3e0;
  background-color: #ffffff;
  cursor: pointer;
  border-radius: 5px;
  color: #00a3e0;
  font-weight: bold;
  flex: 1;
  min-width: 120px;
}

.tabs button.active {
  background-color: #00a3e0;
  color: white;
}

.info-card-status {
  background-color: #f1f9ff;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  height: auto;
  min-height: 180px;
}

.info-card-status h3 {
  margin-bottom: 20px;
  font-size: 1.5rem;
  color: #0056b3;
  font-weight: bold;
}

.info-card {
  background-color: #f1f9ff;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  height: auto;
  min-height: 300px;
}

.info-card h3 {
  margin-bottom: 20px;
  font-size: 1.5rem;
  color: #0056b3;
  font-weight: bold;
}

.card-content p {
  margin-bottom: 10px;
  font-size: 1.2rem;
}

.card-content-time p {
  margin-bottom: 10px;
  font-size: 1.2rem;
}

.status-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-top: 10px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #ffffff;
  padding: 10px 15px;
  font-size: 1.2rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  flex-wrap: wrap;
  gap: 5px;
}

.status-button {
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 1rem;
  border: none;
  cursor: default;
  white-space: nowrap;
}

.active {
  background-color: #00a3e0;
  color: white;
}

.inactive {
  background-color: #a0a0a0;
  color: white;
}

.unlocked {
  background-color: #4caf50;
  color: white;
}

.locked {
  background-color: #f44336;
  color: white;
}

.time-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
  background-color: #ffffff;
  padding: 10px 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.time-icon {
  margin-right: 10px;
  color: #00a3e0;
  width: 24px;
  height: 24px;
  min-width: 24px;
}

.time-details {
  display: flex;
  flex-direction: column;
}

.time-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.time-value {
  font-size: 0.8rem;
  color: #666;
}

/* Fixed buttons - made responsive */
.action-buttons-container {
  position: fixed;
  bottom: 70px;
  right: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 999;
}

@media (min-width: 768px) {
  .action-buttons-container {
    flex-direction: row;
    right: 20px;
    gap: 15px;
  }
}

.edit-profile-button, .change-password-button {
  background-color: transparent;
  color: #0a0a0a;
  border: 2px solid #0b0b0b;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s, color 0.3s;
  white-space: nowrap;
}

.edit-profile-button:hover, .change-password-button:hover {
  background-color: #a0e1f9;
  color: white;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 0 15px;
}

.modal-container {
  width: 100%;
  max-width: 500px;
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  animation: fadeIn 0.3s ease;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
  position: sticky;
  top: 0;
  background-color: white;
  z-index: 1;
}

.modal-close {
  background-color: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #888;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #d0d0d0;
  border-radius: 5px;
  font-size: 1rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.btn-cancel {
  padding: 10px 15px;
  background-color: #f5f5f5;
  border: 1px solid #d0d0d0;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-save {
  padding: 10px 15px;
  background-color: #00a3e0;
  border: 1px solid #00a3e0;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
}

.password-input {
  display: flex;
  align-items: center;
  position: relative;
}

.password-toggle-btn {
  background: transparent;
  border: none;
  position: absolute;
  right: 10px;
  cursor: pointer;
  color: #00a3e0;
  padding: 0;
}

.password-toggle-btn svg {
  width: 20px;
  height: 20px;
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive styles */
@media screen and (max-width: 992px) {
  .profile-container {
    grid-template-columns: 1fr 1.5fr;
  }
  
  .avatar {
    width: 180px;
    height: 180px;
  }
  
  .user-name {
    font-size: 1.8rem;
  }
}

@media screen and (max-width: 768px) {
  .profile-container {
    grid-template-columns: 1fr;
  }
  
  .tabs button {
    padding: 8px 15px;
    font-size: 0.9rem;
  }
  
  .status-grid {
    grid-template-columns: 1fr;
  }
  
  .info-card h3, .info-card-status h3 {
    font-size: 1.3rem;
  }
  
  .card-content p, .card-content-time p, .status-item {
    font-size: 1rem;
  }
}

@media screen and (max-width: 576px) {
  .avatar {
    width: 150px;
    height: 150px;
  }
  
  .user-name {
    font-size: 1.5rem;
  }
  
  .user-username {
    font-size: 1rem;
  }
  
  .badge {
    padding: 5px 8px;
    font-size: 0.9rem;
  }
  
  .tabs {
    flex-direction: column;
    gap: 8px;
  }
  
  .tabs button {
    width: 100%;
  }
  
  .info-card, .info-card-status {
    padding: 12px;
  }
  
  .status-button {
    padding: 4px 10px;
    font-size: 0.9rem;
  }
  
  .form-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-cancel, .btn-save {
    width: 100%;
    text-align: center;
  }
}

/* iPad specific adjustments */
@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
  .profile-container {
    gap: 15px;
    padding: 0 10px;
  }
  
  .left-column {
    padding: 12px;
  }
  
  .avatar {
    width: 160px;
    height: 160px;
  }
  
  .info-card, .info-card-status {
    min-height: auto;
  }
}

/* iPhone specific adjustments */
@media only screen and (min-device-width: 320px) and (max-device-width: 480px) {
  .profile-container {
    margin: 5px auto;
    padding: 0 10px;
  }
  
  .avatar-container {
    margin-bottom: 20px;
    margin-top: 10px;
  }
  
  .avatar {
    width: 120px;
    height: 120px;
  }
  
  .user-name {
    font-size: 1.3rem;
    margin-top: 10px;
  }
  
  .action-buttons-container {
    bottom: 20px;
    right: 10px;
  }
  
  .edit-profile-button, .change-password-button {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
  
  .modal-container {
    width: 95%;
  }
}

/* Support for ultra-wide screens */
@media screen and (min-width: 1400px) {
  .profile-container {
    max-width: 1400px;
  }
}

/* Print styles */
@media print {
  .edit-profile-button, 
  .change-password-button, 
  .file-upload-button,
  .tabs button {
    display: none !important;
  }
  
  .profile-container {
    display: block;
  }
  
  .left-column,
  .right-column {
    width: 100%;
    margin-bottom: 20px;
  }
  
  .info-card,
  .info-card-status {
    break-inside: avoid;
  }
}

/* Accessibility improvements */
:focus {
  outline: 3px solid #00a3e0;
  outline-offset: 2px;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  body.dark-mode-enabled {
    background-color: #121212;
    color: #e0e0e0;
  }
  
  body.dark-mode-enabled .left-column {
    background-color: #1e2a38;
  }
  
  body.dark-mode-enabled .info-card,
  body.dark-mode-enabled .info-card-status {
    background-color: #1e2a38;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  }
  
  body.dark-mode-enabled .user-name {
    color: #4da3ff;
  }
  
  body.dark-mode-enabled .info-card h3,
  body.dark-mode-enabled .info-card-status h3 {
    color: #4da3ff;
  }
  
  body.dark-mode-enabled .status-item,
  body.dark-mode-enabled .time-item {
    background-color: #2d3748;
  }
  
  body.dark-mode-enabled .modal-container,
  body.dark-mode-enabled .modal-header {
    background-color: #1e2a38;
    color: #e0e0e0;
  }
  
  body.dark-mode-enabled .form-input {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #e0e0e0;
  }
  
  body.dark-mode-enabled .btn-cancel {
    background-color: #2d3748;
    color: #e0e0e0;
    border-color: #4a5568;
  }
}

/* Orientation changes */
@media screen and (orientation: landscape) and (max-height: 500px) {
  .profile-container {
    margin-top: 5px;
  }
  
  .avatar {
    width: 100px;
    height: 100px;
  }
  
  .avatar-container {
    margin-bottom: 10px;
    margin-top: 10px;
  }
  
  .user-name {
    margin-top: 10px;
    font-size: 1.3rem;
  }
  
  .modal-container {
    max-height: 85vh;
  }
}

/* RTL support */
[dir="rtl"] .info-item svg {
  margin-right: 0;
  margin-left: 10px;
}

[dir="rtl"] .time-icon {
  margin-right: 0;
  margin-left: 10px;
}

[dir="rtl"] .password-toggle-btn {
  right: auto;
  left: 10px;
}

/* Improved buttons for mobile */
@media (hover: none) {
  .file-upload-button,
  .edit-profile-button,
  .change-password-button,
  .btn-save,
  .btn-cancel {
    padding: 12px 20px; /* Larger touch targets */
  }
}

/* Fix for fixed positioning on iOS */
@supports (-webkit-touch-callout: none) {
  .action-buttons-container {
    position: fixed;
    bottom: 80px; /* Account for iOS Safari bottom bar */
  }
}
</style>





