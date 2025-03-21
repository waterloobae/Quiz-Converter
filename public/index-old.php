<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H5P Quiz Display</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- H5P Core Stylesheet -->
    <link rel="stylesheet" href="http://<?=$_SERVER['HTTP_HOST']?>/vendor/h5p/h5p-core/styles/h5p.css">

    <!-- H5P Core Scripts -->
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/vendor/h5p/h5p-core/js/h5p.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/vendor/h5p/h5p-core/js/h5p-event-dispatcher.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/vendor/h5p/h5p-core/js/h5p-content-type.js"></script>
</head>
<body>

    <h1>H5P Quiz</h1>

    <div class="h5p-container"></div>

    <script>
        // Ensure H5P.externalDispatcher is defined
        if (H5P.externalDispatcher) {
            // Handle the H5P content ready event
            H5P.externalDispatcher.on('contentReady', function () {
                console.log('H5P content is ready');
            });
        } else {
            console.error('H5P.externalDispatcher is undefined');
        }

        // Initialize H5P content
        H5P.newRunnable({
            library: "H5P.MultipleChoice 1.0", // Specify the library
            jsonContent: {}, // JSON content of the H5P
            params: {
                question: "What is the capital of France?",
                choices: [
                    {text: "Berlin", correct: false},
                    {text: "Madrid", correct: false},
                    {text: "Paris", correct: true},
                    {text: "Rome", correct: false}
                ],
                behaviour: {
                    enableRetry: true,
                    enableSolutionsButton: true
                }
            },
            metadata: {
                title: "Sample Quiz",
                contentId: 1, // Unique content ID
                container: document.querySelector('.h5p-container')
            }
        }, document.querySelector('.h5p-container'), {});
    </script>

</body>
</html>
