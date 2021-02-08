<?php
namespace App\Service\LEKLogger;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Monolog\Formatter\FormatterInterface;

class LEKLogFormatter implements FormatterInterface
{
    private $server_name;
    private static $requestId;

    public function __construct(
        RequestStack $requestStack,
        ContainerInterface $container
    ) {
        $this->requestStack = $requestStack->getCurrentRequest();
        $this->server_name = $container->getParameter('lek_server_name');
    }

    private function getRequestId()
    {
        if (empty(self::$requestId)) {
            self::$requestId = uniqid();
        }
        return self::$requestId;
    }

    public function format(array $record)
    {
        $message = $record['level_name'];
        $message .= ' ' . $this->server_name;
        if (!empty($record['context']['command'])) {
            $message .= ' command_id:' . $this->getRequestId();
            $message .= ' command:"' . $record['context']['command'] . '"';
            $message .= ' user:"cli"';
        } else {
            $message .= ' request_id:' . $this->getRequestId();
            if (!empty($_SERVER['REQUEST_METHOD'])) {
                $message .= ' method:"' . $_SERVER['REQUEST_METHOD'] . '"';
            }
            if (!empty($_SERVER['REQUEST_URI'])) {
                $message .= ' path:"' . $_SERVER['REQUEST_URI'] . '"';
            }
            if (!empty($_SERVER['QUERY_STRING'])) {
                $message .= ' query_string:"' . $_SERVER['QUERY_STRING'] . '"';
            }
            if (!empty($record['context']['route'])) {
                $message .= ' route:"' . $record['context']['route'] . '"';
            }
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $message .= ' user_ip:"' . $_SERVER['REMOTE_ADDR'] . '"';
            }
            $message .= ' user:' . '"anonymous"';
        }

        $message .= ' channel:"' . $record['channel'] . '"';

        $message .= ' message:"' . rawurlencode($record['message']) . '"';

        if ($record['level_name'] == 'ERROR' && !empty($record['context']['exception'])) {
            $message .= ' trace:"' . rawurlencode($record['context']['exception']->getTraceAsString()) . '"';
        } else {
            $message .= ' trace:""';
        }
        $message .= ' extra:' . json_encode($record['extra']);
        return $message;
    }

    public function formatBatch(array $records)
    {
        $messages = [];
        foreach ($records as $record) {
            $messages[] = $this->format($record);
        }
        return $messages;
    }
}
