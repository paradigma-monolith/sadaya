<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
  ShieldAlert,
  FileQuestion,
  Clock,
  ServerCrash,
  Wrench,
  ArrowLeft,
  Home,
  RotateCw,
  LogIn,
  Sun,
  Moon,
} from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
  status: {
    type: Number,
    required: true,
  },
  message: {
    type: String,
    default: '',
  },
  isAuthenticated: {
    type: Boolean,
    default: false,
  },
});

const { isDark, toggleTheme } = useTheme();

const metaMap = {
  403: {
    code: '403',
    badge: 'Akses Dibatasi',
    title: 'Anda Tidak Memiliki Izin Mengakses Halaman Ini',
    description:
      'Hak akses peran akun Anda belum mencakup modul atau tindakan ini. Hubungi administrator BKK Kelas I Bandung jika diperlukan eskalasi akses.',
    icon: ShieldAlert,
    accentClass: 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/60',
  },
  404: {
    code: '404',
    badge: 'Tidak Ditemukan',
    title: 'Halaman atau Dokumen Tidak Ditemukan',
    description:
      'Alamat URL yang dituju tidak tersedia, sudah dipindahkan, atau nomor dokumen tidak lagi tercatat dalam sistem SADAYA.',
    icon: FileQuestion,
    accentClass: 'bg-teal-50 dark:bg-teal-950/50 text-[#005851] dark:text-teal-300 border-teal-200/80 dark:border-teal-800/60',
  },
  419: {
    code: '419',
    badge: 'Sesi Kedaluwarsa',
    title: 'Token Keamanan Halaman Telah Berakhir',
    description:
      'Halaman dibiarkan terbuka terlalu lama sehingga sesi keamanan CSRF perlu diperbarui sebelum mengirim ulang formulir.',
    icon: Clock,
    accentClass: 'bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-300 border-sky-200/80 dark:border-sky-800/60',
  },
  500: {
    code: '500',
    badge: 'Gangguan Server',
    title: 'Terjadi Kesalahan Internal pada Layanan',
    description:
      'Tim teknis menerima catatan kesalahan ini secara otomatis. Silakan muat ulang halaman atau kembali ke Dashboard utama.',
    icon: ServerCrash,
    accentClass: 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border-rose-200/80 dark:border-rose-800/60',
  },
  503: {
    code: '503',
    badge: 'Pemeliharaan',
    title: 'Sistem SADAYA Sedang dalam Pemeliharaan',
    description:
      'Layanan sementara dihentikan untuk peningkatan keandalan atau sinkronisasi data. Coba muat ulang beberapa saat lagi.',
    icon: Wrench,
    accentClass: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 border-indigo-200/80 dark:border-indigo-800/60',
  },
};

const info = computed(() => {
  return (
    metaMap[props.status] || {
      code: String(props.status || 500),
      badge: 'Terjadi Kendala',
      title: 'Permintaan Tidak Dapat Diproses',
      description: 'Silakan muat ulang halaman atau kembali ke halaman utama SADAYA.',
      icon: ServerCrash,
      accentClass: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    }
  );
});

const reloadPage = () => {
  if (typeof window !== 'undefined') {
    window.location.reload();
  }
};

const goBack = () => {
  if (typeof window !== 'undefined' && window.history.length > 1) {
    window.history.back();
  } else {
    router.visit(props.isAuthenticated ? '/dashboard' : '/login');
  }
};
</script>

<template>
  <Head :title="`${info.code} — ${info.badge} • SADAYA`" />

  <div class="min-h-screen bg-[#f4f7f9] dark:bg-[#0b1116] text-slate-800 dark:text-slate-100 flex flex-col items-center justify-center px-4 py-10 relative transition-colors font-sans">
    <!-- Theme Toggle -->
    <button
      type="button"
      @click="toggleTheme"
      :aria-label="isDark ? 'Aktifkan Mode Terang' : 'Aktifkan Mode Gelap'"
      :title="isDark ? 'Mode Terang' : 'Mode Gelap'"
      class="fixed top-4 right-4 w-9 h-9 rounded-lg bg-white dark:bg-[#111820] border border-slate-200/90 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-[#005851] dark:hover:text-[#5eead4] shadow-xs flex items-center justify-center transition-colors cursor-pointer"
    >
      <Sun v-if="isDark" class="w-4 h-4 text-amber-400" />
      <Moon v-else class="w-4 h-4" />
    </button>

    <div class="w-full max-w-lg bg-white dark:bg-[#111820] border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
      <!-- Top Brand Bar -->
      <div class="px-6 py-4 bg-[#004640] text-white flex items-center justify-between border-b border-[#003B36]">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center font-mono font-bold text-xs">
            SD
          </div>
          <div>
            <div class="text-xs font-bold tracking-tight">SADAYA &bull; BKK Kelas I Bandung</div>
            <div class="text-[10px] text-[#99DFD6]/85 font-mono">Sistem Informasi Kesehatan Pelabuhan &amp; Bandara</div>
          </div>
        </div>
        <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-[#002B27] text-[#99CC33] border border-[#00A99D]/30">
          HTTP {{ info.code }}
        </span>
      </div>

      <!-- Error Content -->
      <div class="p-6 sm:p-8 space-y-5 text-center">
        <div class="flex justify-center">
          <div
            class="w-16 h-16 rounded-2xl border flex items-center justify-center"
            :class="info.accentClass"
          >
            <component :is="info.icon" class="w-8 h-8" />
          </div>
        </div>

        <div class="space-y-2">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold uppercase tracking-wider bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300">
            {{ info.code }} &bull; {{ info.badge }}
          </span>
          <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">
            {{ info.title }}
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
            {{ info.description }}
          </p>
        </div>

        <div
          v-if="message"
          class="text-left p-3 rounded-lg bg-slate-50 dark:bg-[#16202a] border border-slate-200/80 dark:border-slate-700/80 text-[11px] font-mono text-slate-600 dark:text-slate-300 break-words"
        >
          Detail: {{ message }}
        </div>

        <div class="pt-2 flex flex-wrap items-center justify-center gap-2.5">
          <button
            v-if="status === 419 || status === 500 || status === 503"
            type="button"
            @click="reloadPage"
            class="clinical-btn-primary px-4 py-2"
          >
            <RotateCw class="w-3.5 h-3.5" />
            <span>Muat Ulang Halaman</span>
          </button>

          <Link
            v-if="isAuthenticated"
            href="/dashboard"
            class="px-4 py-2 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition-colors"
            :class="status === 404 || status === 403 ? 'clinical-btn-primary' : 'clinical-btn-secondary'"
          >
            <Home class="w-3.5 h-3.5" />
            <span>Kembali ke Dashboard</span>
          </Link>

          <Link
            v-else
            href="/login"
            class="px-4 py-2 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition-colors"
            :class="status === 404 || status === 403 ? 'clinical-btn-primary' : 'clinical-btn-secondary'"
          >
            <LogIn class="w-3.5 h-3.5" />
            <span>Masuk ke SADAYA</span>
          </Link>

          <button
            type="button"
            @click="goBack"
            class="clinical-btn-secondary px-4 py-2"
          >
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Kembali</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
