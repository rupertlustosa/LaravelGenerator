<?php


namespace Rlustosa\LaravelGenerator\Http\Controllers;


use Illuminate\Routing\Controller;
use Illuminate\View\View;

class LustosaGenerateController extends Controller
{

    /**
     * Show the dashboard.
     *
     * @return View
     */
    public function index()
    {

        return view('lustosa-generator::layout')
            ->with('telescopeScriptVariables', [
                'path' => 'rlustosa',
                'timezone' => config('app.timezone'),
                'recording' => true,
            ]);
    }
}