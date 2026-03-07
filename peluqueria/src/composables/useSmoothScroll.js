// src/composables/useSmoothScroll.js
export function useSmoothScroll() {
  const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
      window.scrollTo({
        top: element.offsetTop - 80,
        behavior: 'smooth'
      });
    }
  };

  return {
    scrollToSection
  };
}