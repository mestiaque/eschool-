<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // SMS / mail credentials now live in the theme's (encrypted) configuration;
    // drop the plain-text copies the old settings page stored.
    public function up(): void
    {
        DB::table('school_settings')->whereIn('key', [
            'sms_provider', 'sms_api_key', 'sms_api_secret', 'sms_sender_id',
            'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
        ])->delete();
    }

    public function down(): void {}
};
