<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FcmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendMeetingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $meetingId;
    protected $notificationType;
    protected $title;
    protected $body;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, int $meetingId, string $notificationType, string $title, string $body, array $data = [])
    {
        $this->userId = $userId;
        $this->meetingId = $meetingId;
        $this->notificationType = $notificationType;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(FcmService $fcmService): void
    {
        try {
            $results = $fcmService->sendToUser($this->userId, $this->title, $this->body, $this->data);

            // Update notification status in database
            if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
                $hasSuccess = !empty($results) && in_array(true, $results);
                
                // If reminder_id is provided in data, update by specific ID
                // Otherwise, update by meeting_id, user_id, and notification_type
                $query = DB::table('meeting_fcm_notifications')
                    ->where('meeting_id', $this->meetingId)
                    ->where('user_id', $this->userId)
                    ->where('notification_type', $this->notificationType);
                
                if (isset($this->data['reminder_id'])) {
                    $query->where('id', $this->data['reminder_id']);
                }
                
                // Update status - allow updating if status is 'sent' (may have been set by scheduler)
                // This handles cases where status was set immediately in scheduler
                $query->whereIn('status', ['pending', 'sent'])
                    ->update([
                        'status' => $hasSuccess ? 'sent' : 'failed',
                        'sent_at' => DB::raw('COALESCE(sent_at, NOW())'), // Keep existing sent_at if already set
                        'updated_at' => now(),
                    ]);
            }

            Log::info('Meeting notification job completed', [
                'user_id' => $this->userId,
                'meeting_id' => $this->meetingId,
                'type' => $this->notificationType
            ]);

        } catch (\Exception $e) {
            Log::error('Meeting notification job failed', [
                'user_id' => $this->userId,
                'meeting_id' => $this->meetingId,
                'error' => $e->getMessage()
            ]);

            // Update status to failed
            if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
                $query = DB::table('meeting_fcm_notifications')
                    ->where('meeting_id', $this->meetingId)
                    ->where('user_id', $this->userId)
                    ->where('notification_type', $this->notificationType);
                
                if (isset($this->data['reminder_id'])) {
                    $query->where('id', $this->data['reminder_id']);
                }
                
                $query->where('status', 'pending')
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_at' => now(),
                    ]);
            }
        }
    }
}
