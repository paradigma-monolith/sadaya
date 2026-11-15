<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
  LogIn,
  Lock,
  Eye,
  EyeOff,
  ShieldCheck,
  CheckCircle2,
  AlertCircle,
  Layers,
  User,
  Sun,
  Moon,
  X,
  Radio,
  Scan,
  FileCheck2,
  Activity,
  Plane,
  ArrowUpRight,
} from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

const { isDark, toggleTheme } = useTheme();

defineProps({
  status: {
    type: String,
    default: null,
  },
});

const showPassword = ref(false);
const capsLockActive = ref(false);
const liveClock = ref('');
let clockTimer = null;

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const logoCandidates = [
  getUrl('/images/LOGO%20KEMENKES%20%26%20KARANTINA.png'),
  getUrl('/storage/images/LOGO%20KEMENKES%20%26%20KARANTINA.png'),
];
const logoAttempt = ref(0);
const currentLogoSrc = computed(() => logoCandidates[logoAttempt.value] || null);

const handleLogoError = () => {
  if (logoAttempt.value < logoCandidates.length) {
    logoAttempt.value += 1;
  }
};

const updateClock = () => {
  const now = new Date();
  liveClock.value = `${now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  })} WIB`;
};

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockTimer) {
    clearInterval(clockTimer);
    clockTimer = null;
  }
});

const form = useForm({
  login: '',
  email: '',
  password: '',
  remember: true,
});

const clearLoginInput = () => {
  form.login = '';
  form.email = '';
};

const handlePasswordKey = (event) => {
  if (event && typeof event.getModifierState === 'function') {
    capsLockActive.value = Boolean(event.getModifierState('CapsLock'));
  }
};

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

const moduleHighlights = [
  {
    code: 'SINKARKES',
    label: 'Pelaporan Bulanan & Harian',
    icon: FileCheck2,
  },
  {
    code: 'SIVERA',
    label: 'Surveilans Vektor & Sanitasi',
    icon: Activity,
  },
  {
    code: 'ARMADA & P3K',
    label: 'Inspeksi Pesawat, Kapal & Obat',
    icon: Plane,
  },
  {
    code: 'RADAR JABAR',
    label: 'Induk BDG + 6 Wilayah Kerja',
    icon: Scan,
  },
];

const wilkerCodes = ['BDG', 'BDO', 'KJT', 'CBN', 'IDM', 'PTB', 'PLR'];
</script>

<template>
  <Head title="Masuk — SADAYA BKK Kelas I Bandung" />

  <div class="min-h-screen bg-[#eff4f5] dark:bg-[#070d12] text-slate-900 dark:text-slate-100 flex flex-col justify-center py-6 px-4 sm:px-6 lg:px-8 relative overflow-hidden selection:bg-[#00A99D] selection:text-white font-sans transition-colors duration-200">
    <!-- Subtle Ambient Architectural Background -->
    <div
      class="pointer-events-none fixed inset-0 opacity-60 dark:opacity-35"
      style="background-image: radial-gradient(circle at 18% 20%, rgba(0, 169, 157, 0.14), transparent 44%), radial-gradient(circle at 85% 82%, rgba(153, 204, 51, 0.12), transparent 42%);"
      aria-hidden="true"
    ></div>
    <div
      class="pointer-events-none fixed inset-0 opacity-[0.32] dark:opacity-[0.14]"
      style="background-image: radial-gradient(rgba(0, 88, 81, 0.18) 1px, transparent 1px); background-size: 24px 24px;"
      aria-hidden="true"
    ></div>

    <!-- Main Split Shell -->
    <div class="relative z-10 w-full max-w-5xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-12 rounded-2xl overflow-hidden bg-white dark:bg-[#0f171e] border border-slate-200/90 dark:border-slate-800/90 shadow-[0_20px_50px_-15px_rgba(0,45,41,0.16)] dark:shadow-[0_24px_60px_-15px_rgba(0,0,0,0.65)]">

        <!-- LEFT SHOWCASE PANEL (Desktop Institutional Command Identity) -->
        <section class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-8 xl:p-9 bg-gradient-to-br from-[#002824] via-[#00443e] to-[#005a52] text-white overflow-hidden">
          <!-- Subtle Polar Radar Background Watermark -->
          <svg
            viewBox="0 0 320 320"
            class="pointer-events-none absolute -right-16 -bottom-16 w-[380px] h-[380px] opacity-[0.13]"
            aria-hidden="true"
          >
            <circle cx="160" cy="160" r="46" fill="none" stroke="#99DFD6" stroke-width="1" />
            <circle cx="160" cy="160" r="92" fill="none" stroke="#99DFD6" stroke-width="1" stroke-dasharray="4 4" />
            <circle cx="160" cy="160" r="138" fill="none" stroke="#99CC33" stroke-width="1" />
            <line x1="160" y1="8" x2="160" y2="312" stroke="#99DFD6" stroke-width="0.7" />
            <line x1="8" y1="160" x2="312" y2="160" stroke="#99DFD6" stroke-width="0.7" />
            <circle cx="160" cy="160" r="4" fill="#99CC33" />
          </svg>
          <div
            class="pointer-events-none absolute -top-24 -left-24 w-72 h-72 rounded-full blur-3xl opacity-30"
            style="background: radial-gradient(circle, #00A99D, transparent 70%);"
            aria-hidden="true"
          ></div>

          <!-- Top Logo Plaque & Live Telemetry Clock -->
          <div class="relative z-10 flex items-start justify-between gap-4">
            <div class="inline-flex items-center bg-[#0b1116]/90 backdrop-blur-sm rounded-xl px-4 py-2.5 shadow-[0_10px_28px_rgba(0,0,0,0.38)] border border-[#00A99D]/35 max-w-full">
              <img
                v-if="currentLogoSrc"
                :src="currentLogoSrc"
                @error="handleLogoError"
                alt="Logo Kemenkes BKK Bandung dan Karantina Kesehatan"
                class="h-10 xl:h-11 w-auto object-contain select-none"
              />
              <div v-else class="flex items-center gap-2 text-white font-bold text-sm">
                <ShieldCheck class="w-6 h-6 text-[#5eead4]" />
                <span>Kemenkes BKK Kelas I Bandung</span>
              </div>
            </div>

            <div class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#00221F]/80 border border-[#00A99D]/35 text-[11px] font-mono text-[#99DFD6] tabular-nums">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#99CC33] opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#99CC33]"></span>
              </span>
              <span>{{ liveClock || '--:--:-- WIB' }}</span>
            </div>
          </div>

          <!-- Hero Identity Copy -->
          <div class="relative z-10 my-7 space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#002B27]/80 border border-[#00A99D]/40 text-[10.5px] xl:text-[11px] font-mono font-semibold tracking-wide text-[#99DFD6]">
              <Radio class="w-3.5 h-3.5 text-[#99CC33] shrink-0" />
              <span>SADAYA &bull; SISTEM ANALISIS DATA PELAYANAN TERINTEGRASI</span>
            </div>

            <div>
              <h1 class="text-2xl xl:text-3xl font-extrabold tracking-tight leading-snug text-white">
                Command Center Kekarantinaan Kesehatan Jawa Barat
              </h1>
              <p class="mt-2.5 text-sm text-teal-100/85 leading-relaxed max-w-lg">
                Platform terpadu Balai Kekarantinaan Kesehatan Kelas I Bandung untuk pemantauan inspeksi lintas wilayah kerja bandara dan pelabuhan secara real-time.
              </p>
            </div>

            <!-- Operational Modules Grid -->
            <div class="grid grid-cols-2 gap-2.5 pt-2">
              <div
                v-for="mod in moduleHighlights"
                :key="mod.code"
                class="rounded-xl bg-white/[0.07] hover:bg-white/[0.11] border border-white/12 px-3.5 py-2.5 backdrop-blur-xs transition-colors"
              >
                <div class="flex items-center gap-2 text-[11px] font-mono font-bold tracking-wider text-[#99CC33]">
                  <component :is="mod.icon" class="w-3.5 h-3.5 text-[#5eead4] shrink-0" />
                  <span>{{ mod.code }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-100/90 font-medium leading-snug">
                  {{ mod.label }}
                </p>
              </div>
            </div>
          </div>

          <!-- Bottom Wilker Node Telemetry Strip -->
          <div class="relative z-10 pt-4 border-t border-white/15 flex flex-wrap items-center justify-between gap-2 text-[11px] font-mono">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="text-teal-200/70 mr-1 uppercase tracking-wider text-[10px]">Wilker:</span>
              <span
                v-for="code in wilkerCodes"
                :key="code"
                class="px-1.5 py-0.5 rounded bg-[#002521]/85 border border-[#00A99D]/30 text-[10px] font-bold"
                :class="code === 'BDG' ? 'text-[#99CC33] border-[#99CC33]/45' : 'text-[#99DFD6]'"
              >
                {{ code }}
              </span>
            </div>
            <span class="text-[10.5px] text-teal-100/75 flex items-center gap-1">
              <Layers class="w-3.5 h-3.5 text-[#5eead4]" />
              <span>SSO Akun Simply</span>
            </span>
          </div>
        </section>

        <!-- RIGHT AUTHENTICATION PANEL -->
        <section class="lg:col-span-5 flex flex-col justify-between p-6 sm:p-8 bg-white dark:bg-[#111820] relative">
          <!-- Top Bar: Mobile Logo Plaque + Theme Switcher -->
          <div class="flex items-start justify-between gap-3">
            <!-- Mobile/Tablet Logo Lockup -->
            <div class="lg:hidden inline-flex items-center bg-[#0b1116] rounded-xl px-3.5 py-2 border border-[#00A99D]/35 shadow-sm max-w-[calc(100%-3rem)]">
              <img
                v-if="currentLogoSrc"
                :src="currentLogoSrc"
                @error="handleLogoError"
                alt="Logo Kemenkes BKK Bandung dan Karantina Kesehatan"
                class="h-8 sm:h-9 w-auto object-contain select-none"
              />
              <div v-else class="flex items-center gap-2 text-white font-bold text-xs">
                <ShieldCheck class="w-5 h-5 text-[#5eead4]" />
                <span>Kemenkes BKK Bandung</span>
              </div>
            </div>

            <!-- Desktop Kicker Pill -->
            <div class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-mono font-bold uppercase tracking-wider bg-[#E6F7F5] dark:bg-[#00A99D]/15 border border-[#99DFD6] dark:border-[#00A99D]/40 text-[#005851] dark:text-[#5eead4]">
              <ShieldCheck class="w-3.5 h-3.5" />
              <span>Akses Operator</span>
            </div>

            <!-- Theme Toggle -->
            <button
              type="button"
              @click="toggleTheme"
              class="shrink-0 p-2 rounded-lg border transition-colors cursor-pointer"
              :class="isDark
                ? 'bg-[#16202a] border-slate-700 text-amber-300 hover:bg-slate-800'
                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-[#005851]'"
              :aria-label="isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
              :title="isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
            >
              <Sun v-if="isDark" class="w-4 h-4" />
              <Moon v-else class="w-4 h-4" />
            </button>
          </div>

          <!-- Form Body -->
          <div class="my-auto pt-5 pb-4">
            <div class="mb-5">
              <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Masuk ke SADAYA
              </h2>
              <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Masuk menggunakan akun resmi <span class="font-semibold text-[#005851] dark:text-[#5eead4]">Simply</span> atau email BKK Bandung.
              </p>
            </div>

            <!-- Status Flash Message -->
            <div
              v-if="status"
              class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2.5"
            >
              <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
              <span>{{ status }}</span>
            </div>

            <!-- Validation Error Alert Banner -->
            <div
              v-if="form.errors.login || form.errors.email || form.errors.password"
              class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-200 text-xs flex items-start gap-2.5"
            >
              <AlertCircle class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" />
              <div>
                <p class="font-semibold text-rose-900 dark:text-rose-100">Otentikasi Gagal</p>
                <p class="mt-0.5">{{ form.errors.login || form.errors.email || form.errors.password }}</p>
              </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Username / NIP / Email Input -->
              <div>
                <label for="login" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                  Username / NIP / Email
                </label>
                <div class="mt-1.5 relative">
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
                    autofocus
                    placeholder="NIP / Username / Email"
                    class="block w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#16202a] focus:bg-white dark:focus:bg-[#192430] border rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25 transition-all font-mono"
                    :class="(form.errors.login || form.errors.email) ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-[#00A99D]'"
                  />
                  <button
                    v-if="form.login"
                    type="button"
                    @click="clearLoginInput"
                    aria-label="Hapus isian username"
                    title="Hapus isian"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer"
                    tabindex="-1"
                  >
                    <X class="w-3.5 h-3.5" />
                  </button>
                </div>
                <p v-if="form.errors.login || form.errors.email" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                  {{ form.errors.login || form.errors.email }}
                </p>
              </div>

              <!-- Password Input -->
              <div>
                <div class="flex items-center justify-between gap-2">
                  <label for="password" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                    Kata Sandi
                  </label>
                  <span
                    v-if="capsLockActive"
                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-700/60 text-[10px] font-semibold text-amber-700 dark:text-amber-300"
                  >
                    <AlertCircle class="w-3 h-3" />
                    Caps Lock Aktif
                  </span>
                </div>
                <div class="mt-1.5 relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <Lock class="w-4 h-4" />
                  </div>
                  <input
                    id="password"
                    v-model="form.password"
                    @keydown="handlePasswordKey"
                    @keyup="handlePasswordKey"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                    class="block w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#16202a] focus:bg-white dark:focus:bg-[#192430] border rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#00A99D]/25 transition-all font-mono"
                    :class="form.errors.password ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-[#00A99D]'"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    :title="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer"
                    tabindex="-1"
                  >
                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                  {{ form.errors.password }}
                </p>
              </div>

              <!-- Remember Me Checkbox & Forgot Password Link -->
              <div class="flex items-center justify-between pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input
                    v-model="form.remember"
                    type="checkbox"
                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-[#16202a] text-[#005851] focus:ring-[#00A99D]/30 transition-colors cursor-pointer"
                  />
                  <span class="text-xs text-slate-600 dark:text-slate-300">Ingat sesi saya</span>
                </label>

                <a
                  :href="getUrl('/forgot-password')"
                  class="text-xs font-semibold text-[#005851] dark:text-[#5eead4] hover:text-[#00A99D] dark:hover:text-white transition-colors"
                >
                  Lupa sandi?
                </a>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full mt-2 py-2.5 px-4 rounded-xl font-semibold text-xs sm:text-sm text-white bg-gradient-to-r from-[#005851] to-[#007B72] hover:from-[#004640] hover:to-[#00665E] focus:outline-none focus:ring-2 focus:ring-[#00A99D] focus:ring-offset-2 dark:focus:ring-offset-[#111820] shadow-[0_6px_18px_-4px_rgba(0,88,81,0.45)] transition-all flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed group cursor-pointer"
              >
                <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                <LogIn v-else class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                <span>{{ form.processing ? 'Memverifikasi Akun...' : 'Masuk ke Command Center' }}</span>
              </button>
            </form>
          </div>

          <!-- Public Self-Service Portals & Security Footnote -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px]">
              <span class="text-slate-400 dark:text-slate-500 font-medium">Layanan Publik:</span>
              <div class="flex items-center gap-1.5">
                <a
                  :href="getUrl('/travelconsent')"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16202a] hover:bg-[#E6F7F5] dark:hover:bg-[#00A99D]/20 text-slate-600 dark:text-slate-300 hover:text-[#005851] dark:hover:text-[#5eead4] border border-slate-200/80 dark:border-slate-700 font-medium transition-colors"
                >
                  <span>Travel Consent</span>
                  <ArrowUpRight class="w-3 h-3" />
                </a>
                <a
                  :href="getUrl('/ship-surveillance/portal')"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16202a] hover:bg-[#E6F7F5] dark:hover:bg-[#00A99D]/20 text-slate-600 dark:text-slate-300 hover:text-[#005851] dark:hover:text-[#5eead4] border border-slate-200/80 dark:border-slate-700 font-medium transition-colors"
                >
                  <span>Portal TANGKAP</span>
                  <ArrowUpRight class="w-3 h-3" />
                </a>
              </div>
            </div>

            <div class="flex items-center justify-between text-[10.5px] text-slate-400 dark:text-slate-500">
              <span>BKK Kelas I Bandung</span>
              <span class="inline-flex items-center gap-1 text-[#007B72] dark:text-[#5eead4] font-mono">
                <ShieldCheck class="w-3 h-3" />
                <span>Sesi Terproteksi</span>
              </span>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>
