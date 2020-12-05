<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;
use App\Mail\sendMail;
use Illuminate\Support\Facades\Mail;

class OffersController extends Controller
{
    public function newOffer(Request $request)
    {
        $rules = [
            'society_name' => 'required|string|min:4',
            'client_email' => "required|email",
            'nb_students' => "required|integer|min:".$request->min_nbStudents,
        ];

        $niceNames = [
            "society_name" => "nom de societe",
            "client_email" => "email du client",
            "nb_students" => "nombre des éleves",
        ];

        $messages=[
            "min"=>"Le Nombre Minimum Des Elèves est: ".$request->min_nbStudents." Elève(s)"
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

        $order->save();

        $mailDetails = [
            "client_name"=>$order->nom,
            "offer_name"=>$order->plan_choisie,
            "nb_students"=>$order->nombre_eleves,
            'total_price'=>$order->prix_total
        ];

        Mail::to([$order->email,"sarah@connectivemarket.com"])->send(new sendMail($mailDetails));

        return response()->json(["order" => $order, 'msg' => 'Votre order a été créée avec succès']);

    }
}
