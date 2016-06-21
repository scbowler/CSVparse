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
        <div class="pick-roster">
            <label for="roster"></label>
            <select name="roster" id="roster">
                <option value="" disabled selected>Choose Cohort Roster</option>
            </select>
        </div>
        <div class="radio-contain hide">
            <label for="prototype">Prototypes</label>
            <input type="radio" name="action" id="proto" value="prototype" checked>
            <label for="rta">Remote TA</label>
            <input type="radio" name="action" id="rta" value="rta">
            <label for="populate">Personalized Report</label>
            <input type="radio" name="action" id="populate" value="report">
            <label for="error">Error Check CSV</label>
            <input type="radio" name="action" id="error" value="error">
        </div>
        <div id="options-div" class="contain">
            <label for="start-date" class="rta">Start Date</label>
            <input type="date" name="start-date" id="start-date" class="rta">
            <label for="end-date" class="rta">End Date</label>
            <input type="date" name="end-date" id="end-date" class="rta">
        </div>
        <div class="btn-contain hide">
            <button id="btn" disabled>Submit</button>
        </div>
    </form>

</body>
</html>