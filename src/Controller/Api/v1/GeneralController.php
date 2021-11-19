<?php

namespace App\Controller\Api\v1;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
class GeneralController extends AbstractController
{
    const
        ERROR_REQUIRED = 1,
        ERROR_TYPE = 2,
        ERROR_LENGTH = 3,
        ERROR_NOT_FOUND = 4,
        ERROR_EMPTY_BODY = 5,
        ERROR_TEMPLATE = 6,
        ERROR_UNAUTHORIZED = 10;


    public $errors = [];

    /**
     * @param $data
     * @param int $status
     * @param array $headers
     * @param array $context
     * @return JsonResponse
     */
    public function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if (is_string($data)) $data = ['message' => $data];
        $response['response'] = $data;
        $response['status'] = $status;

        if (!empty($this->errors)){
            $response = array_merge($response, ['errors' => $this->errors]);
            $response['status'] = Response::HTTP_UNPROCESSABLE_ENTITY;
        }
        return parent::json($response, $status, $headers, $context);
    }

    /**
     * @param string $error_message
     * @param $error_code
     * @param false $throw
     * @throws Exception
     */
    public function addError(string $error_message, $error_code, bool $throw = false)
    {
        $this->errors[] = [
            'code' => $error_code,
            'message' => $error_message
        ];
        if ($throw) throw new Exception($error_message);
    }

    /**
     * @param $data
     * @param $rules
     * @return bool
     * @throws Exception
     */
    public function validateParams($data, $rules): bool
    {
        if (!$data) $this->addError('Body is empty', self::ERROR_EMPTY_BODY);
        foreach ($rules as $rule) {
            if ($rule['required'])
            {
                if (!$data->get($rule['name'])) $this->addError('Parameter ' . $rule['name'] . ' is required!', self::ERROR_REQUIRED);
            }
        }
        if (!empty($this->errors)) throw new Exception('You have errors, check and fix them');
        return true;
    }
}