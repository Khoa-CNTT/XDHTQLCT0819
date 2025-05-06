<template>
    <div>
      <h2>Đăng nhập</h2>
      <form @submit.prevent="login">
        <input v-model="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Mật khẩu" required />
        <button type="submit">Đăng nhập</button>
      </form>
    </div>
  </template>

  <script>
  import axios from "axios";
  export default {
    data() {
      return { email: "", password: "" };
    },
    methods: {
      async login() {
        try {
          const response = await axios.post("/login", {
            email: this.email,
            password: this.password,
          });
          localStorage.setItem("token", response.data.token);
          this.$router.push("/dashboard");
        } catch (error) {
          alert("Đăng nhập thất bại!");
        }
      },
    },
  };
  </script>
