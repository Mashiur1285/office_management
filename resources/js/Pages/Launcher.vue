<template>
    <Head title="Welcome" />

    <div class="launcher-root min-h-screen flex flex-col relative overflow-hidden" @mousemove="onMouseMove">

        <!-- Cursor spotlight -->
        <div class="pointer-events-none fixed inset-0 z-0 transition-opacity duration-300"
             :style="spotlightStyle"></div>

        <!-- Background mesh -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-1/4 w-[600px] h-[600px] rounded-full opacity-20"
                 style="background: radial-gradient(circle, #3b82f6 0%, transparent 70%); filter: blur(80px);"></div>
            <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] rounded-full opacity-15"
                 style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%); filter: blur(80px);"></div>
            <div class="absolute top-1/2 left-0 w-[400px] h-[400px] rounded-full opacity-10"
                 style="background: radial-gradient(circle, #06b6d4 0%, transparent 70%); filter: blur(100px);"></div>
        </div>

        <!-- Header -->
        <header class="relative z-10 flex items-center justify-between px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="w-16 h-16 rounded-2xl overflow-hidden ring-1 ring-white/15 shadow-xl flex-shrink-0 bg-white p-1">
                    <img src="/images/zulia.jpeg" alt="Logo" class="w-full h-full object-contain" />
                </div>
                <div>
                    <p class="text-white font-semibold text-[15px] leading-tight">{{ appName }}</p>
                    <p class="text-white/30 text-[10px] tracking-widest uppercase">Management</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-white/5 ring-1 ring-white/10">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-[11px] font-bold">
                        {{ userInitial }}
                    </div>
                    <span class="text-white/70 text-sm font-medium">{{ userName }}</span>
                    <span class="text-white/20 text-xs">·</span>
                    <span class="text-white/30 text-xs capitalize">{{ userRole }}</span>
                </div>
                <button @click="logout"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-white/30 hover:text-white/70 hover:bg-white/5 ring-1 ring-white/[0.06] hover:ring-white/10 transition-all duration-200 text-sm">
                    <font-awesome-icon icon="arrow-right-from-bracket" class="w-3 h-3" />
                    <span>Logout</span>
                </button>
            </div>
        </header>

        <!-- Main -->
        <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-6 pb-10">

            <!-- Greeting -->
            <div class="text-center mb-12 greeting-enter">
                <p class="text-white/30 text-xs font-medium tracking-[0.3em] uppercase mb-3">Welcome back</p>
                <h1 class="text-5xl font-black text-white mb-3 tracking-tight" style="letter-spacing: -0.02em;">
                    {{ userName }}
                </h1>
                <p class="text-white/30 text-sm">Select a module to continue</p>
            </div>

            <!-- Tiles -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 w-full max-w-5xl">
                <button
                    v-for="(mod, index) in visibleModules"
                    :key="mod.name"
                    class="tile group relative flex flex-col items-center justify-center gap-3.5 p-5 rounded-2xl text-left overflow-hidden"
                    :style="{ '--glow': mod.iconColor, animationDelay: `${index * 50}ms` }"
                    @click="navigate(mod.route)"
                >
                    <!-- Glass base -->
                    <div class="absolute inset-0 rounded-2xl transition-all duration-300"
                         style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);"></div>
                    <!-- Hover glow border -->
                    <div class="tile-glow-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-all duration-300"
                         :style="{ boxShadow: `inset 0 0 0 1px ${mod.iconColor}40, 0 0 30px ${mod.iconColor}20` }"></div>
                    <!-- Top accent line -->
                    <div class="tile-accent-line absolute top-0 left-4 right-4 h-px rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300"
                         :style="{ background: `linear-gradient(90deg, transparent, ${mod.iconColor}80, transparent)` }"></div>

                    <!-- Icon -->
                    <div class="relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 group-hover:scale-110"
                         :style="{ background: `${mod.iconColor}18` }">
                        <font-awesome-icon :icon="mod.icon" class="w-5 h-5 transition-all duration-300" :style="{ color: mod.iconColor }" />
                    </div>

                    <!-- Text -->
                    <div class="relative text-center w-full">
                        <p class="text-white/85 font-semibold text-[13px] leading-tight group-hover:text-white transition-colors duration-200">{{ mod.name }}</p>
                        <p class="text-white/25 text-[10px] mt-0.5 leading-tight group-hover:text-white/40 transition-colors duration-200">{{ mod.description }}</p>
                    </div>
                </button>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 text-center py-4">
            <p class="text-white/15 text-[11px] tracking-wider">{{ appName }} © {{ new Date().getFullYear() }}</p>
        </footer>
    </div>
</template>

<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const mouseX = ref(-9999);
const mouseY = ref(-9999);

const onMouseMove = (e) => {
    mouseX.value = e.clientX;
    mouseY.value = e.clientY;
};

const spotlightStyle = computed(() => ({
    background: `radial-gradient(700px circle at ${mouseX.value}px ${mouseY.value}px,
        rgba(99,102,241,0.10) 0%,
        rgba(59,130,246,0.06) 30%,
        transparent 65%
    )`,
}));

defineOptions({ layout: null });

const $page = usePage();
const userPermissions = computed(() => $page.props.userPermissions || []);
const userRoles      = computed(() => $page.props.userRoles || []);
const appName        = computed(() => $page.props.settings?.app_name || "Zulia");
const userName       = computed(() => $page.props.auth?.user?.name || "User");
const userInitial    = computed(() => userName.value.charAt(0).toUpperCase());
const userRole       = computed(() => userRoles.value[0] || "Staff");

const hasPermission    = (p) => !p || userPermissions.value.includes(p) || userPermissions.value.includes("*") || userPermissions.value.includes("superadmin");
const hasAnyPermission = (ps) => !ps || ps.some(hasPermission);
const isAdmin          = computed(() => userRoles.value.some(r => ["admin","super-admin","superadmin","Admin","Super Admin"].includes(r)));

const allModules = [
    { name:"Dashboard",        description:"Overview & analytics",       icon:"house",          route:"/dashboard",              permission:"dashboard.view",       iconColor:"#60a5fa" },
    { name:"Operations",       description:"Quotations & Invoices",      icon:"cubes",          route:"/quotations",             permissions:["quotation.view","invoice.view"], iconColor:"#fb923c" },
    { name:"Airline Tickets",  description:"Flight ticket management",   icon:"plane-departure",route:"/airline-tickets",        permission:null,                   iconColor:"#38bdf8" },
    { name:"Registrations",    description:"Clients, Agents & more",     icon:"layer-group",    route:"/clients",                permissions:["client.view","agent.view","office-staff.view","bd-company.view","foreign-company.view"], iconColor:"#a78bfa" },
    { name:"Database",         description:"View-only records",          icon:"database",       route:"/database/clients",       permissions:["client.view","agent.view","office-staff.view","bd-company.view","foreign-company.view"], iconColor:"#34d399" },
    { name:"Accounts",         description:"Financial reports",          icon:"chart-line",     route:"/accounting",             permission:"accounting.view",       iconColor:"#2dd4bf" },
    { name:"Reports",          description:"Business reports",           icon:"chart-bar",      route:"/reports/refund-report",  permissions:["reports.view","reports.*","accounting.view","invoice.view","client.view"], iconColor:"#fb7185" },
    { name:"ACL",              description:"Users & Roles",              icon:"user-shield",    route:"/users",                  adminOnly:true,                    iconColor:"#818cf8" },
    { name:"Settings",         description:"System configuration",       icon:"gear",           route:"/settings",               permission:"settings.view",        iconColor:"#fbbf24" },
    { name:"My Notepad",       description:"Personal notes",             icon:"file-lines",     route:"/notepad",                permission:null,                   iconColor:"#7dd3fc" },
];

const visibleModules = computed(() =>
    allModules.filter(m => {
        if (m.adminOnly)   return isAdmin.value;
        if (m.permissions) return hasAnyPermission(m.permissions);
        return hasPermission(m.permission);
    })
);

const navigate = (r) => router.visit(r);
const logout   = () => router.post(route("logout"));
</script>

<style scoped>
.launcher-root {
    background: #07090f;
    background-image:
        radial-gradient(ellipse 80% 50% at 20% 20%, rgba(59,130,246,0.07) 0%, transparent 100%),
        radial-gradient(ellipse 60% 40% at 80% 80%, rgba(139,92,246,0.06) 0%, transparent 100%);
}

/* Greeting entrance */
.greeting-enter {
    animation: greetIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes greetIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Tile entrance */
.tile {
    animation: tileIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    cursor: pointer;
    background: transparent;
    border: none;
    outline: none;
}
.tile:hover {
    transform: translateY(-6px) scale(1.06);
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    z-index: 10;
}

@keyframes tileIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.96);
        filter: blur(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
    }
}
</style>
