<?php
namespace modules\articlegenerator\web\controllers;

use Craft;
use craft\elements\Entry;
use craft\helpers\App;
use craft\web\Controller;
use yii\web\Response;

class GenerateController extends Controller
{
    private function getApiKey(): ?string
    {
        return App::env('ARTICLE_GENERATOR_API_KEY') ?: null;
    }

    private function sharedParams(): array
    {
        $request = Craft::$app->request;
        return [
            'entryId'     => (int)$request->getRequiredBodyParam('entryId'),
            'perspective' => trim((string)$request->getBodyParam('perspective', '')),
            'brief'       => trim((string)$request->getBodyParam('brief', '')),
        ];
    }

    private function buildSystemPrompt(string $perspective): string
    {
        $perspectiveInstruction = $perspective
            ? "Write from the perspective of a {$perspective}. Let this perspective shape the language, framing, metaphors, and treatment recommendations throughout — not just a passing mention."
            : '';

        return <<<EOT
You are an expert mental health content writer for a counselling directory serving Brighton & Hove, UK.
{$perspectiveInstruction}

The directory connects people in Brighton & Hove with local therapists. Always write with hyper-local intent.
The final FAQ must name Brighton, Hove, or nearby areas specifically and reference the directory as a local Brighton & Hove resource — never as a national UK directory.

Return only valid JSON. No markdown, no extra text — just the raw JSON object.
EOT;
    }

    private function buildBriefSection(string $brief): string
    {
        if (!$brief) return '';
        return "\n\nBrief/notes from the editor:\n{$brief}";
    }

    private function callClaude(string $systemPrompt, string $userPrompt, string $apiKey): string
    {
        $client   = Craft::createGuzzleClient();
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ],
            'json' => [
                'model'      => 'claude-sonnet-4-6',
                'max_tokens' => 8000,
                'system'     => $systemPrompt,
                'messages'   => [['role' => 'user', 'content' => $userPrompt]],
            ],
        ]);

        $data = json_decode((string)$response->getBody(), true);
        $raw  = $data['content'][0]['text'] ?? '';

        // Strip markdown code fences if present
        $raw = preg_replace('/^```json\s*/m', '', $raw);
        $raw = preg_replace('/^```\s*$/m', '', $raw);

        return trim($raw);
    }

    private function parseJson(string $raw): array
    {
        $parsed = json_decode($raw, true);
        if (!$parsed) {
            throw new \Exception('Claude returned invalid JSON: ' . substr($raw, 0, 300));
        }
        return $parsed;
    }

    private function saveFaqs(Entry $entry, array $faqs): void
    {
        if (empty($faqs)) return;

        $faqData = [];
        foreach ($faqs as $faq) {
            $faqData[] = [
                'type'   => 'faq',
                'fields' => [
                    'question' => $faq['question'],
                    'answer'   => $faq['answer'],
                ],
            ];
        }
        $entry->setFieldValues(['faqs' => $faqData]);
    }

    /**
     * Generate the full article from the entry title.
     * Minimum required: entry must have a title.
     */
    public function actionCreate(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $this->asJson(['success' => false, 'error' => 'ARTICLE_GENERATOR_API_KEY not set in .env']);
        }

        ['entryId' => $entryId, 'perspective' => $perspective, 'brief' => $brief] = $this->sharedParams();

        $entry = Craft::$app->entries->getEntryById($entryId);
        if (!$entry || !$entry->title) {
            return $this->asJson(['success' => false, 'error' => 'Entry not found or has no title. Save the entry with a title first.']);
        }

        $systemPrompt = $this->buildSystemPrompt($perspective);

        $userPrompt = <<<EOT
Write a complete SEO and AEO-optimised article for a Brighton & Hove counselling directory.

Title: "{$entry->title}"{$this->buildBriefSection($brief)}

Return this exact JSON structure:
{
  "intro": "Max 115 characters. A compelling single sentence that captures the article's core theme.",
  "body": "900-1100 word article body as HTML. Use <h2> for subheadings, <p> for paragraphs, <ul>/<li> for lists where appropriate. No <h1>.",
  "keyTakeaways": "4-5 takeaways, one per line, no bullet characters or hyphens",
  "faqs": [
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "Where can I find support in Brighton?", "answer": "Hyper-local Brighton & Hove specific answer naming the directory, Brighton, Hove, and nearby areas."}
  ]
}
EOT;

        try {
            $raw     = $this->callClaude($systemPrompt, $userPrompt, $apiKey);
            $data    = $this->parseJson($raw);
        } catch (\Exception $e) {
            return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
        }

        $intro = mb_substr($data['intro'] ?? '', 0, 115);

        $entry->setFieldValues([
            'intro'        => $intro,
            'body'         => $data['body'] ?? '',
            'keyTakeaways' => $data['keyTakeaways'] ?? '',
        ]);

        $this->saveFaqs($entry, $data['faqs'] ?? []);

        if (!Craft::$app->elements->saveElement($entry)) {
            return $this->asJson(['success' => false, 'error' => implode(', ', $entry->getFirstErrors())]);
        }

        return $this->asJson([
            'success'  => true,
            'mode'     => 'create',
            'faqCount' => count($data['faqs'] ?? []),
        ]);
    }

    /**
     * Generate takeaways and FAQs from an existing body.
     * Minimum required: entry must have a body with at least 100 characters.
     */
    public function actionEnrich(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $this->asJson(['success' => false, 'error' => 'ARTICLE_GENERATOR_API_KEY not set in .env']);
        }

        ['entryId' => $entryId, 'perspective' => $perspective, 'brief' => $brief] = $this->sharedParams();

        $entry = Craft::$app->entries->getEntryById($entryId);
        if (!$entry) {
            return $this->asJson(['success' => false, 'error' => 'Entry not found.']);
        }

        $body = strip_tags((string)$entry->body);
        if (strlen($body) < 100) {
            return $this->asJson(['success' => false, 'error' => 'Body is too short. Write the article body first, then generate takeaways and FAQs.']);
        }

        $systemPrompt = $this->buildSystemPrompt($perspective);

        $userPrompt = <<<EOT
Read this article body and generate structured fields from it.{$this->buildBriefSection($brief)}

Return this exact JSON structure:
{
  "keyTakeaways": "4-5 takeaways, one per line, no bullet characters or hyphens",
  "faqs": [
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."},
    {"question": "Where can I find support in Brighton?", "answer": "Hyper-local Brighton & Hove specific answer naming the directory, Brighton, Hove, and nearby areas."}
  ]
}

Article body:
{$body}
EOT;

        try {
            $raw  = $this->callClaude($systemPrompt, $userPrompt, $apiKey);
            $data = $this->parseJson($raw);
        } catch (\Exception $e) {
            return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
        }

        $entry->setFieldValues(['keyTakeaways' => $data['keyTakeaways'] ?? '']);
        $this->saveFaqs($entry, $data['faqs'] ?? []);

        if (!Craft::$app->elements->saveElement($entry)) {
            return $this->asJson(['success' => false, 'error' => implode(', ', $entry->getFirstErrors())]);
        }

        return $this->asJson([
            'success'  => true,
            'mode'     => 'enrich',
            'faqCount' => count($data['faqs'] ?? []),
        ]);
    }
}
