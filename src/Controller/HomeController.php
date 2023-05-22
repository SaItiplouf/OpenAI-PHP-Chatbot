<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Messages;
use App\Services\OpenAiService;

class HomeController extends AbstractController
{
    private $entityManager;
    private $openAiService;

    public function __construct(EntityManagerInterface $entityManager, OpenAiService $openAiService) {
        $this->entityManager = $entityManager;
        $this->openAiService = $openAiService;
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $messageRepository = $this->entityManager->getRepository(Messages::class);
        $messages = $messageRepository->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'messages' => $messages
        ]);
    }
    #[Route('/chat', name: 'send_chat', methods: "POST")]
    public function chat(Request $request): Response
    {

        $question = $request->request->get('text');
        if (empty($question)) {
            $this->addFlash('error', 'La question ne peut pas être vide');
            return $this->redirectToRoute('app_home');
        }
        $messageslist = $this->entityManager->getRepository(Messages::class);

        if ($messageslist !== null) {
            $dataUser = $messageslist->findOneBy(['role' => 'user'], ['id' => 'DESC']);
            $dataAssistant = $messageslist->findOneBy(['role' => 'assistant'], ['id' => 'DESC']);
        }

        $callapi = $this->openAiService->apiRequest($question, $dataUser, $dataAssistant);
        $this->UpdateMessageUser($question);
        $this->UpdateMessageAssistant($callapi);

        $this->addFlash('success', 'Votre question a été soumise avec succès');
        return $this->redirectToRoute('app_home');
    }
    public function UpdateMessageUser(string $question): void
    {
        $messageUser = (new \App\Entity\Messages())
            ->setContent($question)
            ->setRole('user')
            ->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($messageUser);
        $this->entityManager->flush();

    }

    public function UpdateMessageAssistant(string $response): void
    {
        $messageAssistant = (new \App\Entity\Messages())
            ->setContent($response)
            ->setRole('assistant')
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($messageAssistant);
        $this->entityManager->flush();
    }


    #[Route('/clear', name: 'app_clear')]
    public function clear(): Response
    {
        $query = $this->entityManager->createQuery('DELETE FROM App\Entity\Messages');
        $query->execute();

        return $this->redirectToRoute('app_home');
    }
}