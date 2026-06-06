<template>
    <footer class="layout-footer bg-white border-t border-gray-200 mt-auto print:hidden">
        <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 items-center gap-4 text-xs text-gray-500">

                <!-- Left: Address -->
                <div class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 text-gray-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    <span class="leading-relaxed">
                        25,26,27 Kazi Nazrul Islam Avenue, Banglamotor Trade Center, 4th Floor, Shahbagh, Dhaka-1000
                    </span>
                </div>

                <!-- Center: Developed by -->
                <div class="flex flex-col items-center gap-1">
                    <img src="/images/aurora.jpeg" alt="Aurora Digital Labs" class="h-7 w-auto object-contain opacity-80" />
                    <span class="text-gray-400 text-[10px]">Developed by <span class="text-gray-500 font-medium">Aurora Digital Labs</span></span>
                </div>

                <!-- Right: Contact -->
                <div class="flex flex-col items-end gap-1">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                        +88 01716 864 109 / +88 01332 502 234
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                        zulia.tourstravelsbd@gmail.com
                    </span>
                </div>

            </div>
        </div>
    </footer>
</template>

<style scoped>
.layout-footer {
    transition: all 0.2s ease-in-out;
}
</style>
