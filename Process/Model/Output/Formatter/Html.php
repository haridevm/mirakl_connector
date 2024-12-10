<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Output\Formatter;

class Html implements FormatterInterface
{
    /**
     * @var string
     */
    private $dateColor;

    /**
     * @var string
     */
    private $errorColor;

    /**
     * @param string $dateColor
     * @param string $errorColor
     */
    public function __construct(
        string $dateColor = '#aaaaaa',
        string $errorColor = '#ca1919'
    ) {
        $this->dateColor = $dateColor;
        $this->errorColor = $errorColor;
    }

    /**
     * @inheritdoc
     */
    public function format(string $str): string
    {
        return preg_replace(
            [
                '#<info>(.+)</info>#',
                '#<date>(.+)</date>#',
                '#<error>(.+)</error>#',
            ],
            [
                '<strong>$1</strong>',
                sprintf('<span style="color: %s;">$1</span>', $this->dateColor),
                sprintf('<span style="color: %s;">$1</span>', $this->errorColor),
            ],
            $str
        );
    }
}