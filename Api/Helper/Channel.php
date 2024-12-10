<?php

declare(strict_types=1);

namespace Mirakl\Api\Helper;

use Mirakl\MMP\Common\Domain\Collection\Channel\ChannelCollection;
use Mirakl\MMP\Front\Request\Channel\GetChannelsRequest;

class Channel extends ClientHelper\MMP
{
    /**
     * (CH11) Fetches all active Mirakl channels
     *
     * @param string|null $locale
     * @return ChannelCollection
     */
    public function getChannels(?string $locale = null)
    {
        $request = new GetChannelsRequest();
        $request->setLocale($this->validateLocale($locale));

        return $this->send($request);
    }
}
