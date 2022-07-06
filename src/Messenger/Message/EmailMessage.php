<?php declare(strict_types=1);

namespace App\Messenger\Message;

class EmailMessage
{
    public function __construct(readonly int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
