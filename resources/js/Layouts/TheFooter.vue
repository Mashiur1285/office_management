<template>
    <footer
        class="layout-footer bg-white border-t border-gray-200 mt-auto print:hidden"
    >
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div
                class="flex items-center justify-center gap-3 text-sm text-gray-600"
            >
                <span class="text-gray-600">Developed by</span>
                <a
                    href="https://auroradigitallabs.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:opacity-80 transition-opacity"
                >
                    <img
                        alt="Aurora Digital Labs"
                        src="/images/aurora.jpeg"
                        class="h-8 object-contain"
                    />
                </a>
                <a
                    href="https://auroradigitallabs.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-semibold text-gray-900 hover:text-blue-600 transition-colors"
                >
                    Aurora Digital Labs
                </a>
            </div>
        </div>
    </footer>
</template>

<script setup>
</script>

<style scoped>
.layout-footer {
    transition: all 0.2s ease-in-out;
}
</style>
