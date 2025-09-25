<?php

namespace App\Traits;

trait ApiResponder
{
    protected $statusCode = 200;

    protected $success = true;

    protected $failure = false;

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    protected function respond($data, $headers = [])
    {
        return response()->json($data, $this->statusCode, $headers);
    }

    public function successStatus($message = 'Success request!')
    {
        return $this->respond([
            'status' => $this->getSuccess(),
            'message' => $message,
        ]);
    }

    public function errorStatus($message = 'Failure request!')
    {
        return $this->respond([
            'status' => 0,
            'message' => $message,
        ]);
    }

    public function respondNoContent($message = 'No content')
    {
        return $this->setStatusCode(204)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

    public function respondWithItem($item, $message = 'Success request!')
    {
        return $this->respond([
            'status' => $this->getSuccess(),
            'message' => $message,
            'data' => $item,

        ]);
    }

    public function respondWrongAnswer($item, $message = 'Success request!')
    {
        return $this->respond([
            'status' => $this->getFailure(),
            'message' => $message,
            'data' => $item,

        ]);
    }

    public function respondWithItemName($item_name, $item, $message = 'Success request!')
    {
        return $this->respond([
            'status' => $this->getSuccess(),
            'message' => $message,
            $item_name => $item,
        ]);
    }

    public function respondWithCollection($collection, $message = 'Success request!')
    {
        return $this->respond([
            'status' => $this->getSuccess(),
            'message' => $message,
            'data' => $collection,
        ]);
    }

    public function respondWithCreated($collection = null, $message = 'Created Successfully')
    {
        return $this->setStatusCode(201)
            ->respond([
                'status' => $this->getSuccess(),
                'message' => __($message),
                'data' => $collection,
            ]);
    }

    public function respondWithUpdated($collection = null, $message = 'Updated Successfully')
    {
        return $this->setStatusCode(200)
            ->respond([
                'status' => $this->getSuccess(),
                'message' => __($message),
                'data' => $collection,
            ]);
    }

    public function respondWithRetrieved($collection = null, $message = 'Retrieved Successfully')
    {
        return $this->setStatusCode(200)
            ->respond([
                'status' => $this->getSuccess(),
                'message' => __($message),
                'data' => $collection,
            ]);
    }

    public function respondWithDeleted($message = 'Deleted Successfully')
    {
        return $this->setStatusCode(200)
            ->respond([
                'status' => $this->getSuccess(),
                'message' => __($message),
            ]);
    }

    public function respondWithMessage($message)
    {
        return $this->setStatusCode(200)
            ->respond([
                'status' => $this->getSuccess(),
                'message' => $message,
            ]);
    }

    public function respondCreated($message = 'Resource created successfully')
    {
        return $this->setStatusCode(201)
            ->respond([
                'status' => $this->getSuccess(),
                'message' => $message,
                //'data' => $data,

            ]);
    }

    protected function errorDatabase($message)
    {
        return $this->setStatusCode(422)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

    protected function respondWithError($message)
    {
        if (!is_array($message)) {
            $message = [
                'body' => [$message],
            ];
        }

        return $this->respond([
            'http_code' => $this->getStatusCode(),
            'error' => [
                'message' => $message,
            ],
        ]);
    }

    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

    public function errorWrongArgs($message)
    {
        return $this->setStatusCode(400)
            ->respond([
                'status' => $this->getFailure(),
                'message' => $message,
            ]);
    }

}
