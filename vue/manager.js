import settings from '../../ActiveServer/vue/settings'

export default {
  moduleName: 'ActiveServer',

  requiredModules: ['ActiveServer'],

  init (appData) {
    settings.init(appData)
  },

  getAdminSystemTabs () {
    return [
      {
        tabName: 'activeserver-system',
        title: 'ACTIVESERVER.LABEL_SETTINGS_TAB',
        component () {
          return import('./components/ActiveSyncAdminSettings')
        },
      },
    ]
  },
  getAdminUserTabs () {
    return [
      {
        tabName: 'activeserver-user',
        paths: [
          'id/:id/activeserver-user',
          'search/:search/id/:id/activeserver-user',
          'page/:page/id/:id/activeserver-user',
          'search/:search/page/:page/id/:id/activeserver-user',
        ],
        title: 'ACTIVESERVER.LABEL_SETTINGS_TAB',
        component () {
          return import('./components/ActiveSyncAdminSettingsPerUser')
        }
      }
    ]
  },
}
