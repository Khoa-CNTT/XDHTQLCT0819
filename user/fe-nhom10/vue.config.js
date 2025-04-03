module.exports = {
  // Example configuration for Vue 3
  chainWebpack: config => {
    config.resolve.alias.set('vue$', 'vue/dist/vue.esm-bundler.js');
  }
};
