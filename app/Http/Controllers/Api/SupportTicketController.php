<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Resources\SupportTicketCollection;
use Illuminate\Http\Request;
use App\Ticket;
use App\User;
use App\TicketReply;
use App\Mail\SupportMailManager;
use Mail;
use App\Upload;

class SupportTicketController extends BaseController
{
    public function index()
    {
        $cat = new SupportTicketCollection(Ticket::where('user_id', auth('api')->id())->get());
        return $this->sendResponse($cat, translate('categories'));
    }

    public function store(Request $request)
    {
        if ($request->hasFile('attachments')) {
            foreach ($request->attachments as $att) {
                $upload = new Upload;
                $upload->file_original_name = null;
                $arr = explode('.', $att->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0)
                        $upload->file_original_name .= $arr[$i];
                    else
                        $upload->file_original_name .= "." . $arr[$i];
                }
                $upload->file_name = $att->store('uploads/all');
                $upload->user_id = auth('api')->id();
                $upload->extension = strtolower($att->getClientOriginalExtension());
                if (isset($type[$upload->extension]))
                    $upload->type = $type[$upload->extension];
                else
                    $upload->type = "others";
                $upload->file_size = $att->getSize();
                $upload->save();
                $arrr[] = $upload->id;
            }
            $arrt = json_encode($arrr);
            $array = str_replace('[', '', $arrt);
            $array1 = str_replace(']', '', $array);
        }
        $ticket = new Ticket;
        $ticket->code = max(100000, (Ticket::latest()->first() != null ? Ticket::latest()->first()->code + 1 : 0)) . date('s');
        $ticket->user_id = auth('api')->id();
        $ticket->subject = $request->subject;
        $ticket->details = $request->details;
        if ($request->attachments != null)
            $ticket->files = $array1;
        else
            $ticket->files = null;
        if ($ticket->save()) {
            $this->send_support_mail_to_admin($ticket);
            return $this->sendResponse($ticket, translate('Ticket has been sent successfully.'));
        } else
            return $this->sendError('error ', translate('Something went wrong'));
    }

    public function ticket_replies(Request $request)
    {
        if ($request->hasFile('attachments')) {
            foreach ($request->attachments as $att) {
                $upload = new Upload;
                $upload->file_original_name = null;
                $arr = explode('.', $att->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0)
                        $upload->file_original_name .= $arr[$i];
                    else
                        $upload->file_original_name .= "." . $arr[$i];
                }
                $upload->file_name = $att->store('uploads/all');
                $upload->user_id = auth('api')->id();
                $upload->extension = strtolower($att->getClientOriginalExtension());
                if (isset($type[$upload->extension]))
                    $upload->type = $type[$upload->extension];
                else
                    $upload->type = "others";
                $upload->file_size = $att->getSize();
                $upload->save();
                $arrr[] = $upload->id;
            }
            $arrt = json_encode($arrr);
            $array = str_replace('[', '', $arrt);
            $array1 = str_replace(']', '', $array);
        }
        $ticket_reply = new TicketReply;
        $ticket_reply->ticket_id = $request->ticket_id;
        $ticket_reply->user_id = auth('api')->id();
        $ticket_reply->reply = $request->reply;
        if ($request->attachments != null)
            $ticket_reply->files = $array1;
        else
            $ticket_reply->files = null;
        $ticket_reply->ticket->viewed = 0;
        $ticket_reply->ticket->status = 'pending';
        $ticket_reply->ticket->save();
        if ($ticket_reply->save())
            return $this->sendResponse($ticket_reply, translate('Reply has been sent successfully'));
        else
            return $this->sendError('error ', translate('Something went wrong'));
    }

    public function send_support_mail_to_admin($ticket)
    {
        $array['view'] = 'emails.support';
        $array['subject'] = 'Support ticket Code is:- ' . $ticket->code;
        $array['from'] = env('MAIL_USERNAME');
        $array['content'] = 'Hi. A ticket has been created. Please check the ticket.';
        $array['link'] = route('support_ticket.admin_show', encrypt($ticket->id));
        $array['sender'] = $ticket->user->name;
        $array['details'] = $ticket->details;
        try {
            Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new SupportMailManager($array));
        } catch (\Exception $e) {}
    }
}
