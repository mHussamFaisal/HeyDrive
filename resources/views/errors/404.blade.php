<!DOCTYPE html>
<html>
<head>
    <title>Page not found</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>
    html, body {
        height: 100%;
    }
    body {
        margin: 0;
        padding: 0;
        width: 100%;
        color: #B0BEC5;
        display: table;
        font-weight: 100;
        font-family: 'Lato', sans-serif;
    }
    .container {
        text-align: center;
        display: table-cell;
        vertical-align: middle;
    }
    .content {
        text-align: center;
        display: inline-block;
    }
    .title {
        font-size: 50px;
        margin-bottom: 40px;
    }
    .link {
        font-size: 24px;
        margin-bottom: 40px;
    }
    .link a {
        color: #000;
        text-decoration: none;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="title">Page not found</div>
            {{--<div class="link"><a href="{{ url('/') }}">Home</div>--}}
        </div>
    </div>
</body>
</html>
