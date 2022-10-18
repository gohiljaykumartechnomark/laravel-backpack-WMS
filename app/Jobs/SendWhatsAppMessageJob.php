<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;
use App\Models\BroadcastHasContact;
use App\Models\Contact;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 3;

    protected $message;


    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $log = \Log::channel('whatsapp');
        try {
            if (isset($this->message->broadcast_id)) {
                
                $sid    = getenv("TWILIO_AUTH_SID");
                $token  = getenv("TWILIO_AUTH_TOKEN");
                $wa_from= getenv("TWILIO_WHATSAPP_FROM");
                $twilio = new Client($sid, $token);

                $contactIds = BroadcastHasContact::where('broadcast_id',$this->message->broadcast_id)
                ->pluck('contact_id')->toArray();

                $contactList = Contact::whereIn('id',$contactIds)->get();

                foreach ($contactList as $key => $contact) {
                    $url = null;
                    $data = [];

                    $recipient = "+" . $contact->country_code . $contact->number;
                    $userName = $contact->name;
                    $timeStamp = date('Y-m-d h:i:s');
                    
                    $data['from'] = "whatsapp:$wa_from";
                    $data['body'] = $this->message->message;

                    if (!empty($this->message->path) && env('APP_ENV') != "local") {
                        $url = ($this->message->path); /*Image Attachment*/
                        $ext = pathinfo($url, PATHINFO_EXTENSION);
                        if (in_array($ext, ['jpg','jpeg','png'])) {
                            $data['MediaUrl'] = $url;
                        } else {
                            /*Send file individualy*/
                            $newData = [];
                            $newData['from'] = "whatsapp:$wa_from";
                            $newData['MediaUrl'] = $url;
                            $twilio->messages->create("whatsapp:$recipient",$data);      
                        }
                    // dd(pathinfo($url, PATHINFO_EXTENSION));
                    }
                    // dd($data);
                    $response = $twilio->messages->create("whatsapp:$recipient",$data);      
                    // dd($response->toArray());
                }
            }

        } catch (\Exception $ex) {
           $log->info('=========Exception block=========');
           $log->info($ex);
        } catch (\Throwable $ex) {
           $log->info('=========Throwable block=========');
           $log->info($ex);
        }
    }
}
