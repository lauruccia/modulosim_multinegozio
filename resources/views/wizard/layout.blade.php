<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attivazione SIM</title>
    <style>
        body{
            margin:0;
            font-family: Arial, sans-serif;
            background:#f3f3f5;
            color:#1f2937;
        }
        .topbar{
            background:#fff;
            padding:20px 40px;
            box-shadow:0 1px 4px rgba(0,0,0,.06);
        }
        .logo{
            font-size:32px;
            font-weight:700;
            color:#19a7e0;
        }
        .container{
            max-width:1100px;
            margin:40px auto;
            padding:0 20px;
        }
        .card{
            background:#fff;
            border-radius:18px;
            box-shadow:0 6px 25px rgba(0,0,0,.06);
            padding:40px;
        }
        .steps{
            display:flex;
            justify-content:center;
            gap:14px;
            margin-bottom:30px;
            flex-wrap:wrap;
        }
        .step{
            width:38px;
            height:38px;
            border-radius:999px;
            border:2px solid #cfd4dc;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#fff;
            color:#7b8794;
            font-weight:700;
        }
        .step.active{
            border-color:#1db5ea;
            background:#1db5ea;
            color:#fff;
        }
        h1{
            text-align:center;
            margin-bottom:35px;
            font-size:36px;
        }
        .grid{
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:20px;
        }
        .field{
            display:flex;
            flex-direction:column;
        }
        .field label{
            font-size:14px;
            margin-bottom:8px;
            color:#6b7280;
        }
        .field input, .field select{
            height:48px;
            border:none;
            border-bottom:2px solid #d1d5db;
            background:transparent;
            font-size:16px;
            outline:none;
        }
        .field input:focus, .field select:focus{
            border-bottom-color:#1db5ea;
        }
        .full{
            grid-column:1 / -1;
        }
        .checks{
            margin-top:30px;
            display:flex;
            flex-direction:column;
            gap:16px;
        }
        .actions{
            margin-top:40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .btn{
            border:none;
            border-radius:999px;
            padding:14px 28px;
            font-size:16px;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
        }
        .btn-back{
            background:#fff;
            color:#374151;
            border:1px solid #d1d5db;
        }
        .btn-next{
            background:#61c7f0;
            color:#fff;
        }
        .error{
            color:#dc2626;
            font-size:13px;
            margin-top:6px;
        }
        @media(max-width:900px){
            .grid{
                grid-template-columns:1fr;
            }
            h1{
                font-size:28px;
            }
            .card{
                padding:24px;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="logo">OPTIMA</div>
    </div>

    <div class="container">
        <div class="steps">
    @foreach($steps as $i => $s)
        @php
            $currentIndex = array_search($step, $steps);
            $stepIndex = array_search($s, $steps);
        @endphp

        <div class="step {{ $stepIndex <= $currentIndex ? 'active' : '' }}">{{ $i + 1 }}</div>
    @endforeach
</div>

        <div class="card">
            @yield('content')
        </div>
    </div>
</body>
</html>
