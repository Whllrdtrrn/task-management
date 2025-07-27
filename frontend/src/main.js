import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/css/main.css'
const app = createApp(App)

app.use(createPinia())
app.use(router)

if (import.meta.env.VITE_PUSHER_APP_KEY) {
  import('laravel-echo').then(({ default: Echo }) => {
    import('pusher-js').then(({ default: Pusher }) => {
      window.Pusher = Pusher

      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true,
        auth: {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        },
        authEndpoint: 'http://localhost:8000/broadcasting/auth', 
      })
    })
  })
} else {
  console.log('WebSocket disabled - no Pusher key provided')
}

app.mount('#app')