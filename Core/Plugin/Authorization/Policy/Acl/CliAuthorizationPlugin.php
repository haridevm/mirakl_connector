<?php
namespace Mirakl\Core\Plugin\Authorization\Policy\Acl;

use Magento\Framework\Authorization\Policy\Acl;
use Mirakl\Core\Model\Cli\AclAuthorizationManager;

class CliAuthorizationPlugin
{
    /**
     * @var AclAuthorizationManager
     */
    private $aclAuthorizationManager;

    /**
     * @param AclAuthorizationManager $aclAuthorizationManager
     */
    public function __construct(AclAuthorizationManager $aclAuthorizationManager)
    {
        $this->aclAuthorizationManager = $aclAuthorizationManager;
    }

    /**
     * @param   Acl         $subject
     * @param   \Closure    $proceed
     * @param   string      $roleId
     * @param   string      $resourceId
     * @param   string      $privilege
     * @return  bool
     */
    public function aroundIsAllowed(Acl $subject, \Closure $proceed, $roleId, $resourceId, $privilege = null)
    {
        if ($this->aclAuthorizationManager->getIsCliMode() && $this->aclAuthorizationManager->getIsCliAuthorized()) {
            return true;
        }

        return $proceed($roleId, $resourceId, $privilege);
    }
}
