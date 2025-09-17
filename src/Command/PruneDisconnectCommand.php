<?php

namespace App\Command;

use App\Service\MercureService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsCommand(
    name: 'app:pruneDisconnect',
    description: 'Inform on public channel that somebody has gone',
)]
class PruneDisconnectCommand extends Command
{
    public function __construct(private HubInterface $hub,private MercureService $mercureService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // todo : $request->getSchemeAndHttpHost()

        $url = 'https://localhost/.well-known/mercure?topic=%2F.well-known%2Fmercure%2Fsubscriptions%7B%2Ftopic%7D%7B%2Fsubscriber%7D';
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $url, [
            'headers' => [
                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhZG1pbiI6dHJ1ZSwibWVyY3VyZSI6eyJwdWJsaXNoIjpbIioiXSwic3Vic2NyaWJlIjpbIioiXX19.O72T5d0SniYJcO7rfE5yAvTBots-vb3s0hTA3oZESwg'
            ],
            'verify_peer' => false,  // ignore le certificat
            'verify_host' => false   // ignore la vérification du hostname
        ]);

        $buffer = '';
        // Boucle sur le flux en continu via des événements
        foreach ($httpClient->stream($response) as $chunk) {
            if ($chunk->isTimeout()) {
                continue; // pas de données, juste attendre
            }

            if ($chunk->isFirst()) {
//                echo "Connexion ouverte au flux Mercure\n";
            }



            foreach ($httpClient->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }

                $content = $chunk->getContent();
                if ($content === '') {
                    continue;
                }

                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        // fin d’un événement SSE → parser le JSON complet
                        if ($buffer !== '') {
                            $obj = json_decode($buffer, true);
                            if ($obj !== null) {
//                                dump($obj);
                                if (isset($obj['payload']['username'])) {

                                    if ($obj['active'] == true) {
                                        $output->writeln("+ Utilisateur connecté : " . $obj['payload']['username']);
                                    }
                                    else {
                                        $output->writeln("- Utilisateur deconnecté : " . $obj['payload']['username']);

                                        $result = ['action' => 'disconnected' , 'nick' => $obj['payload']['username']] ;

                                        $update = new Update(
                                            'https://localhost' . '/public',
                                            json_encode($result)
                                        );

                                        $this->hub->publish($update);

                                    }
                                }
                            }
                            $buffer = '';
                        }
                        continue;
                    }

                    if (str_starts_with($line, 'data:')) {
                        // retirer le préfixe 'data:' et concaténer
                        $buffer .= substr($line, 5);
                    }
                }
            }

            if ($chunk->isLast()) {
//                echo "Flux terminé\n";
                break;
            }
        }


        return Command::SUCCESS;
    }
}
