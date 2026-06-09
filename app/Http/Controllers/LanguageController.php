<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['en', 'km']), 404);
        session(['locale' => $locale]);
        return redirect()->back()->withHeaders(['Vary' => 'Accept-Language']);
    }
}
