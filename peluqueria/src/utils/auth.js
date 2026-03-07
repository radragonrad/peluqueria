// src/utils/auth.js
import { useStore } from '../store.js';

export const initializeAuth = async () => {
  const store = useStore();
  
  try {
    const res = await fetch('/api/check_auth.php', {
      credentials: 'include'
    });
    
    if (res.ok) {
      const data = await res.json();
      if (data.logged_in && data.rol === 'usuario') {
        store.setUsuarioLogueado(true, data.email);
        return true;
      }
    }
  } catch (error) {
    console.error('Error al verificar autenticación:', error);
  }
  
  store.setUsuarioLogueado(false, '');
  return false;
};