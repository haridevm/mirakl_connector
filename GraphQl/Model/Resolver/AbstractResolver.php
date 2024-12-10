<?php

declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver;

use GraphQL\Error\ClientAware;
use GraphQL\Server\RequestError;
use GuzzleHttp\Exception\ClientException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;

abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @param ContextInterface $context
     * @throws GraphQlAuthorizationException
     */
    protected function checkLoggedCustomer($context)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
    }

    /**
     * @param array  $args
     * @param string $path
     * @param bool   $required
     * @return mixed|null
     * @throws GraphQlInputException
     */
    protected function getInput(array $args, $path, $required = false)
    {
        $data = $args;

        foreach (explode('.', $path) as $field) {
            if (!array_key_exists($field, $data)) {
                if ($required) {
                    throw new GraphQlInputException(__('Required parameter "%1" is missing', $path));
                }

                return null;
            }

            $data = $data[$field];
        }

        return $data;
    }

    /**
     * @param \Exception $e
     * @return ClientAware
     */
    protected function mapSdkError(\Exception $e)
    {
        $message = $e->getMessage();

        if ($e instanceof ClientException) {
            $response = \Mirakl\parse_json_response($e->getResponse());

            if (isset($response['message'])) {
                $message = $response['message'];
            }

            if ($e->getCode() == 404) {
                return new GraphQlNoSuchEntityException(__($message), $e, $e->getCode());
            }
        }

        return new RequestError($message, $e->getCode(), $e);
    }
}
