<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send, CheckCircle2, Sun, Moon } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

const { isDark, toggleTheme } = useTheme();

defineProps({
  status: {
    type: String,
    default: null,
  },
});

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(getUrl('/forgot-password'));
};
</script>

<template>
  <Head title="Lupa Kata Sandi - BKK Kelas I Bandung" />

  <div class="min-h-screen bg-[#f4f7f9] dark:bg-[#0b1116] text-slate-900 dark:text-slate-100 flex flex-col justify-center py-10 sm:px-6 lg:px-8 relative selection:bg-[#00A99D] selection:text-white font-sans transition-colors duration-200">
    <!-- Top-Right Theme Toggle -->
    <button
      type="button"
      @click="toggleTheme"
      class="fixed top-4 right-4 z-20 p-2 rounded-lg border transition-colors cursor-pointer"
      :class="isDark
        ? 'bg-[#111820] border-slate-700 text-amber-300 hover:bg-slate-800'
        : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-[#005851]'"
      :title="isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
    >
      <Sun v-if="isDark" class="w-4 h-4" />
      <Moon v-else class="w-4 h-4" />
    </button>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <div class="w-11 h-11 rounded-xl bg-[#004640] mx-auto flex items-center justify-center font-bold text-[#99CC33] font-mono text-sm shadow-xs border border-[#00A99D]/40">
        BKK
      </div>
      <h2 class="mt-3.5 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
        Atur Ulang Kata Sandi
      </h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
        Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
      </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white dark:bg-[#111820] py-6 px-6 sm:px-8 shadow-[0_1px_3px_rgba(15,23,42,0.06)] dark:shadow-2xl rounded-xl border border-slate-200/90 dark:border-slate-800">
        <!-- Flash Status -->
        <div v-if="status" class="mb-4 p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
          <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
              Alamat Email Terdaftar
            </label>
            <div class="mt-1 relative rounded-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Mail class="w-4 h-4" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                placeholder="nama@contoh.com"
                class="block w-full pl-9 pr-3 py-2 text-sm bg-slate-50 dark:bg-[#16202a] focus:bg-white dark:focus:bg-[#1a2632] border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25 font-mono transition-colors"
                :class="form.errors.email ? 'border-rose-400 dark:border-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-[#00A99D]'"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
              {{ form.errors.email }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2.5 px-4 rounded-lg font-semibold text-xs text-white bg-[#005851] hover:bg-[#004640] shadow-xs transition-all flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer"
          >
            <Send class="w-3.5 h-3.5" />
            <span>{{ form.processing ? 'Mengirim...' : 'Kirim Tautan Reset' }}</span>
          </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
          <a :href="getUrl('/login')" class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 hover:text-[#005851] dark:hover:text-[#4fd1c5] font-medium transition-colors">
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Kembali ke Halaman Masuk</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
