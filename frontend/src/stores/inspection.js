import { defineStore } from 'pinia'
import { fetchInspections } from '../services/inspection'

export const useInspectionStore = defineStore('inspection', {
  state: () => ({
    inspections: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
    },
    loading: false,
    error: null,
  }),

  actions: {
    async loadInspections(status, page = 1) {
      this.loading = true
      this.error = null

      try {
        const data = await fetchInspections(status, page)
        this.inspections = data.data
        this.pagination = data.meta
      } catch (err) {
        this.error = 'Failed to load inspections.'
        console.error(err)
      } finally {
        this.loading = false
      }
    },
  },
})
