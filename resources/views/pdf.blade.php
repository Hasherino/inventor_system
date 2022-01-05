<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body{
            font-family: 'Roboto Condensed', sans-serif;
        }
        h1 {
            text-align: center;
            font-weight: 400;
            margin: .3em;
            color: #333333;
            font-size: 30px;
        }
        h2{
            font-weight: 400;
            font-size: 28px;
            color: #333333;
            margin: .7em 0 .5em;
        }
        header {
            border-bottom: solid 2px #C5C5C5;
        }
        #icon {
            height: 35px;
        }
        table {
            border-spacing: 0;
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: #a7a7a7 solid 1px;
            padding: .5em;
        }
        td {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
        }
        th {
            font-weight: 400;
            font-size: 16px;
            text-align: left;
            letter-spacing: .04em;
        }
        .info {
            margin-top: 1em;
        }
        .info th {
            width: 140px;
        }
        footer{
            text-align: right;
        }
    </style>
</head>

<body>
    <header>
        <img id="icon" src="{{ resource_path("teltonika_logo_blue.png") }}" alt="">
        <h1>{{ $gear->name }}</h1>
    </header>

    <div class="container">
        <table class="info">
            <tr>
                <th >Aprašymas:</th>
                <td>{{ $gear->description }}</td>
            </tr>
            <tr>
                <th>Savininkas:</th>
                <td>{{ $gear->owner }}</td>
            </tr>
            <tr>
                <th>Kodas:</th>
                <td>{{ $gear->code }}</td>
            </tr>
            <tr>
                <th>Serijos Numeris:</th>
                <td>{{ $gear->serial_number }}</td>
            </tr>
            <tr>
                <th>Vieneto Kaina:</th>
                <td>{{ $gear->unit_price }}</td>
            </tr>
        </table>

        <h2>Istorijos Išrašas</h2>
        <table>
            <tr>
                <th>Įvykio data</th>
                <th>Savininkas</th>
                <th>Kas?</th>
                <th>Veiksmas</th>
                <th>Kam?</th>
            </tr>
            @foreach($history as $row)
                <tr>
                    <td>{{ $row->created_at }}</td>
                    <td>{{ $row->owner }}</td>
                    <td>{{ $row->sender }}</td>
                    @if($row->event == 0)
                        <td>Paskolino</td>
                    @elseif($row->event == 1)
                        <td>Grąžino</td>
                    @elseif($row->event == 2)
                        <td>Atidavė</td>
                    @elseif($row->event == 3)
                        <td>Ištrynė</td>
                    @endif
                    <td>{{ $row->user }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
