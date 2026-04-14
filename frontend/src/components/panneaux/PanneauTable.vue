<template>
  <div
    class="bg-white rounded shadow-sm overflow-hidden border border-[#E5E7EB]"
  >
    <table class="w-full text-left border-collapse">
      <thead class="bg-[#F0F4FF] border-b border-[#E5E7EB]">
        <tr>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider"
            style="color: #1b3b8a"
          >
            Référence
          </th>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider"
            style="color: #1b3b8a"
          >
            Ville
          </th>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider"
            style="color: #1b3b8a"
          >
            Faces
          </th>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider"
            style="color: #1b3b8a"
          >
            Faces Libres
          </th>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider text-center"
            style="color: #1b3b8a"
          >
            Éclairage
          </th>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider"
            style="color: #1b3b8a"
          >
            Statut
          </th>
          <th
            class="p-4 text-xs font-semibold uppercase tracking-wider text-right"
            style="color: #1b3b8a"
          >
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#E5E7EB]">
        <tr v-if="panneaux.length === 0">
          <td colspan="7" class="py-12 text-center" style="color: #9ca3af">
            <div class="flex flex-col items-center gap-2">
              <i class="fa-solid fa-sign-hanging text-3xl"></i>
              <span class="text-sm font-medium"> Aucun panneau trouve </span>
            </div>
          </td>
        </tr>
        <tr
          v-for="panneau in panneaux"
          :key="panneau.id"
          class="hover:bg-gray-50 transition-colors"
        >
          <td class="p-4 text-sm font-medium" style="color: #374151">
            {{ panneau.reference }}
          </td>

          <td class="p-4 text-sm" style="color: #6b7280">
            {{ panneau.ville }}
          </td>

          <td class="p-4 text-sm" style="color: #374151">
            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-bold">
              {{ panneau.faces_count || 0 }}
            </span>
          </td>

          <td class="p-4 text-sm">
            <span :class="getLibreClass(panneau.faces_libres_count)">
              {{ panneau.faces_libres_count || 0 }} /
              {{ panneau.faces_count || 0 }}
            </span>
          </td>

          <td class="p-4 text-center">
            <i
              v-if="panneau.eclaire"
              class="fa-solid fa-lightbulb text-yellow-500"
              title="Éclairé"
            ></i>
            <i
              v-else
              class="fa-solid fa-moon text-gray-300"
              title="Non éclairé"
            ></i>
          </td>

          <td class="p-4 text-sm">
            <span
              :class="getStatusClass(panneau.statut)"
              class="px-2.5 py-0.5 rounded-full text-xs font-medium"
            >
              {{ formatStatut(panneau.statut) }}
            </span>
          </td>

          <td class="p-4 text-right space-x-2">
            <button
              @click="$emit('edit', panneau)"
              class="p-1.5 hover:bg-blue-50 rounded text-blue-600 transition-colors"
              title="Modifier"
            >
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button
              @click="$emit('archive', panneau.id)"
              class="p-1.5 hover:bg-red-50 rounded text-red-600 transition-colors"
              title="Archiver"
            >
              <i class="fa-solid fa-box-archive"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  panneaux: {
    type: Array,
    required: true,
  },
});

defineEmits(["edit", "archive"]);

// Gestion des classes CSS pour les badges de statut
function getStatusClass(statut) {
  switch (statut) {
    case "actif":
      return "bg-green-100 text-green-700";
    case "maintenance":
      return "bg-orange-100 text-orange-700";
    case "hors_service":
      return "bg-red-100 text-red-700";
    default:
      return "bg-gray-100 text-gray-700";
  }
}

// Formatage du texte pour l'affichage
function formatStatut(statut) {
  return statut.replace("_", " ").toUpperCase();
}

// Style conditionnel pour les faces libres
function getLibreClass(count) {
  if (count > 0) return "text-green-600 font-bold";
  return "text-red-500 font-medium";
}
</script>
