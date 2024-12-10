<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Message;

use Mirakl\MMP\FrontOperator\Domain\Reason;

class FormReply extends FormNew
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_formTitle = 'Answer';

    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_reasonsLabel = 'Update Topic';

    /**
     * @inheritdoc
     */
    public function getFormAction()
    {
        $thread = $this->getThread();

        return $this->getUrl('marketplace/message/postReply', [
            'thread' => $thread->getId()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getFormField($field)
    {
        return 'edit_' . $field;
    }

    /**
     * @inheritdoc
     */
    public function isReasonSelected(Reason $reason)
    {
        $thread = $this->getThread();

        return ($this->getPostMessage($this->getFormField('subject')) == $reason->getLabel())
            || ($thread && $thread->getTopic()->getValue() == $reason->getLabel());
    }
}
