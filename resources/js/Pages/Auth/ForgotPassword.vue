<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send, CheckCircle2, AlertCircle } from 'lucide-vue-next';

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

  <div class="min-h-screen bg-[#f4f6f8] text-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative selection:bg-emerald-500 selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <div class="w-12 h-12 rounded-2xl bg-slate-900 mx-auto flex items-center justify-center font-black text-white font-mono text-sm shadow-md">
        BKK
      </div>
      <h2 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">
        Atur Ulang Kata Sandi
      </h2>
      <p class="mt-1.5 text-xs text-slate-500 max-w-sm mx-auto">
        Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-900/5 rounded-2xl border border-slate-200/90">
        <!-- Flash Status -->
        <div v-if="status" class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600" />
          <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">
              Alamat Email Terdaftar
            </label>
            <div class="mt-1.5 relative rounded-xl">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Mail class="w-4 h-4" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                placeholder="nama@contoh.com"
                class="block w-full pl-10 pr-3.5 py-2.5 text-sm bg-slate-50 focus:bg-white border rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-mono"
                :class="form.errors.email ? 'border-rose-400' : 'border-slate-200 focus:border-emerald-500'"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-600">
              {{ form.errors.email }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-slate-900 hover:bg-slate-800 shadow-xs transition-all flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer"
          >
            <Send class="w-3.5 h-3.5" />
            <span>{{ form.processing ? 'Mengirim...' : 'Kirim Tautan Reset' }}</span>
          </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
          <a :href="getUrl('/login')" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 transition-colors">
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Kembali ke Halaman Masuk</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
