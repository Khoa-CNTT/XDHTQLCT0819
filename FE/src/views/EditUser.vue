<template>
  <div class="rocker-user-management">
      <div class="card shadow-sm mb-4">
          <div class="card-body d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="fas fa-users me-2"></i>Quản lý người dùng</h5>
          </div>
      </div>

      <div class="card shadow-sm mb-4">
          <div class="card-body">
              <div class="row g-3 mb-3">
                  <div class="col-md-4">
                      <input type="text" v-model="search" class="form-control" placeholder="Tìm kiếm người dùng..." />
                  </div>
                  <div class="col-md-3">
                      <select class="form-select" v-model="sortField">
                          <option value="fullName">Tên</option>
                          <option value="email">Email</option>
                      </select>
                  </div>
                  <div class="col-md-3">
                      <select class="form-select" v-model="sortOrder">
                          <option value="asc">Tăng dần</option>
                          <option value="desc">Giảm dần</option>
                      </select>
                  </div>
              </div>

              <div class="table-responsive">
                  <table class="table table-hover align-middle">
                      <thead>
                          <tr>
                              <th>#</th>
                              <th>Avatar</th>
                              <th>Họ tên</th>
                              <th>Email</th>
                              <th>SĐT</th>
                              <th>Địa chỉ</th>
                              <th>Hành động</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr v-for="(user, index) in filteredUsers" :key="user.id">
                              <td>{{ index + 1 }}</td>
                              <td>
                                  <img :src="user.avatar ? apiImage(user.avatar) : defaultAvatar"
                                      class="avatar-sm rounded-circle" />
                              </td>
                              <td>{{ user.fullName }}</td>
                              <td>{{ user.email }}</td>
                              <td>{{ user.phone }}</td>
                              <td>{{ user.address }}</td>
                              <td>
                                  <button class="btn btn-sm btn-warning me-2" @click="openEdit(user)">Sửa</button>
                                  <button class="btn btn-sm btn-danger" @click="deleteUser(user.id)">Xoá</button>
                              </td>
                          </tr>
                      </tbody>
                  </table>
              </div>

              <div class="d-flex justify-content-center mt-3">
                  <button class="btn btn-outline-primary me-2" @click="prevPage" :disabled="page === 1">Trước</button>
                  <button class="btn btn-outline-primary" @click="nextPage"
                      :disabled="page * perPage >= users.length">Sau</button>
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
                          <input v-model="selectedUser.fullName" type="text" class="form-control" required />
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">Tên đăng nhập</label>
                          <input v-model="selectedUser.username" type="text" class="form-control" required />
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">Email</label>
                          <input v-model="selectedUser.email" type="email" class="form-control" required />
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">Số điện thoại</label>
                          <input v-model="selectedUser.phone" type="text" class="form-control" />
                      </div>
                      <div class="col-md-12">
                          <label class="form-label">Địa chỉ</label>
                          <input v-model="selectedUser.address" type="text" class="form-control" />
                      </div>
                      <div class="col-md-12">
                          <label class="form-label">Ảnh đại diện</label>
                          <input type="file" class="form-control" @change="onAvatarSelected" accept="image/jpg,png" />
                      </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2 mt-3">
                      <button type="button" class="btn btn-secondary" @click="cancelEdit">Huỷ</button>
                      <button type="submit" class="btn btn-success">Cập nhật</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

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
          const filtered = this.users.filter((user) =>
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
          return sorted.slice((this.page - 1) * this.perPage, this.page * this.perPage);
      },
  },
  methods: {
      async fetchUsers() {
          const res = await axios.get("/api/users", {
              headers: { Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
          });
          this.users = res.data;
      },
      apiImage(path) {
          return `http://localhost:8000/storage/${path}`;
      },
      openEdit(user) {
          this.selectedUser = { ...user };
          this.isEditing = true;
      },
      async submitEdit() {
          const toast = useToast();
          try {
              const payload = {
                  username: this.selectedUser.username,
                  email: this.selectedUser.email,
                  phone: this.selectedUser.phone || '',
                  fullName: this.selectedUser.fullName,
                  address: this.selectedUser.address || '',
              };
              await axios.put("/api/user/update-profile", payload, {
                  headers: { Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
              });
              if (this.avatarFile) {
                  const formData = new FormData();
                  formData.append("avatar", this.avatarFile);
                  await axios.post("/api/user/avatar", formData, {
                      headers: {
                          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                          "Content-Type": "multipart/form-data",
                      },
                  });
              }
              this.isEditing = false;
              this.fetchUsers();
              toast.success("✅ Cập nhật thành công!");
          } catch (error) {
              toast.error("❌ Cập nhật thất bại!");
              console.error(error);
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
              title: 'Xác nhận xoá',
              text: '⚠️ Bạn có chắc chắn muốn xoá người dùng này không?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Xoá',
              cancelButtonText: 'Huỷ',
          });

          if (result.isConfirmed) {
              try {
                  await axios.delete(`/api/user/${id}`, {
                      headers: { Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
                  });
                  this.users = this.users.filter(u => u.id !== id);
                  toast.success("🗑️ Xoá thành công!");
              } catch (err) {
                  toast.error("❌ Xoá thất bại!");
                  console.error(err);
              }
          }
      },
      nextPage() {
          this.page++;
      },
      prevPage() {
          if (this.page > 1) this.page--;
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
