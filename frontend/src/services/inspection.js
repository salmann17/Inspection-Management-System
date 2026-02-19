import api from './api'

export async function fetchInspections(status, page = 1) {
  const params = { page }
  if (status) params.status = status

  const response = await api.get('/inspections', { params })
  return response.data
}
