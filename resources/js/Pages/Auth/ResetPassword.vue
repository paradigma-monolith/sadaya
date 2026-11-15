<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, KeyRound, Eye, EyeOff, Sun, Moon } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

const { isDark, toggleTheme } = useTheme();

const props = defineProps({
  token: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    default: '',
  },
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(getUrl('/reset-password'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Buat Kata Sandi Baru - BKK Kelas I Bandung" />

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
        Perbarui Kata Sandi
      </h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Masukkan kata sandi baru Anda untuk akun {{ email }}.
      </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white dark:bg-[#111820] py-6 px-6 sm:px-8 shadow-[0_1px_3px_rgba(15,23,42,0.06)] dark:shadow-2xl rounded-xl border border-slate-200/90 dark:border-slate-800">
        <form @submit.prevent="submit" class="space-y-4">
          <input type="hidden" v-model="form.token" />
          <input type="hidden" v-model="form.email" />

          <div>
            <label for="new_password" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
              Kata Sandi Baru
            </label>
            <div class="mt-1 relative rounded-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Lock class="w-4 h-4" />
              </div>
              <input
                id="new_password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Minimal 8 karakter"
                class="block w-full pl-9 pr-10 py-2 text-sm bg-slate-50 dark:bg-[#16202a] focus:bg-white dark:focus:bg-[#1a2632] border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25 transition-colors"
                :class="form.errors.password ? 'border-rose-400 dark:border-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-[#00A99D]'"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ form.errors.password }}</p>
          </div>

          <div>
            <label for="confirm_password" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
              Konfirmasi Kata Sandi
            </label>
            <div class="mt-1 relative rounded-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Lock class="w-4 h-4" />
              </div>
              <input
                id="confirm_password"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                placeholder="Ulangi kata sandi"
                class="block w-full pl-9 pr-10 py-2 text-sm bg-slate-50 dark:bg-[#16202a] focus:bg-white dark:focus:bg-[#1a2632] border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25 transition-colors"
                :class="form.errors.password_confirmation ? 'border-rose-400 dark:border-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-[#00A99D]'"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                :aria-label="showConfirmPassword ? 'Sembunyikan konfirmasi kata sandi' : 'Lihat konfirmasi kata sandi'"
              >
                <EyeOff v-if="showConfirmPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-2 py-2.5 px-4 rounded-lg font-semibold text-xs text-white bg-[#005851] hover:bg-[#004640] shadow-xs transition-all flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer"
          >
            <KeyRound class="w-3.5 h-3.5" />
            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Kata Sandi Baru' }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
