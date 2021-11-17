<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ApiService
{

    /**
     * @param Request $request
     * @return Request
     */
    public function formatRequest(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) return $request;
        $request->request->replace($data);
        return $request;
    }
}