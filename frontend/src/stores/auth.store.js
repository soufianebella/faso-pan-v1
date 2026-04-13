// src/stores/auth.store.js
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { authApi } from "@/api/auth.api";

export const useAuthStore = defineStore("auth", () => {
  // ── State
  //  On initialise depuis localStorage
  // → si l'utilisateur rafraîchit, le token est conservé
  const token = ref(localStorage.getItem("token") || null);
  const user = ref(null);

  // ── Computed
  const isAuthenticated = computed(() => !!token.value);
  const userRole = computed(() => user.value?.role || null);

  // ── Actions

  /**
   * Login : credentials → token + user dans le state
   * Étapes : appel API → stockage state → localStorage → retour
   */
  async function login(credentials) {
    // http.js retourne response.data directement
    // donc data = { token, user }
    const data = await authApi.login(credentials);

    token.value = data.token;
    user.value = data.user;

    //  Persistance : survit au rafraîchissement
    localStorage.setItem("token", data.token);
  }

  /**
   * Logout : révocation serveur → nettoyage local → redirection
   */
  async function logout() {
    //  try/catch : même si le serveur est down,
    // on nettoie le state local
    try {
      await authApi.logout();
    } catch {
      // Token déjà révoqué ou serveur injoignable → on continue
    } finally {
      _clearSession();
    }
  }

  /**
   * fetchMe : synchronise le state avec la réalité du serveur
   * Appelé au démarrage de l'app (App.vue ou router guard)
   * Si le token est expiré → logout automatique
   */
  async function fetchMe() {
    try {
      user.value = await authApi.me();
    } catch {
      // Token invalide → on nettoie sans redirection
      // Le router guard s'occupera de la redirection
      _clearSession();
    }
  }

  /**
   * Vérifie si l'utilisateur a un rôle donné
   * Usage : authStore.hasRole('super_admin')
   */
  function hasRole(role) {
    return userRole.value === role;
  }

  /**
   * Nettoyage interne — privé (convention _ = privé)
   * Centralisé ici pour ne pas dupliquer dans logout et fetchMe
   */
  function _clearSession() {
    token.value = null;
    user.value = null;
    localStorage.removeItem("token");
  }

  return {
    // State
    token,
    user,
    // Computed
    isAuthenticated,
    userRole,
    // Actions
    login,
    logout,
    fetchMe,
    hasRole,
  };
});
