<template>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th class="chevron-col"></th>
          <th>Request No</th>
          <th>Service Type</th>
          <th>Scope of Work</th>
          <th>Status</th>
          <th class="center">Items</th>
          <th class="center">Lots</th>
          <th>Created By</th>
          <th>Created At</th>
          <th></th>
        </tr>
      </thead>
      <tbody v-for="row in inspections" :key="row.id">
        <!-- Main row -->
        <tr class="main-row" :class="{ expanded: expandedId === row.id }" @click="toggle(row.id)">
          <td class="chevron-col">
            <span class="chevron" :class="{ open: expandedId === row.id }">›</span>
          </td>
          <td class="request-no">{{ row.request_no }}</td>
          <td>{{ row.service_type_category }}</td>
          <td>{{ row.scope_of_work_code }}</td>
          <td><StatusBadge :status="row.status" /></td>
          <td class="center">{{ row.total_items }}</td>
          <td class="center">{{ row.total_lots }}</td>
          <td>{{ row.created_by }}</td>
          <td>{{ formatDate(row.created_at) }}</td>
          <td class="action-col" @click.stop>
            <button
              v-if="row.workflow_status_group === 'OPEN'"
              class="btn-edit"
              @click="router.push('/inspections/' + row.id + '/edit')"
            >Edit</button>
            <button class="btn-detail" @click="router.push('/inspections/' + row.id)">Detail</button>
          </td>
        </tr>

        <!-- Expanded row -->
        <tr v-if="expandedId === row.id" class="expand-row">
          <td colspan="10" class="expand-cell">
            <div class="expand-content">
              <p v-if="!row.items || row.items.length === 0" class="no-items">
                No items in this inspection.
              </p>

              <template v-else>
                <div v-for="item in row.items" :key="item.id" class="item-block">
                  <!-- Item header -->
                  <div class="item-header">
                    <span class="item-name">{{ item.item_name }}</span>
                    <span class="item-meta">{{ item.item_category }} · Qty Required: {{ item.qty_required }}</span>
                  </div>

                  <!-- Lots table -->
                  <table class="lots-table" v-if="item.lots && item.lots.length > 0">
                    <thead>
                      <tr>
                        <th>Lot No</th>
                        <th>Owner</th>
                        <th>Allocation</th>
                        <th>Condition</th>
                        <th class="center">Sample Qty</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="lot in item.lots" :key="lot.id">
                        <td class="lot-no">{{ lot.lot }}</td>
                        <td>{{ lot.owner }}</td>
                        <td>{{ lot.allocation }}</td>
                        <td>{{ lot.condition }}</td>
                        <td class="center">{{ lot.sample_qty }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <p v-else class="no-items">No lots assigned.</p>
                </div>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import StatusBadge from './StatusBadge.vue'

const router = useRouter()

defineProps({
  inspections: { type: Array, required: true },
})

const expandedId = ref(null)

function toggle(id) {
  expandedId.value = expandedId.value === id ? null : id
}

function formatDate(val) {
  if (!val) return '-'
  return new Date(val).toLocaleDateString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric',
  })
}
</script>

<style scoped>
.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  font-size: 13px;
}

thead tr {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}

th {
  padding: 10px 14px;
  text-align: left;
  font-weight: 600;
  color: #64748b;
  white-space: nowrap;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Main rows */
.main-row td {
  padding: 11px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
}

.main-row {
  cursor: pointer;
  transition: background 0.12s;
}

.main-row:hover td,
.main-row.expanded td {
  background: #f0f7ff;
}

.request-no {
  font-weight: 600;
  color: #1d4ed8;
}

.center {
  text-align: center;
}

/* Chevron */
.chevron-col {
  width: 32px;
  padding: 0 8px !important;
}

.chevron {
  display: inline-block;
  font-size: 18px;
  color: #94a3b8;
  line-height: 1;
  transition: transform 0.2s;
  user-select: none;
}

.chevron.open {
  transform: rotate(90deg);
  color: #1d4ed8;
}

/* Expanded row */
.expand-row td {
  padding: 0;
  border-bottom: 2px solid #e2e8f0;
}

.expand-cell {
  padding: 0 !important;
}

.expand-content {
  background: #f8fafc;
  padding: 14px 20px 16px 52px;
  animation: slideDown 0.18s ease;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Item block */
.item-block {
  margin-bottom: 14px;
}

.item-block:last-child {
  margin-bottom: 0;
}

.item-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}

.item-name {
  font-weight: 600;
  color: #0f172a;
  font-size: 13px;
}

.item-meta {
  font-size: 12px;
  color: #64748b;
}

/* Lots sub-table */
.lots-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}

.lots-table thead tr {
  background: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
}

.lots-table th {
  padding: 7px 12px;
  font-size: 11px;
  color: #94a3b8;
}

.lots-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #f1f5f9;
  color: #475569;
}

.lots-table tbody tr:last-child td {
  border-bottom: none;
}

.lot-no {
  font-weight: 600;
  color: #334155;
}

.action-col {
  width: 140px;
  text-align: center;
  white-space: nowrap;
}

.btn-edit {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #f59e0b;
  border-radius: 5px;
  background: #fff;
  color: #f59e0b;
  cursor: pointer;
  white-space: nowrap;
  margin-right: 6px;
}

.btn-edit:hover {
  background: #fffbeb;
}

.btn-detail {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #2563eb;
  border-radius: 5px;
  background: #fff;
  color: #2563eb;
  cursor: pointer;
  white-space: nowrap;
}

.btn-detail:hover {
  background: #eff6ff;
}

.no-items {
  color: #94a3b8;
  font-size: 12px;
  padding: 6px 0;
}
</style>
