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

  <div class="min-h-screen bg-[#f4f7f9] text-slate-900 flex flex-col justify-center py-10 sm:px-6 lg:px-8 relative selection:bg-[#00A99D] selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <div class="w-11 h-11 rounded-xl bg-[#004640] mx-auto flex items-center justify-center font-black text-white font-mono text-sm shadow-sm border border-emerald-400/20">
        BKK
      </div>
      <h2 class="mt-3.5 text-xl font-bold tracking-tight text-slate-900">
        Atur Ulang Kata Sandi
      </h2>
      <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
        Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
      </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white py-6 px-6 sm:px-8 shadow-sm rounded-xl border border-slate-200/90">
        <!-- Flash Status -->
        <div v-if="status" class="mb-4 p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600" />
          <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">
              Alamat Email Terdaftar
            </label>
            <div class="mt-1 relative rounded-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <Mail class="w-4 h-4" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                placeholder="nama@contoh.com"
                class="block w-full pl-9 pr-3 py-2 text-sm bg-slate-50 focus:bg-white border rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25 font-mono"
                :class="form.errors.email ? 'border-rose-400' : 'border-slate-200 focus:border-[#00A99D]'"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">
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

        <div class="mt-5 pt-4 border-t border-slate-100 text-center">
          <a :href="getUrl('/login')" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-[#005851] font-medium transition-colors">
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Kembali ke Halaman Masuk</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
