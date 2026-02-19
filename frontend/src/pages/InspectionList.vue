<template>
  <div class="page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">Inspection Record</h1>
      <button class="btn-primary">+ Create Inspection</button>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        class="tab"
        :class="{ active: activeTab === tab.value }"
        @click="switchTab(tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Card -->
    <div class="card">
      <!-- Loading -->
      <div v-if="store.loading" class="state-box">
        <span class="spinner" /> Loading...
      </div>

      <!-- Error -->
      <div v-else-if="store.error" class="state-box error">
        {{ store.error }}
      </div>

      <!-- Empty -->
      <div v-else-if="store.inspections.length === 0" class="state-box">
        No inspections found for this tab.
      </div>

      <!-- Table -->
      <InspectionTable v-else :inspections="store.inspections" />

      <!-- Pagination -->
      <div v-if="!store.loading && store.pagination.last_page > 1" class="pagination">
        <button
          class="page-btn"
          :disabled="store.pagination.current_page === 1"
          @click="changePage(store.pagination.current_page - 1)"
        >
          ← Prev
        </button>
        <span class="page-info">
          Page {{ store.pagination.current_page }} of {{ store.pagination.last_page }}
          &nbsp;·&nbsp; {{ store.pagination.total }} records
        </span>
        <button
          class="page-btn"
          :disabled="store.pagination.current_page === store.pagination.last_page"
          @click="changePage(store.pagination.current_page + 1)"
        >
          Next →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useInspectionStore } from '../stores/inspection'
import InspectionTable from '../components/InspectionTable.vue'

const store = useInspectionStore()

const tabs = [
  { label: 'Open',       value: 'open'       },
  { label: 'For Review', value: 'for_review' },
  { label: 'Completed',  value: 'completed'  },
]

const activeTab = ref('open')

onMounted(() => store.loadInspections(activeTab.value))

function switchTab(tab) {
  activeTab.value = tab
  store.loadInspections(tab, 1)
}

function changePage(page) {
  store.loadInspections(activeTab.value, page)
}
</script>

<style scoped>
.page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 28px 24px;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
}

.btn-primary {
  background: #1d4ed8;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 18px;
  font-weight: 600;
  transition: background 0.15s;
}

.btn-primary:hover {
  background: #1e40af;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 4px;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 20px;
}

.tab {
  padding: 8px 20px;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  color: #64748b;
  font-weight: 500;
  transition: color 0.15s, border-color 0.15s;
}

.tab:hover {
  color: #1d4ed8;
}

.tab.active {
  color: #1d4ed8;
  border-bottom-color: #1d4ed8;
  font-weight: 600;
}

/* Card */
.card {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

/* State boxes */
.state-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 48px 24px;
  color: #94a3b8;
  font-size: 14px;
}

.state-box.error {
  color: #dc2626;
}

/* Spinner */
.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #e2e8f0;
  border-top-color: #1d4ed8;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 12px 16px;
  border-top: 1px solid #f1f5f9;
}

.page-btn {
  padding: 5px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  color: #475569;
  font-weight: 500;
  transition: background 0.15s, border-color 0.15s;
}

.page-btn:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #1d4ed8;
  color: #1d4ed8;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: default;
}

.page-info {
  font-size: 13px;
  color: #64748b;
}
</style>
