<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, FormsConstants as FC, SettingsConstants as SC};
use App\Enums\{CountryName, DEICategory, Gender};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobApplicationsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JOB_APPS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('job')->index();
            $table->uuid(AC::COL_APL_ID)->nullable()->index(); // ? applicant_id, if a user in the system somehow
            $table->string('name', 254)->nullable()->index();
            $table->string('email', 254)->nullable()->index(); // ? normalized with self::normalizeEmail helper
            $table->string('phone', 32)->nullable()->index(); // ? normalized with self::normalizePhone helper
            $table->unique(['job', 'email']);
            $table->unique(['job', 'phone']);
            $table->string('source', 254)->nullable()->index(); // ? e.g., linkedin, indeed, company_website, referral, etc.
            $table->uuid('announcement')->nullable()->index(); // ? the job announcement id, if applicable, MUST be the same as in the linked 'job', if 'announcement' is not null in the referred row in DC::TABLE_JOBS
            $table->string('country', 64)->default(CountryName::Brazil->value)->nullable(); // * constrained at model with CountryName enum either through direct cases or through the keys of the enum, as available in CountryName::normalize, falling back to null if not found
            $table->string('state', 1024)->nullable(); // * if the country is the code or full name of Brazil, Argentina, Bolivia, Colombia, Chile, China, Ecuador, Guyana, Paraguay, Peru, Portugal, Suriname, United States, Uruguay or Venezuela, then use normalize/tryFrom from the following enums, respectively (if failed, nullify): BrazilState, ArgentinaProvince, BoliviaDepartment, ChileRegion, ChinaState, ColombiaDepartment, EcuadorProvince, GuyanaRegion, ParaguayDepartment, PeruDepartment, PortugalState, SurinameDistrict, UnitedStatesState, UruguayDepartment, VenezuelaState; for other countries, just store the string as is; if 'country' is null, then try to "reverse search" the state in all enums and set the country accordingly, if found; otherwise, leave both as null
            $table->string('city', 1024)->nullable();
            $table->string('address', 1024)->nullable(); // ? if country, state or city are null AND the 'zip' is null, attempt to fill them based on tokens on this string
            $table->string('zip', 32)->nullable(); // ? normalized with self::normalizeZip helper; if country, state or city are null, attempt to fill them based on zip code data from a reliable source (e.g., brasilapi.com.br for Brazil, other external APIs for other countries, local database, etc.) at model level
            $table->ipAddress('ip')->nullable();
            $table->enum(AC::COL_DEI_CTG, array_column(DEICategory::cases(), 'value'))->nullable()->index();
            $table->timestamp(AC::COL_APL_AT)->nullable()->index(); // ? should never be a future date
            $table->timestamp(AC::COL_LST_RVW_AT)->nullable()->index(); // ? should NEVER be lower than COL_APL_AT
            $table->uuid(AC::COL_RFR_ID)->nullable()->index(); // ? referrer_id, if available in the system
            $table->string(AC::COL_RFR_NM, 254)->nullable()->index(); // ? who referred the candidate, if any
            $table->string(AC::COL_RFR_EML, 254)->nullable()->index(); // ? contact of the referrer, if any
            $table->unsignedDecimal(AC::COL_EXP_SLR, 15, 2)->nullable(); // ? expected_salary
            $table->string(AC::COL_EXP_SLR_CURR, 5)->nullable()->default(SC::DEF_SITE_CURRENCY_ID)->index(); // ? expected_contract_type, e.g., clt, pj, freelancer, etc.
            $table->string(AC::COL_CUR_EMP, 254)->nullable()->index(); // ? current_employer
            $table->string(AC::COL_CUR_PST, 254)->nullable(); // ? current position
            $table->unsignedDecimal(AC::COL_CUR_SLR, 15, 2)->nullable(); // ? current_salary
            $table->string(AC::COL_CUR_SLR_CURR, 5)->nullable()->default(SC::DEF_SITE_CURRENCY_ID)->index(); // ? current_salary_currency
            $table->uuid(AC::COL_WRK_AUTH)->nullable()->index(); // ? work_authorization document id
            $table->boolean(AC::COL_WRK_AUTH_APV)->default(false)->nullable()->index(); // ? work_authorization_approved, enforced at model level as boolean
            $table->unsignedTinyInteger(AC::COL_NTC_PRD)->default(30); // * notice_period in days
            $table->dateTime(AC::COL_NXT_ITV_AT)->nullable()->index(); // ? should NEVER be lower than COL_APL_AT neither lower than COL_LST_RVW_AT and should ALWAYS be a date in the future or today
            $table->json('interviews')->nullable(); // ? list of interview IDs in DC::TABLE_ITV_SCD
            $table->text(AC::COL_ITV_NTS)->nullable();
            $table->uuid(AC::COL_ITV_NT_ID)->nullable(); // ? interview_note_id, the user who made the last note in DC::TABLE_JB_AP_NTS
            $table->json(AC::COL_ITV_SCRS)->nullable(); // ? associative array of (uuid or alias):integer scores for each interview stage
            $table->uuid(FC::COL_FM_ID)->nullable()->index(); // ? related form, like an application form
            $table->text('profile')->nullable(); //* in legacy this was saved as string. It will be assumed that this is the stringified version of the document or just the body or even the path, but a column ('profile_document' for linking with the direct doc will be added as well. If the id exists in DC::TABLE_DOCS, we can select DC::COL_FL_PT ('file_path') and 'url' to attempt to get the document and parse its context as text, with proper sanitization and try/catching, and, finally, fill this column with the text content for future reference. If the parsing returns empty value or fails, just leave the column alone.
            $table->uuid(AC::COL_PRF_DOC)->nullable(); // ? profile_document
            $table->string('portfolio')->nullable(); // ? github, behance, etc
            $table->string('website')->nullable();
            $table->text('resume')->nullable(); // * in legacy this was saved as string. It will be assumed that this is the stringified version of the document or just the body or even the path, but a column ('resume_document' for linking with the direct doc will be added as well. If the id exists in DC::TABLE_DOCS, we can select DC::COL_FL_PT ('file_path') and 'url' to attempt to get the document and parse its context as text, with proper sanitization and try/catching, and, finally, fill this column with the text content for future reference. If the parsing returns empty value or fails, just leave the column alone.
            $table->uuid(AC::COL_RSM_DOC)->nullable(); // ? resume_document
            $table->text(AC::COL_CV_LT)->nullable(); // * cover_letter; in legacy this was saved as text. It will be assumed that this is the stringified version of the document or just the body, but a column ('cover_letter_document' for linking with the direct doc will be added as well. If the id exists in DC::TABLE_DOCS, we can select DC::COL_FL_PT ('file_path') and 'url' to attempt to get the document and parse its context as text, with proper sanitization and try/catching, and, finally, fill this column with the text content for future reference. If the parsing returns empty value or fails, just leave the column alone.
            $table->uuid(AC::COL_CV_LT_DOC)->nullable(); // ? cover_letter_document
            $table->date('dob')->nullable(); // ? constrained at model level to never accept future dates neither people with age > 124 years or < 18 years
            $table->enum('gender', array_column(Gender::cases(), 'value'))->default(Gender::Other->value)->nullable();
            $table->text('experience')->nullable(); // * similar logic of document fetching and attempt to parse
            $table->uuid(AC::COL_EXP_DOC)->nullable(); // ? experience_document
            $table->text('education')->nullable(); // * similar logic of document fetching and attempt to parse
            $table->uuid(AC::COL_ED_DOC)->nullable(); // ? education_document, similarly attempt of parsing as previous document columns
            $table->unsignedTinyInteger('stage')->default(1); // * this is never explained in legacy code...
            $table->unsignedInteger('order')->default(0); // * this is never explained in legacy code...
            $table->text('skill')->nullable(); // * in legacy this was saved as string. It will be assumed that this is the stringified version of the document or just the body or even the path, but a column ('skill_document' for linking with the direct doc will be added as well. If the id exists in DC::TABLE_DOCS, we can select DC::COL_FL_PT ('file_path') and 'url' to attempt to get the document and parse its context as text, with proper sanitization and try/catching, and, finally, fill this column with the text content for future reference. If the parsing returns empty value or fails, just leave the column alone.
            $table->uuid(AC::COL_SKL_DOC)->nullable(); // ? skill_document
            $table->unsignedTinyInteger('rating')->default(0); // * this is never explained in legacy code... but it's probably 0-100, so clamped as such
            $table->text(AC::COL_RJC_RS)->nullable();
            $table->date(AC::COL_RJC_AT)->nullable(); // ? should NEVER be lower than COL_APL_AT neither lower than COL_LST_RVW_AT neither a future date. Making this not null should immediately nullify AC::COL_NXT_ITV_AT
            $table->text('feedback')->nullable(); // * similar logic of document fetching and attempt to parse
            $table->uuid(AC::COL_FDB_DOC)->nullable(); // ? feedback_document
            $table->unsignedTinyInteger(AC::COL_IS_ARC)->default(0); // * pseudoboolean, 0 || % 2 == 0 ==> false
            $table->text(AC::COL_CT_QT)->nullable(); // * supposed to be a json object with question-answer pairs
            $table->uuid(AC::COL_CT_QT_ID)->nullable();
            $table->boolean(AC::COL_TRMS_ACPT)->default(false)->nullable(); // ? terms_accepted
            $table->json('certifications')->nullable(); // ? array of ids | urls | file paths to be queried agains the DC::TABLE_DOCS (OR in the case of urls, checking the validity of the url and testing with http HEAD if it returns 200 and a valid format for Document, using the isDocument from MimeType enum, OR approving the url domain (ex.: google domains, udemy domains, amazon/aws domains, etc.) before accepting it as valid) (OR, in the case of file_paths, checking if the file exists and is readable within the own server, with proper sanitization and path traversal prevention)
            $table->json('awards')->nullable(); // ? similar logic to certifications
            $table->json('publications')->nullable(); // ? similar logic to certifications, but also accepting domains from researchgate, academia.edu, google scholar, etc.
            $table->json('projects')->nullable(); // ? similar logic to certifications, but also accepting domains from github, gitlab, bitbucket, etc.
            $table->json('languages')->nullable(); // ? Associative arrays to detail the language as the hiring company prefers to implement the FormData. The main key (id, language_id, key, etc.) can be an uuid to reference DC::TABLE_LANGS or just the language name (ex.: English, Portuguese, Spanish, Mandarin, etc.), and the value can be the proficiency level (ex.: basic, intermediate, advanced, fluent, native, etc.) or a numeric scale (ex.: 1-10, 1-5 stars, A1-C2, etc.), but it's flexible to the company's needs.
            $table->json('references')->nullable();
            $table->json('diversity')->nullable(); // ? sexuality, ethnicity, veteran_status, etc.
            $table->json('disabilities')->nullable();
            $table->json(AC::COL_SC_MD)->nullable(); // ? social_media links as json object with key-value pairs, filtered as such and sanitized
            $table->json('questions')->nullable(); // ? array of question IDs in DC::TABLE_CUSTOM_QUESTIONS or descriptive objects/arrays with Q&A, flexible due to how companies may want to implement it
            $table->json('tests')->nullable(); // ? array with key (test slug, url or id as a Document row) and the result as a decimal value
            $table->json('notes')->nullable(); // ? array of ids to be queried in DC::TABLE_JB_AP_NTS OR following similar search logic as certifications, awards, etc. MUST include AC::COL_ITV_NT_ID as one of the notes, if that column is not null
            $table->json('attachments')->nullable(); // ? array of 'name', 'id' or 'file_path' for DC::TABLE_DOCS rows
            $table->foreign('job')
                ->references('id')
                ->on(DC::TABLE_JOBS)
                ->cascadeOnDelete();
            foreach (
                [
                    AC::COL_APL_ID => DC::TABLE_USERS,
                    'announcement' => DC::TABLE_ANC,
                    AC::COL_PRF_DOC => DC::TABLE_DOCS,
                    AC::COL_RSM_DOC => DC::TABLE_DOCS,
                    AC::COL_EXP_DOC => DC::TABLE_DOCS,
                    AC::COL_ED_DOC => DC::TABLE_DOCS,
                    AC::COL_SKL_DOC => DC::TABLE_DOCS,
                    AC::COL_FDB_DOC => DC::TABLE_DOCS,
                    AC::COL_CV_LT_DOC => DC::TABLE_DOCS,
                    AC::COL_WRK_AUTH => DC::TABLE_DOCS,
                    AC::COL_CT_QT_ID => DC::TABLE_CUSTOM_QUESTIONS,
                    AC::COL_RFR_ID => DC::TABLE_USERS,
                    FC::COL_FM_ID => DC::TABLE_FORM_BUILD,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    AC::COL_APL_ID,
                    'job',
                    'announcement',
                    AC::COL_PRF_DOC,
                    AC::COL_RSM_DOC,
                    AC::COL_ED_DOC,
                    AC::COL_EXP_DOC,
                    AC::COL_SKL_DOC,
                    AC::COL_FDB_DOC,
                    AC::COL_CV_LT_DOC,
                    AC::COL_WRK_AUTH,
                    AC::COL_CT_QT_ID,
                    AC::COL_RFR_ID,
                    FC::COL_FM_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
