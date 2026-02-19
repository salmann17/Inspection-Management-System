<template>
  <div>
    <h1>Inspections</h1>

    <!-- Tab Filter -->
    <div style="margin-bottom: 12px">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        @click="switchTab(tab.value)"
        :style="activeTab === tab.value ? 'font-weight: bold; text-decoration: underline;' : ''"
        style="margin-right: 12px; cursor: pointer; background: none; border: none; font-size: 14px"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <p v-if="store.loading">Loading...</p>

    <!-- Error -->
    <p v-else-if="store.error" style="color: red">{{ store.error }}</p>

    <!-- Empty -->
    <p v-else-if="store.inspections.length === 0">No inspections found.</p>

    <!-- Table -->
    <table v-else border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse">
      <thead>
        <tr>
          <th>Request No</th>
          <th>Service Type</th>
          <th>Scope of Work</th>
          <th>Status</th>
          <th>Items</th>
          <th>Lots</th>
          <th>Created By</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="inspection in store.inspections" :key="inspection.id">
          <td>{{ inspection.request_no }}</td>
          <td>{{ inspection.service_type_category }}</td>
          <td>{{ inspection.scope_of_work_code }}</td>
          <td>{{ inspection.status }}</td>
          <td>{{ inspection.total_items }}</td>
          <td>{{ inspection.total_lots }}</td>
          <td>{{ inspection.created_by }}</td>
          <td>{{ formatDate(inspection.created_at) }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div v-if="store.pagination.last_page > 1" style="margin-top: 12px">
      <button :disabled="store.pagination.current_page === 1" @click="changePage(store.pagination.current_page - 1)">
        Prev
      </button>
      <span style="margin: 0 8px">
        Page {{ store.pagination.current_page }} of {{ store.pagination.last_page }}
        ({{ store.pagination.total }} total)
      </span>
      <button :disabled="store.pagination.current_page === store.pagination.last_page" @click="changePage(store.pagination.current_page + 1)">
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useInspectionStore } from '../stores/inspection'

const store = useInspectionStore()

const tabs = [
  { label: 'Open',       value: 'open'       },
  { label: 'For Review', value: 'for_review' },
  { label: 'Completed',  value: 'completed'  },
]

const activeTab = ref('open')

onMounted(() => {
  store.loadInspections(activeTab.value)
})

function switchTab(tab) {
  activeTab.value = tab
  store.loadInspections(tab, 1)
}

function changePage(page) {
  store.loadInspections(activeTab.value, page)
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-GB')
}
</script>
