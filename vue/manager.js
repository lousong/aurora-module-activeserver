import settings from "../../ActiveServer/vue/settings";
export default {
    name: 'ActiveServerWebclient',
    init (appData) {
        settings.init(appData)
    },
    getAdminSystemTabs () {
        return [
            {
                name: 'activeserver-system',
                title: 'ACTIVESERVER.LABEL_SETTINGS_TAB',
                component () {
                    return import('src/../../../ActiveServer/vue/components/ActiveSyncAdminSettings')
                },
            },
        ]
    },
}
