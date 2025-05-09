<template>
  <div class="container mx-auto py-8 px-4">
    <div class="grid gap-8 md:grid-cols-3">
      <!-- Left column - Avatar and basic info -->
      <div class="md:col-span-1">
        <div class="card">
          <div class="card-header flex flex-col items-center text-center">
            <div class="relative mb-4">
              <div class="avatar h-32 w-32">
                <img
                  v-if="userData.avatar"
                  :src="`/${userData.avatar}`"
                  :alt="userData.fullName"
                  class="avatar-image"
                />
                <div v-else class="avatar-fallback text-2xl">
                  {{ userData.fullName.substring(0, 2).toUpperCase() }}
                </div>
              </div>
              <span
                v-if="userData.isActived"
                class="badge absolute bottom-0 right-0 bg-[#00A3E0] text-white"
              >
                Hoạt động
              </span>
            </div>
            <h2 class="card-title text-2xl text-[#00A3E0]">
              {{ userData.fullName }}
            </h2>
            <p class="card-description flex items-center justify-center gap-1">
              <user-icon class="h-4 w-4" />
              {{ userData.username }}
            </p>
            <span class="badge-outline mt-2 border-[#00A3E0] text-[#00A3E0]">
              {{ translateRole(userData.role).toUpperCase() }}
            </span>
          </div>
          <div class="card-content space-y-4">
            <div class="flex items-start gap-2">
              <mail-icon class="h-5 w-5 text-[#00A3E0] mt-0.5" />
              <div class="space-y-1">
                <p class="text-sm font-medium text-[#00A3E0]">Email</p>
                <p class="text-sm text-muted-foreground">
                  {{ userData.email }}
                </p>
              </div>
            </div>
            <div class="flex items-start gap-2">
              <phone-icon class="h-5 w-5 text-[#00A3E0] mt-0.5" />
              <div class="space-y-1">
                <p class="text-sm font-medium text-[#00A3E0]">Điện thoại</p>
                <p class="text-sm text-muted-foreground">
                  {{ userData.phone }}
                </p>
              </div>
            </div>
            <div class="flex items-start gap-2">
              <map-pin-icon class="h-5 w-5 text-[#00A3E0] mt-0.5" />
              <div class="space-y-1">
                <p class="text-sm font-medium text-[#00A3E0]">Địa chỉ</p>
                <p class="text-sm text-muted-foreground">
                  {{ userData.address }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column - Detailed information -->
      <div class="md:col-span-2">
        <div class="tabs-container w-full">
          <div class="tabs-list grid w-full grid-cols-2">
            <button
              @click="activeTab = 'account'"
              :class="[
                'tab-trigger',
                activeTab === 'account'
                  ? 'bg-[#00A3E0] text-white'
                  : 'bg-[#E8F7FC]',
              ]"
            >
              Thông tin tài khoản
            </button>
            <button
              @click="activeTab = 'financial'"
              :class="[
                'tab-trigger',
                activeTab === 'financial'
                  ? 'bg-[#00A3E0] text-white'
                  : 'bg-[#E8F7FC]',
              ]"
            >
              Chi tiết tài chính
            </button>
          </div>

          <!-- Account Tab Content -->
          <div v-if="activeTab === 'account'" class="space-y-4 mt-4">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-black">Trạng thái tài khoản</h3>
              </div>
              <div class="card-content space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    class="flex items-center justify-between p-4 border rounded-lg"
                  >
                    <span class="font-medium text-black">Trạng thái</span>
                    <span
                      :class="[
                        'badge',
                        userData.status
                          ? 'bg-[#00A3E0] text-white'
                          : 'bg-red-500 text-white',
                      ]"
                    >
                      {{ userData.status ? "Hoạt động" : "Không hoạt động" }}
                    </span>
                  </div>
                  <div
                    class="flex items-center justify-between p-4 border rounded-lg"
                  >
                    <span class="font-medium text-black">Tình trạng</span>
                    <span
                      :class="[
                        'badge',
                        !userData.isBlocked
                          ? 'bg-[#4CAF50] text-white'
                          : 'bg-red-500 text-white',
                      ]"
                    >
                      {{ userData.isBlocked ? "Đã khóa" : "Không khóa" }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-black">Thời gian tài khoản</h3>
              </div>
              <div class="card-content space-y-4">
                <div class="flex items-start gap-2">
                  <calendar-days-icon class="h-5 w-5 text-[#00A3E0] mt-0.5" />
                  <div class="space-y-1">
                    <p class="text-sm font-medium text-black">Ngày tạo</p>
                    <p class="text-sm text-muted-foreground">
                      {{ formatDate(userData.created_at) }}
                    </p>
                  </div>
                </div>
                <div class="flex items-start gap-2">
                  <calendar-days-icon class="h-5 w-5 text-[#00A3E0] mt-0.5" />
                  <div class="space-y-1">
                    <p class="text-sm font-medium text-black">
                      Cập nhật lần cuối
                    </p>
                    <p class="text-sm text-muted-foreground">
                      {{ formatDate(userData.updated_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Financial Tab Content -->
          <div v-if="activeTab === 'financial'" class="space-y-4 mt-4">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-black">Tổng quan tài chính</h3>
              </div>
              <div class="card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="card">
                    <div class="card-header pb-2">
                      <h4 class="card-title text-sm font-medium text-black">
                        Thu nhập hàng tháng
                      </h4>
                    </div>
                    <div class="card-content">
                      <div class="flex items-center">
                        <dollar-sign-icon class="h-4 w-4 text-[#00A3E0] mr-1" />
                        <span class="text-2xl font-bold">{{
                          formatCurrency(userData.monthly_income)
                        }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-header pb-2">
                      <h4 class="card-title text-sm font-medium text-black">
                        Chi tiêu hàng tháng
                      </h4>
                    </div>
                    <div class="card-content">
                      <div class="flex items-center">
                        <dollar-sign-icon class="h-4 w-4 text-[#00A3E0] mr-1" />
                        <span class="text-2xl font-bold">
                          {{
                            formatCurrency(userData.monthly_customer_spending)
                          }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-black">Cài đặt tiền tệ</h3>
              </div>
              <div class="card-content">
                <div class="flex items-center gap-2">
                  <dollar-sign-icon class="h-5 w-5 text-[#00A3E0]" />
                  <span class="font-medium text-black">Tiền tệ ưa thích:</span>
                  <span class="badge-outline border-[#00A3E0] text-[#00A3E0]">
                    {{ userData.currency }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-4">
          <button
            @click="openEditModal"
            class="btn-outline border-[#00A3E0] text-[#00A3E0] hover:bg-[#E8F7FC]"
          >
            Chỉnh sửa hồ sơ
          </button>
          <button class="btn bg-[#00A3E0] hover:bg-[#0089BD] text-white">
            Lưu thay đổi
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Profile Modal -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Chỉnh sửa hồ sơ</h3>
          <button @click="closeModal" class="modal-close">
            <x-icon class="h-5 w-5" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveChanges">
            <div class="form-group">
              <div class="avatar-upload">
                <div class="avatar h-24 w-24 mx-auto">
                  <img
                    v-if="editedUser.avatar"
                    :src="`/${editedUser.avatar}`"
                    :alt="editedUser.fullName"
                    class="avatar-image"
                  />
                  <div v-else class="avatar-fallback text-xl">
                    {{ editedUser.fullName.substring(0, 2).toUpperCase() }}
                  </div>
                </div>
                <div class="avatar-upload-controls mt-2 text-center">
                  <label for="avatar-upload" class="avatar-upload-btn">
                    <upload-icon class="h-4 w-4 mr-1" />
                    Tải ảnh lên
                  </label>
                  <input
                    id="avatar-upload"
                    type="file"
                    accept="image/*"
                    class="hidden"
                  />
                </div>
              </div>

              <div class="form-row">
                <label class="form-label">Họ và tên</label>
                <input
                  v-model="editedUser.fullName"
                  type="text"
                  class="form-input"
                  :class="{ 'input-error': errors.fullName }"
                />
                <p v-if="errors.fullName" class="error-message">
                  {{ errors.fullName }}
                </p>
              </div>

              <div class="form-row">
                <label class="form-label">Tên người dùng</label>
                <input
                  v-model="editedUser.username"
                  type="text"
                  class="form-input"
                  :class="{ 'input-error': errors.username }"
                />
                <p v-if="errors.username" class="error-message">
                  {{ errors.username }}
                </p>
              </div>

              <div class="form-row">
                <label class="form-label">Email</label>
                <input
                  v-model="editedUser.email"
                  type="email"
                  class="form-input"
                  :class="{ 'input-error': errors.email }"
                />
                <p v-if="errors.email" class="error-message">
                  {{ errors.email }}
                </p>
              </div>

              <div class="form-row">
                <label class="form-label">Điện thoại</label>
                <input
                  v-model="editedUser.phone"
                  type="tel"
                  class="form-input"
                  :class="{ 'input-error': errors.phone }"
                />
                <p v-if="errors.phone" class="error-message">
                  {{ errors.phone }}
                </p>
              </div>

              <div class="form-row">
                <label class="form-label">Địa chỉ</label>
                <textarea
                  v-model="editedUser.address"
                  class="form-textarea"
                  :class="{ 'input-error': errors.address }"
                ></textarea>
                <p v-if="errors.address" class="error-message">
                  {{ errors.address }}
                </p>
              </div>

              <div class="form-row">
                <label class="form-label">Tiền tệ</label>
                <select v-model="editedUser.currency" class="form-select">
                  <option value="VND">VND - Việt Nam Đồng</option>
                  <option value="USD">USD - Đô la Mỹ</option>
                  <option value="EUR">EUR - Euro</option>
                </select>
              </div>

              <div class="form-row">
                <label class="form-label">Thu nhập hàng tháng</label>
                <div class="form-input-group">
                  <span class="input-prefix">{{ editedUser.currency }}</span>
                  <input
                    v-model.number="editedUser.monthly_income"
                    type="number"
                    class="form-input with-prefix"
                  />
                </div>
              </div>

              <div class="form-row">
                <label class="form-label">Chi tiêu hàng tháng</label>
                <div class="form-input-group">
                  <span class="input-prefix">{{ editedUser.currency }}</span>
                  <input
                    v-model.number="editedUser.monthly_customer_spending"
                    type="number"
                    class="form-input with-prefix"
                  />
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                @click="closeModal"
                class="btn-outline border-gray-300 text-gray-700 hover:bg-gray-100"
              >
                Hủy bỏ
              </button>
              <button
                type="submit"
                class="btn bg-[#00A3E0] hover:bg-[#0089BD] text-white"
              >
                Lưu thay đổi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from "vue";
import {
  User as UserIcon,
  Mail as MailIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  CalendarDays as CalendarDaysIcon,
  DollarSign as DollarSignIcon,
  X as XIcon,
  Upload as UploadIcon,
} from "lucide-vue-next";

export default {
  name: "UserProfile",
  components: {
    UserIcon,
    MailIcon,
    PhoneIcon,
    MapPinIcon,
    CalendarDaysIcon,
    DollarSignIcon,
    XIcon,
    UploadIcon,
  },
  setup() {
    const activeTab = ref("account");
    const isModalOpen = ref(false);

    const userData = ref({
      id: 3,
      fullName: "HUY Lê",
      username: "Letronghuy1111",
      email: "hale22.02031982@gmail.com",
      phone: "09830571330",
      address: "121321",
      avatar: "avatars/bIr7RqkWiAG38uqYNMyiNQmhf3CTfHh45vHoExKh.png",
      created_at: "2025-05-09T05:29:23.000000Z",
      updated_at: "2025-05-09T08:23:10.000000Z",
      currency: "VND",
      monthly_income: 0,
      monthly_customer_spending: 0,
      isActived: 1,
      isBlocked: 0,
      role: "user",
      status: true,
    });

    // Create a copy for editing
    const editedUser = ref({ ...userData.value });

    // Form validation errors
    const errors = reactive({
      fullName: "",
      username: "",
      email: "",
      phone: "",
      address: "",
    });

    // Open edit modal
    const openEditModal = () => {
      // Reset the edited user data to the current user data
      editedUser.value = { ...userData.value };
      // Clear any previous errors
      Object.keys(errors).forEach((key) => {
        errors[key] = "";
      });
      isModalOpen.value = true;
    };

    // Close modal
    const closeModal = () => {
      isModalOpen.value = false;
    };

    // Validate form
    const validateForm = () => {
      let isValid = true;

      // Reset errors
      Object.keys(errors).forEach((key) => {
        errors[key] = "";
      });

      // Validate fullName
      if (!editedUser.value.fullName.trim()) {
        errors.fullName = "Họ và tên không được để trống";
        isValid = false;
      }

      // Validate username
      if (!editedUser.value.username.trim()) {
        errors.username = "Tên người dùng không được để trống";
        isValid = false;
      }

      // Validate email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!editedUser.value.email.trim()) {
        errors.email = "Email không được để trống";
        isValid = false;
      } else if (!emailRegex.test(editedUser.value.email)) {
        errors.email = "Email không hợp lệ";
        isValid = false;
      }

      // Validate phone
      const phoneRegex = /^[0-9]{10,11}$/;
      if (!editedUser.value.phone.trim()) {
        errors.phone = "Số điện thoại không được để trống";
        isValid = false;
      } else if (!phoneRegex.test(editedUser.value.phone)) {
        errors.phone = "Số điện thoại không hợp lệ (10-11 số)";
        isValid = false;
      }

      // Validate address
      if (!editedUser.value.address.trim()) {
        errors.address = "Địa chỉ không được để trống";
        isValid = false;
      }

      return isValid;
    };

    // Save changes
    const saveChanges = () => {
      if (validateForm()) {
        // Update the user data with the edited values
        userData.value = {
          ...editedUser.value,
          updated_at: new Date().toISOString(),
        };
        // Close the modal
        closeModal();
        // Here you would typically send the updated data to your API
        console.log("Saved user data:", userData.value);
      }
    };

    // Translate role to Vietnamese
    const translateRole = (role) => {
      const translations = {
        user: "người dùng",
        admin: "quản trị viên",
        moderator: "điều hành viên",
      };
      return translations[role.toLowerCase()] || role;
    };

    // Format dates for better readability
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

    // Format currency values
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: userData.value.currency,
      }).format(amount);
    };

    return {
      activeTab,
      userData,
      editedUser,
      isModalOpen,
      errors,
      openEditModal,
      closeModal,
      saveChanges,
      formatDate,
      formatCurrency,
      translateRole,
    };
  },
};
</script>

<style scoped>
.container {
  width: 100%;
  margin-left: auto;
  margin-right: auto;
}

.grid {
  display: grid;
}

.card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  overflow: hidden;
  margin-bottom: 1rem;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.card-content {
  padding: 1.5rem;
}

.card-title {
  font-weight: 600;
  font-size: 1.25rem;
}

.card-description {
  color: #6b7280;
  font-size: 0.875rem;
}

.avatar {
  position: relative;
  border-radius: 9999px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #e5e7eb;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  font-weight: 500;
}

.badge {
  display: inline-flex;
  align-items: center;
  border-radius: 9999px;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-outline {
  display: inline-flex;
  align-items: center;
  border-radius: 9999px;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
  background: transparent;
  border: 1px solid;
}

.tabs-container {
  width: 100%;
}

.tabs-list {
  display: grid;
  border-radius: 0.375rem;
  overflow: hidden;
}

.tab-trigger {
  padding: 0.75rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  transition: background-color 0.2s, color 0.2s;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background-color 0.2s, color 0.2s;
}

.btn-outline {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  background: transparent;
  border: 1px solid;
  transition: background-color 0.2s, color 0.2s;
}

.text-muted-foreground {
  color: #6b7280;
}

.space-y-1 > * + * {
  margin-top: 0.25rem;
}

.space-y-4 > * + * {
  margin-top: 1rem;
}

.relative {
  position: relative;
}

.absolute {
  position: absolute;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.items-start {
  align-items: flex-start;
}

.justify-center {
  justify-content: center;
}

.justify-between {
  justify-content: space-between;
}

.justify-end {
  justify-content: flex-end;
}

.gap-1 {
  gap: 0.25rem;
}

.gap-2 {
  gap: 0.5rem;
}

.gap-4 {
  gap: 1rem;
}

.mt-0\.5 {
  margin-top: 0.125rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.mt-4 {
  margin-top: 1rem;
}

.mt-6 {
  margin-top: 1.5rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.mr-1 {
  margin-right: 0.25rem;
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

.text-center {
  text-align: center;
}

.text-2xl {
  font-size: 1.5rem;
}

.text-xl {
  font-size: 1.25rem;
}

.text-sm {
  font-size: 0.875rem;
}

.font-medium {
  font-weight: 500;
}

.font-bold {
  font-weight: 700;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.border {
  border: 1px solid #e5e7eb;
}

.p-4 {
  padding: 1rem;
}

.pb-2 {
  padding-bottom: 0.5rem;
}

.h-32 {
  height: 8rem;
}

.w-32 {
  width: 8rem;
}

.h-24 {
  height: 6rem;
}

.w-24 {
  width: 6rem;
}

.h-5 {
  height: 1.25rem;
}

.w-5 {
  width: 1.25rem;
}

.h-4 {
  height: 1rem;
}

.w-4 {
  width: 1rem;
}

.bottom-0 {
  bottom: 0;
}

.right-0 {
  right: 0;
}

.hidden {
  display: none;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-container {
  background-color: white;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
    0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.modal-close {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #6b7280;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  margin-top: 1.5rem;
}

/* Form styles */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.form-input,
.form-textarea,
.form-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #00a3e0;
  box-shadow: 0 0 0 2px rgba(0, 163, 224, 0.2);
}

.form-textarea {
  min-height: 5rem;
  resize: vertical;
}

.input-error {
  border-color: #ef4444;
}

.error-message {
  color: #ef4444;
  font-size: 0.75rem;
}

.form-input-group {
  display: flex;
  align-items: center;
  width: 100%;
}

.input-prefix {
  display: flex;
  align-items: center;
  padding: 0 0.75rem;
  background-color: #f3f4f6;
  border: 1px solid #d1d5db;
  border-right: none;
  border-radius: 0.375rem 0 0 0.375rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.form-input.with-prefix {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.5rem;
}

.avatar-upload-btn {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  background-color: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  color: #374151;
  cursor: pointer;
  transition: background-color 0.2s;
}

.avatar-upload-btn:hover {
  background-color: #e5e7eb;
}

/* Responsive styles */
@media (min-width: 768px) {
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .md\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .md\:col-span-1 {
    grid-column: span 1 / span 1;
  }

  .md\:col-span-2 {
    grid-column: span 2 / span 2;
  }
}
</style>
