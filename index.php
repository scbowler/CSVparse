<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Tracker Info</title>
</head>
<body>
    <h1>Student Tracker Parser</h1>

    <form action="parse.php" method="post">
        <textarea name="csvFile" id="csv" cols="30" rows="10" placeholder="Copy csv data here"></textarea>
        <label for="prototype">Prototypes</label>
        <input type="radio" name="action" id="prototype" value="prototype">
        <label for="rta">Remote TA</label>
        <input type="radio" name="action" id="rta" value="rta">
        <button>Submit</button>
    </form>
</body>
</html>