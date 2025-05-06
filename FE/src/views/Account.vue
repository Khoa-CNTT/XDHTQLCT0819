<template>
  <div class="rocker-account container-fluid px-2">
    <div class="card shadow-sm mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Quản lý tài khoản</h5>
        <button class="btn btn-primary" @click="openAddForm">+ Thêm tài khoản</button>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="row mb-3 g-2">
          <div class="col-md-6">
            <input v-model="search" type="text" class="form-control" placeholder="Tìm kiếm tài khoản..." />
          </div>
          <div class="col-md-6">
            <select v-model="filterType" class="form-select">
              <option value="">Tất cả loại tài khoản</option>
              <option value="mbank">MB Bank</option>
            </select>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Tên</th>
                <th>Loại</th>
                <th>Số thẻ</th>
                <th>Hết hạn</th>
                <th>Chính</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(acc, index) in filteredAccounts" :key="acc.id">
                <td>{{ index + 1 }}</td>
                <td>{{ acc.name }}</td>
                <!-- <td><img v-if="acc.type === 'mbank'" src="/images/mbbank.png" alt="MB" width="24" class="me-1" />{{ acc.type }}</td> -->
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img :src="getAccountIcon(acc.type)" :alt="acc.type" width="24" height="24" style="object-fit: contain" />
                    <span>{{ getDisplayType(acc.type) }}</span>
                  </div>
                </td>
                <td>{{ acc.number_card }}</td>
                <td>{{ formatDate(acc.expired) }}</td>
                <td>
                  <span v-if="acc.is_primary" class="badge bg-success">Chính</span>
                  <button v-else class="btn btn-sm btn-outline-secondary" @click="setPrimary(acc.id)">Chọn</button>
                </td>
                <td>
                  <button class="btn btn-sm btn-warning me-2" @click="openEditForm(acc)">Sửa</button>
                  <button class="btn btn-sm btn-danger" @click="deleteAccount(acc.id)">Xoá</button>
                </td>
              </tr>
              <tr v-if="filteredAccounts.length === 0">
                <td colspan="7" class="text-center">Không có tài khoản phù hợp</td>
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
              <option disabled value="">-- Chọn loại --</option>
              <option value="mbank">MB Bank</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Số thẻ</label>
            <input v-model="form.number_card" type="text" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Ngày hết hạn</label>
            <input v-model="form.expired" type="date" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Mật khẩu (PIN)</label>
            <input v-model="form.password" type="password" class="form-control" required />
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
import { useToast } from 'vue-toastification';

export default {
  name: 'AccountManagementRocker',
  data() {
    return {
      toast: useToast(),
      search: '',
      filterType: '',
      showForm: false,
      isEditing: false,
      form: {
        id: null,
        name: '',
        type: '',
        number_card: '',
        expired: '',
        password: ''
      },
      accounts: []
    };
  },
  computed: {
    filteredAccounts() {
      return this.accounts.filter(acc => {
        return (
          acc.name.toLowerCase().includes(this.search.toLowerCase()) &&
          (!this.filterType || acc.type === this.filterType)
        );
      });
    }
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString('vi-VN');
    },

    getDisplayType(type) {
    const map = {
      mbank: 'MB Bank',
      vcb: 'Vietcombank',
      tpbank: 'TPBank',
      crypto: 'Crypto Wallet',
      cash: 'Tiền mặt'
    };
    return map[type] || type;
  },
  getAccountIcon(type) {
    const iconMap = {
      mbank: '/images/mbbank.png',
      vcb: '/images/vietcombank.png',
      tpbank: '/images/tpbank.png',
      crypto: '/images/crypto.png',
      cash: '/images/cash.png'
    };
    return iconMap[type] || '/images/default-bank.png';
  },

    openAddForm() {
      this.showForm = true;
      this.isEditing = false;
      this.form = { id: null, name: '', type: '', number_card: '', expired: '', password: '' };
    },
    openEditForm(account) {
      this.showForm = true;
      this.isEditing = true;
      this.form = { ...account, password: '' };
    },
    async fetchAccounts() {
      try {
        const res = await axios.get('/api/account', {
          headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
        });
        this.accounts = res.data;
      } catch (err) {
        console.error(err);
        this.toast.error('❌ Không thể tải tài khoản.');
      }
    },
    async submitForm() {
      try {
        const payload = { ...this.form };
        if (this.isEditing) {
          await axios.put(`/api/account/${this.form.id}`, payload, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
          });
          this.toast.success('✅ Cập nhật tài khoản thành công!');
        } else {
          await axios.post('/api/account', payload, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
          });
          this.toast.success('✅ Thêm tài khoản thành công!');
        }
        this.showForm = false;
        await this.fetchAccounts();
      } catch (err) {
        console.error(err);
        if (err.response?.status === 422) {
          const messages = Object.values(err.response.data.errors || {}).flat().join(', ');
          this.toast.error(`❌ Lỗi: ${messages}`);
        } else {
          this.toast.error('❌ Thao tác thất bại!');
        }
      }
    },
    cancelForm() {
      this.showForm = false;
    },
    async deleteAccount(id) {
      if (!confirm('⚠️ Xác nhận xoá tài khoản?')) return;
      try {
        await axios.delete(`/api/account/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
        });
        await this.fetchAccounts();
        this.toast.success('🗑️ Đã xoá tài khoản!');
      } catch (err) {
        console.error(err);
        this.toast.error('❌ Không thể xoá!');
      }
    },
    async setPrimary(id) {
      try {
        await axios.put(`/api/account/set-primary-account/${id}`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
        });
        this.toast.success('⭐ Đã thiết lập tài khoản chính!');
        await this.fetchAccounts();
      } catch (err) {
        console.error(err);
        this.toast.error('❌ Không thể cập nhật tài khoản chính!');
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
  border-radius: 12px;
  max-width: 500px;
  width: 100%;
}
</style>
