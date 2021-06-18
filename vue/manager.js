import settings from '../../ActiveServer/vue/settings'

export default {
  moduleName: 'ActiveServer',

  requiredModules: [],

  init (appData) {
    settings.init(appData)
  },

  getAdminSystemTabs () {
    return [
      {
        tabName: 'activeserver-system',
        title: 'ACTIVESERVER.LABEL_SETTINGS_TAB',
        component () {
          return import('src/../../../ActiveServer/vue/components/ActiveSyncAdminSettings')
        },
      },
    ]
  },
}
