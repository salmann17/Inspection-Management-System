<template>
  <div class="page">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <span>Quality &amp; HSE</span>
      <span class="sep">›</span>
      <span>Inspection</span>
      <span class="sep">›</span>
      <span class="active">Create Inspection</span>
    </nav>

    <!-- Page header -->
    <div class="page-header">
      <div class="header-left">
        <button class="btn-back" @click="$router.push('/inspections')">← Back</button>
        <h1 class="page-title">Create Inspection</h1>
      </div>
      <button class="btn-primary">Save</button>
    </div>

    <!-- Main card -->
    <div class="card">
      <!-- Card section title -->
      <div class="section-title">Inspection Header</div>

      <div class="form-layout">
        <!-- LEFT: main fields -->
        <div class="form-fields">
          <!-- Row 1 -->
          <div class="form-row">
            <div class="field">
              <label>Service Type <span class="req">*</span></label>
              <select v-model="form.service_type" @change="onServiceTypeChange">
                <option value="">— Select —</option>
                <option value="NEW_ARRIVAL">New Arrival</option>
                <option value="MAINTENANCE">Maintenance</option>
                <option value="ON_SPOT">On Spot</option>
              </select>
            </div>

            <div class="field">
              <label>Scope of Work <span class="req">*</span></label>
              <select v-model="form.scope_of_work" :disabled="!form.service_type" @change="onScopeChange">
                <option value="">— Select —</option>
                <option v-for="s in filteredScopes" :key="s.code" :value="s.code">
                  {{ s.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Scope Included tags -->
          <div class="field" v-if="scopeItems.length > 0">
            <label>Scope Included</label>
            <div class="tag-list">
              <span v-for="tag in scopeItems" :key="tag" class="tag">{{ tag }}</span>
            </div>
          </div>

          <!-- Row 2 -->
          <div class="form-row">
            <div class="field">
              <label>Location</label>
              <input v-model="form.location" type="text" placeholder="e.g. Warehouse A" />
            </div>

            <div class="field">
              <label>Estimated Completion Date</label>
              <input v-model="form.estimated_completion_date" type="date" />
            </div>
          </div>

          <!-- Row 3 -->
          <div class="form-row">
            <div class="field">
              <label>Related To</label>
              <input v-model="form.related_to" type="text" placeholder="e.g. Work Order / PO No." />
            </div>

            <div class="field">
              <label>Charge to Customer?</label>
              <div class="toggle-row">
                <label class="toggle">
                  <input type="checkbox" v-model="form.charge_to_customer" />
                  <span class="slider" />
                </label>
                <span class="toggle-label">{{ form.charge_to_customer ? 'Yes' : 'No' }}</span>
              </div>
            </div>
          </div>

          <!-- Customer name (conditional) -->
          <div class="form-row" v-if="form.charge_to_customer">
            <div class="field">
              <label>Customer Name <span class="req">*</span></label>
              <input v-model="form.customer_name" type="text" placeholder="Customer / Company Name" />
            </div>
          </div>
        </div>

        <!-- RIGHT: status panel -->
        <div class="status-panel">
          <div class="status-label">Status</div>
          <div class="status-badge draft">Draft</div>
          <p class="status-hint">Status is set automatically and cannot be changed manually.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const form = ref({
  service_type:               '',
  scope_of_work:              '',
  location:                   '',
  estimated_completion_date:  '',
  related_to:                 '',
  charge_to_customer:         false,
  customer_name:              '',
})

// Static scope data (will come from API later)
const allScopes = [
  { code: 'SOW-NA-001', name: 'Inbound Quality Inspection',    parent: 'NEW_ARRIVAL' },
  { code: 'SOW-NA-002', name: 'Documentation Verification',    parent: 'NEW_ARRIVAL' },
  { code: 'SOW-NA-003', name: 'Packaging Integrity Check',     parent: 'NEW_ARRIVAL' },
  { code: 'SOW-NA-004', name: 'Quantity Verification',         parent: 'NEW_ARRIVAL' },
  { code: 'SOW-MT-001', name: 'Preventive Maintenance Check',  parent: 'MAINTENANCE'  },
  { code: 'SOW-MT-002', name: 'Corrective Repair Inspection',  parent: 'MAINTENANCE'  },
  { code: 'SOW-MT-003', name: 'Calibration Verification',      parent: 'MAINTENANCE'  },
  { code: 'SOW-OS-001', name: 'Visual Spot Check',             parent: 'ON_SPOT'      },
  { code: 'SOW-OS-002', name: 'Functional Spot Test',          parent: 'ON_SPOT'      },
  { code: 'SOW-OS-003', name: 'Safety Compliance Check',       parent: 'ON_SPOT'      },
]

// Predefined scope items per scope code
const scopeItemMap = {
  'SOW-NA-001': ['Visual Thread', 'Visual Body', 'Full Length Drift', 'End Protectors Check'],
  'SOW-NA-002': ['Mill Certificate', 'Packing List', 'Purchase Order Match'],
  'SOW-NA-003': ['Outer Packaging', 'Inner Wrapping', 'Seal Integrity'],
  'SOW-NA-004': ['Physical Count', 'Tag Verification', 'Weight Measurement'],
  'SOW-MT-001': ['Lubrication Check', 'Torque Verification', 'Wear Measurement'],
  'SOW-MT-002': ['Crack Detection', 'Dimensional Check', 'Surface Condition'],
  'SOW-MT-003': ['Pressure Gauge', 'Temperature Sensor', 'Flow Meter'],
  'SOW-OS-001': ['Visual Thread', 'Visual Body', 'Coating Condition'],
  'SOW-OS-002': ['Valve Operation', 'Actuator Test', 'Leak Test'],
  'SOW-OS-003': ['Grounding Check', 'Labeling', 'Fire Safety Compliance'],
}

const filteredScopes = computed(() =>
  allScopes.filter((s) => s.parent === form.value.service_type),
)

const scopeItems = computed(() =>
  scopeItemMap[form.value.scope_of_work] ?? [],
)

function onServiceTypeChange() {
  form.value.scope_of_work = ''
}

function onScopeChange() {
  // scope items update reactively via computed
}
</script>

<style scoped>
.page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 28px 24px;
}

/* Breadcrumb */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 16px;
}
.breadcrumb .sep  { color: #cbd5e1; }
.breadcrumb .active { color: #1d4ed8; font-weight: 500; }

/* Page header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}
.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
}
.btn-back {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 6px 14px;
  color: #475569;
  font-weight: 500;
  transition: border-color 0.15s, color 0.15s;
}
.btn-back:hover { border-color: #1d4ed8; color: #1d4ed8; }

.btn-primary {
  background: #1d4ed8;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 22px;
  font-weight: 600;
  transition: background 0.15s;
}
.btn-primary:hover { background: #1e40af; }

/* Card */
.card {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  padding: 24px;
}

.section-title {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #64748b;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 20px;
}

/* Form layout: left fields + right status panel */
.form-layout {
  display: flex;
  gap: 32px;
  align-items: flex-start;
}
.form-fields {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

/* Field */
.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.req { color: #dc2626; }

.field input,
.field select {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 14px;
  color: #1e293b;
  background: #fff;
  outline: none;
  transition: border-color 0.15s;
}
.field input:focus,
.field select:focus { border-color: #1d4ed8; }
.field select:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

/* Scope tags */
.tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.tag {
  background: #dbeafe;
  color: #1d4ed8;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

/* Toggle */
.toggle-row {
  display: flex;
  align-items: center;
  gap: 10px;
  height: 38px;
}
.toggle {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 22px;
}
.toggle input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute;
  inset: 0;
  background: #e2e8f0;
  border-radius: 22px;
  cursor: pointer;
  transition: background 0.2s;
}
.slider::before {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  left: 3px;
  bottom: 3px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
}
.toggle input:checked + .slider { background: #1d4ed8; }
.toggle input:checked + .slider::before { transform: translateX(18px); }
.toggle-label { font-size: 14px; color: #475569; }

/* Status panel */
.status-panel {
  width: 180px;
  flex-shrink: 0;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.status-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #94a3b8;
}
.status-badge.draft {
  display: inline-block;
  background: #f1f5f9;
  color: #475569;
  border-radius: 12px;
  padding: 4px 14px;
  font-size: 13px;
  font-weight: 600;
  text-align: center;
}
.status-hint {
  font-size: 11px;
  color: #94a3b8;
  line-height: 1.5;
}
</style>


<style scoped>
.page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 28px 24px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 16px;
}

.breadcrumb .sep {
  color: #cbd5e1;
}

.breadcrumb .active {
  color: #1d4ed8;
  font-weight: 500;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.btn-back {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 6px 14px;
  color: #475569;
  font-weight: 500;
  transition: border-color 0.15s, color 0.15s;
}

.btn-back:hover {
  border-color: #1d4ed8;
  color: #1d4ed8;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
}

.card {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  padding: 40px 24px;
}

.placeholder {
  text-align: center;
  color: #94a3b8;
  font-size: 14px;
}
</style>
