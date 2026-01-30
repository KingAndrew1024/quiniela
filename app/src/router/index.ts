import AdminForecastsView from '@/views/admin/AdminForecastsView.vue'
import AdminHomeView from '@/views/admin/AdminHomeView.vue'
import AdminMatchesView from '@/views/admin/AdminMatchesView.vue'
import AdminResultsView from '@/views/admin/AdminResultsView.vue'
import AdminTeamsView from '@/views/admin/AdminTeamsView.vue'
import HomeView from '@/views/HomeView.vue'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '',
      component: HomeView,
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminHomeView,
      children: [
        {
          path: '/matches',
          name: 'matches',
          component: AdminMatchesView,
        },
        {
          path: '/teams',
          name: 'teams',
          component: AdminTeamsView,
        },
        {
          path: '/forecasts',
          name: 'forecasts',
          component: AdminForecastsView,
        },
        {
          path: '/results',
          name: 'results',
          component: AdminResultsView,
        },
      ],
    },
  ],
})

export default router
