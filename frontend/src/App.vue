<template>
  <div class="min-h-screen bg-gray-100 text-gray-900">
    <router-view />
    <ToastNotifications />
  </div>
</template>
<script setup>
import ToastNotifications from '@/components/ToastNotifications.vue'
</script>