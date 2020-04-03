<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Carbon\Carbon;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param null|mixed $id
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function di($id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('queue_push')) {
    /**
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

if (! function_exists('amqp_produce')) {
    /**
     * Produce a amqp message.
     */
    function amqp_produce(ProducerMessageInterface $message): bool
    {
        return di()->get(Producer::class)->produce($message, true);
    }
}

if (! function_exists('date_load')) {
    /**
     * @param int|string $date
     * @return \Carbon\Carbon
     */
    function date_load($date): ?Carbon
    {
        if (is_int($date)) {
            return Carbon::createFromTimestamp($date);
        }

        if (empty($date)) {
            return null;
        }

        return Carbon::make($date);
    }
}

if (! function_exists('str_mobile')) {
    /**
     * @param mixed $mobile
     * @param mixed $replacement
     * @param mixed $start
     * @param mixed $length
     */
    function str_mobile($mobile, $replacement, $start, $length): string
    {
        return substr_replace($mobile, $replacement, $start, $length);
    }
}

if (! function_exists('input_has')) {
    /**
     * 入参中是否存在有效的数据 空字符为无效.
     * @return string
     */
    function input_has(array $array, string $key): bool
    {
        if (! isset($array[$key])) {
            return false;
        }

        if ($array[$key] === '') {
            return false;
        }

        return true;
    }
}
