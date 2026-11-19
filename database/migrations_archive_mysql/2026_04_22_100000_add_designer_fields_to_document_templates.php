<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('document_templates', 'body_content')) {
                $table->longText('body_content')->nullable()->after('layout_path')
                    ->comment('Contenu HTML configuré via le designer (publipostage)');
            }
            if (!Schema::hasColumn('document_templates', 'page_config')) {
                $table->json('page_config')->nullable()->after('pdf_config')
                    ->comment('Format page, orientation, marges');
            }
            if (!Schema::hasColumn('document_templates', 'use_designer')) {
                $table->boolean('use_designer')->default(false)->after('body_content')
                    ->comment('Si true, utilise body_content au lieu du fichier Blade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (Schema::hasColumn('document_templates', 'body_content')) {
                $table->dropColumn('body_content');
            }
            if (Schema::hasColumn('document_templates', 'page_config')) {
                $table->dropColumn('page_config');
            }
            if (Schema::hasColumn('document_templates', 'use_designer')) {
                $table->dropColumn('use_designer');
            }
        });
    }
};
