import http from "./http";

export const authApi = {
  /**
   * @param {Object} credentials - { email, password }
   */
  login: (credentials) => http.post("/v1/login", credentials),

  logout: () => http.post("/v1/logout"),

  /**
   * Récupère les informations de l'utilisateur et ses rôles via le token actif
   */
  me: () => http.get("/v1/me"),

  updateProfile: (data) => http.put("/v1/me", data),
};
