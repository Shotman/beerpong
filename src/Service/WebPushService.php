<?php

namespace App\Service;

use AllowDynamicProperties;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

#[AllowDynamicProperties] class WebPushService
{
    private WebPush $webPush;

    /**
     * @throws \ErrorException
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly ParameterBagInterface $parameterBag,
                                private readonly CacheInterface $webpushCache,
                                private readonly RequestStack $requestStack,
                                private readonly Packages $packageInterface,
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
        $webPush = new WebPush($auth);
        $webPush->setReuseVAPIDHeaders(true);
        $this->webPush = $webPush;
    }

    public function registerSubscription($subscribtion): void
    {
        $subs = $this->webpushCache->getItem('subscriptions');
        $subsArray = $subs->get();
        $subscriptionObject = Subscription::create(json_decode($subscribtion["content"],true));
        $find = null;
        if(!empty($subsArray)){
            $subsArrayContext = $subsArray[$subscribtion["context"]];
            $find = array_find($subsArrayContext, function ($value, $key) use ($subscriptionObject) {
                /**
                 * @var Subscription $value
                 */
                return $value == $subscriptionObject;

            });
        }
        if(is_null($find)) {
            $subsArray[$subscribtion["context"]][] = $subscriptionObject;
            $subs->set($subsArray);
        }
        $this->webpushCache->save($subs);
    }

    private function getSubscriptions(string $context): array
    {
        return $this->webpushCache->getItem('subscriptions')->get()[$context] ?? [];
    }

    public function sendPushNotification(string $title, string $content, string $context)
    {
        $iconUrl = join("",[$this->requestStack->getMainRequest()->getSchemeAndHttpHost(),$this->packageInterface->getUrl('images/notification_icon.png')]);
        $subscriptions = $this->getSubscriptions($context);
        dump($subscriptions);
        $webPush = $this->webPush;
        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification($subscription,json_encode(
                [
                    "title" => $title,
                    "content" => $content,
                    "icon" => $iconUrl,
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
}