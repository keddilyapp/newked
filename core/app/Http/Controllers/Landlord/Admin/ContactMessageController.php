<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:form-builder');
    }

    public function index()
    {
        $all_contact_messages = ContactMessage::latest()->get();
        return view('landlord.admin.contact.contact-message',compact('all_contact_messages'));
    }

    public function view($id)
    {
       if(!empty($id)){
           $message = ContactMessage::find($id);
           if($message->status == 1){
            ContactMessage::where('id',$id)->update(['status' => 0]);
           }
       }
        return view('landlord.admin.contact.contact-message-view',compact('message'));
    }

    public function delete(Request $request,$id){
        ContactMessage::findOrFail($id)->delete();
        return response()->danger(ResponseMessage::delete());
    }

    public function bulk_action(Request $request){

        ContactMessage::whereIn('id',$request->ids)->delete();
        return redirect()->back()->with([
            'msg' => __('Contact Message Delete Success...'),
            'type' => 'danger'
        ]);
    }
}
