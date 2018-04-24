<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\ActiveServer;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractWebclientModule
{
	protected $aRequireModules = array(
		'Licensing'
	);
	
	
	public function init() 
	{
		$this->extendObject(
			'Aurora\Modules\Core\Classes\User', 
			array(
				'Enabled'	=> array('bool', $this->getConfig('EnableForNewUsers'), true)
			)
		);
		$this->subscribeEvent('Core::Login::after', array($this, 'onAfterLogin'), 10);
		$this->subscribeEvent('Core::CreateUser::after', array($this, 'onAfterCreateUser'), 10);
	}	
	
	protected function getFreeUsersSlots()
	{
		$oLicensing = \Aurora\System\Api::GetModule('Licensing');
		$iLicensedUsersCount = (int) $oLicensing->GetUsersCount('ActiveServer');
		$iUsersCount = $this->GetUsersCount();
		return $iLicensedUsersCount - $iUsersCount;
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
	
	public function onAfterCreateUser(&$aArgs, &$mResult)
	{
		$iUserId = isset($mResult) && (int) $mResult > 0 ? $mResult : 0;
		if ($iUserId > 0 && $this->getFreeUsersSlots() < 0)
		{
			$oCoreModuleDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
			$oUser = $oCoreModuleDecorator->GetUser($iUserId);

			if ($oUser)
			{
				$oUser->{$this->GetName() . '::Enabled'} = false;
				$oCoreModuleDecorator->UpdateUserObject($oUser);
			}
		}
	}	

	public function GetEnableModuleForCurrentUser()
	{
		$bResult = false;
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		$iUserId = \Aurora\System\Api::getAuthenticatedUserId();
		if ($iUserId)
		{
			$oCoreDecorator = \Aurora\Modules\Core\Module::Decorator();
			if ($oCoreDecorator)
			{
				$oUser = $oCoreDecorator->GetUser($iUserId);
			}
			if ($oUser)
			{
				$bResult = $oUser->{$this->GetName() . '::Enabled'};
			}
		}
		
		return $bResult;
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
		
		$oLicensing = \Aurora\System\Api::GetModule('Licensing');
		$iLicensedUsersCount = (int) $oLicensing->GetUsersCount('ActiveServer');
		$iUsersCount = $this->GetUsersCount();
		if ($iUsersCount >= $iLicensedUsersCount && $EnableModule && !$oUser->{$this->GetName() . '::Enabled'})
		{
			throw new Exceptions\UserLimitExceeded(1, null, 'ActiveSync user limit exceeded.');
		}
		
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
		\Aurora\System\Api::checkUserRoleIsAtLeast( \Aurora\System\Enums\UserRole::NormalUser);
		
		$oLicensing = \Aurora\System\Api::GetModule('Licensing');
		
		$bEnableModuleForUser = false;
		
		$iUserId = \Aurora\System\Api::getAuthenticatedUserId();
		if ($iUserId)
		{
			$oCoreDecorator = \Aurora\Modules\Core\Module::Decorator();
			if ($oCoreDecorator)
			{
				$oUser = $oCoreDecorator->GetUser($iUserId);
			}
			if ($oUser)
			{
				$bEnableModuleForUser = $oUser->{$this->GetName() . '::Enabled'};
			}
		}
		
		return array(
			'EnableModule' => !$this->getConfig('Disabled', false),
			'EnableModuleForUser' => $bEnableModuleForUser,
			'EnableForNewUsers' => $this->getConfig('EnableForNewUsers', false),
			'UsersCount' => $this->GetUsersCount(),
			'LicensedUsersCount' => $oLicensing->GetUsersCount('ActiveServer'),
			'Server' => $this->getConfig('Server', ''),
			'LinkToManual' => $this->getConfig('LinkToManual', '')
		);
	}
	
	public function UpdateSettings($EnableModule, $EnableForNewUsers, $Server, $LinkToManual)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::TenantAdmin);
		
		try
		{
			$this->setConfig('Disabled', !$EnableModule);
			$this->setConfig('EnableForNewUsers', $EnableForNewUsers);
			$this->setConfig('Server', $Server);
			$this->setConfig('LinkToManual', $LinkToManual);
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
