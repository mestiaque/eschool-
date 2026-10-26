<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // books.quantity is the number of physical copies; older rows drifted from
    // the real book_copies count.
    public function up(): void
    {
        DB::statement('UPDATE books SET quantity = (SELECT COUNT(*) FROM book_copies WHERE book_copies.book_id = books.id)');
    }

    public function down(): void {}
};
