<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <!-- Full-page blended background -->
    <div class="min-h-screen relative overflow-hidden flex items-center justify-center"
         style="background: linear-gradient(120deg, #f5a623 0%, #f0a020 35%, #fcd08a 58%, #fff8f0 75%, #ffffff 100%);">

        <!-- Soft radial glow left -->
        <div class="absolute top-0 left-0 w-2/3 h-full pointer-events-none"
             style="background: radial-gradient(ellipse at 20% 50%, rgba(245,166,35,0.5) 0%, transparent 70%);"></div>

        <!-- Soft radial glow right -->
        <div class="absolute top-0 right-0 w-1/2 h-full pointer-events-none"
             style="background: radial-gradient(ellipse at 80% 50%, rgba(255,255,255,0.9) 0%, transparent 70%);"></div>

        <!-- ── LOGO top-left ── -->
        <div class="absolute top-5 left-7 z-20 flex items-center gap-3">
            <img src="/images/zulia.jpeg" alt="Logo" class="w-14 h-14 object-contain rounded-2xl shadow-md" />
            <div>
                <p class="text-white font-bold text-base leading-tight drop-shadow">Zulia Tours & Travels</p>
                <p class="text-white/70 text-[11px] uppercase tracking-widest">BD Limited</p>
            </div>
        </div>

        <!-- ── MAIN CONTENT ── -->
        <div class="relative z-10 w-full max-w-6xl mx-auto px-6 flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-0 py-20 lg:py-0">

            <!-- LEFT: Airplane + tagline -->
            <div class="flex flex-col items-center lg:items-start gap-6 lg:w-1/2">

                <!-- Airplane card with thumbtack -->
                <div class="relative w-72 sm:w-80 lg:w-96">
                    <!-- Thumbtack -->
                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center"
                         style="filter: drop-shadow(0 6px 10px rgba(0,0,0,0.3));">
                        <div class="w-9 h-9 rounded-full"
                             style="background: radial-gradient(circle at 35% 30%, #ff8a80, #c0392b);
                                    box-shadow: 0 4px 14px rgba(192,57,43,0.55), inset 0 -2px 4px rgba(0,0,0,0.2);">
                            <div class="w-2.5 h-2.5 rounded-full bg-white/55 mt-1 ml-1"></div>
                        </div>
                        <div class="w-2 h-5 rounded-b-full -mt-1"
                             style="background: linear-gradient(to bottom, #c0392b, #7b241c);"></div>
                    </div>

                    <!-- Photo card -->
                    <div class="w-full rounded-2xl overflow-hidden"
                         style="height: 240px;
                                background-image: url('/images/airplane.png');
                                background-size: cover;
                                background-position: center;
                                transform: rotate(-5deg);
                                box-shadow: 0 25px 60px rgba(0,0,0,0.25), 0 8px 20px rgba(0,0,0,0.15);
                                border: 3px solid rgba(255,255,255,0.6);">
                    </div>
                </div>

                <!-- Tagline -->
                <div class="text-center lg:text-left pl-0 lg:pl-4">
                    <h2 class="text-white text-3xl lg:text-4xl font-black leading-tight drop-shadow-md">
                        Your Gateway<br/>to the World
                    </h2>
                    <p class="text-white/75 text-sm mt-2">Manage your travel operations with ease.</p>
                    <div class="flex items-center gap-2 mt-5 justify-center lg:justify-start">
                        <span v-for="i in 4" :key="i" class="rounded-full transition-all"
                              :class="i === 1 ? 'w-6 h-2 bg-white' : 'w-2 h-2 bg-white/40'"></span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Floating form card -->
            <div class="lg:w-[420px] w-full">
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_8px_60px_rgba(0,0,0,0.12)] border border-white/80 px-8 py-10">

                    <!-- Heading -->
                    <div class="mb-7">
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-1">SIGN IN</h1>
                        <p class="text-gray-400 text-sm">Welcome back — sign in to continue</p>
                    </div>

                    <!-- Status -->
                    <div v-if="status" class="mb-5 p-3 rounded-xl bg-blue-50 border border-blue-200 text-sm text-blue-700">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                placeholder="you@example.com"
                                class="w-full px-4 py-3.5 rounded-xl text-gray-900 text-sm placeholder-gray-300 outline-none transition-all duration-200 border focus:shadow-[0_0_0_3px_rgba(245,166,35,0.2)] focus:border-orange-400"
                                :class="form.errors.email ? 'bg-red-50 border-red-300' : 'bg-white border-gray-200 focus:bg-white'"
                            />
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Password</label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    required
                                    placeholder="••••••••"
                                    class="w-full px-4 py-3.5 pr-12 rounded-xl text-gray-900 text-sm placeholder-gray-300 outline-none transition-all duration-200 border focus:shadow-[0_0_0_3px_rgba(245,166,35,0.2)] focus:border-orange-400"
                                    :class="form.errors.password ? 'bg-red-50 border-red-300' : 'bg-white border-gray-200 focus:bg-white'"
                                />
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-300 hover:text-gray-600 transition-colors">
                                    <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember + Forgot -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input v-model="form.remember" type="checkbox"
                                       class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400 focus:ring-offset-0" />
                                <span class="text-gray-500 text-sm group-hover:text-gray-700 transition-colors">Remember me</span>
                            </label>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                  class="text-sm text-orange-500 hover:text-orange-700 font-medium transition-colors">
                                Forgot password?
                            </Link>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 rounded-xl font-bold text-sm text-white transition-all duration-300 mt-1 disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-[1px]"
                            style="background: linear-gradient(135deg, #f5a623, #e8920f);
                                   box-shadow: 0 6px 24px rgba(245,166,35,0.45);"
                            @mouseover="$event.target.style.boxShadow='0 8px 32px rgba(245,166,35,0.6)'"
                            @mouseleave="$event.target.style.boxShadow='0 6px 24px rgba(245,166,35,0.45)'"
                        >
                            <span v-if="!form.processing">Sign In →</span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                                Signing in...
                            </span>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-gray-300 text-xs">
                        © {{ new Date().getFullYear() }} Zulia Tours & Travels BD Limited
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
