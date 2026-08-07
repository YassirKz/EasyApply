<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            color: #1f2937;
            line-height: 1.7;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .letter-content {
            font-size: 15px;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="letter-content">
            {!! $lettreTexte !!}
        </div>
    </div>
</body>
</html>
