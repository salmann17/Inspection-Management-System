<template>
  <span class="badge" :class="colorClass">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const colorMap = {
  NEW:             'blue',
  IN_PROGRESS:     'blue',
  READY_TO_REVIEW: 'orange',
  APPROVED:        'green',
  COMPLETED:       'green',
}

const labelMap = {
  NEW:             'New',
  IN_PROGRESS:     'In Progress',
  READY_TO_REVIEW: 'Ready to Review',
  APPROVED:        'Approved',
  COMPLETED:       'Completed',
}

const colorClass = computed(() => colorMap[props.status] ?? 'gray')
const label      = computed(() => labelMap[props.status] ?? props.status)
</script>

<style scoped>
.badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.3px;
}
.blue   { background: #dbeafe; color: #1d4ed8; }
.orange { background: #ffedd5; color: #c2410c; }
.green  { background: #dcfce7; color: #15803d; }
.gray   { background: #f3f4f6; color: #6b7280; }
</style>
