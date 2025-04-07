<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Repository\TournamentRepository;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AllowDynamicProperties] class WebPushService
{
    private WebPush $webPush;

    /**
     * @throws \ErrorException
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly ParameterBagInterface $parameterBag,
                                private readonly CacheInterface        $webpushCache,
                                private readonly RequestStack          $requestStack,
                                private readonly Packages              $packageInterface,
                                private readonly TournamentRepository  $tournamentRepository,
                                private readonly RouterInterface       $router,
    )
    {
        $this->webpushCache->get('subscriptions', function ($item){
            $item->expiresAfter(7200);
            return [];
        });
        $this->registerWebPush();
    }

    /**
     * @throws \ErrorException
     */
    private function registerWebPush(): void
    {
        $auth = [
            'VAPID' => [
                'subject' => $this->requestStack->getMainRequest()->getSchemeAndHttpHost(), // can be a mailto: or your website address
                'publicKey' => $this->parameterBag->get('app')["webPush"]["publicKey"], // (recommended) uncompressed public key P-256 encoded in Base64-URL
                'privateKey' => $this->parameterBag->get('app')["webPush"]["privateKey"], // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
            ],
        ];
        $options = [
            "TTL" => 43200,
            "urgency" => "high"
        ];
        $webPush = new WebPush($auth, $options);
        $webPush->setReuseVAPIDHeaders(true);
        $this->webPush = $webPush;
    }

    public function registerSubscription($subscribtion, string $sessionId): void
    {
        $subs = $this->webpushCache->getItem('subscriptions');
        $subsArray = $subs->get();
        $subscriptionObject = Subscription::create(json_decode($subscribtion["content"],true));
        if(empty($subsArray) || !array_key_exists($sessionId, $subsArray)) {
            $subsArray[$subscribtion["context"]][$sessionId] = $subscriptionObject;
        }
        $subs->set($subsArray);
        $this->webpushCache->save($subs);
    }

    public function unregisterSubscription($subscribtion, string $sessionId): void
    {
        $subs = $this->webpushCache->getItem('subscriptions');
        $subsArray = $subs->get();
        $context = $subscribtion["context"];
        if (!empty($subsArray) && array_key_exists($context, $subsArray) && array_key_exists($sessionId, $subsArray[$context])) {
            unset($subsArray[$context][$sessionId]);
            if (empty($subsArray[$context])) {
                unset($subsArray[$context]);
            }
        }
        $subs->set($subsArray);
        $this->webpushCache->save($subs);
    }

    public function getSubscriptions(string $context): array
    {
        return $this->webpushCache->getItem('subscriptions')->get()[$context] ?? [];
    }

    public function sendPushNotification(string $title, string $content, string $context)
    {
        $iconUrl = join("",[$this->requestStack->getMainRequest()->getSchemeAndHttpHost(),$this->packageInterface->getUrl('images/notification_icon.png')]);
        $badgeUrl = join("",[$this->requestStack->getMainRequest()->getSchemeAndHttpHost(),$this->packageInterface->getUrl('images/notification_badge.jpg')]);
        $subscriptions = $this->getSubscriptions($context);
        $tournament = $this->tournamentRepository->findOneBy(["challongeId" => $context]);
        $tournamentRoute = $this->router->generate("app_tournament_show", ["id" => $tournament->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $webPush = $this->webPush;
        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification($subscription,json_encode(
                [
                    "title" => $title,
                    "content" => $content,
                    "icon" => $iconUrl,
                    "badge" => $badgeUrl,
                    "url" => $tournamentRoute,
                ]
            ));
        }
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                echo "<br/>[v] Message sent successfully for subscription {$endpoint}.";
            } else {
                echo "<br/>[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
            }
        }

    }

    public function hasSubscribed(string $session, string $tournamentId): bool
    {
        $subs = $this->webpushCache->getItem('subscriptions');
        $subsArray = $subs->get();
        return !empty($subsArray) && array_key_exists($tournamentId, $subsArray) && array_key_exists($session, $subsArray[$tournamentId]);
    }
}