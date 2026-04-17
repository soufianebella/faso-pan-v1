import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth.store";
import { useDashboardStore } from "@/stores/dashboard.store";

const routes = [
  {
    path: "/login",
    name: "login",
    component: () => import("@/views/auth/LoginView.vue"),
    meta: { public: true },
  },
  {
    path: "/",
    component: () => import("@/layouts/AppLayout.vue"),
    children: [
      {
        path: "",
        name: "dashboard",
        component: () => import("@/views/auth/DashboardView.vue"),
      },
      {
        path: "users",
        name: "users",
        component: () => import("@/views/users/UsersView.vue"),
      },
      {
        path: "panneaux",
        name: "panneaux",
        component: () => import("@/views/panneaux/PanneauxView.vue"),
      },
      {
        path: "campagnes",
        name: "campagnes",
        component: () => import("@/views/campagnes/CampagnesView.vue"),
      },
      {
        path: "taches",
        name: "taches",
        component: () => import("@/views/taches/TachesView.vue"),
      },
      {
        path: "statistiques",
        name: "statistiques",
        component: () => import("@/views/statistiques/StatistiquesView.vue"),
      },
      {
        path: "campagnes",
        name: "campagnes",
        component: () => import("@/views/campagnes/CampagnesView.vue"),
      },

      {
        path: "taches",
        name: "taches",
        component: () => import("@/views/taches/TachesView.vue"),
      },

      {
        path: "",
        name: "dashboard",
        component: () => import("@/views/dashboard/DashboardView.vue"),
      },

      {
        path: "statistiques",
        name: "statistiques",
        component: () => import("@/views/statistiques/StatistiquesView.vue"),
      },
    ],
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();

  if (to.meta.public) return true;

  if (!auth.isAuthenticated) return { name: "login" };

  if (!auth.user) {
    await auth.fetchMe();
    if (!auth.isAuthenticated) return { name: "login" };
  }

  if (to.name === "login" && auth.isAuthenticated) {
    return { name: "dashboard" };
  }

  return true;
});

export default router;
