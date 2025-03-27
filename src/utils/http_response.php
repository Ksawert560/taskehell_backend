<?php
class HttpResponse extends Exception {
    private int $statusCode;
    private array $payload;
    private $response;

    public function __construct(int $statusCode, array $payload) {
        $this -> statusCode = $statusCode;
        $this -> payload = $payload;

        parent::__construct(json_encode($payload), $statusCode);
    }

    public static function fromStatus(array $payload, int $statusCode = 500): self {
        $response = new self($statusCode, $payload);

        if ($statusCode < 200 || $statusCode >= 300)
            throw $response;
    
        return $response;
    }

    public function respond() {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode($this->payload);
        exit;
    }

    public function getPayload(): array {
        return $this->payload;
    }
}
?>