<template>
  <div class="rocker-user-management">
    <div class="card shadow-sm mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-users me-2"></i>Quản lý liên hệ người dùng
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
              placeholder="Tìm kiếm tên người liên hệ..."
            />
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Mô tả</th>
                <th>Ngày tạo liên hệ</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(contact, index) in filteredContacts"
                :key="contact.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ contact.name }}</td>
                <td>{{ contact.email }}</td>
                <td>{{ contact.description }}</td>
                <td>{{ formatDate(contact.created_at) }}</td>
                <td>
                  <button
                    class="btn btn-sm btn-warning me-2"
                    @click="openEdit(contact)"
                  >
                    Sửa
                  </button>
                  <button
                    class="btn btn-sm btn-danger"
                    @click="deleteContact(contact.id)"
                  >
                    Xoá
                  </button>
                </td>
              </tr>
              <tr v-if="filteredContacts.length === 0">
                <td colspan="6" class="text-center">Không có dữ liệu</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal chỉnh sửa -->
    <div v-if="isEditing" class="modal-backdrop">
      <div class="modal-content shadow p-4">
        <h5 class="mb-3">Cập nhật liên hệ</h5>
        <form @submit.prevent="submitEdit">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Họ tên</label>
              <input
                v-model="selectedContact.name"
                type="text"
                class="form-control"
                required
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input
                v-model="selectedContact.email"
                type="email"
                class="form-control"
                disabled
              />
            </div>
            <div class="col-md-12">
              <label class="form-label">Mô tả</label>
              <textarea
                v-model="selectedContact.description"
                class="form-control"
                rows="3"
                required
              ></textarea>
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
  name: "ContactAdmin",
  data() {
    return {
      search: "",
      contacts: [], // ensure this starts as an array
      isEditing: false,
      selectedContact: {},
    };
  },
  computed: {
    filteredContacts() {
      // Only filter if contacts is an array
      if (Array.isArray(this.contacts)) {
        return this.contacts.filter((contact) =>
          contact.name.toLowerCase().includes(this.search.toLowerCase())
        );
      }
      return [];
    },
  },
  methods: {
    formatDate(dateStr) {
      const options = { year: "numeric", month: "2-digit", day: "2-digit" };
      return new Date(dateStr).toLocaleDateString("vi-VN", options);
    },
    async fetchContacts() {
      try {
        const res = await axios.get("/api/contact", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        if (Array.isArray(res.data.data)) {
          this.contacts = res.data.data;
        } else if (res.data && Array.isArray(res.data.data)) {
          this.contacts = res.data.data;
        } else {
          this.contacts = [];
        }
      } catch (error) {
        console.error("Lỗi tải dữ liệu liên hệ:", error);
      }
    },
    openEdit(contact) {
      this.isEditing = true;
      this.selectedContact = { ...contact };
    },
    cancelEdit() {
      this.isEditing = false;
      this.selectedContact = {};
    },
    async submitEdit() {
      const toast = useToast();
      try {
        await axios.put(
          `/api/contact/${this.selectedContact.id}`,
          {
            name: this.selectedContact.name,
            description: this.selectedContact.description,
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          }
        );
        toast.success("Cập nhật thành công!");
        this.fetchContacts();
        this.cancelEdit();
      } catch (error) {
        console.log(error.response.data.message);
        
        const errors = error.response?.data?.errors;
        if (errors) {
          Object.values(errors)
            .flat()
            .forEach((msg) => {
              toast.error(msg, { timeout: 3000 });
            });
        } else {
          toast.error("Cập nhật thất bại!", { timeout: 3000 });
        }
      }
    },
    async deleteContact(id) {
      const toast = useToast();
      const result = await Swal.fire({
        title: "Xác nhận xoá",
        text: "Bạn có chắc chắn muốn xoá liên hệ này không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Xoá",
        cancelButtonText: "Huỷ",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/api/contact/${id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          toast.success("Xoá thành công!");
          this.fetchContacts();
        } catch (error) {
          toast.error("Xoá thất bại!");
        }
      }
    },
  },
  mounted() {
    this.fetchContacts();
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
</style>
