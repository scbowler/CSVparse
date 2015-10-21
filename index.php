<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Tracker Info</title>

    <link rel="stylesheet" href="assets/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="assets/main.js"></script>
</head>
<body>
    <h1>Student Tracker Parser</h1>

    <form action="actions/parse.php" method="post">
        <input type="file" name="File Upload" id="txtFileUpload" accept=".csv">
        <textarea name="csvFile" id="csv" cols="80" rows="3" placeholder="Copy csv data here -OR- Click button above to load a CSV file"></textarea>
        <div class="radio-contain">
            <label for="prototype">Prototypes</label>
            <input type="radio" name="action" id="proto" value="prototype" checked>
            <label for="rta">Remote TA</label>
            <input type="radio" name="action" id="rta" value="rta">
            <label for="populate">Populate Student List</label>
            <input type="radio" name="action" id="populate" value="report">
        </div>
        <div class="contain">
            <label for="maxProto" class="proto show">Total Prototypes Due</label>
            <input type="number" name="maxProto" id="maxProto" class="proto show" placeholder="Total prototypes">
            <div>
                <button type="button" id="auto-pop" class="proto show">Auto Populate Total Prototypes</button>
            </div>
            <label for="start-date" class="rta">Start Date</label>
            <input type="date" name="start-date" id="start-date" class="rta">
            <label for="end-date" class="rta">End Date</label>
            <input type="date" name="end-date" id="end-date" class="rta">
        </div>
        <div class="btn-contain">
            <button id="btn">Submit</button>
        </div>
    </form>

</body>
</html>