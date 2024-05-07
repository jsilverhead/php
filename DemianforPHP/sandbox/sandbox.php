<?php

class SandboxException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return "Exception found in " . __CLASS__ . ": " . $this->code . $this->message;
    }

    public function printMessage(): string
    {
        return __CLASS__ . " found. " . "Code: " . ($this->code ? $this->code : "no code") . ". Message: " . ($this->message ? $this->message : "no message") . "\n";
    }
}

try {
    if (!isset($statement)) {
        throw new SandboxException("Statement isn't set", 400);
    }
} catch (SandboxException $e) {
    echo $e->printMessage();
} catch (Exception $e) {
    echo $e->getMessage();
} finally {
    echo "Operation closed";
}