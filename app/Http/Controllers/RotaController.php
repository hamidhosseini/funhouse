<?php

namespace App\Http\Controllers;

use App\Models\Rota;
use Illuminate\Http\Request;
use App\Services\RotaService;

class RotaController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rota  $rota
     * @return \Illuminate\Http\Response
     */
    public function show(Rota $rota)
    {
        $rota = new RotaService(1);
        return $rota->calculateManningMinutes();
    }
}
