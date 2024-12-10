<?php
declare(strict_types=1);

namespace Mirakl\Process\Ui\Component;

trait GetParentIdInRequestTrait
{
    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        $parentId = $this->request->getParam('id');
        if (!$parentId) {
            $parentId = $this->request->getParam('parent_id');
        }

        return $parentId ? (int) $parentId : null;
    }
}
