<?php

class ErrorMessage
{
    protected $msg;

    public function __construct(string $msg)
    {
        $this->msg = $msg;
    }

    public function message(): string
    {
        return $this->msg;
    }

    public function asJson()
    {
        return json_encode(['status' => 'error', 'message' => $this->message()]);
    }

    public static function methodNotAllowed() : self
    {
        return new self('Method not allowed');
    }

    public static function resourceNotFound() : self
    {
        return new self('Resource not found');
    }

    public static function movieIdRequired() : self
    {
        return new self('Movie id is required');
    }

    public static function internalServerError() : self
    {
        return new self('Internal server error');
    }
    public static function userAdminNotFound(): self
    {
        return new self("No admin found");
    }
    public static function userNotFound(): self
    {
        return new self("No user found");
    }

    public static function notValidTicket() : self
    {
        return new self('Ticket is not valid');
    }
}
