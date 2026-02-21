<template>
  <div class="page">

    <!-- Loading -->
    <div v-if="loading" class="state-box">
      <span class="spinner" /> Loading...
    </div>

    <!-- Not found / error -->
    <div v-else-if="error" class="state-box error">
      {{ error }}
    </div>

    <!-- Content -->
    <template v-else-if="inspection">

      <!-- Header -->
      <div class="page-header">
        <div>
          <p class="page-subtitle">Inspection Record</p>
          <h1 class="page-title">{{ inspection.inspection_no }}</h1>
        </div>
        <div class="header-right">
          <StatusBadge :status="inspection.status" />
          <button class="btn-secondary" @click="$router.push('/inspections')">← Back</button>
        </div>
      </div>

      <!-- Detail Card -->
      <div class="card">
        <h2 class="section-title">Inspection Details</h2>

        <div class="grid">
          <div class="field">
            <label>Service Type</label>
            <span>{{ inspection.service_type || '—' }}</span>
          </div>
          <div class="field">
            <label>Scope of Work</label>
            <span>{{ inspection.scope_of_work || '—' }}</span>
          </div>
          <div class="field">
            <label>Location</label>
            <span>{{ inspection.location || '—' }}</span>
          </div>
          <div class="field">
            <label>Est. Completion Date</label>
            <span>{{ inspection.estimated_completion_date || '—' }}</span>
          </div>
          <div class="field">
            <label>Related To</label>
            <span>{{ inspection.related_to || '—' }}</span>
          </div>
          <div class="field">
            <label>Charge to Customer</label>
            <span>{{ inspection.charge_to_customer ? 'Yes' : 'No' }}</span>
          </div>
          <div v-if="inspection.charge_to_customer" class="field">
            <label>Customer Name</label>
            <span>{{ inspection.customer_name || '—' }}</span>
          </div>
        </div>
      </div>

      <!-- Items Card -->
      <div class="card">
        <h2 class="section-title">Order Items</h2>

        <div v-if="inspection.items.length === 0" class="state-box">
          No items found.
        </div>

        <div v-for="(item, idx) in inspection.items" :key="item.id" class="item-block">
          <div class="item-header">
            <span class="item-index">#{{ idx + 1 }}</span>
            <span class="item-name">{{ item.description }}</span>
            <span class="item-qty">Qty Required: <strong>{{ item.qty_required }}</strong></span>
          </div>

          <div v-if="item.lots && item.lots.length > 0" class="lot-table-wrap">
            <table class="lot-table">
              <thead>
                <tr>
                  <th>Lot</th>
                  <th>Allocation</th>
                  <th>Owner</th>
                  <th>Condition</th>
                  <th>Available Qty</th>
                  <th>Sample Qty</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="lot in item.lots" :key="lot.id">
                  <td>{{ lot.lot || '—' }}</td>
                  <td>{{ lot.allocation || '—' }}</td>
                  <td>{{ lot.owner || '—' }}</td>
                  <td>{{ lot.condition || '—' }}</td>
                  <td>{{ lot.available_qty ?? '—' }}</td>
                  <td>{{ lot.sample_qty ?? '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p v-else class="no-lots">No lots assigned.</p>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import StatusBadge from '../components/StatusBadge.vue'
import { fetchInspectionDetail } from '../services/inspection'

const route      = useRoute()
const loading    = ref(true)
const error      = ref(null)
const inspection = ref(null)

onMounted(async () => {
  try {
    inspection.value = await fetchInspectionDetail(route.params.id)
  } catch (err) {
    error.value = err.response?.status === 404
      ? 'Inspection not found.'
      : 'Failed to load inspection. Please try again.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page {
  padding: 24px 32px;
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
}

.page-subtitle {
  font-size: 13px;
  color: #6b7280;
  margin: 0 0 4px;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 24px;
  margin-bottom: 20px;
}

.section-title {
  font-size: 15px;
  font-weight: 600;
  color: #1d4ed8;
  margin: 0 0 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e5e7eb;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.field label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #9ca3af;
  margin-bottom: 4px;
}

.field span {
  font-size: 14px;
  color: #111827;
}

.item-block {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 16px;
  overflow: hidden;
}

.item-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.item-index {
  font-size: 12px;
  font-weight: 700;
  color: #6b7280;
  min-width: 24px;
}

.item-name {
  flex: 1;
  font-size: 14px;
  font-weight: 600;
  color: #111827;
}

.item-qty {
  font-size: 13px;
  color: #6b7280;
}

.lot-table-wrap {
  overflow-x: auto;
}

.lot-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.lot-table th {
  background: #f3f4f6;
  padding: 8px 12px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.lot-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
}

.lot-table tbody tr:last-child td {
  border-bottom: none;
}

.no-lots {
  font-size: 13px;
  color: #9ca3af;
  padding: 12px 16px;
  margin: 0;
}

.state-box {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 40px;
  justify-content: center;
  color: #6b7280;
  font-size: 14px;
}

.state-box.error {
  color: #dc2626;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #d1d5db;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-secondary {
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 500;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #fff;
  color: #374151;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #f3f4f6;
}
</style>
