<?php declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use App\Service\EmailVerificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        readonly private EmailVerificationService $verificationService,
        readonly private EmailRepository $emailRepository,
        readonly private LoggerInterface $logger,
    ) {
    }

    public function __invoke(EmailMessage $message)
    {
        $email = $this->emailRepository->findOneBy(['id' => $message->getId()]);
        if ($email === null) {
            $this->logger->error('Unable to find email', ['id' => $message->getId()]);

            throw new UnrecoverableMessageHandlingException('Unable to find email');
        }

        $this->verificationService->verify($email);
    }
}
