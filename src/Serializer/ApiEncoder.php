<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ApiEncoder implements EncoderInterface, DecoderInterface
{
    public function encode($data, $format, array $context = [])
    {
        $result = [];
        $result['success'] = true;
        if ($context['success']) {
            $result['data'] = $data;
            $result['meta'] = [];
            if (!empty($context['currentPage'])) {
                $result['meta']['currentPage'] = $context['currentPage'];
            }
            if (!empty($context['perPage'])) {
                $result['meta']['perPage'] = $context['perPage'];
            }
            if (isset($context['pagesTotal']) && !empty($context['perPage'])) {
                $result['meta']['pagesTotal'] = ceil($context['pagesTotal'] / $context['perPage']);
            }
        } else {
            $result['success'] = false;
            if (!empty($context['error_message'])) {
                $result['message'] = $context['error_message'];
            }
            if (!empty($context['error_code'])) {
                $result['code'] = $context['error_code'];
            }
            if (!empty($context['error_traceback'])) {
                $result['traceback'] = $context['error_traceback'];
            }
            if (!empty($context['error_file'])) {
                $result['file'] = $context['error_file'];
            }
            if (!empty($context['error_line'])) {
                $result['line'] = $context['error_line'];
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function supportsEncoding($format)
    {
        return 'api' === $format;
    }

    public function decode($data, $format, array $context = [])
    {
        return json_decode($data);
    }

    public function supportsDecoding($format)
    {
        return 'api' === $format;
    }

    private function flatten($data, $prefix = null)
    {
        $flatt = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($this->flatten($value, $key) as $subkey => $subval) {
                    $flatt[$subkey] = $subval;
                }
            } else {
                $flatt[$prefix ? $prefix . '_' . $key : $key] = $value;
            }
        }
        return $flatt;
    }
}
