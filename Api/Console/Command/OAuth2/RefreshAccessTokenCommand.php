<?php

declare(strict_types=1);

namespace Mirakl\Api\Console\Command\OAuth2;

use Magento\Framework\Console\Cli;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshAccessTokenCommand extends Command
{
    /**
     * @var TokenHandlerInterface
     */
    private $tokenHandler;

    /**
     * @param TokenHandlerInterface $tokenHandler
     * @param string|null           $name
     */
    public function __construct(TokenHandlerInterface $tokenHandler, string $name = null)
    {
        parent::__construct($name);
        $this->tokenHandler = $tokenHandler;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('mirakl:oauth2:refresh-access-token')
            ->setDescription('Will refresh access token for Mirakl OAuth 2.0 Client authentication');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->tokenHandler->refresh();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
