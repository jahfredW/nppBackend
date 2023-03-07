<?php


namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;


class JWTCreatedListener{
    /**
 * @var RequestStack
 * @var EntityManagerInterface
 */
private $requestStack;
private $em;

/**
 * @param RequestStack $requestStack
 */
public function __construct(EntityManagerInterface $em, RequestStack $requestStack)
{
    $this->requestStack = $requestStack;
    $this->em = $em;
}

/**
 * @param JWTCreatedEvent $event
 *
 * @return void
 */
public function onJWTCreated(JWTCreatedEvent $event)
{
    $request = $this->requestStack->getCurrentRequest();

    $payload = $event->getData();


    $user = $this->em->getRepository(User::class)->findOneByEmail($payload['username']); 

    $payload['id'] = $user->getId();
    $payload['pseudo'] = $user->getPseudo();
    // $payload['ip'] = $request->getClientIp();

    $event->setData($payload);

    // $header        = $event->getHeader();
    // $header['cty'] = 'JWT';

    // $event->setHeader($header);
}
}