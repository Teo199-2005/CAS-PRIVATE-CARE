<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    // Valid notification types from the database enum
    protected array $validTypes = ['Appointments', 'Payments', 'Clients', 'Caregivers', 'System'];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active'
        ]);
    }

    /** @test */
    public function can_create_notification()
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification message',
            'type' => 'System',
            'read' => false
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Test Notification'
        ]);
    }

    /** @test */
    public function notification_defaults_to_unread()
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'System'
        ]);

        $this->assertFalse((bool) $notification->read);
    }

    /** @test */
    public function can_mark_notification_as_read()
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'System',
            'read' => false
        ]);

        $notification->update(['read' => true]);

        $notification->refresh();
        $this->assertTrue((bool) $notification->read);
    }

    /** @test */
    public function can_get_unread_notifications()
    {
        Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Unread 1',
            'message' => 'Message',
            'type' => 'System',
            'read' => false
        ]);

        Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Read 1',
            'message' => 'Message',
            'type' => 'System',
            'read' => true
        ]);

        Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Unread 2',
            'message' => 'Message',
            'type' => 'System',
            'read' => false
        ]);

        $unread = Notification::where('user_id', $this->user->id)
            ->where('read', false)
            ->count();

        $this->assertEquals(2, $unread);
    }

    /** @test */
    public function can_delete_notification()
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'title' => 'To Delete',
            'message' => 'This will be deleted',
            'type' => 'System'
        ]);

        $notificationId = $notification->id;
        $notification->delete();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notificationId
        ]);
    }

    /** @test */
    public function notifications_are_user_specific()
    {
        $user2 = User::factory()->create(['user_type' => 'client']);

        Notification::create([
            'user_id' => $this->user->id,
            'title' => 'User 1 Notification',
            'message' => 'Message',
            'type' => 'System'
        ]);

        Notification::create([
            'user_id' => $user2->id,
            'title' => 'User 2 Notification',
            'message' => 'Message',
            'type' => 'System'
        ]);

        $user1Notifications = Notification::where('user_id', $this->user->id)->count();
        $user2Notifications = Notification::where('user_id', $user2->id)->count();

        $this->assertEquals(1, $user1Notifications);
        $this->assertEquals(1, $user2Notifications);
    }

    /** @test */
    public function notification_types_are_valid()
    {
        foreach ($this->validTypes as $type) {
            $notification = Notification::create([
                'user_id' => $this->user->id,
                'title' => "Test {$type}",
                'message' => 'Message',
                'type' => $type
            ]);

            $this->assertEquals($type, $notification->type);
        }
    }

    /** @test */
    public function can_mark_all_as_read()
    {
        for ($i = 0; $i < 5; $i++) {
            Notification::create([
                'user_id' => $this->user->id,
                'title' => "Notification {$i}",
                'message' => 'Message',
                'type' => 'System',
                'read' => false
            ]);
        }

        Notification::where('user_id', $this->user->id)
            ->update(['read' => true]);

        $unread = Notification::where('user_id', $this->user->id)
            ->where('read', false)
            ->count();

        $this->assertEquals(0, $unread);
    }

    /** @test */
    public function can_delete_all_notifications()
    {
        for ($i = 0; $i < 5; $i++) {
            Notification::create([
                'user_id' => $this->user->id,
                'title' => "Notification {$i}",
                'message' => 'Message',
                'type' => 'System'
            ]);
        }

        Notification::where('user_id', $this->user->id)->delete();

        $count = Notification::where('user_id', $this->user->id)->count();
        $this->assertEquals(0, $count);
    }
}
