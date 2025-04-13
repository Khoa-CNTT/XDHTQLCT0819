<template>
    <div>
      <h1>Bảng điều khiển</h1>
      <p>Chào mừng bạn đến với hệ thống!</p>
      <button @click="logout">Đăng xuất</button>
    </div>
  </template>

  <script>
  export default {
    methods: {
      logout() {
        localStorage.removeItem("token");
        this.$router.push("/login");
      },
    },
  };
  </script>
