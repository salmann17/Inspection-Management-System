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
          <button
            v-if="transitionAction"
            class="btn-primary"
            :disabled="transitioning"
            @click="doTransition"
          >
            <span v-if="transitioning" class="spinner spinner-sm" />
            {{ transitionAction.label }}
          </button>
          <button class="btn-secondary" @click="$router.push('/inspections')">← Back</button>
        </div>
      </div>

      <div v-if="transitionError" class="banner-error">{{ transitionError }}</div>

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
            <span>{{ inspection.scope_of_work?.name || '—' }}</span>
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

      <!-- Scope of Work Card -->
      <div class="card">
        <h2 class="section-title">Scope of Work</h2>
        <template v-if="inspection.scope_of_work">
          <div class="sow-name">{{ inspection.scope_of_work.name }}</div>
          <div class="field" style="margin-top: 16px;">
            <label>Scope Included</label>
            <div v-if="inspection.scope_of_work.included_items?.length" class="scope-items-list">
              <div
                v-for="item in inspection.scope_of_work.included_items"
                :key="item.code"
                class="scope-item-card"
              >
                <div class="scope-item-name">{{ item.name }}</div>
                <div v-if="item.description" class="scope-item-desc">{{ item.description }}</div>
              </div>
            </div>
            <span v-else class="text-muted">—</span>
          </div>
        </template>
        <span v-else class="text-muted">—</span>
      </div>

      <!-- Charges to Customer Card -->
      <div v-if="inspection.charge_to_customer" class="card">
        <h2 class="section-title">Charges to Customer</h2>

        <div v-if="inspection.charges && inspection.charges.length > 0" class="charge-table-wrap">
          <table class="charge-table">
            <thead>
              <tr>
                <th>Order No</th>
                <th>Service Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="charge in inspection.charges" :key="charge.id">
                <td>{{ charge.order_no || '—' }}</td>
                <td>{{ charge.service_description || '—' }}</td>
                <td class="num">{{ charge.qty }}</td>
                <td class="num">{{ formatCurrency(charge.unit_price) }}</td>
                <td class="num">{{ formatCurrency(charge.qty * charge.unit_price) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-muted" style="margin: 0 0 16px;">No charges recorded yet.</p>

        <!-- Add Charge Form -->
        <div v-if="inspection.workflow_status_group !== 'COMPLETED'" class="add-charge-section">
          <h3 class="subsection-title">Add Charge</h3>
          <div v-if="chargeError" class="banner-error" style="margin-bottom: 12px;">{{ chargeError }}</div>
          <div class="charge-form">
            <div class="charge-field">
              <label>Order No</label>
              <input v-model="chargeForm.order_no" type="text" placeholder="e.g. ORD-001" />
            </div>
            <div class="charge-field charge-field-wide">
              <label>Service Description</label>
              <input v-model="chargeForm.service_description" type="text" placeholder="Description" />
            </div>
            <div class="charge-field">
              <label>Qty</label>
              <input v-model.number="chargeForm.qty" type="number" min="1" />
            </div>
            <div class="charge-field">
              <label>Unit Price</label>
              <input v-model.number="chargeForm.unit_price" type="number" min="0" step="0.01" />
            </div>
            <div class="charge-field charge-field-btn">
              <button class="btn-primary" :disabled="addingCharge" @click="submitCharge">
                <span v-if="addingCharge" class="spinner spinner-sm" />
                Add
              </button>
            </div>
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
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import StatusBadge from '../components/StatusBadge.vue'
import { fetchInspectionDetail, updateInspectionStatus, addInspectionCharge } from '../services/inspection'

const route           = useRoute()
const loading         = ref(true)
const error           = ref(null)
const inspection      = ref(null)
const transitioning   = ref(false)
const transitionError = ref(null)
const addingCharge    = ref(false)
const chargeError     = ref(null)

const chargeForm = ref({
  order_no: '',
  service_description: '',
  qty: 1,
  unit_price: 0,
})

const TRANSITIONS = {
  OPEN:       { label: 'Submit for Review', next: 'FOR_REVIEW' },
  FOR_REVIEW: { label: 'Mark as Completed', next: 'COMPLETED' },
}

const transitionAction = computed(() =>
  inspection.value ? (TRANSITIONS[inspection.value.workflow_status_group] ?? null) : null
)

async function loadInspection() {
  try {
    inspection.value = await fetchInspectionDetail(route.params.id)
  } catch (err) {
    error.value = err.response?.status === 404
      ? 'Inspection not found.'
      : 'Failed to load inspection. Please try again.'
  } finally {
    loading.value = false
  }
}

async function doTransition() {
  transitionError.value = null
  transitioning.value = true
  try {
    await updateInspectionStatus(route.params.id, transitionAction.value.next)
    await loadInspection()
  } catch {
    transitionError.value = 'Failed to update status. Please try again.'
  } finally {
    transitioning.value = false
  }
}

onMounted(loadInspection)

async function submitCharge() {
  chargeError.value = null
  addingCharge.value = true
  try {
    const result = await addInspectionCharge(route.params.id, chargeForm.value)
    inspection.value.charges = result.charges
    chargeForm.value = { order_no: '', service_description: '', qty: 1, unit_price: 0 }
  } catch (err) {
    chargeError.value = err.response?.data?.message || 'Failed to add charge. Please try again.'
  } finally {
    addingCharge.value = false
  }
}

function formatCurrency(val) {
  if (val == null) return '—'
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val)
}
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

.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
}

.tag {
  display: inline-block;
  padding: 3px 12px;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.text-muted {
  font-size: 14px;
  color: #9ca3af;
}

.sow-name {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 6px;
}

.scope-items-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 6px;
}

.scope-item-card {
  padding: 10px 14px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.scope-item-name {
  font-size: 13px;
  font-weight: 600;
  color: #1d4ed8;
}

.scope-item-desc {
  font-size: 12px;
  color: #6b7280;
  margin-top: 3px;
  line-height: 1.5;
}

.charge-table-wrap {
  overflow-x: auto;
}

.charge-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.charge-table th {
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

.charge-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
}

.charge-table tbody tr:last-child td {
  border-bottom: none;
}

.charge-table .num {
  text-align: right;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  background: #2563eb;
  color: #fff;
  cursor: pointer;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-sm {
  width: 12px;
  height: 12px;
  border-width: 2px;
  border-color: rgba(255,255,255,0.4);
  border-top-color: #fff;
}

.banner-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  border-radius: 8px;
  padding: 10px 16px;
  font-size: 13px;
  margin-bottom: 16px;
}

.add-charge-section {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.subsection-title {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 12px;
}

.charge-form {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: flex-end;
}

.charge-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 120px;
}

.charge-field label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: #9ca3af;
}

.charge-field input {
  padding: 7px 10px;
  font-size: 13px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
}

.charge-field input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
}

.charge-field-wide {
  flex: 1;
  min-width: 200px;
}

.charge-field-btn {
  min-width: auto;
}
</style>
