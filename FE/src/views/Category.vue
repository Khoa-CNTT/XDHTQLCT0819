<template>
  <div class="rocker-category">
    <div class="card shadow-sm mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Quản lý danh mục</h5>
        <button class="btn btn-primary" @click="openAdd">+ Thêm danh mục</button>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <input
          type="text"
          v-model="search"
          class="form-control mb-3"
          placeholder="Tìm kiếm danh mục..."
        />
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th>Loại</th>
                <th>Biểu tượng</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(cat, index) in filteredCategories" :key="cat.id">
                <td>{{ index + 1 }}</td>
                <td>{{ cat.name }}</td>
                <td>{{ cat.slug }}</td>
                <td>{{ cat.type }}</td>
                <td><i :class="cat.icon"></i> {{ cat.icon }}</td>
                <td>
                  <button class="btn btn-sm btn-warning me-2" @click="openEdit(cat)">Sửa</button>
                  <button class="btn btn-sm btn-danger" @click="deleteCategory(cat.id)">Xoá</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="modal-backdrop">
      <div class="modal-content shadow p-4">
        <h5 class="mb-3">{{ isEditing ? 'Cập nhật' : 'Thêm' }} danh mục</h5>
        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input v-model="form.name" type="text" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Loại</label>
            <select v-model="form.type" class="form-select" required>
              <option value="">Chọn loại</option>
              <option value="income">Thu nhập</option>
              <option value="expense">Chi tiêu</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Biểu tượng</label>
            <input v-model="form.icon" type="text" class="form-control" placeholder="vd: fas fa-wallet" />
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
import Swal from 'sweetalert2';

export default {
  name: 'CategoryManagementRocker',
  data() {
    return {
      search: '',
      showForm: false,
      isEditing: false,
      form: {
        id: null,
        name: '',
        type: '',
        icon: ''
      },
      categories: []
    };
  },
  computed: {
    filteredCategories() {
      return this.categories.filter(cat =>
        cat.name.toLowerCase().includes(this.search.toLowerCase())
      );
    }
  },
  watch: {
  'form.name'(val) {
    const name = val.toLowerCase();
    if (name.includes('ăn') || name.includes('uống') || name.includes('restaurant') || name.includes('cafe') || name.includes('đi ăn')) {
      this.form.icon = 'fas fa-utensils';
    } else if (name.includes('xe') || name.includes('di chuyển') || name.includes('taxi') || name.includes('bus') || name.includes('xăng')) {
      this.form.icon = 'fas fa-car-side';
    } else if (name.includes('mua') || name.includes('sắm') || name.includes('quần áo') || name.includes('điện tử')) {
      this.form.icon = 'fas fa-shopping-cart';
    } else if (name.includes('lương') || name.includes('thu nhập') || name.includes('wallet')) {
      this.form.icon = 'fas fa-wallet';
    } else if (name.includes('đầu tư') || name.includes('chứng khoán') || name.includes('bất động sản') || name.includes('quỹ')) {
      this.form.icon = 'fas fa-chart-line';
    } else if (name.includes('giáo dục') || name.includes('học') || name.includes('học phí') || name.includes('trường') || name.includes('khoa học')) {
      this.form.icon = 'fas fa-graduation-cap';
    } else if (name.includes('sức khoẻ') || name.includes('khám') || name.includes('thuốc') || name.includes('bệnh viện')) {
      this.form.icon = 'fas fa-heartbeat';
    } else if (name.includes('nhà') || name.includes('thuê') || name.includes('trọ')) {
      this.form.icon = 'fas fa-home';
    } else if (name.includes('điện') || name.includes('nước') || name.includes('internet') || name.includes('hóa đơn điện thoại')) {
      this.form.icon = 'fas fa-bolt';
    } else if (name.includes('con cái') || name.includes('trẻ') || name.includes('trẻ em')) {
      this.form.icon = 'fas fa-child';
    } else if (name.includes('giải trí') || name.includes('phim') || name.includes('chơi') || name.includes('sự kiện') || name.includes('game')) {
      this.form.icon = 'fas fa-gamepad';
    } else if (name.includes('quần áo') || name.includes('thời trang')) {
      this.form.icon = 'fas fa-tshirt';
    } else if (name.includes('quà tặng') || name.includes('từ thiện') || name.includes('khoản khác')) {
      this.form.icon = 'fas fa-gift';
    } else if (name.includes('bonus') || name.includes('thưởng') || name.includes('khen thưởng')) {
      this.form.icon = 'fas fa-gift';
    } else if (name.includes('tour') || name.includes('đi du lịch') || name.includes('vé máy bay') || name.includes('phượt')) {
      this.form.icon = 'fas fa-plane-departure';
    } else if (name.includes('thực phẩm') || name.includes('đồ uống') || name.includes('gia dụng') || name.includes('mua sắm tiêu dùng')) {
      this.form.icon = 'fas fa-cocktail';
    } else if (name.includes('bảo hiểm') || name.includes('baohiem') || name.includes('bảo hiểm xe')) {
      this.form.icon = 'fas fa-shield-alt';
    } else if (name.includes('rút tiền') || name.includes('nạp tiền') || name.includes('thanh toán tiền mặt')) {
      this.form.icon = 'fas fa-money-bill-wave';
    } else if (name.includes('chuyển khoản') || name.includes('chuyển tiền')) {
      this.form.icon = 'fas fa-exchange-alt';
    } else if (name.includes('mua hàng') || name.includes('mua online') || name.includes('shopping online')) {
      this.form.icon = 'fas fa-cart-plus';
    } else if (name.includes('pay') || name.includes('trang thanh toán') || name.includes('mua hàng online')) {
      this.form.icon = 'fas fa-credit-card';
    } else if (name.includes('âm nhạc') || name.includes('bài hát') || name.includes('hoạt động vui chơi')) {
      this.form.icon = 'fas fa-music';
    } else if (name.includes('du lịch') || name.includes('tour du lịch') || name.includes('phượt')) {
      this.form.icon = 'fas fa-umbrella-beach';
    } else {
      this.form.icon = '';
    }
  }
}
,
  methods: {
    async fetchCategories() {
      const res = await axios.get('/api/categories', {
        headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` },
      });
      this.categories = res.data;
    },
    openAdd() {
      this.showForm = true;
      this.isEditing = false;
      this.form = { id: null, name: '', type: '', icon: '' };
    },
    openEdit(category) {
      this.showForm = true;
      this.isEditing = true;
      this.form = { ...category };
    },
    async submitForm() {
      const toast = useToast();
      try {
        const payload = {
          name: this.form.name,
          type: this.form.type,
          icon: this.form.icon
        };
        if (this.isEditing) {
          await axios.put(`/api/categories/${this.form.id}`, payload, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
          });
          toast.success("✅ Cập nhật danh mục thành công!");
        } else {
          await axios.post('/api/categories', payload, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
          });
          toast.success("✅ Thêm danh mục mới thành công!");
        }
        this.showForm = false;
        await this.fetchCategories();
      } catch (err) {
        if (err.response?.status === 422) {
          const messages = Object.values(err.response.data.errors).flat().join(', ');
          toast.error(`❌ Lỗi: ${messages}`);
        } else {
          toast.error("❌ Thao tác thất bại. Vui lòng thử lại!");
        }
      }
    },
    cancelForm() {
      this.showForm = false;
      this.form = { id: null, name: '', type: '', icon: '' };
      useToast().info("🔔 Đã huỷ chỉnh sửa / thêm danh mục.");
    },
    async deleteCategory(id) {
      const toast = useToast();
      const result = await Swal.fire({
        title: 'Xác nhận xoá',
        text: '⚠️ Bạn có chắc chắn muốn xoá danh mục này không?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xoá',
        cancelButtonText: 'Huỷ'
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/api/categories/${id}`, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
          });
          toast.success('🗑️ Xoá danh mục thành công!');
          await this.fetchCategories();
        } catch (err) {
          console.error(err);
          toast.error('❌ Xoá danh mục thất bại!');
        }
      }
    }
  },
  mounted() {
    this.fetchCategories();
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
