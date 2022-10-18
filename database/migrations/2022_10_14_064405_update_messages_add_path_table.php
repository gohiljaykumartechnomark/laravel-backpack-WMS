<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMessagesAddPathTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('messages', 'path'))
        {
            Schema::table('messages', function (Blueprint $table)
            {
                $table->string('path')->nullable()->after('message');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('messages', 'path'))
        {
            Schema::table('messages', function (Blueprint $table)
            {
                $table->dropColumn('path');
            });
        }
    }
}
