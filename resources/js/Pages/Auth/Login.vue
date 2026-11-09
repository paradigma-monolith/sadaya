<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
  LogIn, 
  Mail, 
  Lock, 
  Eye, 
  EyeOff, 
  ShieldCheck, 
  Sparkles, 
  CheckCircle2, 
  AlertCircle,
  Layers,
  Cpu,
  User
} from 'lucide-vue-next';

defineProps({
  status: {
    type: String,
    default: null,
  },
});

const showPassword = ref(false);

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const form = useForm({
  login: '',
  email: '',
  password: '',
  remember: true,
});

const submit = () => {
  if (!form.email && form.login) {
    form.email = form.login;
  }
  form.post(getUrl('/login'), {
    onFinish: () => form.reset('password'),
    onSuccess: () => {
      window.location.href = getUrl('/');
    },
  });
};
</script>

<template>
  <Head title="Masuk - BKK Kelas I Bandung" />

  <div class="min-h-screen bg-[#f4f6f8] text-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative selection:bg-emerald-500 selection:text-white">
    <!-- Header / Brand -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <div class="inline-flex items-center justify-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center font-black text-white text-base font-mono shadow-md">
          BKK
        </div>
      </div>

      <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-700">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Command Center &bull; Induk &amp; 6 Wilker</span>
      </div>

      <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
        BKK Kelas I Bandung
      </h1>
      <p class="mt-1.5 text-sm text-slate-500">
        Sistem Informasi Kekarantinaan Kesehatan &amp; Pengawasan Wilayah Kerja Jawa Barat.
      </p>
    </div>

    <!-- Main Card -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-900/5 rounded-2xl border border-slate-200/90 relative">
        
        <!-- Status Flash Message -->
        <div v-if="status" class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600" />
          <span>{{ status }}</span>
        </div>

        <!-- Validation Error Alert Banner -->
        <div v-if="form.errors.login || form.errors.email || form.errors.password" class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2.5">
          <AlertCircle class="w-4 h-4 shrink-0 text-rose-600 mt-0.5" />
          <div>
            <p class="font-semibold text-rose-900">Gagal Masuk</p>
            <p class="mt-0.5">{{ form.errors.login || form.errors.email || form.errors.password }}</p>
          </div>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Username / NIP / Email Input -->
          <div>
            <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">
              Username / NIP / Email
            </label>
            <div class="mt-1.5 relative rounded-xl">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <User class="w-4 h-4" />
              </div>
              <input
                id="login"
                v-model="form.login"
                @input="form.email = form.login"
                type="text"
                autocomplete="username"
                required
                placeholder="NIP / Username / Email (Simply)"
                class="block w-full pl-10 pr-3.5 py-2.5 text-sm bg-slate-50 focus:bg-white border rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all font-mono"
                :class="(form.errors.login || form.errors.email) ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-emerald-500'"
              />
            </div>
            <p class="mt-1 text-[11px] text-slate-400">
              Gunakan akun dan kata sandi yang sama persis seperti pada aplikasi Simply.
            </p>
            <p v-if="form.errors.login || form.errors.email" class="mt-1.5 text-xs text-rose-600">
              {{ form.errors.login || form.errors.email }}
            </p>
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">
                Kata Sandi
              </label>
            </div>
            <div class="mt-1.5 relative rounded-xl">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Lock class="w-4 h-4" />
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                placeholder="••••••••"
                class="block w-full pl-10 pr-10 py-2.5 text-sm bg-slate-50 focus:bg-white border rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all font-mono"
                :class="form.errors.password ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-emerald-500'"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition-colors"
                tabindex="-1"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Remember Me Checkbox & Forgot Password Link -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input
                v-model="form.remember"
                type="checkbox"
                class="w-4 h-4 rounded border-slate-300 bg-white text-slate-900 focus:ring-emerald-500/30 transition-colors cursor-pointer"
              />
              <span class="text-xs text-slate-600">Ingat saya</span>
            </label>

            <a :href="getUrl('/forgot-password')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
              Lupa sandi?
            </a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-2 py-2.5 px-4 rounded-xl font-semibold text-sm text-white bg-slate-900 hover:bg-slate-800 active:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 shadow-sm transition-all flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed group cursor-pointer"
          >
            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <LogIn v-else class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
            <span>{{ form.processing ? 'Memverifikasi...' : 'Masuk ke Dashboard' }}</span>
          </button>
        </form>
      </div>

      <!-- Architecture Footnote Pill -->
      <div class="mt-6 text-center space-y-2">
        <div class="inline-flex items-center gap-3 text-xs text-slate-500">
          <span class="inline-flex items-center gap-1">
            <ShieldCheck class="w-3.5 h-3.5 text-emerald-600" />
            <span>BKK Kelas I Bandung</span>
          </span>
          <span class="text-slate-300">&bull;</span>
          <span class="inline-flex items-center gap-1">
            <Layers class="w-3.5 h-3.5 text-slate-600" />
            <span>Induk &amp; 6 Wilker</span>
          </span>
          <span class="text-slate-300">&bull;</span>
          <span class="inline-flex items-center gap-1">
            <Cpu class="w-3.5 h-3.5 text-emerald-600" />
            <span>Real-Time Telemetry</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
