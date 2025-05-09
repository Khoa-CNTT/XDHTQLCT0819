<template>
  <div class="rocker-user-management">
    <div class="card shadow-sm mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-users me-2"></i>Quản lý người dùng
        </h5>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <input
              type="text"
              v-model="search"
              class="form-control"
              placeholder="Tìm kiếm người dùng..."
            />
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Hình ảnh</th>
                <th>Tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th>Trạng Thái</th>
                <th>Tình Trạng</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(user, index) in filteredUsers" :key="user.id">
                <td>{{ index + 1 }}</td>
                <td>
                  <img
                    :src="user.avatar ? apiImage(user.avatar) : defaultAvatar"
                    class="avatar-sm rounded-circle"
                  />
                </td>
                <td>{{ user.fullName }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.phone }}</td>
                <td>{{ user.address }}</td>
                <td>
                  <span
                    :class="{
                      badge: true,
                      'badge-success': user.status === 1,
                      'badge-danger': user.status !== 1,
                    }"
                    :style="{
                      backgroundColor:
                        user.status === 1 ? '#d4edda' : '#f8d7da',
                      color: user.status === 1 ? '#155724' : '#721c24',
                    }"
                  >
                    {{ user.status === 1 ? "Đang hoạt động" : "Tạm ngưng" }}
                  </span>
                </td>
                <td>
                  <button
                    :class="{
                      btn: true,
                      'btn-outline-danger': user.isBlocked === 1,
                      'btn-outline-success': user.isBlocked !== 1,
                    }"
                    @click="toggleBlockStatus(user.id)"
                  >
                    {{ user.isBlocked === 1 ? "Đã bị khoá" : "Đã Kích Hoạt" }}
                  </button>
                </td>

                <td>
                  <button
                    class="btn btn-sm btn-warning me-2"
                    @click="openEdit(user)"
                  >
                    Sửa
                  </button>
                  <button
                    class="btn btn-sm btn-danger"
                    @click="deleteUser(user.id)"
                  >
                    Xoá
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
          <button
            class="btn btn-outline-primary me-2"
            @click="prevPage"
            :disabled="page === 1"
          >
            Trước
          </button>
          <button
            class="btn btn-outline-primary"
            @click="nextPage"
            :disabled="page * perPage >= totalUsers"
          >
            Sau
          </button>
        </div>
      </div>
    </div>

    <div v-if="isEditing" class="modal-backdrop">
      <div class="modal-content shadow p-4">
        <h5 class="mb-3">Cập nhật người dùng</h5>
        <form @submit.prevent="submitEdit">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Họ tên</label>
              <input
                v-model="selectedUser.fullName"
                type="text"
                class="form-control"
                required
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Tên đăng nhập</label>
              <input
                v-model="selectedUser.username"
                type="text"
                class="form-control"
                required
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input
                v-model="selectedUser.email"
                type="email"
                class="form-control"
                disabled
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Số điện thoại</label>
              <input
                v-model="selectedUser.phone"
                type="text"
                class="form-control"
              />
            </div>
            <div class="col-md-12">
              <label class="form-label">Địa chỉ</label>
              <input
                v-model="selectedUser.address"
                type="text"
                class="form-control"
              />
            </div>
            <div class="col-md-12">
              <label class="form-label">Ảnh đại diện</label>
              <input
                type="file"
                class="form-control"
                @change="onAvatarSelected"
                accept="image/jpg,png"
              />
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-secondary" @click="cancelEdit">
              Huỷ
            </button>
            <button type="submit" class="btn btn-success">Cập nhật</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";

export default {
  name: "UserManagementRocker",
  data() {
    return {
      users: [],
      search: "",
      sortField: "fullName",
      sortOrder: "asc",
      page: 1,
      perPage: 5,
      defaultAvatar: "/default-avatar.png",
      selectedUser: null,
      isEditing: false,
      avatarFile: null,
    };
  },
  computed: {
    filteredUsers() {
      const filtered = this.users.filter(
        (user) =>
          user.fullName.toLowerCase().includes(this.search.toLowerCase()) ||
          user.email.toLowerCase().includes(this.search.toLowerCase())
      );
      const sorted = filtered.sort((a, b) => {
        const fieldA = a[this.sortField]?.toLowerCase();
        const fieldB = b[this.sortField]?.toLowerCase();
        return this.sortOrder === "asc"
          ? fieldA.localeCompare(fieldB)
          : fieldB.localeCompare(fieldA);
      });
      return sorted.slice(
        (this.page - 1) * this.perPage,
        this.page * this.perPage
      );
    },
  },
  methods: {
    async fetchUsers() {
      try {
        const res = await axios.get(
          `/api/users?page=${this.page}&perPage=${this.perPage}`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          }
        );
        this.users = res.data.data;
        this.totalUsers = res.data.total;
      } catch (error) {
        console.error(error);
      }
    },
    async toggleBlockStatus(id) {
      const toast = useToast();
      try {
        const response = await axios.put(
          `/api/user/block/${id}`,
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          }
        );

        this.fetchUsers();
        toast.success(response.data.message);
      } catch (error) {
        toast.error("Thao tác thất bại!");
      }
    },
    apiImage(path) {
      return `http://localhost:8080/storage/${path}`;
    },
    openEdit(user) {
      this.selectedUser = { ...user };
      this.isEditing = true;
    },
    async submitEdit() {
      const toast = useToast();
      try {
        const payload = {
          id: this.selectedUser.id,
          username: this.selectedUser.username,
          phone: this.selectedUser.phone || "",
          fullName: this.selectedUser.fullName,
          address: this.selectedUser.address || "",
        };
        const res = await axios.put("/api/user/update", payload, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        if (this.avatarFile) {
          const formData = new FormData();
          formData.append("avatar", this.avatarFile);
          formData.append("id", this.selectedUser.id);
          await axios.post("/api/user/avatar", formData, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
              "Content-Type": "multipart/form-data",
            },
          });
        }

        this.isEditing = false;
        this.fetchUsers();
        toast.success("Cập nhật thành công!");
      } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
          Object.keys(errors).forEach((field) => {
            errors[field].forEach((msg) => {
              toast.error(`${msg}`, {
                timeout: 3000,
                position: "top-right",
              });
            });
          });
        } else {
          toast.error("Cập nhật thất bại!", {
            timeout: 3000,
            position: "top-right",
          });
        }
      }
    },
    onAvatarSelected(e) {
      this.avatarFile = e.target.files[0];
    },
    cancelEdit() {
      this.selectedUser = null;
      this.avatarFile = null;
      this.isEditing = false;
    },
    async deleteUser(id) {
      const toast = useToast();
      const result = await Swal.fire({
        title: "Xác nhận xoá",
        text: "Bạn có chắc chắn muốn xoá người dùng này không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Xoá",
        cancelButtonText: "Huỷ",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/api/user/${id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          this.users = this.users.filter((u) => u.id !== id);
          toast.success("Xoá thành công!");
        } catch (err) {
          toast.error("Xoá thất bại!");
          console.error(err);
        }
      }
    },
    nextPage() {
      if (this.page * this.perPage < this.totalUsers) {
        this.page++;
        this.fetchUsers();
      }
    },

    prevPage() {
      if (this.page > 1) {
        this.page--;
        this.fetchUsers();
      }
    },
  },
  mounted() {
    this.fetchUsers();
  },
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 10px;
  max-width: 600px;
  width: 100%;
}

.avatar-sm {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid #e5e7eb;
}
</style>
