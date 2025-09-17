<?php
namespace App\Service;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mercure\HubInterface;

class MercureService
{
    private HubInterface $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    public function createJwtToken(array $subscribe = [], array $publish = [], array $additionalClaims = []): string
    {
        return $this->hub->getFactory()->create($subscribe, $publish, $additionalClaims);
    }

    public function FetchActiveSubscribers($topic): array
    {
        // curl -k -N   "https://localhost/.well-known/mercure/subscriptions/https%3A%2F%2Flocalhost%2Fpublic"   -H "Accept: text/event-stream"   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhZG1pbiI6dHJ1ZSwibWVyY3VyZSI6eyJwdWJsaXNoIjpbIioiXSwic3Vic2NyaWJlIjpbIioiXX19.O72T5d0SniYJcO7rfE5yAvTBots-vb3s0hTA3oZESwg"
        // TODO generate bearer
        $url = $this->hub->getPublicUrl() . "/subscriptions/" . urlencode('https://localhost' . $topic) ;

        $httpClient = HttpClient::create();
        $content = $httpClient->request('GET',$url,[
            'headers' => [
                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhZG1pbiI6dHJ1ZSwibWVyY3VyZSI6eyJwdWJsaXNoIjpbIioiXSwic3Vic2NyaWJlIjpbIioiXX19.O72T5d0SniYJcO7rfE5yAvTBots-vb3s0hTA3oZESwg'
            ],
            'verify_peer' => false,  // ignore le certificat
            'verify_host' => false   // ignore la vérification du hostname
        ])->toArray(false);


        $subscriptions = $content['subscriptions'] ;

        $users = [] ;
        foreach ($subscriptions as $sub)
        {
            $users[] = ['id' => $sub['id'] , 'name' => $sub['payload']['username']] ;
        }

        return $users;

    }

}
