<?php


namespace Rlustosa\LaravelGenerator\Http\Controllers;


use Illuminate\Routing\Controller;

class LustosaGenerateController extends Controller
{

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
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