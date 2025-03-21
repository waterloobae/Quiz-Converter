<?php

class MoodleQuizGenerator
{
    private $questions;
    private $xml;

    public function __construct(array $questions)
    {
        $this->questions = $questions;
        $this->xml = new DOMDocument("1.0", "UTF-8");
        $this->xml->formatOutput = true;
    }

    public function generateXML()
    {
        $quiz = $this->xml->createElement("quiz");
        $this->xml->appendChild($quiz);

        foreach ($this->questions as $question) {
            if ($question['type'] === 'multiple_choice') {
                $quiz->appendChild($this->generateMultipleChoice($question));
            } elseif ($question['type'] === 'numeric') {
                $quiz->appendChild($this->generateNumericQuestion($question));
            }
        }

        $filename = "moodle_quiz.xml";
        $this->xml->save($filename);
        echo "Moodle Quiz XML file generated: $filename\n";
    }

    private function generateMultipleChoice($question)
    {
        $questionNode = $this->xml->createElement("question");
        $questionNode->setAttribute("type", "multichoice");

        // Question Name
        $nameNode = $this->xml->createElement("name");
        $textNode = $this->xml->createElement("text", htmlspecialchars($question['question']));
        $nameNode->appendChild($textNode);
        $questionNode->appendChild($nameNode);

        // Question Text
        $questionTextNode = $this->xml->createElement("questiontext");
        $questionTextNode->setAttribute("format", "html");
        $textNode = $this->xml->createElement("text", htmlspecialchars($this->wrapLatex($question['question'])));
        $questionTextNode->appendChild($textNode);
        $questionNode->appendChild($questionTextNode);

        // Answers
        foreach ($question['answers'] as $answer) {
            $answerNode = $this->xml->createElement("answer");
            $answerNode->setAttribute("fraction", $answer['correct'] ? "100" : "0");

            $textNode = $this->xml->createElement("text", htmlspecialchars($this->wrapLatex($answer['text'])));
            $answerNode->appendChild($textNode);

            // Feedback (Solution)
            if (!empty($question['solution'])) {
                $feedbackNode = $this->xml->createElement("feedback");
                $feedbackTextNode = $this->xml->createElement("text", htmlspecialchars($this->wrapLatex($question['solution'])));
                $feedbackNode->appendChild($feedbackTextNode);
                $answerNode->appendChild($feedbackNode);
            }

            $questionNode->appendChild($answerNode);
        }

        return $questionNode;
    }

    private function generateNumericQuestion($question)
    {
        $questionNode = $this->xml->createElement("question");
        $questionNode->setAttribute("type", "numerical");

        // Question Name
        $nameNode = $this->xml->createElement("name");
        $textNode = $this->xml->createElement("text", htmlspecialchars($question['question']));
        $nameNode->appendChild($textNode);
        $questionNode->appendChild($nameNode);

        // Question Text
        $questionTextNode = $this->xml->createElement("questiontext");
        $questionTextNode->setAttribute("format", "html");
        $textNode = $this->xml->createElement("text", htmlspecialchars($this->wrapLatex($question['question'])));
        $questionTextNode->appendChild($textNode);
        $questionNode->appendChild($questionTextNode);

        // Correct Answer
        $answerNode = $this->xml->createElement("answer");
        $answerNode->setAttribute("fraction", "100");

        $textNode = $this->xml->createElement("text", $question['answer']);
        $answerNode->appendChild($textNode);

        // Solution Feedback
        if (!empty($question['solution'])) {
            $feedbackNode = $this->xml->createElement("feedback");
            $feedbackTextNode = $this->xml->createElement("text", htmlspecialchars($this->wrapLatex($question['solution'])));
            $feedbackNode->appendChild($feedbackTextNode);
            $answerNode->appendChild($feedbackNode);
        }

        $questionNode->appendChild($answerNode);

        return $questionNode;
    }

    private function wrapLatex($text)
    {
        return str_replace(["$", "\\("], ["\\(", "\\["], $text);
    }
}

// Example usage
$questions = [
    [
        "type" => "multiple_choice",
        "question" => "What is the capital of France?",
        "answers" => [
            ["text" => "Paris", "correct" => true],
            ["text" => "Berlin", "correct" => false],
            ["text" => "Madrid", "correct" => false]
        ],
        "solution" => "Paris is the capital of France."
    ],
    [
        "type" => "numeric",
        "question" => "Solve: $5 + 7$",
        "answer" => 12,
        "solution" => "The correct answer is 12 because $5 + 7 = 12$."
    ]
];

$generator = new MoodleQuizGenerator($questions);
$generator->generateXML();