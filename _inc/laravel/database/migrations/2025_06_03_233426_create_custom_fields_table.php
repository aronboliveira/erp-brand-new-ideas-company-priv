<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateCustomFieldsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CUSTOM_FIELDS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');
            $table->string('module');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->string('default', 254)->nullable();
            $table->string('placeholder', 254)->nullable(); // ? nullable for testing, enforced as string in boot/save, nullified for non-textual types
            $table->string('pattern', 1024)->nullable(); // ? nullable for testing, enforced as string in boot/save, nullified for non-textual types
            $table->boolean('readonly')->default(false)->nullable(); // ? nullable for testing, enforced as string in boot/save, nullified for non-textual types
            $table->boolean('required')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save
            $table->boolean('multiline')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save, only for textual types
            $table->boolean('multiple')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save, only for email, file and select types
            $table->boolean('autocapitalize')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save, nullified for url/email/password and all non-textual types
            $table->boolean('autocomplete')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save, nullified for all non-textual types
            $table->boolean('autocorrect')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save, nullified for url/email/password and all non-textual
            $table->boolean('disabled')->default(false)->nullable(); // ? nullable for testing, enforced as boolean in boot/save
            $table->string('min')->default('0')->nullable(); // * enforced at boot/save, usable by numeric or date-related fields
            $table->string('max')->default('9007199254740991')->nullable(); // * enforced at boot/save, usable by numeric or date-related fields, default changed for date-related fields at boot/save
            $table->string('step')->default('any')->nullable(); // * enforced at boot/save, usable by numeric or date-related fields
            $table->unsignedSmallInteger('minlength')->default(0)->nullable(); // ? enforced at boot/save, usable by text-based fields
            $table->unsignedInteger('maxlength')->default(1024)->nullable(); // ? enforced at boot/save, usable by text-based fields
            $table->unsignedInteger('rows')->default(2)->nullable(); // ? number of rows for textarea, enforced at boot/save, nullified for non-textarea types
            $table->unsignedInteger('cols')->default(20)->nullable(); // ? number of columns for textarea, enforced at boot/save, nullified for non-textarea types
            $table->enum('wrap', ['soft', 'hard'])->default('soft')->nullable(); // ? whether to wrap text in textarea, enforced at boot/save, nullified for non-textarea types
            $table->enum('spellcheck', ['true', 'default', 'false'])->default('false')->nullable(); // ? whether to enable spellcheck, enforced at boot/save, nullified for non-textual types
            $table->json('options')->nullable(); // ? a json object containing options for fields, expecting { text: string, value: string, selected: ?boolean, disabled: ?boolean, group: ?string }[], nullified if not a select or textual (text, number, etc., due to <datalist>)  type at boot/save
            $table->json('optgroups')->nullable(); // ? a json object containing option groups for fields, nullified if not a select or textual (text, number, etc., due to <datalist>) type at boot/save
            $table->json('aria')->nullable(); // ? a json object containing key:value aria attributes to apply to the html (must be validated through acceptable keys)
            $table->json('dataset')->nullable(); // ? a json object key:values containing data-* attributes to apply to the html
            $table->json('accepts')->nullable(); // ? for file inputs, a json array of accepted mime types or extensions, checked through the MimeType enum
            $table->json('selectors')->nullable(); // ? a list of selectors to apply to the html, separated by . (class), or # (id) 
            $table->json('size')->nullable(); // ? a json object containing width and height keys, values in px/em/%/rem/vw/vh units, enforced at boot/save
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
