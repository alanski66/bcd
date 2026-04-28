<?php
namespace modules\articlegenerator\console\controllers;

use Craft;
use craft\console\Controller;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use yii\console\ExitCode;

/**
 * Article generator commands.
 *
 * php craft article/save-drafts   — saves the three AI comparison drafts
 * php craft article/delete-drafts — removes them when done
 */
class ArticleController extends Controller
{
    public $defaultAction = 'save-drafts';

    private function getDrafts(): array
    {
        return [
            'qwen' => [
                'title'        => '[QWEN] Understanding the link between trauma and generalised anxiety',
                'intro'        => 'GAD involves excessive worry that often stems from past trauma. Understanding this link is key to lasting relief.',
                'body'         => '<h2>What is Generalised Anxiety Disorder?</h2><p>GAD is characterised by persistent feelings of unease or fear that interfere with daily activities. Unlike specific phobias or panic disorders, GAD\'s fears are not linked to a particular object or situation.</p><h2>How Does Trauma Contribute to GAD?</h2><p>Trauma can significantly increase the risk of developing Generalised Anxiety Disorder. Types of trauma that may contribute include early childhood trauma such as abuse or neglect, adverse life events like serious accidents or health crises, and acute stress disorders from intense experiences.</p><h2>Symptoms of Generalised Anxiety Disorder</h2><p>Common symptoms include persistent worry about multiple areas of life, difficulty concentrating, sleep disturbances, and physical symptoms like sweating, trembling, and muscle tension.</p><h2>Treatment Options for GAD</h2><p>Effective treatments include Cognitive Behavioral Therapy (CBT) to identify and challenge anxious thoughts, medication such as SSRIs, mindfulness and relaxation techniques including deep breathing and meditation, and support groups.</p><h2>Coping Strategies for Managing Anxiety</h2><p>Developing healthy coping mechanisms is crucial. Exercise regularly to reduce stress, maintain a healthy diet, get enough sleep, and practise mindfulness and breathing exercises.</p><h2>When to Seek Professional Help</h2><p>If your anxiety interferes with daily life, consider seeking professional assistance. Therapists and psychiatrists specialise in helping individuals overcome GAD and related conditions.</p>',
                'keyTakeaways' => "Trauma significantly increases the risk of developing Generalised Anxiety Disorder.\nSymptoms of GAD include persistent worry, physical tension, and sleep disturbances.\nEffective treatments for GAD include CBT, medication, mindfulness techniques, and support groups.\nDeveloping healthy coping strategies is essential for managing anxiety.\nProfessional help should be considered if anxiety interferes with daily life.",
                'faqs'         => [
                    ['question' => 'Can trauma cause Generalised Anxiety Disorder?',           'answer' => 'Yes, trauma can significantly increase the risk of developing GAD by triggering excessive and persistent worry about everyday situations.'],
                    ['question' => 'What are common symptoms of Generalised Anxiety Disorder?','answer' => 'Common symptoms include persistent worry about multiple areas of life, difficulty concentrating, sleep disturbances, and physical symptoms like sweating and trembling.'],
                    ['question' => 'How is Generalised Anxiety Disorder treated?',              'answer' => 'Effective treatments for GAD include CBT, medication, mindfulness techniques, and support groups.'],
                    ['question' => 'What are some coping strategies for managing anxiety?',    'answer' => 'Coping strategies include exercise, maintaining a healthy diet, getting enough sleep, practising mindfulness, and engaging in relaxation exercises like deep breathing.'],
                    ['question' => 'When should I seek professional help for GAD?',            'answer' => 'Seek professional help if your anxiety interferes with daily life or significantly impacts your quality of life.'],
                    ['question' => 'Can trauma affect anyone, regardless of background?',      'answer' => 'Yes, trauma can affect anyone. It\'s important to acknowledge and address the impact of past experiences on mental health.'],
                ],
            ],

            'llama' => [
                'title'        => '[LLAMA] Understanding the link between trauma and generalised anxiety',
                'intro'        => 'Living with GAD is debilitating. Research shows it is frequently linked to past trauma, not just everyday stress.',
                'body'         => '<h2>The Impact of Trauma on Mental Health</h2><p>Traumatic events, such as physical or emotional abuse, neglect, or accidents, can have a profound impact on mental health. When we experience trauma, our body\'s stress response system — the hypothalamic-pituitary-adrenal (HPA) axis — is activated, leading to increased production of stress hormones like cortisol and adrenaline.</p><h2>The Long-Term Effects of Trauma</h2><p>While the immediate effects of trauma may subside with time, long-term consequences can persist: hypervigilance, a heightened state of alertness making it difficult to relax; avoidance behaviours; and intrusive memories or flashbacks.</p><h2>The Connection between Trauma and Anxiety</h2><p>Trauma contributes to GAD in several ways. Repeated exposure to stress hormones alters brain chemistry, increasing anxiety sensitivity. Traumatic experiences condition the body to respond to harmless stimuli as threats. Avoidance behaviours and hypervigilance then create a self-reinforcing cycle of anxiety that can be challenging to break.</p><h2>Breaking the Cycle: Effective Treatment Options</h2><p>Effective treatment options include cognitive-behavioural therapy (CBT), which addresses negative thought patterns; EMDR (Eye Movement Desensitisation and Reprocessing), which reduces the distressing impact of traumatic memories; and mindfulness-based interventions like meditation and deep breathing.</p><h2>Seeking Support</h2><p>If you\'re struggling with GAD or suspect a link to past trauma, it\'s essential to seek professional help from a qualified therapist who can provide a safe space, help develop coping strategies, and guide you through recovery.</p>',
                'keyTakeaways' => "Trauma can contribute to the development of generalised anxiety disorder (GAD).\nNeurobiological changes, conditioned fear response, and self-perpetuating cycles all play a role in the trauma-anxiety link.\nEffective treatment options include cognitive-behavioural therapy (CBT), EMDR, and mindfulness-based interventions.\nA qualified therapist can provide support and guidance throughout the healing process.\nAddressing trauma is crucial for managing GAD symptoms effectively.",
                'faqs'         => [
                    ['question' => 'What are some common signs of GAD?',                         'answer' => 'Excessive worry, restlessness, irritability, difficulty concentrating, sleep disturbances, and physical symptoms like headaches or muscle tension can be indicative of GAD.'],
                    ['question' => 'Can CBT help with trauma-related anxiety?',                  'answer' => 'Yes, CBT is often effective in addressing negative thought patterns and behaviours associated with trauma, which can contribute to GAD symptoms.'],
                    ['question' => 'How does EMDR work?',                                        'answer' => 'EMDR involves eye movements or other forms of stimulation that help the brain process traumatic memories, reducing their distressing impact on daily life.'],
                    ['question' => 'What is mindfulness-based stress reduction (MBSR)?',         'answer' => 'MBSR combines elements of yoga, meditation, and deep breathing to help manage anxiety symptoms by promoting relaxation and increasing self-awareness.'],
                    ['question' => 'Why is seeking professional help important?',                 'answer' => 'A qualified therapist can provide guidance on coping strategies, offer a safe space for sharing traumatic experiences, and facilitate the healing process.'],
                    ['question' => 'What if I\'m unsure whether my symptoms relate to trauma?',  'answer' => 'Consulting with a mental health professional is recommended. They can help clarify your situation and recommend appropriate treatment options.'],
                ],
            ],

            'claude' => [
                'title'        => '[CLAUDE] Understanding the link between trauma and generalised anxiety',
                'intro'        => 'Persistent, hard-to-explain anxiety often has roots in unresolved trauma — even when the conscious mind has moved on.',
                'body'         => '<h2>How Trauma Shapes the Anxious Mind</h2><p>Trauma isn\'t only what happens during a single catastrophic event. For many people, it accumulates quietly — through years of emotional neglect, unpredictable relationships, chronic stress, or experiences that were never fully acknowledged. What these experiences share is their impact on the nervous system: they teach the body that the world is fundamentally unsafe.</p><p>Generalised anxiety disorder (GAD) is characterised by persistent, wide-ranging worry that feels difficult to control. Unlike a phobia anchored to a specific trigger, GAD-related anxiety floats — attaching itself to work, relationships, health, finances, and the future. This diffuse quality is often a clue that something deeper than circumstance is driving it.</p><h2>The Nervous System Doesn\'t Forget</h2><p>When we experience something overwhelming, the brain\'s threat-detection system — the amygdala — encodes it as a survival memory. This is adaptive in the short term: your body learns to stay alert so the danger doesn\'t catch you off guard again. The problem is that this alert state can become the nervous system\'s default setting long after the original threat has passed.</p><p>People with trauma-driven anxiety often describe feeling on edge for no identifiable reason — hypervigilant, easily startled, unable to fully relax. The gap between what you feel and what you can logically account for is one of the most confusing aspects of living with unresolved trauma.</p><h2>Why Trauma and Anxiety Often Go Unconnected</h2><p>Many people arrive in therapy for anxiety without recognising trauma as a factor. Trauma doesn\'t always look like a single dramatic event — it can be the accumulated effect of growing up in an emotionally unstable household, experiencing chronic dismissal, or navigating prolonged uncertainty. Because these experiences feel ordinary or hard to name, people minimise them.</p><p>There\'s also the dissociative aspect of trauma memory. Sometimes the emotional weight of an experience becomes separated from the conscious recollection — you may not feel distressed when you think about the event, but your body responds as though you do.</p><h2>What the Anxiety Is Protecting You From</h2><p>One of the most important reframes in trauma-informed therapy is understanding anxiety not as a malfunction but as a protective strategy. The worry, the catastrophising, the constant mental scanning — these are the mind\'s attempt to feel in control of an environment that once felt uncontrollable. Understanding that your anxiety has a logic — even if it\'s now outdated — is often the first step toward relating to it differently.</p><h2>How Therapy Can Help</h2><p>Trauma-informed approaches such as EMDR, somatic therapy, and trauma-focused CBT work directly with the nervous system rather than just the content of anxious thoughts. In therapy, you might explore early experiences that shaped your sense of safety, learn to recognise the body\'s signals as information rather than alarm, and gradually build the capacity to tolerate uncertainty without it escalating into persistent worry.</p>',
                'keyTakeaways' => "Generalised anxiety often has roots in unresolved trauma, even when that trauma isn't obvious or dramatic.\nTrauma teaches the nervous system that danger is constant — GAD can be the long-term result of that learned state.\nThe anxious mind is often protecting you from feelings connected to past experience, not present reality.\nTrauma and anxiety frequently go unlinked because trauma can be cumulative, subtle, or dissociated from conscious memory.\nTrauma-informed therapy addresses the nervous system, not just anxious thoughts — and offers more lasting relief.",
                'faqs'         => [
                    ['question' => 'Can childhood experiences cause anxiety in adulthood?',               'answer' => 'Yes. Childhood experiences — particularly those involving instability, emotional neglect, or unpredictability — shape the nervous system\'s baseline sense of safety. When that baseline is set to "threat," generalised anxiety in adulthood is a common result, even when the original experiences feel distant.'],
                    ['question' => 'How do I know if my anxiety is trauma-related?',                     'answer' => 'Signs include anxiety that feels disproportionate to your current circumstances, a persistent sense of dread without a clear cause, hypervigilance, difficulty relaxing, and a long history of worry that hasn\'t responded well to standard anxiety management techniques.'],
                    ['question' => 'What is the difference between PTSD and trauma-driven generalised anxiety?', 'answer' => 'PTSD typically involves intrusive re-experiencing of a specific traumatic event. Trauma-driven GAD is more diffuse — the anxiety spreads across many areas of life rather than linking back to one identifiable event. Many people with GAD have experienced trauma without meeting the full clinical criteria for PTSD.'],
                    ['question' => 'Can therapy really help if I\'ve had anxiety for years?',            'answer' => 'Yes. Long-standing anxiety often reflects an unprocessed nervous system response rather than a fixed personality trait. Trauma-informed therapies work at a deeper level than standard anxiety management and can produce meaningful change even after years of symptoms.'],
                    ['question' => 'Do I need to remember my trauma clearly for therapy to work?',       'answer' => 'No. Some trauma-informed approaches — particularly somatic and body-based therapies — work with the nervous system directly, without requiring detailed narrative recall. The body holds trauma even when conscious memory is incomplete or absent.'],
                    ['question' => 'Where can I find a trauma-informed therapist for anxiety in the UK?','answer' => 'Our directory lists qualified counsellors and therapists across the UK who specialise in trauma and anxiety. You can filter by location, therapy type, and whether they offer online sessions.'],
                ],
            ],
        ];
    }

    /**
     * Saves three AI comparison drafts (qwen, llama, claude) into Craft.
     */
    public function actionSaveDrafts(): int
    {
        /** @var \craft\services\Entries $entriesService */
        $entriesService = Craft::$app->entries;
        $section        = $entriesService->getSectionByHandle('articles');
        $entryType      = $entriesService->getEntryTypeByHandle('article');

        if (!$section || !$entryType) {
            $this->stderr("Could not find 'articles' section or 'article' entry type.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        foreach ($this->getDrafts() as $key => $data) {
            $this->stdout("Saving {$key} draft... ");

            $entry            = new Entry();
            $entry->sectionId = $section->id;
            $entry->typeId    = $entryType->id;
            $entry->title     = $data['title'];
            $entry->enabled   = false;

            $entry->setFieldValues([
                'intro'        => $data['intro'],
                'body'         => $data['body'],
                'keyTakeaways' => $data['keyTakeaways'],
            ]);

            if (!Craft::$app->elements->saveElement($entry)) {
                $this->stderr("FAILED: " . json_encode($entry->getFirstErrors()) . "\n");
                continue;
            }

            // Build FAQ Matrix data
            $faqData = [];
            foreach ($data['faqs'] as $faq) {
                $faqData[] = [
                    'type'   => 'faq',
                    'fields' => [
                        'question' => $faq['question'],
                        'answer'   => $faq['answer'],
                    ],
                ];
            }
            $entry->setFieldValues(['faqs' => $faqData]);
            Craft::$app->elements->saveElement($entry);

            $url = $entry->getUrl() ?? '(check section URL settings)';
            $cp  = UrlHelper::cpUrl("entries/articles/{$entry->id}");

            $this->stdout("OK (ID: {$entry->id})\n");
            $this->stdout("  CP:      {$cp}\n");
            $this->stdout("  Preview: {$url}\n\n");
        }

        $this->stdout("Done. Run 'php craft article/delete-drafts' when finished reviewing.\n");
        return ExitCode::OK;
    }

    /**
     * Deletes the three comparison drafts by title prefix.
     */
    public function actionDeleteDrafts(): int
    {
        $prefixes = ['[QWEN]', '[LLAMA]', '[CLAUDE]'];
        $deleted  = 0;

        foreach ($prefixes as $prefix) {
            $entries = Entry::find()->title("{$prefix}*")->status(null)->all();
            foreach ($entries as $entry) {
                Craft::$app->elements->deleteElement($entry);
                $this->stdout("Deleted: {$entry->title}\n");
                $deleted++;
            }
        }

        $this->stdout($deleted ? "Done — {$deleted} draft(s) deleted.\n" : "No matching drafts found.\n");
        return ExitCode::OK;
    }
}
