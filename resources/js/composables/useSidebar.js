import { ref } from 'vue';

const isOpen = ref(false); // Mobile drawer open state
const isCollapsed = ref(true); // Default to Slim Icon Rail like reference design

if (typeof window !== 'undefined') {
  const savedCollapsed = localStorage.getItem('sadaya_sidebar_rail_collapsed');
  if (savedCollapsed !== null) {
    isCollapsed.value = savedCollapsed === 'true';
  }
}

export function useSidebar() {
  const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('sadaya_sidebar_rail_collapsed', isCollapsed.value.toString());
    }
  };

  const toggleOpen = () => {
    isOpen.value = !isOpen.value;
  };

  const closeMobile = () => {
    isOpen.value = false;
  };

  const openMobile = () => {
    isOpen.value = true;
  };

  return {
    isOpen,
    isCollapsed,
    toggleCollapse,
    toggleOpen,
    closeMobile,
    openMobile,
  };
}
