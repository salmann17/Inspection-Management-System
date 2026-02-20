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

    <!-- ── Inspection Header card ─────────────────────────────── -->
    <div class="card" style="margin-bottom: 20px">
      <div class="section-title">Inspection Header</div>

      <div class="form-layout">
        <div class="form-fields">
          <!-- Service Type + Scope of Work -->
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
              <select v-model="form.scope_of_work" :disabled="!form.service_type">
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

          <!-- Location + Date -->
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

          <!-- Related To + Charge to Customer -->
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

          <!-- Customer Name (conditional) -->
          <div class="form-row" v-if="form.charge_to_customer">
            <div class="field">
              <label>Customer Name <span class="req">*</span></label>
              <input v-model="form.customer_name" type="text" placeholder="Customer / Company Name" />
            </div>
          </div>
        </div>

        <!-- Status panel -->
        <div class="status-panel">
          <div class="status-label">Status</div>
          <div class="status-badge-draft">Draft</div>
          <p class="status-hint">Status is set automatically and cannot be changed manually.</p>
        </div>
      </div>
    </div>

    <!-- ── Order Information card ─────────────────────────────── -->
    <div class="card">
      <div class="section-header">
        <div class="section-title" style="margin-bottom: 0; border: none; padding: 0">Order Information</div>
        <button class="btn-add-item" @click="addItem">+ Add Item</button>
      </div>

      <!-- Empty state -->
      <div v-if="form.items.length === 0" class="items-empty">
        No items added yet. Click "+ Add Item" to begin.
      </div>

      <!-- Item list -->
      <div v-for="(item, itemIdx) in form.items" :key="itemIdx" class="item-block">
        <!-- Item header row -->
        <div class="item-header">
          <span class="item-number">Item {{ itemIdx + 1 }}</span>
          <button class="btn-remove" @click="removeItem(itemIdx)">✕ Remove</button>
        </div>

        <!-- Item fields -->
        <div class="form-row" style="margin-bottom: 14px">
          <div class="field">
            <label>Item Description <span class="req">*</span></label>
            <input v-model="item.description" type="text" placeholder="e.g. Carbon Steel Pipe 6&quot; Sch40" />
          </div>
          <div class="field">
            <label>Qty Required <span class="req">*</span></label>
            <input v-model.number="item.qty_required" type="number" min="0" placeholder="0" />
          </div>
        </div>

        <!-- Lots section -->
        <div class="lots-section">
          <div class="lots-header">
            <span class="lots-label">Lots</span>
            <button class="btn-add-lot" @click="addLot(itemIdx)">+ Add Lot</button>
          </div>

          <div v-if="item.lots.length === 0" class="lots-empty">
            No lots added. Click "+ Add Lot".
          </div>

          <table v-else class="lots-table">
            <thead>
              <tr>
                <th>Lot</th>
                <th>Allocation</th>
                <th>Owner</th>
                <th>Condition</th>
                <th>Avail. Qty</th>
                <th>Sample Qty</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(lot, lotIdx) in item.lots" :key="lotIdx">
                <td>
                  <select v-model="lot.lot" @change="onLotChange(itemIdx, lotIdx)">
                    <option value="">— Select —</option>
                    <option v-for="l in lotOptions()" :key="l" :value="l">{{ l }}</option>
                  </select>
                </td>
                <td>
                  <select v-model="lot.allocation" :disabled="!lot.lot" @change="onAllocationChange(itemIdx, lotIdx)">
                    <option value="">— Select —</option>
                    <option v-for="a in allocationOptions(lot.lot)" :key="a" :value="a">{{ a }}</option>
                  </select>
                </td>
                <td>
                  <select v-model="lot.owner" :disabled="!lot.allocation" @change="onOwnerChange(itemIdx, lotIdx)">
                    <option value="">— Select —</option>
                    <option v-for="o in ownerOptions(lot.lot, lot.allocation)" :key="o" :value="o">{{ o }}</option>
                  </select>
                </td>
                <td>
                  <select v-model="lot.condition" :disabled="!lot.owner" @change="onConditionChange(itemIdx, lotIdx)">
                    <option value="">— Select —</option>
                    <option v-for="c in conditionOptions(lot.lot, lot.allocation, lot.owner)" :key="c" :value="c">{{ c }}</option>
                  </select>
                </td>
                <td><input v-model.number="lot.available_qty" type="number" readonly class="readonly" /></td>
                <td><input v-model.number="lot.sample_qty"    type="number" min="0" placeholder="0" /></td>
                <td>
                  <button class="btn-remove-lot" @click="removeLot(itemIdx, lotIdx)">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const form = ref({
  service_type:              '',
  scope_of_work:             '',
  location:                  '',
  estimated_completion_date: '',
  related_to:                '',
  charge_to_customer:        false,
  customer_name:             '',
  items:                     [],
})

// ── Master data (static for now) ─────────────────────────────────

const allScopes = [
  { code: 'SOW-NA-001', name: 'Inbound Quality Inspection',   parent: 'NEW_ARRIVAL' },
  { code: 'SOW-NA-002', name: 'Documentation Verification',   parent: 'NEW_ARRIVAL' },
  { code: 'SOW-NA-003', name: 'Packaging Integrity Check',    parent: 'NEW_ARRIVAL' },
  { code: 'SOW-NA-004', name: 'Quantity Verification',        parent: 'NEW_ARRIVAL' },
  { code: 'SOW-MT-001', name: 'Preventive Maintenance Check', parent: 'MAINTENANCE'  },
  { code: 'SOW-MT-002', name: 'Corrective Repair Inspection', parent: 'MAINTENANCE'  },
  { code: 'SOW-MT-003', name: 'Calibration Verification',     parent: 'MAINTENANCE'  },
  { code: 'SOW-OS-001', name: 'Visual Spot Check',            parent: 'ON_SPOT'      },
  { code: 'SOW-OS-002', name: 'Functional Spot Test',         parent: 'ON_SPOT'      },
  { code: 'SOW-OS-003', name: 'Safety Compliance Check',      parent: 'ON_SPOT'      },
]

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

// ── Item / Lot helpers ────────────────────────────────────────────

function newLot() {
  return { lot: '', allocation: '', owner: '', condition: '', available_qty: 0, sample_qty: 0 }
}

function addItem() {
  form.value.items.push({ description: '', qty_required: 0, lots: [newLot()] })
}

function removeItem(itemIdx) {
  form.value.items.splice(itemIdx, 1)
}

function addLot(itemIdx) {
  form.value.items[itemIdx].lots.push(newLot())
}

function removeLot(itemIdx, lotIdx) {
  form.value.items[itemIdx].lots.splice(lotIdx, 1)
}

// ── Inventory lots (static — replace with API call when ready) ───

const inventoryLots = [
  { lot_no: 'LOT-10001', allocation: 'PROJECT-ALPHA', owner: 'CHEVRON',  condition: 'NEW',  available_qty: 120 },
  { lot_no: 'LOT-10001', allocation: 'PROJECT-ALPHA', owner: 'CHEVRON',  condition: 'USED', available_qty: 35  },
  { lot_no: 'LOT-10001', allocation: 'PROJECT-BETA',  owner: 'PETRONAS', condition: 'NEW',  available_qty: 60  },
  { lot_no: 'LOT-10002', allocation: 'PROJECT-BETA',  owner: 'PETRONAS', condition: 'NEW',  available_qty: 80  },
  { lot_no: 'LOT-10002', allocation: 'PROJECT-BETA',  owner: 'PETRONAS', condition: 'USED', available_qty: 20  },
  { lot_no: 'LOT-10002', allocation: 'PROJECT-GAMMA', owner: 'SHELL',    condition: 'NEW',  available_qty: 50  },
  { lot_no: 'LOT-10003', allocation: 'PROJECT-GAMMA', owner: 'SHELL',    condition: 'NEW',  available_qty: 90  },
  { lot_no: 'LOT-10003', allocation: 'PROJECT-DELTA', owner: 'TOTAL',    condition: 'NEW',  available_qty: 110 },
  { lot_no: 'LOT-10003', allocation: 'PROJECT-DELTA', owner: 'TOTAL',    condition: 'USED', available_qty: 15  },
  { lot_no: 'LOT-10004', allocation: 'PROJECT-ALPHA', owner: 'CHEVRON',  condition: 'NEW',  available_qty: 200 },
  { lot_no: 'LOT-10005', allocation: 'PROJECT-DELTA', owner: 'TOTAL',    condition: 'NEW',  available_qty: 75  },
]

// ── Cascading dropdown options ────────────────────────────────────

function lotOptions() {
  return [...new Set(inventoryLots.map((r) => r.lot_no))]
}

function allocationOptions(lotNo) {
  return [...new Set(
    inventoryLots.filter((r) => r.lot_no === lotNo).map((r) => r.allocation),
  )]
}

function ownerOptions(lotNo, allocation) {
  return [...new Set(
    inventoryLots
      .filter((r) => r.lot_no === lotNo && r.allocation === allocation)
      .map((r) => r.owner),
  )]
}

function conditionOptions(lotNo, allocation, owner) {
  return [...new Set(
    inventoryLots
      .filter((r) => r.lot_no === lotNo && r.allocation === allocation && r.owner === owner)
      .map((r) => r.condition),
  )]
}

function resolveAvailableQty(lotNo, allocation, owner, condition) {
  const match = inventoryLots.find(
    (r) => r.lot_no === lotNo && r.allocation === allocation && r.owner === owner && r.condition === condition,
  )
  return match ? match.available_qty : 0
}

// ── Lot cascade handlers ──────────────────────────────────────────

function onLotChange(itemIdx, lotIdx) {
  const lot = form.value.items[itemIdx].lots[lotIdx]
  const first = inventoryLots.find((r) => r.lot_no === lot.lot)
  lot.allocation   = first ? first.allocation  : ''
  lot.owner        = first ? first.owner        : ''
  lot.condition    = first ? first.condition    : ''
  lot.available_qty = first ? first.available_qty : 0
}

function onAllocationChange(itemIdx, lotIdx) {
  const lot = form.value.items[itemIdx].lots[lotIdx]
  const first = inventoryLots.find((r) => r.lot_no === lot.lot && r.allocation === lot.allocation)
  lot.owner         = first ? first.owner     : ''
  lot.condition     = first ? first.condition : ''
  lot.available_qty = first ? first.available_qty : 0
}

function onOwnerChange(itemIdx, lotIdx) {
  const lot = form.value.items[itemIdx].lots[lotIdx]
  const first = inventoryLots.find(
    (r) => r.lot_no === lot.lot && r.allocation === lot.allocation && r.owner === lot.owner,
  )
  lot.condition     = first ? first.condition : ''
  lot.available_qty = first ? first.available_qty : 0
}

function onConditionChange(itemIdx, lotIdx) {
  const lot = form.value.items[itemIdx].lots[lotIdx]
  lot.available_qty = resolveAvailableQty(lot.lot, lot.allocation, lot.owner, lot.condition)
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
.breadcrumb .sep    { color: #cbd5e1; }
.breadcrumb .active { color: #1d4ed8; font-weight: 500; }

/* Page header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}
.header-left { display: flex; align-items: center; gap: 14px; }
.page-title  { font-size: 22px; font-weight: 700; color: #0f172a; }

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
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
  padding: 24px;
}

.section-title {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #64748b;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 20px;
}

/* Section header row (title + add button side by side) */
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 20px;
}

/* Form layout */
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

/* Fields */
.field { display: flex; flex-direction: column; gap: 6px; }
.field label {
  font-size: 11px;
  font-weight: 700;
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
.field select:focus   { border-color: #1d4ed8; }
.field select:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

/* Tags */
.tag-list { display: flex; flex-wrap: wrap; gap: 6px; }
.tag {
  background: #dbeafe;
  color: #1d4ed8;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

/* Toggle */
.toggle-row { display: flex; align-items: center; gap: 10px; height: 38px; }
.toggle { position: relative; display: inline-block; width: 40px; height: 22px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute; inset: 0;
  background: #e2e8f0; border-radius: 22px;
  cursor: pointer; transition: background 0.2s;
}
.slider::before {
  content: ''; position: absolute;
  width: 16px; height: 16px; left: 3px; bottom: 3px;
  background: #fff; border-radius: 50%; transition: transform 0.2s;
}
.toggle input:checked + .slider               { background: #1d4ed8; }
.toggle input:checked + .slider::before      { transform: translateX(18px); }
.toggle-label { font-size: 14px; color: #475569; }

/* Status panel */
.status-panel {
  width: 180px; flex-shrink: 0;
  background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 8px; padding: 16px;
  display: flex; flex-direction: column; gap: 8px;
}
.status-label {
  font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8;
}
.status-badge-draft {
  background: #f1f5f9; color: #475569;
  border-radius: 12px; padding: 4px 14px;
  font-size: 13px; font-weight: 600; text-align: center;
}
.status-hint { font-size: 11px; color: #94a3b8; line-height: 1.5; }

/* ── Order Information ────────────────────────────────────────── */

.btn-add-item {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  padding: 6px 14px;
  font-weight: 600;
  font-size: 13px;
  transition: background 0.15s;
}
.btn-add-item:hover { background: #dbeafe; }

.items-empty {
  padding: 32px;
  text-align: center;
  color: #94a3b8;
  font-size: 13px;
  border: 1px dashed #e2e8f0;
  border-radius: 8px;
}

/* Item block */
.item-block {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
  background: #fafafa;
}
.item-block:last-child { margin-bottom: 0; }

.item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}
.item-number {
  font-size: 13px;
  font-weight: 700;
  color: #1d4ed8;
}
.btn-remove {
  background: none;
  border: 1px solid #fca5a5;
  color: #dc2626;
  border-radius: 6px;
  padding: 3px 10px;
  font-size: 12px;
  font-weight: 500;
  transition: background 0.15s;
}
.btn-remove:hover { background: #fee2e2; }

/* Lots section */
.lots-section { margin-top: 6px; }

.lots-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.lots-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #94a3b8;
}
.btn-add-lot {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  transition: border-color 0.15s, color 0.15s;
}
.btn-add-lot:hover { border-color: #1d4ed8; color: #1d4ed8; }

.lots-empty {
  font-size: 12px;
  color: #94a3b8;
  padding: 10px 0;
}

/* Lots table */
.lots-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.lots-table thead tr {
  background: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
}
.lots-table th {
  padding: 7px 10px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: #94a3b8;
}
.lots-table td {
  padding: 6px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.lots-table tbody tr:last-child td { border-bottom: none; }
.lots-table input,
.lots-table select {
  width: 100%;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 6px 8px;
  font-size: 13px;
  color: #1e293b;
  background: #fff;
  outline: none;
  transition: border-color 0.15s;
}
.lots-table input:focus,
.lots-table select:focus    { border-color: #1d4ed8; }
.lots-table input.readonly  { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
.lots-table select:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

.btn-remove-lot {
  background: none;
  border: none;
  color: #dc2626;
  font-size: 14px;
  padding: 4px 6px;
  border-radius: 4px;
  transition: background 0.15s;
}
.btn-remove-lot:hover { background: #fee2e2; }
</style>
