import _ from 'lodash'

import typesUtils from 'src/utils/types'

class ActiveServerSettings {
  constructor (appData) {
    const activeServerWebclientData = typesUtils.pObject(appData.ActiveServer)
    if (!_.isEmpty(activeServerWebclientData)) {
      this.enableModule = typesUtils.pBool(activeServerWebclientData.EnableModule, this.enableModule)
      this.enableModuleForUser = typesUtils.pBool(activeServerWebclientData.EnableModuleForUser, this.enableModuleForUser)
      this.enableForNewUsers = typesUtils.pBool(activeServerWebclientData.EnableForNewUsers, this.enableForNewUsers)
      this.usersCount = typesUtils.pInt(activeServerWebclientData.UsersCount, this.usersCount)
      this.licensedUsersCount = activeServerWebclientData.LicensedUsersCount
      this.usersFreeSlots = activeServerWebclientData.UsersFreeSlots
      this.server = activeServerWebclientData.Server
      this.linkToManual = activeServerWebclientData.LinkToManual
    }
  }

  saveActiveServerSettings ({ enableModule, enableForNewUsers, server, linkToManual }) {
    this.enableModule = enableModule
    this.enableForNewUsers = enableForNewUsers
    this.server = server
    this.linkToManual = linkToManual
  }
}

let settings = null

export default {
  init (appData) {
    settings = new ActiveServerSettings(appData)
  },
  saveActiveServerSettings (data) {
    settings.saveActiveServerSettings(data)
  },
  getActiveServerSettings () {
    return {
      enableModule: settings.enableModule,
      enableModuleForUser: settings.enableModuleForUser,
      enableForNewUsers: settings.enableForNewUsers,
      usersCount: settings.usersCount,
      licensedUsersCount: settings.licensedUsersCount,
      usersFreeSlots: settings.usersFreeSlots,
      server: settings.server,
      linkToManual: settings.linkToManual,
    }
  },

}
