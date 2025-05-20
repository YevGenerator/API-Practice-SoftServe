<?php
class AuthenticatedUser
{
    private ?object $payload;

    public function __construct(?object $payload)
    {
        $this->payload = $payload;
    }

    public function isAuthenticated(): bool
    {
        return $this->payload !== null;
    }

    public function getId(): ?int
    {
        return $this->payload->user_id ?? null;
    }

    public function getRole(): ?string
    {
        return $this->payload->role ?? null;
    }

    public function isAdmin(): bool
    {
        return $this->getRole() === 'admin';
    }

    public function isUser(): bool
    {
        return $this->getRole() === 'user';
    }

    public function getPayload(): ?object
    {
        return $this->payload;
    }
}
