<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, KeyRound, AlertCircle } from 'lucide-vue-next';

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

  <div class="min-h-screen bg-[#f4f7f9] text-slate-900 flex flex-col justify-center py-10 sm:px-6 lg:px-8 relative selection:bg-[#00A99D] selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <div class="w-11 h-11 rounded-xl bg-[#004640] mx-auto flex items-center justify-center font-black text-white font-mono text-sm shadow-sm border border-emerald-400/20">
        BKK
      </div>
      <h2 class="mt-3.5 text-xl font-bold tracking-tight text-slate-900">
        Perbarui Kata Sandi
      </h2>
      <p class="mt-1 text-xs text-slate-500">
        Masukkan kata sandi baru Anda untuk akun {{ email }}.
      </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white py-6 px-6 sm:px-8 shadow-sm rounded-xl border border-slate-200/90">
        <form @submit.prevent="submit" class="space-y-4">
          <input type="hidden" v-model="form.token" />
          <input type="hidden" v-model="form.email" />

          <div>
            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">
              Kata Sandi Baru
            </label>
            <div class="mt-1 relative rounded-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <Lock class="w-4 h-4" />
              </div>
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="Minimal 8 karakter"
                class="block w-full pl-9 pr-3 py-2 text-sm bg-slate-50 focus:bg-white border rounded-lg text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25"
                :class="form.errors.password ? 'border-rose-400' : 'border-slate-200 focus:border-[#00A99D]'"
              />
            </div>
            <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
          </div>

          <div>
            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">
              Konfirmasi Kata Sandi
            </label>
            <div class="mt-1 relative rounded-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <Lock class="w-4 h-4" />
              </div>
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                placeholder="Ulangi kata sandi"
                class="block w-full pl-9 pr-3 py-2 text-sm bg-slate-50 focus:bg-white border rounded-lg text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25"
                :class="form.errors.password_confirmation ? 'border-rose-400' : 'border-slate-200 focus:border-[#00A99D]'"
              />
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
