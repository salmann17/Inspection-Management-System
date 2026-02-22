import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import InspectionTable from '@/components/InspectionTable.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/inspections/:id', component: { template: '<div />' } }],
})

function mountTable(inspections = []) {
  return mount(InspectionTable, {
    props: { inspections },
    global: {
      plugins: [router],
      stubs: {
        StatusBadge: {
          props: ['status'],
          template: '<span class="badge">{{ status }}</span>',
        },
      },
    },
  })
}

const sampleRow = (overrides = {}) => ({
  id: '1',
  request_no: 'INS-20250101-0001',
  service_type_category: 'NEW_ARRIVAL',
  scope_of_work_code: 'SOW-NA-001',
  status: 'NEW',
  workflow_status_group: 'OPEN',
  total_items: 3,
  total_lots: 5,
  created_by: 'System',
  created_at: '2025-01-01T00:00:00.000Z',
  items: [],
  ...overrides,
})

describe('InspectionTable', () => {
  it('renders rows for each inspection', () => {
    const wrapper = mountTable([sampleRow(), sampleRow({ id: '2', request_no: 'INS-20250101-0002' })])
    const mainRows = wrapper.findAll('.main-row')
    expect(mainRows).toHaveLength(2)
  })

  it('displays request number and service type', () => {
    const wrapper = mountTable([sampleRow()])
    expect(wrapper.text()).toContain('INS-20250101-0001')
    expect(wrapper.text()).toContain('NEW_ARRIVAL')
  })

  it('shows detail button for every row', () => {
    const wrapper = mountTable([sampleRow()])
    const btn = wrapper.find('.btn-detail')
    expect(btn.exists()).toBe(true)
    expect(btn.text()).toBe('Detail')
  })

  it('expands row on click to show items', async () => {
    const wrapper = mountTable([sampleRow({
      items: [{ id: 'i1', item_name: 'Bearing', item_category: 'Parts', qty_required: 10, lots: [] }],
    })])

    expect(wrapper.find('.expand-row').exists()).toBe(false)
    await wrapper.find('.main-row').trigger('click')
    expect(wrapper.find('.expand-row').exists()).toBe(true)
    expect(wrapper.text()).toContain('Bearing')
  })

  it('collapses expanded row on second click', async () => {
    const wrapper = mountTable([sampleRow()])
    await wrapper.find('.main-row').trigger('click')
    expect(wrapper.find('.expand-row').exists()).toBe(true)

    await wrapper.find('.main-row').trigger('click')
    expect(wrapper.find('.expand-row').exists()).toBe(false)
  })
})
