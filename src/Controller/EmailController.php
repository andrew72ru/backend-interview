<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Email;
use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/email-verification')]
class EmailController extends AbstractController
{
    public function __construct(
        readonly private EmailRepository $emailRepository,
        readonly private ValidatorInterface $validator,
        readonly private SerializerInterface $serializer,
        readonly private MessageBusInterface $messageBus,
        readonly private EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'app_email_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        // TODO Get email from JSON request content
        $email = $request->request->get('email') or throw new BadRequestHttpException("Missing email");
        $entity = $this->emailRepository->findOneBy(['email' => $email]);

        if ($entity === null) {
            $entity = new Email();
            $entity->setEmail($email);
            $errors = $this->validator->validate($entity);
            if (count($errors) > 0) {
                throw new UnprocessableEntityHttpException("Invalid email address provided");
            }

            $this->em->persist($entity);
            $this->em->flush();
        }
        $this->messageBus->dispatch(new EmailMessage($entity->getId()));

        return new JsonResponse([['success' => true, 'id' => $entity->getId()]]);
    }

    #[Route('/{email}', name: 'app_email_show', methods: ['GET'])]
    public function show(string $email): JsonResponse
    {
        $entity = $this->emailRepository->findOneBy(['email' => $email]);
        if ($entity === null) {
            throw new NotFoundHttpException('Email not found');
        }

        if ($entity->getEmailVerifications()->isEmpty()) {
            return new JsonResponse(
                data: ['message' => \sprintf('Email \'%s\' not verified yet', $email)],
                status: Response::HTTP_NO_CONTENT, // TODO Why "no content"?
            );
        }

        return new JsonResponse(
            data: $this->serializer->serialize($entity, 'json'),
            json: true,
        );
    }

    #[Route('/{email}', name: 'app_email_delete', methods: ['DELETE'])]
    public function delete(string $email): JsonResponse
    {
        $entity = $this->emailRepository->findOneBy(['email' => $email]);
        if ($entity === null) {
            throw new NotFoundHttpException('Email not found');
        }

        $this->emailRepository->remove($entity, true);

        return new JsonResponse(['success' => true]);
    }
}
