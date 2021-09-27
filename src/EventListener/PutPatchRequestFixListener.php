<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class PutPatchRequestFixListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getMethod() != 'PUT' && $request->getMethod() != 'PATCH') {
            return;
        }

        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];
        $a_blocks = preg_split("/-+$boundary/", $request->getContent());
        array_pop($a_blocks);
        foreach ($a_blocks as $block) {
            if (empty($block)) {
                continue;
            }
            if (strpos($block, 'application/octet-stream') !== false) {
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            } else {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $request->request->set($matches[1], $matches[2]);
        }
    }
}
