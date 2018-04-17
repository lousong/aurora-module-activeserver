'use strict';

var
	_ = require('underscore'),
	
	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js')
;

module.exports = {
	ServerModuleName: 'ActiveServer',
	HashModuleName: 'activeserver',
	EnableModule: false,
	UsersCount: 0,
	LicensedUsersCount: 0,
	
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
			this.UsersCount = Types.pInt(oAppDataSection.UsersCount, this.UsersCount);
			this.LicensedUsersCount = Types.pInt(oAppDataSection.LicensedUsersCount, this.LicensedUsersCount);
		}		
	},
	
	updateAdmin: function (sEnableModule)
	{
		this.EnableModule = sEnableModule;
	}
};
