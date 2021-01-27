<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use App\Services\Traits\ApiResponse;

class ApiException
{
    use ApiResponse;

    /**
     * @var Exception
     */
    public $exception;

    /**
     * @var Request
     */
    public $request;

    /**
     * 异常类名
     *
     * @var
     */
    protected $exceptionClass;

    /**
     * ExceptionReport constructor.
     * @param Request $request
     * @param Exception $exception
     */
    function __construct(Request $request, Exception $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
    }

    /**
     * @var array
     */
    public $responseInfoMap = [
        ExampleException::class => ['code' => 400, 'message' => ''],
    ];

    /**
     * @return bool
     */
    public function shouldReturn()
    {
        foreach (array_keys($this->responseInfoMap) as $exceptionClass){
            if ($this->exception instanceof $exceptionClass){
                $this->exceptionClass = $exceptionClass;

                return true;
            }
        }

        return false;
    }

    /**
     * 生成异常处理对象
     *
     * @param Exception $e
     * @return ExceptionResponse
     */
    public static function make(Exception $e)
    {
        return new static(request(), $e);
    }

    /**
     * @return mixed
     */
    public function report()
    {
        $responseInfo = $this->responseInfoMap[$this->exceptionClass];
        if ($this->exception->getMessage()) {
            $responseInfo['message'] = $this->exception->getMessage();
        }

        return $this->failed($responseInfo['message'], $responseInfo['code']);
    }

}