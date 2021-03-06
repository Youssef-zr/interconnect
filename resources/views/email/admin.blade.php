<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>hello</title>
    <style>
       .email{
           text-transform: capitalize;
           font-family:Verdana, Geneva, Tahoma, sans-serif;
           background:#ffff;
           padding:10px 15px;
           border:1px solid #eeee;
       }

       .email h3{
           color:#08acfd;
           font-weight:500;
           font-size:20px;
       }

       .email p{
        color:#333;
        font-size:15px;
        margin:5px 0 !important;
       }
       .email .color{
           color:#9bc31c
       }
       .email h5{
        margin-bottom:5px !important;
    }

    .email ul{
        margin-top:0px;
        list-style-type: square;
    }
    </style>
</head>
<body>

    <section class="email">
        <div class="header">
            <h3 class="title">Salut , Sarah</h3>
        </div>
        <div class="body">
            <div class="offer-name">
                <p>Un Client à Choisi La <span class="color">{{$details['offer_name']}}</span> </p>
            </div>
            <div class="nb-students">
                <p>Le Nombre Délèves choisi ( <span class="color">{{$details['nb_students']}}</span> élèves )</p>
            </div>
            @if (count($details['extra'])>0)
            <h5>Extra Services:</h5>
            <ul>
                @foreach ($details['extra'] as $extra)
                <li>{{$extra}}</li>
                @endforeach
            </ul>
            @endif
            <div class="total-price">
                <p>Le Montant Total De Cette offre ( <span class="color">{{$details['total_price']}}</span> )</p>
            </div>
        </div>
        <div class="footer">
            <p>
                Accèder Dirèctement A Lordre Via Ce Lien (<a href="{{ $details['lien'] }}">{{ $details['lien'] }}</a> )
            </p>
        </div>
        <img src="{{url('images/laxial.ico')}}" alt="laxial image">
    </section>

    </body>
</html>
