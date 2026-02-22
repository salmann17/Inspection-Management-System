import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import StatusBadge from '@/components/StatusBadge.vue'

describe('StatusBadge', () => {
  it('renders correct label for known statuses', () => {
    const map = {
      NEW: 'New',
      IN_PROGRESS: 'In Progress',
      READY_TO_REVIEW: 'Ready to Review',
      APPROVED: 'Approved',
      COMPLETED: 'Completed',
    }

    for (const [status, label] of Object.entries(map)) {
      const wrapper = mount(StatusBadge, { props: { status } })
      expect(wrapper.text()).toBe(label)
    }
  })

  it('falls back to raw status string for unknown status', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'UNKNOWN_STATUS' } })
    expect(wrapper.text()).toBe('UNKNOWN_STATUS')
  })

  it('applies correct color class', () => {
    const blueWrapper = mount(StatusBadge, { props: { status: 'NEW' } })
    expect(blueWrapper.find('.badge').classes()).toContain('blue')

    const orangeWrapper = mount(StatusBadge, { props: { status: 'READY_TO_REVIEW' } })
    expect(orangeWrapper.find('.badge').classes()).toContain('orange')

    const greenWrapper = mount(StatusBadge, { props: { status: 'COMPLETED' } })
    expect(greenWrapper.find('.badge').classes()).toContain('green')
  })

  it('applies gray class for unknown status', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'SOMETHING_ELSE' } })
    expect(wrapper.find('.badge').classes()).toContain('gray')
  })
})
