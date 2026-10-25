<script setup>
import HybridNavbar from '../Components/HybridNavbar.vue';
</script>

<template>
  <!-- Hybrid Navbar Layout Wrapper in SPA Mode -->
  <HybridNavbar mode="spa" currentRoute="spa-home">
    <!-- Router View Content (Full Width) -->
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </HybridNavbar>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
