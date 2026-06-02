<template>
    <Head title="My Notepad" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Personal Notepad</h1>
                    <p class="text-sm text-gray-600 mt-1">Private and encrypted - only you can see this</p>
                </div>
                <div class="flex items-center gap-3">
                    <span v-if="saveStatus" :class="saveStatusClass" class="text-sm font-medium">
                        {{ saveStatusText }}
                    </span>
                    <button
                        v-if="isUnlocked"
                        @click="lockNotepad"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Lock Notepad
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="!isUnlocked" class="relative min-h-[600px] flex items-center justify-center bg-gray-50">
                    <div class="max-w-md w-full mx-4">
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                    {{ modeTitle }}
                                </h2>
                                <p class="text-sm text-gray-600">
                                    {{ modeSubtitle }}
                                </p>
                            </div>

                            <form v-if="mode === 'setup'" @submit.prevent="setupNotepad">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New 4-digit PIN</label>
                                    <div class="relative">
                                        <input
                                            v-model="setupPin"
                                            ref="setupPinInput"
                                            :type="showSetupPin ? 'text' : 'password'"
                                            inputmode="numeric"
                                            pattern="\d{4}"
                                            maxlength="4"
                                            required
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter 4-digit PIN"
                                            :disabled="isSubmitting"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            @click="showSetupPin = !showSetupPin"
                                        >
                                            <svg v-if="!showSetupPin" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm PIN</label>
                                    <div class="relative">
                                        <input
                                            v-model="setupPinConfirm"
                                            :type="showSetupPinConfirm ? 'text' : 'password'"
                                            inputmode="numeric"
                                            pattern="\d{4}"
                                            maxlength="4"
                                            required
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Re-enter PIN"
                                            :disabled="isSubmitting"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            @click="showSetupPinConfirm = !showSetupPinConfirm"
                                        >
                                            <svg v-if="!showSetupPinConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="formError" class="mt-2 text-sm text-red-600">{{ formError }}</p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!isSubmitting">Set PIN & Unlock</span>
                                    <span v-else>Setting...</span>
                                </button>
                            </form>

                            <form v-else-if="mode === 'unlock'" @submit.prevent="unlockNotepad">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">4-digit PIN</label>
                                    <div class="relative">
                                        <input
                                            v-model="unlockPin"
                                            ref="unlockPinInput"
                                            :type="showUnlockPin ? 'text' : 'password'"
                                            inputmode="numeric"
                                            pattern="\d{4}"
                                            maxlength="4"
                                            required
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter PIN"
                                            :disabled="isSubmitting"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            @click="showUnlockPin = !showUnlockPin"
                                        >
                                            <svg v-if="!showUnlockPin" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="formError" class="mt-2 text-sm text-red-600">{{ formError }}</p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!isSubmitting">Unlock Notepad</span>
                                    <span v-else>Unlocking...</span>
                                </button>
                                <button
                                    type="button"
                                    class="mt-3 w-full text-sm text-blue-600 hover:text-blue-700"
                                    @click="mode = 'reset'; formError = ''"
                                >
                                    Forgot PIN?
                                </button>
                            </form>

                            <form v-else @submit.prevent="resetNotepadPassword">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Password</label>
                                    <div class="relative">
                                        <input
                                            v-model="accountPassword"
                                            ref="resetAccountInput"
                                            :type="showAccountPassword ? 'text' : 'password'"
                                            required
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter account password"
                                            :disabled="isSubmitting"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            @click="showAccountPassword = !showAccountPassword"
                                        >
                                            <svg v-if="!showAccountPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New 4-digit PIN</label>
                                    <div class="relative">
                                        <input
                                            v-model="resetPin"
                                            :type="showResetPin ? 'text' : 'password'"
                                            inputmode="numeric"
                                            pattern="\d{4}"
                                            maxlength="4"
                                            required
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter new PIN"
                                            :disabled="isSubmitting"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            @click="showResetPin = !showResetPin"
                                        >
                                            <svg v-if="!showResetPin" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm PIN</label>
                                    <div class="relative">
                                        <input
                                            v-model="resetPinConfirm"
                                            :type="showResetPinConfirm ? 'text' : 'password'"
                                            inputmode="numeric"
                                            pattern="\d{4}"
                                            maxlength="4"
                                            required
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Re-enter new PIN"
                                            :disabled="isSubmitting"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            @click="showResetPinConfirm = !showResetPinConfirm"
                                        >
                                            <svg v-if="!showResetPinConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="formError" class="mt-2 text-sm text-red-600">{{ formError }}</p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!isSubmitting">Reset PIN & Unlock</span>
                                    <span v-else>Resetting...</span>
                                </button>
                                <button
                                    type="button"
                                    class="mt-3 w-full text-sm text-blue-600 hover:text-blue-700"
                                    @click="mode = 'unlock'; formError = ''"
                                >
                                    Back to unlock
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div v-else class="p-6">
                    <div class="space-y-4">
                        <div>
                            <textarea
                                v-model="content"
                                rows="20"
                                class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white font-mono"
                                placeholder="Start writing your notes here... Your content is automatically encrypted and saved."
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-between text-sm text-gray-600 pt-2 border-t border-gray-100">
                            <div class="flex items-center gap-4">
                                <span>Words: <span class="font-semibold text-gray-900">{{ wordCount }}</span></span>
                                <span>Characters: <span class="font-semibold text-gray-900">{{ charCount }}</span></span>
                            </div>
                            <div v-if="lastSavedAt" class="text-xs">
                                Last saved: <span class="font-semibold">{{ formatTime(lastSavedAt) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Your notepad is encrypted and private</p>
                        <p class="text-xs text-blue-700 mt-1">Content is encrypted in the database and protected by your personal 4-digit PIN.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    notepad: {
        type: Object,
        required: true,
    },
});

const isUnlocked = ref(false);
const hasPassword = ref(!!props.notepad.has_password);
const mode = ref(hasPassword.value ? 'unlock' : 'setup');

const setupPin = ref('');
const setupPinConfirm = ref('');
const unlockPin = ref('');
const accountPassword = ref('');
const resetPin = ref('');
const resetPinConfirm = ref('');
const formError = ref('');
const isSubmitting = ref(false);
const showSetupPin = ref(false);
const showSetupPinConfirm = ref(false);
const showUnlockPin = ref(false);
const showAccountPassword = ref(false);
const showResetPin = ref(false);
const showResetPinConfirm = ref(false);

const setupPinInput = ref(null);
const unlockPinInput = ref(null);
const resetAccountInput = ref(null);

const content = ref('');
const saveStatus = ref('');
const lastSavedAt = ref(null);
let saveTimeoutId = null;

const modeTitle = computed(() => {
    if (mode.value === 'setup') return 'Set Your Notepad PIN';
    if (mode.value === 'reset') return 'Reset Notepad PIN';
    return 'Unlock Your Notepad';
});

const modeSubtitle = computed(() => {
    if (mode.value === 'setup') return 'Create a 4-digit PIN to secure your notepad.';
    if (mode.value === 'reset') return 'Verify your account and set a new 4-digit PIN.';
    return 'Enter your 4-digit PIN to access your encrypted notes.';
});

const wordCount = computed(() => {
    return content.value.trim().split(/\s+/).filter(w => w.length > 0).length;
});

const charCount = computed(() => content.value.length);

const saveStatusClass = computed(() => {
    const classes = {
        saving: 'text-yellow-600',
        saved: 'text-blue-600',
        error: 'text-red-600',
    };
    return classes[saveStatus.value] || '';
});

const saveStatusText = computed(() => {
    const texts = {
        saving: 'Saving...',
        saved: 'Saved',
        error: 'Error saving',
    };
    return texts[saveStatus.value] || '';
});

const setUnlockedState = (response) => {
    content.value = response.data.content || '';
    lastSavedAt.value = response.data.updated_at ? new Date(response.data.updated_at) : null;
    isUnlocked.value = true;
};

const setupNotepad = async () => {
    formError.value = '';

    if (setupPin.value !== setupPinConfirm.value) {
        formError.value = 'PIN mismatch. Please try again.';
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(route('notepad.setup'), {
            password: setupPin.value,
        });

        if (response.data.success) {
            hasPassword.value = true;
            mode.value = 'unlock';
            setupPin.value = '';
            setupPinConfirm.value = '';
            setUnlockedState(response);
        }
    } catch (error) {
        if (error.response?.status === 422) {
            formError.value = 'PIN must be exactly 4 digits.';
        } else {
            formError.value = 'An error occurred. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const unlockNotepad = async () => {
    formError.value = '';
    isSubmitting.value = true;

    try {
        const response = await axios.post(route('notepad.unlock'), {
            password: unlockPin.value,
        });

        if (response.data.success) {
            unlockPin.value = '';
            setUnlockedState(response);
        }
    } catch (error) {
        if (error.response?.status === 409) {
            hasPassword.value = false;
            mode.value = 'setup';
            formError.value = 'PIN not set yet. Please create one.';
        } else if (error.response?.status === 401) {
            formError.value = 'Invalid PIN. Please try again.';
        } else if (error.response?.status === 422) {
            formError.value = 'PIN must be exactly 4 digits.';
        } else {
            formError.value = 'An error occurred. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const resetNotepadPassword = async () => {
    formError.value = '';

    if (resetPin.value !== resetPinConfirm.value) {
        formError.value = 'PIN mismatch. Please try again.';
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(route('notepad.reset'), {
            account_password: accountPassword.value,
            new_password: resetPin.value,
        });

        if (response.data.success) {
            hasPassword.value = true;
            mode.value = 'unlock';
            accountPassword.value = '';
            resetPin.value = '';
            resetPinConfirm.value = '';
            setUnlockedState(response);
        }
    } catch (error) {
        if (error.response?.status === 401) {
            formError.value = 'Account password is incorrect.';
        } else if (error.response?.status === 422) {
            formError.value = 'PIN must be exactly 4 digits.';
        } else {
            formError.value = 'An error occurred. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const saveContent = async () => {
    saveStatus.value = 'saving';

    try {
        const response = await axios.put(route('notepad.update'), {
            content: content.value,
        });

        if (response.data.success) {
            saveStatus.value = 'saved';
            lastSavedAt.value = new Date(response.data.updated_at);

            setTimeout(() => {
                saveStatus.value = '';
            }, 2000);
        }
    } catch (error) {
        saveStatus.value = 'error';
        console.error('Save error:', error);
    }
};

const lockNotepad = () => {
    content.value = '';
    isUnlocked.value = false;
    unlockPin.value = '';
    formError.value = '';
    mode.value = hasPassword.value ? 'unlock' : 'setup';
};

const formatTime = (date) => {
    if (!date) return '';
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} minute(s) ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hour(s) ago`;

    return date.toLocaleString('en-BD', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

watch(
    () => content.value,
    () => {
        if (!isUnlocked.value) return;

        clearTimeout(saveTimeoutId);
        saveStatus.value = '';

        saveTimeoutId = setTimeout(() => {
            saveContent();
        }, 1500);
    }
);

watch(mode, async () => {
    await nextTick();
    if (mode.value === 'setup' && setupPinInput.value) {
        setupPinInput.value.focus();
    }
    if (mode.value === 'unlock' && unlockPinInput.value) {
        unlockPinInput.value.focus();
    }
    if (mode.value === 'reset' && resetAccountInput.value) {
        resetAccountInput.value.focus();
    }
});

onMounted(() => {
    if (mode.value === 'setup' && setupPinInput.value) {
        setupPinInput.value.focus();
    }
    if (mode.value === 'unlock' && unlockPinInput.value) {
        unlockPinInput.value.focus();
    }
    if (mode.value === 'reset' && resetAccountInput.value) {
        resetAccountInput.value.focus();
    }
});
</script>
