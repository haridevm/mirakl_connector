<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;

$objectManager = Bootstrap::getObjectManager();

/**
 * @var ProcessResource $processResource
 */
$processResource = $objectManager->create(ProcessResource::class);

/**
 * @var Process $process
 */
$process = $objectManager->create(Process::class);

$process->setName('Process Completed')
    ->setType('TEST')
    ->setStatus(Process::STATUS_COMPLETED)
    ->setHelper('Mirakl\Process\Helper\Data')
    ->setMethod('synchronize')
    ->setParams(['foo', ['bar']])
    ->setCreatedAt('2023-03-22 10:35:58')
    ->setUpdatedAt('2023-03-22 10:35:58');
$processResource->save($process);

$childProcess = $objectManager->create(Process::class);
$childProcess->setName('Child Process Completed')
    ->setType('TEST')
    ->setStatus(Process::STATUS_COMPLETED)
    ->setParentId($process->getId())
    ->setHelper('Mirakl\Process\Helper\Data')
    ->setMethod('run')
    ->setParams(['foo', ['bar']])
    ->setCreatedAt('2023-03-22 10:35:58')
    ->setUpdatedAt('2023-03-22 10:35:58');
$processResource->save($childProcess);

$process = $objectManager->create(Process::class);
$process->setName('Process Processing')
    ->setType('TEST')
    ->setStatus(Process::STATUS_PROCESSING)
    ->setHelper('Mirakl\Process\Helper\Data')
    ->setMethod('run')
    ->setParams(['foo', ['bar']])
    ->setCreatedAt('2023-03-22 10:35:58')
    ->setUpdatedAt('2023-03-22 10:35:58');
$processResource->save($process);

$process = $objectManager->create(Process::class);
$process->setName('Pending process 1')
    ->setType('TESTS')
    ->setHelper('Mirakl\Process\Helper\Data')
    ->setMethod('run')
    ->setParams(['foo', ['bar']]);
$processResource->save($process);

$process = $objectManager->create(Process::class);
$process->setName('Pending process 1')
    ->setType('TESTS')
    ->setHelper('Mirakl\Process\Helper\Data')
    ->setMethod('run')
    ->setParams(['foo', ['bar']]);
$processResource->save($process);
