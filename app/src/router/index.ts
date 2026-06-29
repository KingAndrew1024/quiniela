import AdminForecastsView from '@/views/admin/AdminForecastsView.vue'
import AdminHomeView from '@/views/admin/AdminHomeView.vue'
import AdminMatchesView from '@/views/admin/AdminMatchesView.vue'
import AdminResultsView from '@/views/admin/AdminResultsView.vue'
import AdminSheetView from '@/views/admin/AdminSheetView.vue'
import AdminTeamsView from '@/views/admin/AdminTeamsView.vue'
import AdminUsersView from '@/views/admin/AdminUsersView.vue'
import HomeView from '@/views/HomeView.vue'
import HomeView2 from '@/views/HomeView2.vue'
import UserSheetView from '@/views/UserSheetView.vue'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '',
      component: HomeView2,
    },
    {
      path: '/:id',
      component: HomeView2,
    },
    {
      path: '/quiniela/:id',
      name: '/quiniela',
      component: UserSheetView,
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      redirect: ''
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminHomeView,
      children: [
        {
          path: '/admin/matches',
          name: 'matches',
          component: AdminMatchesView,
        },
        {
          path: '/admin/teams',
          name: 'teams',
          component: AdminTeamsView,
        },
        {
          path: '/admin/users',
          name: 'users',
          component: AdminUsersView,
        },
        {
          path: '/admin/forecasts',
          name: 'forecasts',
          component: AdminForecastsView,
        },
        {
          path: '/admin/results',
          name: 'results',
          component: AdminResultsView,
        },
        {
          path: '/admin/sheet',
          name: 'sheet',
          component: AdminSheetView,
        },
      ],
    },
  ],
})

export default router
