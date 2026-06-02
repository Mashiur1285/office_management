import { ref } from 'vue';

const sidebarCollapsed = ref(
    typeof window !== 'undefined'
        ? localStorage.getItem('sidebar_collapsed') === 'true'
        : false
);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem('sidebar_collapsed', sidebarCollapsed.value);
    }
};

export function useSidebar() {
    return { sidebarCollapsed, toggleSidebar };
}
