import Vue from 'vue'
import App from './App.vue'
import router from './router';
import store from './store';
import axios from 'axios';
import vuetify from './plugins/vuetify'

axios.defaults.withCredentials = true
axios.defaults.baseURL = 'http://127.0.0.1:8000';

Vue.config.productionTip = false
new Vue({
  store,
  router,
  vuetify,
  render: h => h(App)
}).$mount('#app')

axios.interceptors.response.use(undefined, function (error) {
  if (error) {
    const originalRequest = error.config;
    if (error.response.status === 401 && !originalRequest._retry) {
  
        originalRequest._retry = true;
        store.dispatch('LogOut')
        return router.push('/login')
    }
  }
})