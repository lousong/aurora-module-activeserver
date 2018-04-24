'use strict';

var
	_ = require('underscore'),
	
	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js')
;

module.exports = {
	ServerModuleName: 'ActiveServer',
	HashModuleName: 'activeserver',
	EnableModule: false,
	EnableModuleForUser: false,
	EnableForNewUsers: false,
	UsersCount: 0,
	LicensedUsersCount: 0,
	Server: '',
	LinkToManual: '',
	
	/**
	 * Initializes settings from AppData object sections.
	 * 
	 * @param {Object} oAppData Object contained modules settings.
	 */
	init: function (oAppData)
	{
		var oAppDataSection = oAppData['%ModuleName%'];
		
		if (!_.isEmpty(oAppDataSection))
		{
			this.EnableModule = Types.pBool(oAppDataSection.EnableModule, this.EnableModule);
			this.EnableModuleForUser = Types.pBool(oAppDataSection.EnableModuleForUser, this.EnableModuleForUser);
			this.EnableForNewUsers = Types.pBool(oAppDataSection.EnableForNewUsers, this.EnableForNewUsers);
			this.UsersCount = Types.pInt(oAppDataSection.UsersCount, this.UsersCount);
			this.LicensedUsersCount = Types.pInt(oAppDataSection.LicensedUsersCount, this.LicensedUsersCount);
			this.Server = oAppDataSection.Server;
			this.LinkToManual = oAppDataSection.LinkToManual;
		}		
	},
	
	updateAdmin: function (sEnableModule, sEnableForNewUsers, sServer, sLinkToManual)
	{
		this.EnableModule = sEnableModule;
		this.EnableForNewUsers = sEnableForNewUsers;
		this.Server = sServer;
		this.LinkToManual = sLinkToManual;
	}
};
