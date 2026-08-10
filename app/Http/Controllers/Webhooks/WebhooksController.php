<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\VideoUpload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{

    public function videoStatusUpdate(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Kinescope webhook', [
                'message' => $payload
            ]);
            if (($payload['event'] ?? null) !== 'media.update.status') {
                Log::error('Kinescope webhook error', [
                    'message' => $payload['event']
                ]);
                throw new Exception($payload['event']);
            }
            $data = $payload['data'] ?? [];
            $kinescopeId = $data['id'] ?? null;
            $status      = $data['status'] ?? null;
            $message     = $data['message'] ?? null;

            if (!$kinescopeId || !$status) {
                Log::error('Kinescope webhook error', [
                    'message' => $payload['event'],
                ]);
                throw new Exception("Kinescope webhook error" . $status);
            }
            if ($status === 'done') {
                $video = VideoUpload::where('source_id', $kinescopeId)->first();
                if ($video) {
                    $video->update([
                        'status' => 'Done',
                    ]);
                } else {
                    throw new Exception("Invalid video");
                }
            }
        } catch (Exception $e) {
            Log::error('Kinescope webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
