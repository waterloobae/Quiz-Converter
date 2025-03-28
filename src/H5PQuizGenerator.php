<?php
namespace Waterloobae\QuizConverter;
use ZipArchive;
use Exception;

class H5PQuizGenerator {
    private $questions;
    private $h5pDir;
    private $outputFile;

    public function __construct(array $questions, $outputFile = "quiz.h5p") {
        $this->questions = $questions;
        $this->h5pDir = sys_get_temp_dir() . "/h5p_quiz_" . uniqid();
        $this->outputFile = $outputFile;

        if (!mkdir($this->h5pDir, 0777, true)) {
            throw new Exception("Failed to create temp directory.");
        }
    }

    public function generate() {
        $content = $this->buildContentJson();
        $this->saveJsonFile("content/content.json", $content);
        $this->addMetadata();
        $this->zipH5P();
    }

    private function buildContentJson() {
        $questionsArray = [];

        foreach ($this->questions as $question) {
            if ($question['type'] === 'multiple_choice') {
                $questionsArray[] = $this->createMultipleChoice($question);
            } elseif ($question['type'] === 'blank') {
                $questionsArray[] = $this->createNumericInput($question);
            }
        }

        return [
            "introPage" => [
                "showIntroPage" => false,
                "startButtonText" => "Start Quiz",
                "introduction" => "Welcome to the quiz! Please answer the following questions."
            ],
            "progressType" => "dots",
            "passPercentage" => 50,
            "questions" => $questionsArray,
            "disableBackwardsNavigation" => false,
            "randomQuestions" => false,
            "endGame" => [
                "showResultPage" => true,
                "showSolutionButton" => true,
                "showRetryButton" => true,
                "noResultMessage" => "Finished",
                "message" => "Your result: ",
                "scoreBarLabel" => "You got @finals out of @totals points",
                "overallFeedback" => [
                    "from" => 0,
                    "to" => 100
                ],
                "solutionButtonText" => "Show solution",
                "retryButtonText" => "Retry",
                "finishButtonText" => "Finish",
                "submitButtonText" => "Submit",
                "showAnimations" => false,
                "skippable" => false,
                "skipButtonText" => "Skip video"
            ],
            "override" => [
                "checkButton" => true
            ],
            "texts" => [
                "prevButton" => "Previous question",
                "nextButton" => "Next question",
                "finishButton" => "Finish",
                "submitButton" => "Submit",
                "textualProgress" => "Question => @current of @total questions",
                "jumpToQuestion" => "Question %d of %total",
                "questionLabel" => "Question",
                "readSpeakerProgress" => "Question @current of @total",
                "unansweredText" => "Unanswered",
                "answeredText" => "Answered",
                "currentQuestionText" => "Current question",
                "navigationLabel" => "Questions",
                "questionSetInstruction" => "Choose question to display",
            ]
        ];
    }

    private function createMultipleChoice($question) {
        return [
            "library" => "H5P.MultiChoice 1.16",
            "params" => [
                "question" => $question['text'],
                "answers" => array_map(function($choice) {
                    return [
                        "text" => $choice["text"],
                        "correct" => $choice["correct"]
                    ];
                }, $question['choices']),
                "l10n" => ["checkAnswer" => "Check", "submitAnswer" => "Submit"],
                "showSolutions" => true,  // Enable solution display
                "solution" => ["text" => $question['solution'] ?? "No explanation available."]
            ]
        ];
    }

    private function createNumericInput($question) {
        return [
            "library" => "H5P.Blanks 1.14",
            "params" => [
                "questions" => [ $question['text']. "<br><br>*" . $question['answer'] . "*" 
                ],
                "behaviour" => [
                    "enableRetry" => true,
                    "enableSolutionsButton" => true
                ],
                "solution" => ["text" => $question['solution'] ?? "No explanation available."]
            ]
        ];
    }

    private function addMetadata() {
        $this->saveJsonFile("h5p.json", [
            "title" => "Generated Quiz",
            "language" => "en",
            "mainLibrary" => "H5P.QuestionSet",
            "license" => "U",
            "defaultLanguage" => "en",
            "embedTypes" => [
              "div"
            ],
            "preloadedDependencies" => [
                ["machineName" => "H5P.QuestionSet", "majorVersion" => 1, "minorVersion" => 20],
                ["machineName" => "H5P.MultiChoice", "majorVersion" => 1, "minorVersion" => 16],
                ["machineName" => "H5P.Blank", "majorVersion" => 1, "minorVersion" => 14],
                ["machineName" => "H5P.MathDisplay", "majorVersion" => 1, "minorVersion" => 0]
            ],
            "preloadedJS" => [
                ["path" => "https://cdnjs.cloudflare.com/ajax/libs/mathjax/3.2.2/es5/tex-mml-chtml.js"]
            ],
            "extraTitle" => "Testing-Question-Set"
        ]);
    }

    private function saveJsonFile($filename, $data) {
        $filePath = "{$this->h5pDir}/$filename";
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function zipH5P() {
        $zip = new ZipArchive();
        if ($zip->open($this->outputFile, ZipArchive::CREATE) === true) {
            $this->addFilesToZip($zip, $this->h5pDir);
            $zip->close();
        } else {
            throw new Exception("Failed to create H5P file.");
        }
    }

    private function addFilesToZip($zip, $folder, $basePath = "") {
        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file === "." || $file === "..") continue;
            $filePath = "$folder/$file";
            $relativePath = $basePath . $file;
            if (is_dir($filePath)) {
                $this->addFilesToZip($zip, $filePath, $relativePath . "/");
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
}

// Example Usage:
/**
$quizQuestions = [
    [
        "type" => "multiple_choice",
        "text" => "What is \\(x^2\\) when \\(x = 2\\)?",
        "choices" => [
            ["text" => "\\(2\\)", "correct" => false],
            ["text" => "\\(4\\)", "correct" => true],
            ["text" => "\\(8\\)", "correct" => false]
        ],
        "solution" => "Since \\(x = 2\\), we calculate \\(2^2 = 4\\)."
    ],
    [
        "type" => "numeric",
        "text" => "Solve for \\(x\\) in \\(x + 3 = 5\\)",
        "answer" => 2,
        "solution" => "Subtract 3 from both sides: \\(x = 5 - 3 = 2\\)."
        ]
    ];
    
    $quizGenerator = new H5PQuizGenerator($quizQuestions);
    $quizGenerator->generate();
    
    echo "Quiz H5P file with solutions created successfully!";
    */