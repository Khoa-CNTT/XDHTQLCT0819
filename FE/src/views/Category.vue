<template>
  <div class="rocker-category">
    <div class="card shadow-sm mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-layer-group me-2"></i>Quản lý danh mục
        </h5>
        <button class="btn btn-primary" @click="openAdd">
          + Thêm danh mục
        </button>
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
                <th>Loại</th>
                <th></th>
                <th>Tổng tiền</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(cat, index) in filteredCategories" :key="cat.id">
                <td>{{ index + 1 }}</td>
                <td>{{ cat.name }}</td>
                <td>
                  <span
                    :class="[
                      'px-2 py-1 rounded text-white fw-bold',
                      cat.type === 'income' ? 'bg-success' : 'bg-danger',
                    ]"
                  >
                    {{ cat.type === "income" ? "Thu nhập" : "Chi tiêu" }}
                  </span>
                </td>
                <td><i :class="cat.icon"></i></td>
                <td>
                  <span
                    :class="[
                      'px-2 py-1 rounded fw-bold',
                      cat.type === 'income'
                        ? 'text-success bg-light'
                        : 'text-danger bg-light',
                    ]"
                  >
                    {{ formatCurrency(cat.total_amount) }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-warning me-2"
                    @click="openEdit(cat)"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-danger me-2"
                    @click="deleteCategory(cat.id)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-info"
                    @click="openCategoryDetail(cat.id)"
                  >
                    <i class="fas fa-info-circle"></i>
                  </button>
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
        <h5 class="mb-3">{{ isEditing ? "Cập nhật" : "Thêm" }} danh mục</h5>
        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input
              v-model="form.name"
              type="text"
              class="form-control"
              required
            />
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
            <input
              v-model="form.icon"
              type="text"
              class="form-control"
              placeholder="vd: fas fa-wallet"
            />
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" @click="cancelForm">
              Huỷ
            </button>
            <button type="submit" class="btn btn-success">Lưu</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDetailModal" class="modal-backdrop">
      <div class="modal-content shadow p-4">
        <h5 class="mb-3">Chi tiết danh mục</h5>
        <div>
          <p><strong>Tên danh mục:</strong> {{ categoryDetail.name }}</p>
          <p>
            <strong>Loại:</strong>
            {{ categoryDetail.type === "income" ? "Thu nhập" : "Chi tiêu" }}
          </p>
          <p>
            <strong>Biểu tượng:</strong> <i :class="categoryDetail.icon"></i>
          </p>
          <p>
            <strong>Tổng số tiền:</strong> {{ formatCurrency(totalAmount) }}
          </p>

          <h6>Giao dịch</h6>
          <ul>
            <li
              v-for="transaction in transactions"
              :key="transaction.id"
              class="d-flex justify-content-between align-items-start mb-3 p-3 bg-light rounded shadow-sm"
            >
              <div>
                <strong>Nội Dung: {{ transaction.description }}</strong
                ><br />
                <small>Ngày: {{ transaction.transaction_date }}</small
                ><br />
                <strong>Số tiền:</strong>
                {{ formatCurrency(transaction.amount) }}
              </div>
              <button
                class="btn btn-sm btn-danger"
                @click="deleteTransacrion(transaction.id, categoryDetail.id)"
              >
                <i class="fas fa-trash"></i>
              </button>
            </li>
          </ul>

          <!-- Button to open Add Transaction Modal -->
          <button
            class="btn btn-primary"
            @click="openAddTransactionModal(categoryDetail.id)"
          >
            Thêm Giao Dịch
          </button>
          <!-- Close modal button -->
          <button class="btn btn-secondary" @click="closeDetailModal">
            Đóng
          </button>
        </div>
      </div>
    </div>

    <!-- Modal for Adding Transaction -->
    <div v-if="showAddTransactionModal" class="modal-backdrop">
      <div class="modal-content shadow p-4">
        <h5 class="mb-3">Thêm Giao Dịch</h5>
        <div>
          <form @submit.prevent="addTransaction">
            <div class="mb-3">
              <label for="description" class="form-label">Nội dung</label>
              <input
                type="text"
                id="description"
                v-model="newTransaction.description"
                class="form-control"
                required
              />
            </div>
            <div class="mb-3">
              <label for="amount" class="form-label">Số tiền</label>
              <input
                type="number"
                id="amount"
                v-model="newTransaction.amount"
                class="form-control"
                required
                min="0"
              />
            </div>
            <div class="mb-3">
              <label for="transaction_date" class="form-label">Ngày</label>
              <input
                type="date"
                id="transaction_date"
                v-model="newTransaction.transaction_date"
                class="form-control"
                required
              />
            </div>
            <button type="submit" class="btn btn-primary">
              Thêm Giao Dịch
            </button>
            <button class="btn btn-secondary" @click="closeAddTransactionModal">
              Đóng
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";
import Swal from "sweetalert2";

export default {
  name: "CategoryManagementRocker",
  data() {
    return {
      search: "",
      showForm: false,
      isEditing: false,
      form: {
        id: null,
        name: "",
        type: "",
        icon: "",
      },
      categories: [],
      showDetailModal: false,
      showAddTransactionModal: false,

      categoryDetail: null,
      transactions: null,
      totalAmount: 0,
      newTransaction: {
        transaction_date: "",
        type: "cash",
        amount: 0,
        description: "",
        address: "",
      },
    };
  },
  computed: {
    filteredCategories() {
      return this.categories.filter((cat) =>
        cat.name.toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  watch: {
    "form.name"(val) {
      const name = val.toLowerCase();
      if (name.includes("ăn") || name.includes("uống")) {
        this.form.icon = "fas fa-utensils";
      } else if (name.includes("xe") || name.includes("di chuyển")) {
        this.form.icon = "fas fa-car-side";
      } else if (name.includes("mua") || name.includes("sắm")) {
        this.form.icon = "fas fa-shopping-cart";
      } else if (name.includes("lương") || name.includes("thu nhập")) {
        this.form.icon = "fas fa-wallet";
      } else if (name.includes("đầu tư")) {
        this.form.icon = "fas fa-chart-line";
      } else if (name.includes("giáo dục") || name.includes("học")) {
        this.form.icon = "fas fa-graduation-cap";
      } else if (
        name.includes("sức khoẻ") ||
        name.includes("khám") ||
        name.includes("thuốc")
      ) {
        this.form.icon = "fas fa-heartbeat";
      } else if (
        name.includes("nhà") ||
        name.includes("thuê") ||
        name.includes("trọ")
      ) {
        this.form.icon = "fas fa-home";
      } else if (
        name.includes("điện") ||
        name.includes("nước") ||
        name.includes("internet")
      ) {
        this.form.icon = "fas fa-bolt";
      } else if (name.includes("con cái") || name.includes("trẻ")) {
        this.form.icon = "fas fa-child";
      } else if (
        name.includes("giải trí") ||
        name.includes("phim") ||
        name.includes("chơi")
      ) {
        this.form.icon = "fas fa-gamepad";
      } else if (name.includes("quần áo") || name.includes("thời trang")) {
        this.form.icon = "fas fa-tshirt";
      } else if (name.includes("khác")) {
        this.form.icon = "fas fa-ellipsis-h";
      } else {
        this.form.icon = "";
      }
    },
  },
  methods: {
    async fetchCategories() {
      const res = await axios.get("/api/categories", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
      });
      this.categories = res.data;
    },
    openAdd() {
      this.showForm = true;
      this.isEditing = false;
      this.form = { id: null, name: "", type: "", icon: "" };
    },
    openAddTransactionModal(categoryId) {
      this.showAddTransactionModal = true;
      this.newTransaction.category_id = categoryId;
    },
    formatCurrency(value) {
      if (!value) return "0 ₫";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(value);
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
          icon: this.form.icon,
        };
        if (this.isEditing) {
          await axios.put(`/api/categories/${this.form.id}`, payload, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          toast.success("✅ Cập nhật danh mục thành công!");
        } else {
          await axios.post("/api/categories", payload, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          toast.success("✅ Thêm danh mục mới thành công!");
        }
        this.showForm = false;
        await this.fetchCategories();
      } catch (err) {
        if (err.response?.status === 422) {
          const messages = Object.values(err.response.data.errors)
            .flat()
            .join(", ");
          toast.error(`❌ Lỗi: ${messages}`);
        } else {
          toast.error("❌ Thao tác thất bại. Vui lòng thử lại!");
        }
      }
    },
    cancelForm() {
      this.showForm = false;
      this.form = { id: null, name: "", type: "", icon: "" };
      useToast().info("🔔 Đã huỷ chỉnh sửa / thêm danh mục.");
    },
    async deleteCategory(id) {
      const toast = useToast();
      const result = await Swal.fire({
        title: "Xác nhận xoá",
        text: "⚠️ Bạn có chắc chắn muốn xoá danh mục này không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Xoá",
        cancelButtonText: "Huỷ",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/api/categories/${id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          toast.success("🗑️ Xoá danh mục thành công!");
          await this.fetchCategories();
        } catch (err) {
          console.error(err);
          toast.error("❌ Xoá danh mục thất bại!");
        }
      }
    },

    async openCategoryDetail(id) {
      try {
        const res = await axios.get(`/api/categories/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        this.categoryDetail = res.data.category;
        this.transactions = res.data.transactions;
        this.totalAmount = res.data.total_amount;
        this.showDetailModal = true;
      } catch (err) {
        useToast().error("Không thể lấy chi tiết danh mục!");
      }
    },
    closeDetailModal() {
      this.showDetailModal = false;
      this.categoryDetail = null;
      this.totalAmount = 0;
    },
    isValidDate(date) {
      const regex = /^\d{4}-\d{2}-\d{2}$/;
      return regex.test(date);
    },

    // HUY TODO:
    async addTransaction() {
      const toast = useToast();
      const isValidDate = this.isValidDate(
        this.newTransaction.transaction_date
      );
      if (!isValidDate) {
        toast.error("Ngày phải đúng định dạng (YYYY-MM-DD)");
        return;
      }

      try {
       const res =  await axios.post("/api/transaction", this.newTransaction, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        });
        toast.success("Giao dịch đã được thêm thành công!");
        this.openCategoryDetail(this.newTransaction.category_id);
        this.closeAddTransactionModal();
        await this.fetchCategories();
      } catch (error) {
        toast.error(error);
        toast.error("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    },
    closeAddTransactionModal() {
      this.showAddTransactionModal = false;
      this.fetchCategories();
    },
    async deleteTransacrion(id, categoryId) {
      const toast = useToast();
      const result = await Swal.fire({
        title: "Xác nhận xoá",
        text: "⚠️ Bạn có chắc chắn muốn xoá giao dịch này khỏi danh mục?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Xoá",
        cancelButtonText: "Huỷ",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/api/transaction/${id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          toast.success("Đã xoá giao dịch khỏi danh mục thành công!");
          this.openCategoryDetail(categoryId);
          await this.fetchCategories();
        } catch (err) {
          console.error(err);
          toast.error("Đã xoá giao dịch khỏi danh mục thất bại!");
        }
      }
    },
  },
  mounted() {
    this.fetchCategories();
  },
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 950px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modal-in 0.3s ease-out;
}

.modal-content h5 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  padding: 1.25rem 1.5rem;
  margin: -1rem -1rem 1rem -1rem;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-content p {
  margin-bottom: 1rem;
  padding: 0.5rem 0;
  font-size: 1rem;
  display: flex;
  align-items: center;
}

.modal-content p strong {
  min-width: 140px;
  display: inline-block;
  font-weight: 600;
  color: #4b5563;
}

.modal-content p i {
  font-size: 1.25rem;
  margin-left: 0.5rem;
}

.modal-content p:nth-child(4) strong + span,
.modal-content p:nth-child(4) {
  color: #e72121;
  font-weight: 700;
}

.modal-content h6 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 1.5rem 0 1rem 0;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  color: #1f2937;
}

.modal-content ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.modal-content li {
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: #f9fafb;
  transition: transform 0.2s, box-shadow 0.2s;
}

.modal-content li:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.modal-content li strong {
  display: block;
  font-size: 1.1rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.modal-content li small {
  display: block;
  color: #6b7280;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.modal-content li strong:last-of-type {
  display: inline-block;
  margin-right: 0.5rem;
  color: #4b5563;
}

/* Close button */
.modal-content .btn-secondary {
  margin-top: 1.5rem;
  padding: 0.6rem 1.5rem;
  background-color: #6b7280;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  float: right;
}

.modal-content .btn-secondary:hover {
  background-color: #4b5563;
}

/* Animation for modal entrance */
@keyframes modal-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Media queries for responsiveness */
@media (max-width: 640px) {
  .modal-content {
    max-width: 100%;
    border-radius: 8px;
  }

  .modal-content p strong {
    min-width: 120px;
  }
}
</style>
