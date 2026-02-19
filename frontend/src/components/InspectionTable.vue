<template>
  <div class="table-wrapper">
    <table>
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
        <tr v-for="row in inspections" :key="row.id">
          <td class="request-no">{{ row.request_no }}</td>
          <td>{{ row.service_type_category }}</td>
          <td>{{ row.scope_of_work_code }}</td>
          <td><StatusBadge :status="row.status" /></td>
          <td class="center">{{ row.total_items }}</td>
          <td class="center">{{ row.total_lots }}</td>
          <td>{{ row.created_by }}</td>
          <td>{{ formatDate(row.created_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import StatusBadge from './StatusBadge.vue'

defineProps({
  inspections: { type: Array, required: true },
})

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

td {
  padding: 11px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
}

tbody tr:hover {
  background: #f8fafc;
}

.request-no {
  font-weight: 600;
  color: #1d4ed8;
}

.center {
  text-align: center;
}
</style>
