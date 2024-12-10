<?php
namespace Mirakl\Core\Model\Cli;

class AclAuthorizationManager
{
    /**
     * @var bool
     */
    private $isCliMode = false;

    /**
     * @var bool
     */
    private $isAuthorized = false;

    /**
     * @param bool $isCliMode
     */
    public function setIsCliMode($isCliMode)
    {
        $this->isCliMode = (bool) $isCliMode;
    }

    /**
     * @return bool
     */
    public function getIsCliMode()
    {
        return $this->isCliMode;
    }

    /**
     * @param bool $isCliAuthorized
     */
    public function setIsCliAuthorized($isAuthorized)
    {
        $this->isAuthorized = (bool) $isAuthorized;
    }

    /**
     * @return bool
     */
    public function getIsCliAuthorized()
    {
        return $this->isAuthorized;
    }
}

