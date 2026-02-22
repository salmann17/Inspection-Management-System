import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/inspections',
    },
    {
      path: '/inspections',
      name: 'inspections',
      component: () => import('../pages/InspectionList.vue'),
    },
    {
      path: '/inspections/create',
      name: 'inspections.create',
      component: () => import('../pages/CreateInspection.vue'),
    },
    {
      path: '/inspections/:id',
      name: 'inspections.detail',
      component: () => import('../pages/InspectionDetail.vue'),
    },
  ],
})

export default router
