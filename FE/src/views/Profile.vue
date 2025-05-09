<template>
    <div class="user-profile mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-3">
                    <img :src="user.avatar ? apiImage(user.avatar) : defaultAvatar" class="avatar-sm rounded-circle" />
                    <h4 class="mt-4">{{ user.fullName }}</h4>
                    <h6>{{ user.email }}</h6>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h4 class="mb-5 border-bottom pb-2 text-muted">Thông tin cá nhân</h4>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Họ và tên:</div>
                            <div class="col-sm-8">{{ user.fullName }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Tên đăng nhập:</div>
                            <div class="col-sm-8">{{ user.username }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Email:</div>
                            <div class="col-sm-8">{{ user.email }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Số điện thoại:</div>
                            <div class="col-sm-8">{{ user.phone }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Địa chỉ:</div>
                            <div class="col-sm-8">{{ user.address }}</div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                Chỉnh sửa thông tin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Chỉnh sửa thông tin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ tên</label>
                                <input v-model="user.fullName" type="text" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tên đăng nhập</label>
                                <input v-model="user.username" type="text" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input v-model="user.email" type="email" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input v-model="user.phone" type="text" class="form-control" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Địa chỉ</label>
                                <input v-model="user.address" type="text" class="form-control" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" class="form-control" @change="onAvatarSelected" accept="image/*" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                            @click="submitProfileUpdate">Lưu thay đổi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { useToast } from 'vue-toastification';
import { Modal } from 'bootstrap';
export default {
    name: "UserProfileView",
    data() {
        return {
            user: {
                username: '',
                email: '',
                phone: '',
                fullName: '',
                address: '',
                avatar: ''
            },
            avatarFile: null,
            defaultAvatar: "/default-avatar.png",
        };
    },
    methods: {
        async fetchProfile() {
            const res = await axios.get('/api/profile/user', {
                headers: { Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
            });
            this.user = res.data;
        },
        apiImage(path) {
            return `http://localhost:8000/storage/${path}`;
        },

        onAvatarSelected(e) {
            this.avatarFile = e.target.files[0];
        },

        async submitProfileUpdate() {
            const toast = useToast();
            try {
                const payload = {
                    username: this.user.username,
                    email: this.user.email,
                    phone: this.user.phone,
                    fullName: this.user.fullName,
                    address: this.user.address,
                };

                await axios.put('/api/profile/update-profile', payload, {
                    headers: { Authorization: `Bearer ${localStorage.getItem("auth_token")}` },
                });

                if (this.avatarFile) {
                    const formData = new FormData();
                    formData.append("avatar", this.avatarFile);
                    await axios.post("/api/profile/update-avatar", formData, {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
                            "Content-Type": "multipart/form-data",
                        },
                    });
                }
                toast.success("✅ Cập nhật thành công!");

                const modalElement = document.getElementById("editProfileModal");
                const modalInstance = new Modal(modalElement);
                modalInstance.hide();
                await this.fetchProfile();
            } catch (error) {
                toast.error("❌ Lỗi khi cập nhật hồ sơ!");
                console.error(error);
            }
        }
    },
    mounted() {
        this.fetchProfile();
    },
};
</script>
<style scoped>
.avatar-sm {
    width: 250px;
    height: 250px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #ddd;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
}


.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-body {
    padding: 30px;
}

h4 {
    font-size: 1.8rem;
    font-weight: bold;
}

h6 {
    font-size: 1.2rem;
    color: gray;
}

button.btn {
    font-size: 1rem;
    padding: 10px 20px;
    border-radius: 25px;
    transition: background-color 0.3s;
}

button.btn:hover {
    background-color: #28a745;
}

.modal-content {
    border-radius: 15px;
}

input.form-control {
    border-radius: 10px;
    padding: 15px;
    font-size: 1.1rem;
}

.row {
    margin-bottom: 20px;
}

.col-sm-4 {
    font-weight: bold;
}
</style>