<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\adminMail;
use App\Mail\clientMail;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OffersController extends Controller
{
    public function newOffer(Request $request)
    {

        $extraServices = $request->extraServices;
        array_shift($extraServices);

        $extra = "";
        foreach ($extraServices as $extraService) {
            if ($extraService == "7800" || $extraService == "780") {
                if ($request->lang == "ar") {
                    $extra .= "/[ frais en extra ( 7.800 MAD ) ]/";
                } else {
                    $extra .= "/[ frais en extra ( 7.80€  ) ]/";
                }
            } elseif ($extraService == "2500" || $extraService == "250") {
                if ($request->lang == "ar") {
                    $extra .= "/[ bibliothèque  en extra ( 2.500 MAD ) ]/";
                } else {
                    $extra .= "/[ bibliothèque  en extra ( 250€  ) ]/";
                }

            } elseif ($extraService == "1500" || $extraService == "150") {
                if ($request->lang == "ar") {
                    $extra .= "/[ transport  en extra ( 1.500 MAD ) ]/";
                } else {
                    $extra .= "/[ transport  en extra ( 150€  ) ]/";
                }
            } elseif ($extraService == "3500" || $extraService == "350") {
                if ($request->lang == "ar") {
                    $extra .= "/[ rapport  en extra ( 3.500 MAD ) ]/";
                } else {
                    $extra .= "/[ rapport  en extra ( 350€  ) ]/";
                }
            } elseif ($extraService == "2000" || $extraService == "200") {
                if ($request->lang == "ar") {
                    $extra .= "/[ personnele  en extra ( 2.000 MAD ) ]/";
                } else {
                    $extra .= "/[ personnele  en extra ( 200€  ) ]/";
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
        $order->langue = $request->lang;
        $order->min_eleves = $request->min_nbStudents;
        $order->max_eleves = $request->max_nbStudents;
        $order->nombre_eleves = $request->nb_students;
        $order->plan_choisie = $request->plan_name;
        $order->prix_total = $request->lang == 'ar' ? $request->total_price . "MAD" : $request->total_price . '‎€';
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
            "nb_students" => $order->nombre_eleves,
            'total_price' => $order->prix_total,
            "lien" => url("/admin/orders/" . $order->id),
            "extra" => $extra,
        ];

        Mail::to([$order->email])->send(new clientMail($mailDetails));
        Mail::to("sarah@connectivemarket.com")->send(new adminMail($mailDetails));

        return response()->json(['msg' => 'Votre order a été créée avec succès']);

    }

    public function ip_info($ip = null, $purpose = "location", $deep_detect = true)
    {
        $output = null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }

                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }

            }
        }

        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), null, strtolower(trim($purpose)));
        $support = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America",
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city" => @$ipdat->geoplugin_city,
                            "state" => @$ipdat->geoplugin_regionName,
                            "country" => @$ipdat->geoplugin_countryName,
                            "country_code" => @$ipdat->geoplugin_countryCode,
                            "continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode,
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1) {
                            $address[] = $ipdat->geoplugin_regionName;
                        }

                        if (@strlen($ipdat->geoplugin_city) >= 1) {
                            $address[] = $ipdat->geoplugin_city;
                        }

                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        return $output;
    }

    public function localCountry(Request $request)
    {
        // $ip = $request->ip();
        // $location = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));

        $localisation = file_get_contents("http://www.geoplugin.net/json.gp?ip=".$this->getRealIpAddr());
        return response()->json(['localisation' => json_decode($localisation)]);

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

}
