<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Conge;
use App\Entity\Projet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ChatbotController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/api/chatbot/chat', name: 'chatbot_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data     = json_decode($request->getContent(), true);
        $question = trim($data['question'] ?? '');
        $userId   = $data['userId'] ?? null;

        if ($question === '') {
            return $this->json(['error' => 'Question vide'], 400);
        }

        ['context' => $context, 'chunks' => $chunks] = $this->buildContext($question, $userId);

        $tStart  = microtime(true);
        $answer  = $this->callGroq($question, $context);
        $elapsed = round(microtime(true) - $tStart, 3);

        $this->logMetrics($question, $answer, $context, $chunks, $elapsed);

        return $this->json(['answer' => $answer]);
    }

    // ─── Construction du contexte RAG ───────────────────────────────────────────

    private function buildContext(string $question, mixed $userId = null): array
    {
        $q      = mb_strtolower($question);
        $parts  = [];
        $chunks = [];

        // Infos personnelles si l'utilisateur parle de lui-même
        if ($userId && preg_match('/mes?\s+(info|profil|donn|congé|contrat)|mon\s+(contrat|profil|salaire)|moi|je\s+suis/u', $q)) {
            try {
                $me = $this->em->find(User::class, (int)$userId);
                if ($me) {
                    $block    = "UTILISATEUR CONNECTÉ :\n"
                        . 'Nom : '          . $me->getPrenom() . ' ' . $me->getNom()  . "\n"
                        . 'Email : '        . $me->getEmail()                          . "\n"
                        . 'Rôle : '         . implode(', ', $me->getRoles())           . "\n"
                        . 'Poste : '        . ($me->getPoste()       ?? 'N/A')         . "\n"
                        . 'Département : '  . ($me->getDepartement() ?? 'N/A')         . "\n"
                        . 'Téléphone : '    . ($me->getTelephone()   ?? 'N/A');
                    $parts[]  = $block;
                    $chunks[] = ['label' => 'PROFIL_UTILISATEUR', 'content' => $block];
                }
            } catch (\Throwable) {}
        }

        // Employés — toujours inclus
        try {
            $employes = $this->em->getRepository(User::class)->findAll();
            $lines    = [];
            foreach ($employes as $u) {
                $lines[] = $u->getPrenom() . ' ' . $u->getNom()
                    . ' — ' . ($u->getPoste()       ?? 'N/A')
                    . ' — ' . ($u->getDepartement() ?? 'N/A')
                    . ' — ' . $u->getEmail();
            }
            if ($lines) {
                $block    = 'EMPLOYÉS (' . count($lines) . ") :\n" . implode("\n", $lines);
                $parts[]  = $block;
                $chunks[] = ['label' => 'EMPLOYES', 'content' => $block];
            }
        } catch (\Throwable) {}

        // Congés si la question y fait référence
        if (preg_match('/cong[eé]|absence|vacance/u', $q)) {
            try {
                $conges = $this->em->getRepository(Conge::class)->findAll();
                $lines  = [];
                foreach (array_slice($conges, 0, 20) as $c) {
                    $lines[] = ($c->getEmploye()?->getPrenom() . ' ' . $c->getEmploye()?->getNom())
                        . ' — ' . $c->getType()
                        . ' du ' . $c->getDateDebut()?->format('d/m/Y')
                        . ' au '  . $c->getDateFin()?->format('d/m/Y')
                        . ' — '   . $c->getStatut();
                }
                if ($lines) {
                    $block    = "CONGÉS RÉCENTS :\n" . implode("\n", $lines);
                    $parts[]  = $block;
                    $chunks[] = ['label' => 'CONGES', 'content' => $block];
                }
            } catch (\Throwable) {}
        }

        // Projets si la question y fait référence
        if (preg_match('/projet|tâche|task|mission/u', $q)) {
            try {
                $projets = $this->em->getRepository(Projet::class)->findAll();
                $lines   = [];
                foreach ($projets as $p) {
                    $lines[] = $p->getNom() . ' — statut: ' . ($p->getStatut() ?? 'N/A');
                }
                if ($lines) {
                    $block    = "PROJETS :\n" . implode("\n", $lines);
                    $parts[]  = $block;
                    $chunks[] = ['label' => 'PROJETS', 'content' => $block];
                }
            } catch (\Throwable) {}
        }

        return ['context' => implode("\n\n", $parts), 'chunks' => $chunks];
    }

    // ─── Métriques d'évaluation RAG ─────────────────────────────────────────────

    /**
     * Calcule et logue 3 métriques :
     *   1. Temps de réponse  — fluidité de l'interaction
     *   2. Précision         — mots-clés de la question retrouvés dans la réponse
     *   3. Rappel            — chunks de contexte effectivement utilisés dans la réponse
     */
    private function logMetrics(
        string $question,
        string $answer,
        string $context,
        array  $chunks,
        float  $elapsed
    ): void {
        $answerLower = mb_strtolower($answer);
        $sep         = str_repeat('═', 60);

        // ── 1. Précision ─────────────────────────────────────────────
        // Ratio : mots-clés de la question présents dans la réponse
        $keywords  = $this->extractKeywords($question);
        $matched   = array_values(array_filter($keywords, fn($w) => mb_strpos($answerLower, $w) !== false));
        $precision = count($keywords) > 0
            ? round(count($matched) / count($keywords) * 100, 1)
            : 0.0;

        // ── 2. Rappel ────────────────────────────────────────────────
        // Ratio : chunks dont au moins un mot-clé figure dans la réponse
        $usedLabels = [];
        foreach ($chunks as $chunk) {
            $chunkWords = $this->extractKeywords($chunk['content']);
            $found      = array_filter($chunkWords, fn($w) => mb_strpos($answerLower, $w) !== false);
            if (count($found) > 0) {
                $usedLabels[] = $chunk['label'];
            }
        }
        $recall = count($chunks) > 0
            ? round(count($usedLabels) / count($chunks) * 100, 1)
            : 0.0;

        // ── 3. Évaluation globale ────────────────────────────────────
        $f1 = ($precision + $recall) > 0
            ? round(2 * $precision * $recall / ($precision + $recall), 1)
            : 0.0;

        $perfLabel = match (true) {
            $elapsed <= 5  => '🟢 Rapide',
            $elapsed <= 15 => '🟡 Correct',
            default        => '🔴 Lent',
        };

        // ── Log dans le terminal PHP ─────────────────────────────────
        error_log($sep);
        error_log('[RAG EVAL] Question     : ' . mb_substr($question, 0, 100));
        error_log('[RAG EVAL] Réponse      : ' . mb_substr(str_replace("\n", ' ', $answer), 0, 100) . (mb_strlen($answer) > 100 ? '…' : ''));
        error_log('[RAG EVAL] ' . str_repeat('─', 48));
        error_log('[RAG EVAL] ⏱  Temps réponse  : ' . $elapsed . 's   ' . $perfLabel);
        error_log('[RAG EVAL] 🎯 Précision      : ' . $precision . '%'
            . '  (' . count($matched) . '/' . count($keywords) . ' mots-clés question→réponse)'
        );
        error_log('[RAG EVAL] 🔍 Rappel         : ' . $recall . '%'
            . '  (' . count($usedLabels) . '/' . count($chunks) . ' chunks utilisés)'
        );
        error_log('[RAG EVAL] ⚖  Score F1       : ' . $f1 . '%');
        error_log('[RAG EVAL] ' . str_repeat('─', 48));
        error_log('[RAG EVAL] Chunks chargés   : ' . count($chunks)
            . (count($chunks) ? ' → ' . implode(', ', array_column($chunks, 'label')) : '')
        );
        error_log('[RAG EVAL] Chunks utilisés  : ' . count($usedLabels)
            . (count($usedLabels) ? ' → ' . implode(', ', $usedLabels) : '')
        );
        error_log('[RAG EVAL] Taille contexte  : ' . strlen($context) . ' chars');
        error_log('[RAG EVAL] Taille réponse   : ' . mb_strlen($answer) . ' chars');
        error_log($sep);
    }

    /**
     * Extrait les mots significatifs (≥3 lettres, hors stopwords FR/EN).
     */
    private function extractKeywords(string $text): array
    {
        static $stopwords = [
            'le','la','les','un','une','des','du','de','et','en','au','aux',
            'je','tu','il','elle','nous','vous','ils','elles','me','te','se',
            'mon','ma','mes','ton','ta','tes','son','sa','ses','leur','leurs',
            'que','qui','quoi','dont','où','mais','donc','ni','car',
            'est','sont','été','avoir','être','ont','the','of','in','is',
            'pour','sur','par','avec','sans','sous','dans','entre','vers',
            'plus','très','tout','bien','aussi','même','comme','quand','si',
            'quel','quelle','quels','quelles','combien','comment','pourquoi',
            'quel','cette','ceux','celles','ici','voici','voilà',
        ];

        preg_match_all('/[a-zàâäéèêëîïôöùûüç]{3,}/u', mb_strtolower($text), $m);

        return array_values(
            array_unique(
                array_filter($m[0], fn($w) => !in_array($w, $stopwords, true))
            )
        );
    }

    // ─── Appel Groq API ──────────────────────────────────────────────────────────

    private function callGroq(string $question, string $context): string
    {
        $apiKey = $_ENV['GROQ_API_KEY'] ?? '';

        $system = "Tu es un assistant RH intelligent pour une application de gestion des ressources humaines. "
                . "Réponds en français de manière concise et professionnelle. "
                . "Utilise les données suivantes pour répondre :\n\n" . $context;

        $payload = json_encode([
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $question],
            ],
            'max_tokens'  => 512,
            'temperature' => 0.4,
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $httpCode !== 200) {
            return "Désolé, je ne peux pas répondre pour le moment. (erreur $httpCode)";
        }

        $decoded = json_decode($raw, true);

        return $decoded['choices'][0]['message']['content']
            ?? "Je n'ai pas pu générer une réponse.";
    }
}
