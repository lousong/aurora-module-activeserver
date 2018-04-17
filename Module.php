<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\ActiveServer;

/**
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractWebclientModule
{
	public function init() 
	{
		$this->extendObject(
			'Aurora\Modules\Core\Classes\User', 
			array(
				'Enabled'	=> array('bool', false, true)
			)
		);
		$this->subscribeEvent('Core::Login::after', array($this, 'onAfterLogin'), 10);
	}	
	
	public function onAfterLogin(&$aArgs, &$mResult)
	{
		$sAgent = $this->oHttp->GetHeader('X-User-Agent');
		if ($sAgent === 'Afterlogic ActiveServer')
		{
			$oUser = \Aurora\System\Api::getAuthenticatedUser();
			$oLicensing = \Aurora\System\Api::GetModule('Licensing');
			if (!($oUser && $oUser->{$this->GetName() . '::Enabled'} && $oLicensing->ValidatePeriod('ActiveServer')))
			{
				$mResult = false;
			}
		}
	}	
	
	public function GetPerUserSettings($UserId)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$oUser = null;
		$oCoreDecorator = \Aurora\Modules\Core\Module::Decorator();
		if ($oCoreDecorator)
		{
			$oUser = $oCoreDecorator->GetUser($UserId);
		}
		if ($oUser)
		{
			return array(
				'EnableModule' => $oUser->{$this->GetName() . '::Enabled'}
			);
		}
		
		return null;
	}
	
	public function UpdatePerUserSettings($UserId, $EnableModule)
	{
		$bResult = false;
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);

		$oCoreModuleDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
		$oUser = $oCoreModuleDecorator->GetUser($UserId);
		if ($oUser)
		{
			$oUser->{$this->GetName() . '::Enabled'} = $EnableModule;
			$bResult = $oCoreModuleDecorator->UpdateUserObject($oUser);
		}
		
		return $bResult;
	}
	
	protected function GetUsersCount()
	{
		$this->oEavManager = new \Aurora\System\Managers\Eav();
		return count($this->oEavManager->getEntities('Aurora\Modules\Core\Classes\User',
			array('PublicId'),
			0,
			0,
			[$this->GetName() . '::Enabled' => true]
		));		
	}

	public function GetSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast( \Aurora\System\Enums\UserRole::SuperAdmin);
		
		$oLicensing = \Aurora\System\Api::GetModule('Licensing');
		
		return array(
			'EnableModule' => !$this->getConfig('Disabled', false),
			'UsersCount' => $this->GetUsersCount(),
			'LicensedUsersCount' => $oLicensing->GetUsersCount('ActiveServer')
		);
	}
	
	public function UpdateSettings($EnableModule)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::TenantAdmin);
		
		try
		{
			$this->setConfig('Disabled', !$EnableModule);
			$this->saveModuleConfig();
		}
		catch (\Exception $ex)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::CanNotSaveSettings);
		}
		
		return true;
	}	
	
	public function GetLicenseInfo()
	{
		$mResult = false;
				
		$oLicensing = \Aurora\System\Api::GetModule('Licensing');
		if ($oLicensing)
		{
			$mResult = $oLicensing->GetLicenseInfo('ActiveServer');
		}
		
		return $mResult;
	}	
}
