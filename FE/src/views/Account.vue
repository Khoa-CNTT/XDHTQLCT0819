<template>
    <div class="rocker-account">
      <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Quản lý tài khoản</h5>
          <button class="btn btn-primary" @click="openAddForm">+ Thêm tài khoản</button>
        </div>
      </div>
  
      <div class="card shadow-sm">
        <div class="card-body">
          <input
            type="text"
            v-model="search"
            class="form-control mb-3"
            placeholder="Tìm kiếm tài khoản..."
          />
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Tên tài khoản</th>
                  <th>Loại</th>
                  <th>Số thẻ</th>
                  <th>Hết hạn</th>
                  <th>PIN</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(acc, index) in filteredAccounts" :key="acc.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ acc.name }}</td>
                  <td>{{ acc.type }}</td>
                  <td>{{ acc.number_card }}</td>
                  <td>{{ formatDate(acc.expired) }}</td>
                  <td>{{ acc.pin_code }}</td>
                  <td>
                    <button class="btn btn-sm btn-warning me-2" @click="openEditForm(acc)">Sửa</button>
                    <button class="btn btn-sm btn-danger" @click="deleteAccount(acc.id)">Xoá</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
  
      <div v-if="showForm" class="modal-backdrop">
        <div class="modal-content shadow p-4">
          <h5 class="mb-3">{{ isEditing ? 'Cập nhật' : 'Thêm' }} tài khoản</h5>
          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label class="form-label">Tên tài khoản</label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Loại</label>
              <select v-model="form.type" class="form-select" required>
                <option value="">-- Chọn loại --</option>
                <option value="vietcombank">Vietcombank</option>
                <option value="vietinbank">Vietinbank</option>
                <option value="mbank">MB Bank</option>
                <option value="sacombank">Sacombank</option>
                <option value="vpbank">VPBank</option>
                <option value="agribank">Agribank</option>
                <option value="crypto">Crypto</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Số thẻ</label>
              <input v-model.number="form.number_card" type="number" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Ngày hết hạn</label>
              <input v-model="form.expired" type="date" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">PIN</label>
              <input v-model.number="form.pin_code" type="number" class="form-control" required />
            </div>
            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" @click="cancelForm">Huỷ</button>
              <button type="submit" class="btn btn-success">Lưu</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'AccountManagementRocker',
    data() {
      return {
        search: '',
        showForm: false,
        isEditing: false,
        form: {
          id: null,
          name: '',
          type: '',
          number_card: '',
          expired: '',
          pin_code: ''
        },
        accounts: []
      };
    },
    computed: {
      filteredAccounts() {
        return this.accounts.filter(acc => acc.name.toLowerCase().includes(this.search.toLowerCase()));
      }
    },
    methods: {
      async fetchAccounts() {
        const res = await axios.get('/api/account', {
          headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
        });
        this.accounts = res.data;
      },
      formatDate(date) {
        return new Date(date).toLocaleDateString('vi-VN');
      },
      openAddForm() {
        this.showForm = true;
        this.isEditing = false;
        this.form = { id: null, name: '', type: '', number_card: '', expired: '', pin_code: '' };
      },
      openEditForm(account) {
        this.showForm = true;
        this.isEditing = true;
        this.form = { ...account };
      },
      async submitForm() {
        try {
          const payload = { ...this.form };
          if (this.isEditing) {
            await axios.put(`/api/account/${this.form.id}`, payload, {
              headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
            });
            alert('✅ Cập nhật tài khoản thành công!');
          } else {
            await axios.post('/api/account', payload, {
              headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
            });
            alert('✅ Thêm tài khoản thành công!');
          }
          this.showForm = false;
          this.fetchAccounts();
        } catch (err) {
          console.error(err);
          alert('❌ Thao tác thất bại. Vui lòng kiểm tra dữ liệu hoặc thử lại sau.');
        }
      },
      cancelForm() {
        this.showForm = false;
      },
      async deleteAccount(id) {
        if (!confirm('⚠️ Bạn có chắc chắn muốn xoá tài khoản này?')) return;
        try {
          await axios.delete(`/api/account/${id}`, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
          });
          this.fetchAccounts();
          alert('🗑️ Xoá tài khoản thành công!');
        } catch (err) {
          console.error(err);
          alert('❌ Xoá thất bại!');
        }
      }
    },
    mounted() {
      this.fetchAccounts();
    }
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
    max-width: 500px;
    width: 100%;
  }
  </style>
  