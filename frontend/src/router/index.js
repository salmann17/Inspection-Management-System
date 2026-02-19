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
  ],
})

export default router
