import api from './api'

export async function fetchMasterData() {
  const response = await api.get('/master-data')
  return response.data
}

export async function createInspection(payload) {
  const response = await api.post('/inspections', payload)
  return response.data
}

export async function fetchInspectionDetail(id) {
  const response = await api.get(`/inspections/${id}`)
  return response.data
}

export async function updateInspectionStatus(id, status) {
  const response = await api.patch(`/inspections/${id}/status`, { status })
  return response.data
}

export async function addInspectionCharge(id, payload) {
  const response = await api.post(`/inspections/${id}/charges`, payload)
  return response.data
}

export async function fetchInspections(status, page = 1) {
  const params = { page }
  if (status) params.status = status

  const response = await api.get('/inspections', { params })
  return response.data
}
