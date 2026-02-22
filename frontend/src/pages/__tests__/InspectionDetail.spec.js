import { describe, it, expect, vi } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import InspectionDetail from '@/pages/InspectionDetail.vue'

const baseInspection = (overrides = {}) => ({
  id: '1',
  inspection_no: 'INS-20250101-0001',
  service_type: 'NEW_ARRIVAL',
  scope_of_work: { code: 'SOW-NA-001', name: 'New Arrival Inspection', included_items: [] },
  location: 'Warehouse A',
  estimated_completion_date: '2025-06-01',
  related_to: null,
  charge_to_customer: false,
  customer_name: null,
  status: 'NEW',
  workflow_status_group: 'OPEN',
  items: [],
  charges: [],
  ...overrides,
})

vi.mock('@/services/inspection', () => ({
  fetchInspectionDetail: vi.fn(),
  updateInspectionStatus: vi.fn(),
  addInspectionCharge: vi.fn(),
}))

import { fetchInspectionDetail } from '@/services/inspection'

async function mountDetail(inspection) {
  fetchInspectionDetail.mockResolvedValue(inspection)

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/inspections/:id', component: InspectionDetail },
    ],
  })

  router.push('/inspections/1')
  await router.isReady()

  const wrapper = mount(InspectionDetail, {
    global: {
      plugins: [router],
      stubs: {
        StatusBadge: { props: ['status'], template: '<span class="badge">{{ status }}</span>' },
      },
    },
  })

  await flushPromises()
  return wrapper
}

describe('InspectionDetail', () => {
  it('does NOT render charges card when charge_to_customer is false', async () => {
    const wrapper = await mountDetail(baseInspection({ charge_to_customer: false }))

    expect(wrapper.text()).not.toContain('Charges to Customer')
  })

  it('renders charges card when charge_to_customer is true', async () => {
    const wrapper = await mountDetail(baseInspection({
      charge_to_customer: true,
      customer_name: 'Acme Corp',
      charges: [
        { id: 'c1', order_no: 'ORD-001', service_description: 'Inspection Fee', qty: 2, unit_price: 100 },
      ],
    }))

    expect(wrapper.text()).toContain('Charges to Customer')
    expect(wrapper.text()).toContain('ORD-001')
    expect(wrapper.text()).toContain('Inspection Fee')
  })

  it('renders items in the order items card', async () => {
    const wrapper = await mountDetail(baseInspection({
      items: [
        { id: 'i1', description: 'Bearing Assembly', qty_required: 5, lots: [] },
        { id: 'i2', description: 'Shaft Coupling', qty_required: 3, lots: [] },
      ],
    }))

    expect(wrapper.text()).toContain('Bearing Assembly')
    expect(wrapper.text()).toContain('Shaft Coupling')
  })

  it('shows transition button for OPEN status', async () => {
    const wrapper = await mountDetail(baseInspection({ workflow_status_group: 'OPEN' }))

    expect(wrapper.text()).toContain('Submit for Review')
  })

  it('shows no transition button for COMPLETED status', async () => {
    const wrapper = await mountDetail(baseInspection({
      status: 'COMPLETED',
      workflow_status_group: 'COMPLETED',
    }))

    expect(wrapper.text()).not.toContain('Submit for Review')
    expect(wrapper.text()).not.toContain('Mark as Completed')
  })
})
