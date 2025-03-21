<?php

class CanvasQuizGenerator {
    private $questions;

    public function __construct(array $questions) {
        $this->questions = $questions;
    }

    public function generateXML() {
        $xml = new SimpleXMLElement('<questestinterop />');
        $assessment = $xml->addChild('assessment', '');
        $assessment->addAttribute('title', 'Canvas Quiz');

        $section = $assessment->addChild('section', '');

        foreach ($this->questions as $index => $question) {
            $item = $section->addChild('item');
            $item->addAttribute('ident', 'Q' . ($index + 1));
            $item->addAttribute('title', htmlspecialchars($question['text']));

            $presentation = $item->addChild('presentation');
            $material = $presentation->addChild('material');
            $mattext = $material->addChild('mattext', htmlspecialchars($question['text']));
            $mattext->addAttribute('texttype', 'text/html');

            $response_lid = $presentation->addChild('response_lid');
            $response_lid->addAttribute('ident', 'Q' . ($index + 1) . '_response');
            $response_lid->addAttribute('rcardinality', 'Single');

            $render_choice = $response_lid->addChild('render_choice');

            foreach ($question['choices'] as $choice_index => $choice) {
                $response_label = $render_choice->addChild('response_label');
                $response_label->addAttribute('ident', 'Q' . ($index + 1) . '_C' . ($choice_index + 1));
                $mattext = $response_label->addChild('material')->addChild('mattext', htmlspecialchars($choice));
                $mattext->addAttribute('texttype', 'text/html');
            }

            $resprocessing = $item->addChild('resprocessing');
            $outcomes = $resprocessing->addChild('outcomes');
            $outcomes->addChild('decvar')->addAttribute('maxvalue', '100');

            foreach ($question['choices'] as $choice_index => $choice) {
                if ($choice_index == $question['correct_index']) {
                    $respcondition = $resprocessing->addChild('respcondition');
                    $respcondition->addAttribute('continue', 'No');
                    $conditionvar = $respcondition->addChild('conditionvar');
                    $varequal = $conditionvar->addChild('varequal', 'Q' . ($index + 1) . '_C' . ($choice_index + 1));
                    $varequal->addAttribute('respident', 'Q' . ($index + 1) . '_response');
                    $respcondition->addChild('setvar', '100')->addAttribute('action', 'Set');
                }
            }
        }
        return $xml->asXML();
    }

    public function saveToFile($filename) {
        file_put_contents($filename, $this->generateXML());
    }
}

// Example usage
$questions = [
    [
        'text' => 'What is 2 + 2?',
        'choices' => ['3', '4', '5'],
        'correct_index' => 1
    ],
    [
        'text' => 'What is the capital of France?',
        'choices' => ['Berlin', 'Paris', 'Madrid'],
        'correct_index' => 1
    ]
];

$generator = new CanvasQuizGenerator($questions);
$generator->saveToFile('canvas_quiz.xml');

echo "Canvas Quiz XML file generated successfully.";
