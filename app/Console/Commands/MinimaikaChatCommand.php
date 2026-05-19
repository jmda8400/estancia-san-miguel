<?php

namespace App\Console\Commands;

use App\Services\GroqChatService;
use App\Services\RagContextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MinimaikaChatCommand extends Command
{
    protected $signature = 'minimaika:chat';

    protected $description = 'Chat de consola usando RAG + capa generativa de Groq';

    private const FALLBACK = 'No tengo esa información confirmada en la base de conocimiento del refugio. Te recomiendo consultar directamente con el refugio para evitar darte un dato incorrecto.';

    /** @var array<int, array{user:string,assistant:string}> */
    private array $history = [];

    public function handle(RagContextService $rag, GroqChatService $groq): int
    {
        $this->info('Minimaika chat iniciado. Escribí "salir" para terminar.');

        while (true) {
            $question = trim((string) $this->ask('Vos'));
            if ($question === '') {
                continue;
            }

            if (in_array(mb_strtolower($question), ['salir', 'exit', 'quit'], true)) {
                $this->info('¡Hasta luego!');

                return self::SUCCESS;
            }

            Log::info('Pregunta recibida en minimaika:chat.', ['question' => $question]);

            $fragments = $rag->retrieveRelevantFragments($question, 3);
            Log::info('Fragmentos recuperados.', ['count' => count($fragments)]);

            if ($fragments === []) {
                Log::warning('Fallback usado por falta de contexto.');
                $answer = self::FALLBACK;
            } else {
                $systemPrompt = 'Eres el asistente virtual del Refugio Agostino Rocca. Respondés consultas de visitantes por WhatsApp. Tu estilo debe ser claro, amable, natural y breve. Usá únicamente la información provista en el contexto de la base de conocimiento. No inventes datos. Si la información no está en el contexto, decí que no tenés ese dato confirmado y sugerí consultar con el refugio. Respondé en español claro y cordial. No pegues fragmentos textuales largos. Resumí y explicá de forma conversacional. La respuesta debe tener entre 80 y 150 palabras como máximo, salvo que el usuario pida más detalle.';

                $historyText = $this->formatRecentHistory();
                $contextText = implode("\n\n", array_map(fn ($f, $i) => 'Fragmento '.($i + 1).":\n{$f}", $fragments, array_keys($fragments)));

                $userPrompt = "Pregunta del usuario:\n{$question}\n\nHistorial reciente:\n{$historyText}\n\nContexto recuperado de la base de conocimiento:\n{$contextText}\n\nInstrucciones:\n- Respondé solamente usando el contexto.\n- Si no hay información suficiente, decí que no tenés ese dato confirmado.\n- No inventes horarios, precios, distancias, disponibilidad, servicios ni condiciones.\n- No menciones \"según el fragmento\" ni \"según la base de conocimiento\".\n- Respondé de forma natural, como si estuvieras contestando por WhatsApp.\n- Máximo 150 palabras.";

                Log::info('Llamando a Groq para redacción final.');
                $generated = $groq->generate($systemPrompt, $userPrompt);

                if ($generated === null) {
                    Log::warning('Fallback usado por falla de Groq.');
                    $answer = self::FALLBACK;
                } else {
                    Log::info('Groq respondió correctamente.');
                    $answer = $generated;
                }
            }

            $this->line("Bot: {$answer}");
            $this->history[] = ['user' => $question, 'assistant' => $answer];
        }
    }

    private function formatRecentHistory(): string
    {
        $recent = array_slice($this->history, -5);

        if ($recent === []) {
            return 'Sin historial previo.';
        }

        $lines = [];
        foreach ($recent as $item) {
            $lines[] = 'Usuario: '.$item['user'];
            $lines[] = 'Asistente: '.$item['assistant'];
        }

        return implode("\n", $lines);
    }
}
