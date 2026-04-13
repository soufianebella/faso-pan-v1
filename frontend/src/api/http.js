import axios from "axios";
import { useAuthStore } from "@/stores/auth.store";

/**
 * Configuration de l'instance Axios pour l'application Fasopan.
 * Centralise l'URL de base et les headers par défaut pour toutes les requêtes API.
 */
const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api",
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

/**
 * Lire localStorage directement dans le request interceptor
localStorage est toujours disponible, pas de dépendance circulaire
 */
http.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
  },
  (error) => Promise.reject(error),
);

/**
 * Intercepteur de réponse.
 * Fournit une gestion globale des erreurs de session.
 * En cas d'erreur 401 (Unauthenticated), le système force la déconnexion
 * locale pour garantir l'intégrité de la session utilisateur.
 */
http.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore();

      // Suppression des données de session et redirection vers le point d'entrée
      authStore.logout();
      window.location.href = "/login";
    }
    return Promise.reject(error);
  },
);

export default http;
