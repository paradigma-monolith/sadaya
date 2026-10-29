import { defineStore } from 'pinia';
import axios from 'axios';

export const useAppStore = defineStore('appStore', {
  state: () => ({
    systemStats: null,
    loading: false,
    error: null,
    apiLatencyMs: null,
  }),

  getters: {
    metrics: (state) => state.systemStats?.metrics || {},
    bkkOffices: (state) => state.systemStats?.bkk_offices || [],
    database: (state) => state.systemStats?.architecture?.database || {},
    server: (state) => state.systemStats?.server || {},
    infrastructure: (state) => state.systemStats?.infrastructure || {},
  },

  actions: {
    async fetchStats() {
      this.loading = true;
      this.error = null;
      const start = performance.now();
      try {
        const response = await axios.get('/api/v1/stats');
        this.systemStats = response.data;
        this.apiLatencyMs = Math.round(performance.now() - start);
      } catch (err) {
        this.error = err.response?.data?.message || err.message;
        console.error('Error fetching system stats:', err);
      } finally {
        this.loading = false;
      }
    },
  },
});
