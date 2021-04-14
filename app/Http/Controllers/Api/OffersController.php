<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\adminMail;
use App\Mail\clientMail;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Plan;

class OffersController extends Controller
{
    public function newOffer(Request $request)
    {
        $currency = $request->country == "Morocco" ? "MAD" : "€";
        // plan price init
        $price_init = $request->extraServices[0];
        // extra services prices check prices and create extra by price
        $extraServices = $request->extraServices;
        array_shift($extraServices);

        // the extra modules sum
        $extra_modules_sum = array_sum($extraServices);

        $extra = "";
        foreach ($extraServices as $extraService) {
            if ($extraService == "7800" || $extraService == "780") {
                if ($request->country == "Morocco") {
                    $extra .= "/[ frais en extra ( 7.800 MAD ) ]/";
                } else {
                    $extra .= "/[ frais en extra ( 780€  ) ]/";
                }
            } elseif ($extraService == "2500" || $extraService == "250") {
                if ($request->country == "Morocco") {
                    $extra .= "/[ bibliothèque  en extra ( 2.500 MAD ) ]/";
                } else {
                    $extra .= "/[ bibliothèque  en extra ( 250€  ) ]/";
                }

            } elseif ($extraService == "1500" || $extraService == "150") {
                if ($request->country == "Morocco") {
                    $extra .= "/[ transport  en extra ( 1.500 MAD ) ]/";
                } else {
                    $extra .= "/[ transport  en extra ( 150€  ) ]/";
                }
            } elseif ($extraService == "3500" || $extraService == "350") {
                if ($request->country == "Morocco") {
                    $extra .= "/[ rapport  en extra ( 3.500 MAD ) ]/";
                } else {
                    $extra .= "/[ rapport  en extra ( 350€  ) ]/";
                }
            } elseif ($extraService == "2000" || $extraService == "200") {
                if ($request->country == "Morocco") {
                    $extra .= "/[ personnel en extra ( 2.000 MAD ) ]/";
                } else {
                    $extra .= "/[ personnel en extra ( 200€  ) ]/";
                }
            }
        }

        $rules = [
            'society_name' => 'required|string|min:4',
            'client_email' => "required|email",
            'nb_students' => "required|integer|min:" . $request->min_nbStudents,
        ];

        $niceNames = [
            "society_name" => "nom de societe",
            "client_email" => "email du client",
            "nb_students" => "nombre des éleves",
        ];

        $messages = [
            "min" => "Le Nombre Minimum Des Elèves est: " . $request->min_nbStudents . " Elève(s)",
        ];

        $data = $this->validate($request, $rules, $messages, $niceNames);

        $order = new Order();
        $order->nom = $request->society_name;
        $order->email = $request->client_email;
        $order->pays = $request->country;
        $order->min_eleves = $request->min_nbStudents;
        $order->max_eleves = $request->max_nbStudents;
        $order->nombre_eleves = $request->nb_students;
        $order->plan_choisie = $request->plan_name;
        $order->prix_total = $request->total_price . $currency;
        $order->formation_en_ligne = $request->formation_en_ligne == true ? "Oui" : "Non";
        $order->extra_services = $extra;

        $order->save();
        if ($extra != '') {
            $extra = trim($extra, '/ ');
            $extra = explode('//', $extra);
        } else {
            $extra = [];
        }

        $mailDetails = [
            "client_name" => $order->nom,
            "offer_name" => $order->plan_choisie,
            'total_price' => $order->prix_total,
            "lien" => url("/admin/orders/" . $order->id),
            "extra" => $extra,
            "price_init" => $price_init.$currency,
            "nb_students" => $order->nombre_eleves,
            "tarif" => $request->iosDonee,
            "modules_price" => $extra_modules_sum . $currency,
        ];

        Mail::to([$order->email])->send(new clientMail($mailDetails));
        Mail::to("yn-neinaa@hotmail.com")->send(new adminMail($mailDetails));

        return response()->json(['msg' => 'votre demande a été crée avec succès']);

    }

    public function localCountry(Request $request)
    {
        $localisation = file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $this->getRealIpAddr());
        return response()->json(['localisation' => json_decode($localisation)->geoplugin_countryName]);
    }

    public function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    public function get_plans(){
        $plans = Plan::all();
        return response()->json(['plans'=>$plans]);
    }
}
