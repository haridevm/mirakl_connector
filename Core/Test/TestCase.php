<?php

declare(strict_types=1);

namespace Mirakl\Core\Test;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $fileName
     * @return bool|string
     */
    protected function getFileContents($fileName)
    {
        return file_get_contents($this->getFilePath($fileName));
    }

    /**
     * @return string
     */
    protected function getFilesDir()
    {
        return realpath(dirname((new \ReflectionClass(static::class))->getFileName()) . '/_files');
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getFilePath($file)
    {
        return $this->getFilesDir() . '/' . $file;
    }

    /**
     * @param string $fileName
     * @param bool   $assoc
     * @return array
     */
    protected function getJsonFileContents($fileName, $assoc = true)
    {
        return json_decode($this->getFileContents($fileName), $assoc);
    }
}
