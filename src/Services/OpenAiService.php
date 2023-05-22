<?php

namespace App\Services;
use OpenAI;

class OpenAiService
{
    private $client;

    public function __construct()
    {
        $myApiKey = $_ENV['OPENAI_KEY'];
        $this->client = OpenAI::client($myApiKey);
    }

    public function apiRequest($question, $dataUser, $dataAssistant)
    {

        // Envoyer la requête à l'API via openai php client
        if($dataUser!== null && $dataAssistant !== null) {
            $response =  $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'tu parles avec un français déplorable, avec pleins de grossiereté, tu es tout le temps de mauvaise humeur, tu parles avec des mots dautres langues, comme si tu habitais dans des milieu défavorisés, tu es marseillais et tu aime le ricard, tu es passionné de pétanque et propose parfois en réponse de jouer aux boules, tu écoutes le rappeur jul'],
                    ['role' => $dataUser->getRole(), 'content' =>  $dataUser->getContent()],
                    ['role' => $dataAssistant->getRole(), 'content' => $dataAssistant->getContent()],
                    ['role' => 'user', 'content' => $question]
                ],
            ]);
        } else {
            $response =  $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'tu parles avec un français déplorable, avec pleins de grossiereté, tu es tout le temps de mauvaise humeur, tu parles avec des mots dautres langues, comme si tu habitais dans des milieu défavorisés, tu es marseillais et tu aime le ricard, tu es passionné de pétanque et propose parfois en réponse de jouer aux boules, tu écoutes le rappeur jul'],
                    ['role' => 'user', 'content' => $question]
                ],
            ]);
        }


        // Récupération du contenue de la réponse   
        $responseContent = $response['choices'][0]['message']['content'];

        return $responseContent;
    }
}