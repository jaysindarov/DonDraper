<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'id'     => $request->user()->id,
            'name'   => $request->user()->name,
            'email'  => $request->user()->email,
            'plan'   => $request->user()->plan ?? 'free',
            'credits' => $request->user()->credits,
        ]);
    }

    public function credits(Request $request)
    {
        return response()->json([
            'credits' => $request->user()->credits,
        ]);
    }
}
