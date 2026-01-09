<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin' || $user->role === 'agent') {
            $tickets = Ticket::all();
            return response()->json($tickets);
        }else{
            $tickets = Ticket::where('user_id', $user->id)->get();
            return response()->json($tickets);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          $validator = Validator::make($request->all(),[
            'title'=>'required|max:50',
            'description' => 'required'
          ]);
          if ($validator->failed()){
            return response()->json($validator->errors(),422);
          }

          $user = Auth::user(); // bejelentkezett user
          $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $user->id,
          ]);
          return response()->json(['ticket'=>$ticket],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return response()->json(['ticket'=>$ticket]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $request->validate([
            'status'=>'in:open,in_progress,closed'
        ]);
        $status = $request->status;
        if ($status == 'closed') {
            $ticket->update(['status'=>$status]);
        }else{
            if ($user->role != 'customer') {
                $ticket->update(['status'=>$status]);
            }
        }
        return response()->json(['ticket'=>$ticket]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->noContent();
    }
}
