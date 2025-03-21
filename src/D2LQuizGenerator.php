<?php

class D2LQuizGenerator {
    private $questions;

    public function __construct(array $questions) {
        $this->questions = $questions;
    }

    public function generateXML() {
        $xml = new SimpleXMLElement('<quiz />');

        foreach ($this->questions as $index => $question) {
            $questionElem = $xml->addChild('question');
            $questionElem->addAttribute('type', 'multiple_choice');

            $textElem = $questionElem->addChild('text', htmlspecialchars($question['text']));

            $answersElem = $questionElem->addChild('answers');
            foreach ($question['choices'] as $choiceIndex => $choice) {
                $answerElem = $answersElem->addChild('answer', htmlspecialchars($choice));
                if ($choiceIndex == $question['correct_index']) {
                    $answerElem->addAttribute('correct', 'true');
                } else {
                    $answerElem->addAttribute('correct', 'false');
                }
            }

            // Add solution if provided
            if (isset($question['solution'])) {
                $solutionElem = $questionElem->addChild('solution', htmlspecialchars($question['solution']));
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
        'correct_index' => 1,
        'solution' => '2 + 2 equals 4 because it is basic arithmetic.'
    ],
    [
        'text' => 'What is the capital of France?',
        'choices' => ['Berlin', 'Paris', 'Madrid'],
        'correct_index' => 1,
        'solution' => 'Paris is the capital of France.'
    ]
];

$generator = new D2LQuizGenerator($questions);
$generator->saveToFile('d2l_quiz.xml');

echo "D2L Quiz XML file generated successfully.";
